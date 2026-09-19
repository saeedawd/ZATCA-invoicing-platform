<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'صيغة البريد غير صحيحة.',
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-slate-600 leading-6">
        نسيت كلمة المرور؟ لا مشكلة. اكتب بريدك وسنرسل لك رابطاً لتعيين كلمة مرور جديدة، ثم تقدر تغيّرها لاحقاً من الملف الشخصي متى ما حابب.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink">
        <div>
            <x-input-label for="email" value="البريد الإلكتروني" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-brand-600 bg-brand-50 px-4 py-2.5 text-sm font-bold text-brand-800 shadow-sm transition hover:border-brand-700 hover:bg-brand-100 hover:text-brand-900 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2"
            >
                العودة لتسجيل الدخول
            </a>

            <x-primary-button class="w-full justify-center sm:w-auto">
                إرسال رابط إعادة التعيين
            </x-primary-button>
        </div>
    </form>
</div>
