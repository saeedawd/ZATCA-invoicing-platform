<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-5">
    <div class="flex items-start gap-3">
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-700">
            <x-icon name="delete" class="h-5 w-5" />
        </span>
        <div>
            <h2 class="text-base font-bold text-rose-900">حذف الحساب</h2>
            <p class="mt-1 text-sm text-rose-800/80">
                حذف الحساب نهائي ولا يمكن التراجع عنه. احفظ نسخة من بياناتك المهمة قبل المتابعة.
            </p>
        </div>
    </div>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        حذف الحساب نهائياً
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">
            <h2 class="text-lg font-bold text-slate-900">تأكيد حذف الحساب</h2>

            <p class="mt-2 text-sm text-slate-600">
                سيتم حذف حسابك بشكل دائم. أدخل كلمة المرور للتأكيد.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="كلمة المرور" />
                <x-text-input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    placeholder="كلمة المرور"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-2">
                <x-secondary-button type="button" x-on:click="$dispatch('close')">
                    إلغاء
                </x-secondary-button>
                <x-danger-button>
                    تأكيد الحذف
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
