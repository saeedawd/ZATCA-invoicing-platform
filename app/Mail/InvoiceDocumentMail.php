<?php

namespace App\Mail;

use App\Domains\Invoicing\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $pdfAbsolutePath,
        public string $organizationName,
    ) {}

    public function envelope(): Envelope
    {
        $from = config('mail.documents_from');
        $kind = $this->invoice->direction === 'purchase' ? 'فاتورة مشتريات' : 'فاتورة';

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                $from['address'],
                $from['name']
            ),
            subject: $kind.' '.$this->invoice->invoice_number.' — '.$this->organizationName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.invoice-document',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfAbsolutePath)
                ->as(($this->invoice->invoice_number ?: 'invoice').'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
