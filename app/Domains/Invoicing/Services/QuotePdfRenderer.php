<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use App\Domains\Invoicing\Models\Quote;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuotePdfRenderer
{
    public function __construct(
        protected InvoicePdfRenderer $invoicePdfRenderer,
    ) {}

    public function render(Quote $quote): string
    {
        $quote->loadMissing(['party', 'lines', 'tenant.organization']);

        $binary = $this->makeBinary($quote);
        $path = sprintf('tenants/%d/quotes/%s.pdf', $quote->tenant_id, $quote->uuid);
        Storage::disk('local')->put($path, $binary);
        $quote->update(['pdf_path' => $path]);

        return $path;
    }

    public function previewDraftBinary(Quote $quoteStub): string
    {
        return $this->makeBinary($quoteStub);
    }

    protected function makeBinary(Quote $quote): string
    {
        $quote->loadMissing(['party', 'lines', 'tenant.organization']);

        $invoice = $this->asInvoice($quote);
        $organization = $quote->tenant?->organization
            ?? auth()->user()?->tenant?->organization;

        return $this->invoicePdfRenderer->renderBinary($invoice, $organization, [
            'document_title_ar' => 'عرض سعر',
            'document_title_en' => 'Quotation',
            'document_number_label' => 'رقم عرض السعر',
            'document_extra_label' => 'صالح حتى',
            'document_extra_value' => optional($quote->valid_until)->format('Y-m-d') ?: '—',
            'show_qr' => false,
            'hide_payment_summary' => true,
            'invoice_footer' => 'عرض سعر صادر عبر منصة فواتير زاتكا — غير ملزم ضريبياً حتى تحويله إلى فاتورة.',
        ]);
    }

    protected function asInvoice(Quote $quote): Invoice
    {
        $invoice = new Invoice([
            'tenant_id' => $quote->tenant_id,
            'uuid' => $quote->uuid ?: (string) Str::uuid(),
            'direction' => 'sales',
            'invoice_type' => 'simplified',
            'document_type_code' => '388',
            'invoice_number' => $quote->quote_number ?: 'معاينة',
            'issue_date' => $quote->issue_date,
            'issue_time' => null,
            'currency' => $quote->currency ?: 'SAR',
            'note' => $quote->note,
            'line_extension_amount' => $quote->line_extension_amount,
            'discount_amount' => $quote->discount_amount,
            'taxable_amount' => $quote->taxable_amount,
            'tax_amount' => $quote->tax_amount,
            'total_amount' => $quote->total_amount,
            'prepaid_amount' => 0,
            'payable_amount' => $quote->total_amount,
            'amount_paid' => 0,
            'balance_due' => $quote->total_amount,
            'payment_status' => 'unpaid',
            'qr_tlv_base64' => null,
            'status' => $quote->status === 'draft' ? 'draft' : 'issued',
        ]);

        $invoice->setRelation('party', $quote->party);
        $invoice->setRelation('lines', $quote->lines->map(function ($line) {
            return new InvoiceLine([
                'line_no' => $line->line_no,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price' => $line->unit_price,
                'discount' => $line->discount,
                'tax_rate' => $line->tax_rate,
                'tax_category' => $line->tax_category,
                'unit_code' => $line->unit_code,
                'line_net' => $line->line_net,
                'line_tax' => $line->line_tax,
                'line_total' => $line->line_total,
            ]);
        }));

        return $invoice;
    }
}
