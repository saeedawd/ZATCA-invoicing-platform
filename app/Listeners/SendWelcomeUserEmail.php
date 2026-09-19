<?php

namespace App\Listeners;

use App\Mail\WelcomeUserMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendWelcomeUserEmail
{
    public function handle(Registered $event): void
    {
        $user = $event->user->loadMissing('tenant.organization');

        try {
            $organizationName = $user->tenant?->organization?->legal_name_ar
                ?: ($user->tenant?->name_ar ?? config('app.name'));

            Mail::mailer('documents')
                ->to($user->email)
                ->send(new WelcomeUserMail($user, (string) $organizationName));
        } catch (Throwable $e) {
            Log::warning('فشل إرسال بريد الترحيب', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
