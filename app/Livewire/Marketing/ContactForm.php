<?php

namespace App\Livewire\Marketing;

use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $message = '';

    public string $website = '';

    public bool $sent = false;

    public function send(): void
    {
        if (filled($this->website)) {
            $this->sent = true;
            $this->reset(['name', 'email', 'message', 'website']);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.required' => 'يرجى إدخال الاسم.',
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
            'message.required' => 'يرجى كتابة رسالتك.',
            'message.min' => 'الرسالة قصيرة جدًا.',
        ]);

        $to = config('seo.contact_to') ?: config('mail.from.address');

        Mail::to($to)->send(new ContactMessageMail(
            name: $validated['name'],
            email: $validated['email'],
            body: $validated['message'],
        ));

        $this->reset(['name', 'email', 'message', 'website']);
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.marketing.contact-form');
    }
}
