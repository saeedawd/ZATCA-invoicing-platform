<div class="mx-auto max-w-5xl space-y-6">
    <x-page-header
        title="الملف الشخصي"
        description="إدارة بيانات حسابك وكلمة المرور وإعدادات الأمان."
    >
        <x-slot name="actions">
            <a href="{{ route('organization.edit') }}" wire:navigate>
                <x-secondary-button type="button">
                    <x-icon name="organization" class="h-4 w-4" />
                    بيانات المنشأة
                </x-secondary-button>
            </a>
        </x-slot>
    </x-page-header>

    @if (session('status'))
        <div class="alert-success">{{ session('status') }}</div>
    @endif

    <x-ui-card :padding="false">
        <div class="flex flex-col gap-5 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:px-6">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-lg font-bold text-brand-800">
                {{ $initials }}
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="truncate text-lg font-bold text-slate-900">{{ $user->name }}</h2>
                <p class="mt-0.5 truncate text-sm text-slate-500">{{ $user->email }}</p>
                <p class="mt-2 text-sm text-slate-600">
                    <span class="font-medium text-slate-800">{{ \App\Support\Labels::userRole($user->role) }}</span>
                    @if ($tenant?->name_ar)
                        <span class="text-slate-300">·</span>
                        <span>{{ $tenant->name_ar }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3">
            <div class="border-b border-slate-100 px-5 py-4 sm:border-b-0 sm:border-l sm:border-slate-100 sm:px-6">
                <div class="text-xs font-medium text-slate-500">الجوال</div>
                <div class="mt-1 text-sm font-semibold text-slate-800" dir="ltr">{{ $user->phone ?: 'غير محدد' }}</div>
            </div>
            <div class="border-b border-slate-100 px-5 py-4 sm:border-b-0 sm:border-l sm:border-slate-100 sm:px-6">
                <div class="text-xs font-medium text-slate-500">حالة الحساب</div>
                <div @class([
                    'mt-1 text-sm font-semibold',
                    'text-emerald-700' => $user->status === 'active',
                    'text-slate-800' => $user->status !== 'active',
                ])>
                    {{ $user->status === 'active' ? 'نشط' : ($user->status ?: '—') }}
                </div>
            </div>
            <div class="px-5 py-4 sm:px-6">
                <div class="text-xs font-medium text-slate-500">آخر دخول</div>
                <div class="mt-1 text-sm font-semibold text-slate-800" dir="ltr">
                    {{ $user->last_login_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') ?: '—' }}
                </div>
            </div>
        </div>
    </x-ui-card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-ui-card>
            <livewire:profile.update-profile-information-form />
        </x-ui-card>

        <x-ui-card>
            <livewire:profile.update-password-form />
        </x-ui-card>
    </div>

    <x-ui-card class="!border-rose-200/80 !bg-rose-50/30">
        <livewire:profile.delete-user-form />
    </x-ui-card>
</div>
