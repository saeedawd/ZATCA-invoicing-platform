<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
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
            subject: 'مرحباً بك في فواتير زاتكا',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.welcome-user',
        );
    }
}
