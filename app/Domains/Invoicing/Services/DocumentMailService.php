<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\Quote;
use App\Mail\InvoiceDocumentMail;
use App\Mail\QuoteDocumentMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DocumentMailService
{
    public function __construct(
        protected InvoicePdfRenderer $invoicePdfRenderer,
        protected QuotePdfRenderer $quotePdfRenderer,
    ) {}

    public function sendInvoice(Invoice $invoice, ?string $toEmail = null): void
    {
        $invoice->loadMissing(['party', 'tenant.organization']);

        $email = $toEmail ?: $invoice->party?->email;
        if (! filled($email)) {
            throw new RuntimeException('لا يوجد بريد إلكتروني للطرف لإرسال المستند.');
        }

        $relativePath = $this->invoicePdfRenderer->render($invoice);
        $absolutePath = Storage::disk('local')->path($relativePath);

        $organizationName = $invoice->tenant?->organization?->legal_name_ar
            ?: config('app.name');

        Mail::mailer('documents')
            ->to($email)
            ->send(new InvoiceDocumentMail($invoice, $absolutePath, $organizationName));
    }

    public function sendInvoiceIfPossible(Invoice $invoice): bool
    {
        try {
            if (! filled($invoice->party?->email) && ! $invoice->relationLoaded('party')) {
                $invoice->loadMissing('party');
            }

            if (! filled($invoice->party?->email)) {
                return false;
            }

            $this->sendInvoice($invoice);

            return true;
        } catch (Throwable $e) {
            Log::warning('فشل إرسال فاتورة بالبريد', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendQuote(Quote $quote, ?string $toEmail = null): void
    {
        $quote->loadMissing(['party', 'tenant.organization']);

        $email = $toEmail ?: $quote->party?->email;
        if (! filled($email)) {
            throw new RuntimeException('لا يوجد بريد إلكتروني للعميل لإرسال عرض السعر.');
        }

        $relativePath = $this->quotePdfRenderer->render($quote);
        $absolutePath = Storage::disk('local')->path($relativePath);

        $organizationName = $quote->tenant?->organization?->legal_name_ar
            ?: config('app.name');

        Mail::mailer('documents')
            ->to($email)
            ->send(new QuoteDocumentMail($quote, $absolutePath, $organizationName));
    }

    public function sendQuoteIfPossible(Quote $quote): bool
    {
        try {
            if (! filled($quote->party?->email) && ! $quote->relationLoaded('party')) {
                $quote->loadMissing('party');
            }

            if (! filled($quote->party?->email)) {
                return false;
            }

            $this->sendQuote($quote);

            return true;
        } catch (Throwable $e) {
            Log::warning('فشل إرسال عرض سعر بالبريد', [
                'quote_id' => $quote->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
