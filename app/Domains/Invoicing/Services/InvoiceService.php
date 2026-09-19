<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Dashboard\Services\DashboardStatsService;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use App\Domains\Invoicing\Models\InvoiceReference;
use App\Domains\Invoicing\Models\InvoiceTaxTotal;
use App\Domains\Zatca\Jobs\SubmitInvoiceToZatcaJob;
use App\Domains\Zatca\Services\ZatcaDocumentService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InvoiceService
{
    public function __construct(
        protected InvoiceCalculator $calculator,
        protected InvoiceNumberGenerator $numberGenerator,
        protected InvoicePdfRenderer $pdfRenderer,
        protected ZatcaDocumentService $zatcaDocumentService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function createDraft(User $user, array $data, array $lines): Invoice
    {
        if (empty($lines)) {
            throw new InvalidArgumentException('يجب إضافة بند واحد على الأقل.');
        }

        $computed = $this->calculator->calculate($lines, (float) ($data['discount_amount'] ?? 0));

        return DB::transaction(function () use ($user, $data, $computed) {
            $invoice = Invoice::create([
                'tenant_id' => $user->tenant_id,
                'direction' => $data['direction'] ?? 'sales',
                'invoice_type' => $data['invoice_type'] ?? 'simplified',
                'document_type_code' => $data['document_type_code'] ?? '388',
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'issue_time' => $data['issue_time'] ?? now()->format('H:i:s'),
                'supply_date' => $data['supply_date'] ?? ($data['issue_date'] ?? now()->toDateString()),
                'party_id' => $data['party_id'] ?? null,
                'payment_means_code' => $data['payment_means_code'] ?? '10',
                'note' => $data['note'] ?? null,
                'created_by' => $user->id,
                'status' => 'draft',
                'zatca_status' => 'draft',
                'amount_paid' => 0,
                'balance_due' => round((float) $computed['totals']['total_amount'], 2),
                'payment_status' => 'unpaid',
                ...$computed['totals'],
            ]);

            $this->syncLinesAndTaxes($invoice, $computed);

            if (! empty($data['referenced_invoice_id'])) {
                InvoiceReference::create([
                    'tenant_id' => $invoice->tenant_id,
                    'invoice_id' => $invoice->id,
                    'referenced_invoice_id' => $data['referenced_invoice_id'],
                    'reason' => $data['reference_reason'] ?? null,
                ]);
            }

            $this->audit($user, 'invoice.created', $invoice, null, $invoice->toArray());
            app(DashboardStatsService::class)->forget((int) $invoice->tenant_id);

            return $invoice->fresh(['lines', 'taxTotals', 'party']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function updateDraft(Invoice $invoice, User $user, array $data, array $lines): Invoice
    {
        if ($invoice->isLocked()) {
            throw new RuntimeException('لا يمكن تعديل فاتورة صادرة.');
        }

        $computed = $this->calculator->calculate($lines, (float) ($data['discount_amount'] ?? 0));

        return DB::transaction(function () use ($invoice, $user, $data, $computed) {
            $old = $invoice->toArray();

            $invoice->update([
                'invoice_type' => $data['invoice_type'] ?? $invoice->invoice_type,
                'document_type_code' => $data['document_type_code'] ?? $invoice->document_type_code,
                'issue_date' => $data['issue_date'] ?? $invoice->issue_date,
                'issue_time' => $data['issue_time'] ?? $invoice->issue_time,
                'supply_date' => $data['supply_date'] ?? $invoice->supply_date,
                'party_id' => $data['party_id'] ?? $invoice->party_id,
                'payment_means_code' => $data['payment_means_code'] ?? $invoice->payment_means_code,
                'note' => $data['note'] ?? $invoice->note,
                'amount_paid' => 0,
                'balance_due' => round((float) $computed['totals']['total_amount'], 2),
                'payment_status' => 'unpaid',
                ...$computed['totals'],
            ]);

            $invoice->lines()->delete();
            $invoice->taxTotals()->delete();
            $this->syncLinesAndTaxes($invoice, $computed);

            $this->audit($user, 'invoice.updated', $invoice, $old, $invoice->fresh()->toArray());
            app(DashboardStatsService::class)->forget((int) $invoice->tenant_id);

            return $invoice->fresh(['lines', 'taxTotals', 'party']);
        });
    }

    public function issue(Invoice $invoice, User $user, bool $submitToZatca = true): Invoice
    {
        if ($invoice->isLocked()) {
            throw new RuntimeException('الفاتورة صادرة مسبقاً.');
        }

        if ($invoice->lines()->count() === 0) {
            throw new RuntimeException('لا يمكن إصدار فاتورة بدون بنود.');
        }

        if ($invoice->direction === 'sales' && $invoice->invoice_type === 'standard' && ! $invoice->party_id) {
            throw new RuntimeException('الفاتورة الضريبية تتطلب عميل.');
        }

        $issued = DB::transaction(function () use ($invoice, $user, $submitToZatca) {
            $key = match (true) {
                $invoice->document_type_code === '381' => $invoice->direction.'_credit',
                $invoice->document_type_code === '383' => $invoice->direction.'_debit',
                default => $invoice->direction.'_invoice',
            };

            $prefix = match ($invoice->direction) {
                'purchase' => 'PINV-',
                default => 'INV-',
            };

            if ($invoice->document_type_code === '381') {
                $prefix = $invoice->direction === 'purchase' ? 'PCN-' : 'CN-';
            } elseif ($invoice->document_type_code === '383') {
                $prefix = $invoice->direction === 'purchase' ? 'PDN-' : 'DN-';
            }

            $invoice->invoice_number = $this->numberGenerator->next($key, $prefix);
            $invoice->status = 'issued';
            $invoice->posted_at = now();
            $invoice->amount_paid = 0;
            $invoice->balance_due = round((float) $invoice->total_amount, 2);
            $invoice->payment_status = 'unpaid';
            $invoice->save();

            if ($invoice->isSales()) {
                $this->zatcaDocumentService->prepareIssuedInvoice($invoice);
            }

            $this->pdfRenderer->render($invoice->fresh(['lines', 'party', 'tenant.organization']));

            $this->audit($user, 'invoice.issued', $invoice, null, [
                'invoice_number' => $invoice->invoice_number,
                'zatca_status' => $invoice->zatca_status,
            ]);

            if ($submitToZatca && $invoice->isSales()) {
                SubmitInvoiceToZatcaJob::dispatch($invoice->id)->onQueue('zatca');
            }

            app(DashboardStatsService::class)->forget((int) $invoice->tenant_id);

            return $invoice->fresh(['lines', 'taxTotals', 'party', 'submissions']);
        });

        app(DocumentMailService::class)->sendInvoiceIfPossible($issued);

        return $issued;
    }

    /**
     * @param  array{lines: array<int, array<string, mixed>>, tax_totals: array<int, array<string, mixed>>}  $computed
     */
    protected function syncLinesAndTaxes(Invoice $invoice, array $computed): void
    {
        foreach ($computed['lines'] as $line) {
            InvoiceLine::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'line_no' => $line['line_no'],
                'product_id' => $line['product_id'] ?? null,
                'description' => $line['description'] ?? 'بند',
                'quantity' => $line['quantity'],
                'unit_code' => $line['unit_code'] ?? 'PCE',
                'unit_price' => $line['unit_price'],
                'discount' => $line['discount'],
                'tax_category' => $line['tax_category'],
                'tax_rate' => $line['tax_rate'],
                'line_net' => $line['line_net'],
                'line_tax' => $line['line_tax'],
                'line_total' => $line['line_total'],
            ]);
        }

        foreach ($computed['tax_totals'] as $taxTotal) {
            InvoiceTaxTotal::create([
                'tenant_id' => $invoice->tenant_id,
                'invoice_id' => $invoice->id,
                'tax_category' => $taxTotal['tax_category'],
                'tax_rate' => $taxTotal['tax_rate'],
                'taxable_amount' => $taxTotal['taxable_amount'],
                'tax_amount' => $taxTotal['tax_amount'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    protected function audit(User $user, string $action, Invoice $invoice, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => $action,
            'auditable_type' => Invoice::class,
            'auditable_id' => $invoice->id,
            'old_values' => $old,
            'new_values' => $new,
            'ip' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
