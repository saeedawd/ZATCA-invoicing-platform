<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = (string) ($user->phone ?? '');
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        session()->flash('status', 'تم حفظ بيانات الحساب.');

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="space-y-5">
    <div class="flex items-start gap-3">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
            <x-icon name="profile" class="h-5 w-5" />
        </span>
        <div>
            <h2 class="text-base font-bold text-slate-900">بيانات الحساب</h2>
            <p class="mt-1 text-sm text-slate-500">حدّث الاسم والبريد والجوال الخاص بحسابك.</p>
        </div>
    </div>

    <form wire:submit="updateProfileInformation" class="space-y-4">
        <div>
            <x-input-label for="name" value="الاسم" />
            <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1.5 block w-full" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="البريد الإلكتروني" />
            <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1.5 block w-full" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                    <p>البريد الإلكتروني غير مفعّل.</p>
                    <button wire:click.prevent="sendVerification" class="mt-1 font-semibold text-brand-700 underline underline-offset-2">
                        إعادة إرسال رابط التفعيل
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-emerald-700">
                            تم إرسال رابط تفعيل جديد إلى بريدك.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone" value="الجوال" />
            <x-text-input wire:model="phone" id="phone" name="phone" type="tel" class="mt-1.5 block w-full" dir="ltr" autocomplete="tel" placeholder="05xxxxxxxx" />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-3 pt-1">
            <x-primary-button wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="updateProfileInformation">حفظ التغييرات</span>
                <span wire:loading wire:target="updateProfileInformation">جاري الحفظ...</span>
            </x-primary-button>
            <x-action-message class="text-sm font-medium text-emerald-700" on="profile-updated">
                تم الحفظ
            </x-action-message>
        </div>
    </form>
</section>
