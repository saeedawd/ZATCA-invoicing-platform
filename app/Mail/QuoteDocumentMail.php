<?php

namespace App\Mail;

use App\Domains\Invoicing\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Quote $quote,
        public string $pdfAbsolutePath,
        public string $organizationName,
    ) {}

    public function envelope(): Envelope
    {
        $from = config('mail.documents_from');

        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                $from['address'],
                $from['name']
            ),
            subject: 'عرض سعر '.$this->quote->quote_number.' — '.$this->organizationName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.quote-document',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfAbsolutePath)
                ->as(($this->quote->quote_number ?: 'quote').'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
