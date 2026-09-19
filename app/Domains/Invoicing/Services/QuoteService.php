<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Dashboard\Services\DashboardStatsService;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\Quote;
use App\Domains\Invoicing\Models\QuoteLine;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class QuoteService
{
    public function __construct(
        protected InvoiceCalculator $calculator,
        protected InvoiceNumberGenerator $numberGenerator,
        protected InvoiceService $invoiceService,
        protected QuotePdfRenderer $pdfRenderer,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function createDraft(User $user, array $data, array $lines): Quote
    {
        if (empty($lines)) {
            throw new InvalidArgumentException('يجب إضافة بند واحد على الأقل.');
        }

        $computed = $this->calculator->calculate($lines, (float) ($data['discount_amount'] ?? 0));

        return DB::transaction(function () use ($user, $data, $computed) {
            $quote = Quote::create([
                'tenant_id' => $user->tenant_id,
                'party_id' => $data['party_id'] ?? null,
                'issue_date' => $data['issue_date'] ?? now()->toDateString(),
                'valid_until' => $data['valid_until'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $user->id,
                'status' => 'draft',
                'line_extension_amount' => $computed['totals']['line_extension_amount'],
                'discount_amount' => $computed['totals']['discount_amount'],
                'taxable_amount' => $computed['totals']['taxable_amount'],
                'tax_amount' => $computed['totals']['tax_amount'],
                'total_amount' => $computed['totals']['total_amount'],
            ]);

            $this->syncLines($quote, $computed['lines']);
            $this->audit($user, 'quote.created', $quote, null, $quote->toArray());

            return $quote->fresh(['lines', 'party']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function updateDraft(Quote $quote, User $user, array $data, array $lines): Quote
    {
        if (! $quote->isEditable()) {
            throw new RuntimeException('لا يمكن تعديل عرض سعر بعد إرساله.');
        }

        if (empty($lines)) {
            throw new InvalidArgumentException('يجب إضافة بند واحد على الأقل.');
        }

        $computed = $this->calculator->calculate($lines, (float) ($data['discount_amount'] ?? 0));

        return DB::transaction(function () use ($quote, $user, $data, $computed) {
            $old = $quote->toArray();

            $quote->update([
                'party_id' => $data['party_id'] ?? $quote->party_id,
                'issue_date' => $data['issue_date'] ?? $quote->issue_date,
                'valid_until' => $data['valid_until'] ?? $quote->valid_until,
                'note' => $data['note'] ?? $quote->note,
                'line_extension_amount' => $computed['totals']['line_extension_amount'],
                'discount_amount' => $computed['totals']['discount_amount'],
                'taxable_amount' => $computed['totals']['taxable_amount'],
                'tax_amount' => $computed['totals']['tax_amount'],
                'total_amount' => $computed['totals']['total_amount'],
            ]);

            $quote->lines()->delete();
            $this->syncLines($quote, $computed['lines']);
            $this->audit($user, 'quote.updated', $quote, $old, $quote->fresh()->toArray());

            return $quote->fresh(['lines', 'party']);
        });
    }

    public function send(Quote $quote, User $user): Quote
    {
        if (! $quote->canSend()) {
            throw new RuntimeException('لا يمكن إرسال هذا العرض.');
        }

        if ($quote->lines()->count() === 0) {
            throw new RuntimeException('لا يمكن إرسال عرض سعر بدون بنود.');
        }

        $sent = DB::transaction(function () use ($quote, $user) {
            $quote->quote_number = $this->numberGenerator->next('sales_quote', 'QT-');
            $quote->status = 'sent';
            $quote->sent_at = now();
            $quote->save();

            $this->pdfRenderer->render($quote->fresh(['lines', 'party', 'tenant.organization']));
            $this->audit($user, 'quote.sent', $quote, null, [
                'quote_number' => $quote->quote_number,
            ]);

            return $quote->fresh(['lines', 'party']);
        });

        app(DocumentMailService::class)->sendQuoteIfPossible($sent);

        return $sent;
    }

    public function accept(Quote $quote, User $user): Quote
    {
        if (! $quote->canAccept()) {
            throw new RuntimeException('لا يمكن قبول هذا العرض.');
        }

        $quote->update(['status' => 'accepted']);
        $this->audit($user, 'quote.accepted', $quote, null, ['status' => 'accepted']);

        return $quote->refresh();
    }

    public function reject(Quote $quote, User $user): Quote
    {
        if (! $quote->canReject()) {
            throw new RuntimeException('لا يمكن رفض هذا العرض.');
        }

        $quote->update(['status' => 'rejected']);
        $this->audit($user, 'quote.rejected', $quote, null, ['status' => 'rejected']);

        return $quote->refresh();
    }

    public function convertToInvoice(Quote $quote, User $user): Invoice
    {
        if (! $quote->canConvert()) {
            throw new RuntimeException('لا يمكن تحويل هذا العرض إلى فاتورة.');
        }

        return DB::transaction(function () use ($quote, $user) {
            $quote->loadMissing('lines');

            $lines = $quote->lines->map(fn (QuoteLine $line) => [
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => (string) $line->quantity,
                'unit_price' => (string) $line->unit_price,
                'discount' => (string) $line->discount,
                'tax_category' => $line->tax_category,
                'tax_rate' => (string) $line->tax_rate,
                'unit_code' => $line->unit_code,
            ])->all();

            $invoice = $this->invoiceService->createDraft($user, [
                'direction' => 'sales',
                'invoice_type' => 'simplified',
                'document_type_code' => '388',
                'party_id' => $quote->party_id,
                'issue_date' => now()->toDateString(),
                'note' => trim(($quote->note ? $quote->note."\n" : '').'محوّل من عرض السعر '.$quote->quote_number),
                'discount_amount' => $quote->discount_amount,
            ], $lines);

            $invoice->forceFill(['quote_id' => $quote->id])->save();

            $quote->update([
                'status' => 'converted',
                'converted_invoice_id' => $invoice->id,
            ]);

            $this->audit($user, 'quote.converted', $quote, null, [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);

            app(DashboardStatsService::class)->forget((int) $quote->tenant_id);

            return $invoice->fresh(['lines', 'party']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $lines
     */
    protected function syncLines(Quote $quote, array $lines): void
    {
        foreach ($lines as $line) {
            QuoteLine::create([
                'tenant_id' => $quote->tenant_id,
                'quote_id' => $quote->id,
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
    }

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    protected function audit(User $user, string $action, Quote $quote, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => $action,
            'auditable_type' => Quote::class,
            'auditable_id' => $quote->id,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
