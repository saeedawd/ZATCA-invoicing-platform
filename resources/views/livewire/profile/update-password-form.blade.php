<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        session()->flash('status', 'تم تحديث كلمة المرور.');

        $this->dispatch('password-updated');
    }
}; ?>

<section class="space-y-5">
    <div class="flex items-start gap-3">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
            <x-icon name="lock" class="h-5 w-5" />
        </span>
        <div>
            <h2 class="text-base font-bold text-slate-900">كلمة المرور</h2>
            <p class="mt-1 text-sm text-slate-500">استخدم كلمة مرور قوية ولا تشاركها مع أحد.</p>
        </div>
    </div>

    <form wire:submit="updatePassword" class="space-y-4">
        <div>
            <x-input-label for="update_password_current_password" value="كلمة المرور الحالية" />
            <x-text-input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="mt-1.5 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="كلمة المرور الجديدة" />
            <x-text-input wire:model="password" id="update_password_password" name="password" type="password" class="mt-1.5 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="تأكيد كلمة المرور" />
            <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1.5 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-3 pt-1">
            <x-primary-button wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updatePassword">تحديث كلمة المرور</span>
                <span wire:loading wire:target="updatePassword">جاري التحديث...</span>
            </x-primary-button>
            <x-action-message class="text-sm font-medium text-emerald-700" on="password-updated">
                تم التحديث
            </x-action-message>
        </div>
    </form>
</section>
