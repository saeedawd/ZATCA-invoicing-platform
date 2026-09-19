<?php

use App\Livewire\Actions\Logout;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component
{
    #[On('profile-updated')]
    public function refreshUserSummary(): void
    {
        // إعادة رسم اسم وبريد المستخدم في الشريط الجانبي
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="relative z-40 lg:sticky lg:top-0 lg:flex lg:h-screen lg:shrink-0">
    <aside
        id="app-sidebar"
        dir="rtl"
        class="app-sidebar fixed inset-y-0 right-0 z-40 flex h-dvh w-[min(16.5rem,85vw)] max-lg:translate-x-full flex-col border-l border-slate-200/80 bg-white shadow-xl transition-transform duration-200 ease-out lg:static lg:z-auto lg:h-full lg:w-60 lg:translate-x-0 lg:shadow-none"
    >
        <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-slate-100 px-3">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex min-w-0 items-center gap-2 font-bold text-brand-800" @click="$dispatch('close-sidebar')">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white ring-1 ring-slate-200/80">
                    <img src="{{ asset('logo.png') }}" alt="فواتير زاتكا" class="h-full w-full object-contain p-0.5">
                </span>
                <span class="truncate text-lg leading-tight">فواتير زاتكا</span>
            </a>
            <button
                type="button"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-50 lg:hidden"
                @click="$dispatch('close-sidebar')"
                aria-label="إغلاق القائمة"
            >
                <x-icon name="close" class="h-5 w-5" />
            </button>
        </div>

        <nav class="flex-1 space-y-4 overflow-y-auto overscroll-contain px-2.5 py-3">
            <div>
                <div class="mb-1.5 px-2.5 text-[10px] font-semibold tracking-wide text-slate-400">التشغيل</div>
                <div class="space-y-0.5">
                    <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="dashboard" wire:navigate.hover>لوحة التحكم</x-sidebar-link>
                    <x-sidebar-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')" icon="sales" wire:navigate.hover>المبيعات</x-sidebar-link>
                    <x-sidebar-link :href="route('quotes.index')" :active="request()->routeIs('quotes.*')" icon="quotes" wire:navigate.hover>عروض الأسعار</x-sidebar-link>
                    <x-sidebar-link :href="route('purchases.index')" :active="request()->routeIs('purchases.*')" icon="purchases" wire:navigate.hover>المشتريات</x-sidebar-link>
                    <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="reports" wire:navigate.hover>التقارير</x-sidebar-link>
                </div>
            </div>

            <div>
                <div class="mb-1.5 px-2.5 text-[10px] font-semibold tracking-wide text-slate-400">الكتالوج</div>
                <div class="space-y-0.5">
                    <x-sidebar-link :href="route('parties.index')" :active="request()->routeIs('parties.*')" icon="parties" wire:navigate.hover>الأطراف</x-sidebar-link>
                    <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="products" wire:navigate.hover>المنتجات</x-sidebar-link>
                </div>
            </div>

            <div>
                <div class="mb-1.5 px-2.5 text-[10px] font-semibold tracking-wide text-slate-400">الإعدادات</div>
                <div class="space-y-0.5">
                    <x-sidebar-link :href="route('organization.edit')" :active="request()->routeIs('organization.edit')" icon="organization" wire:navigate.hover>المنشأة</x-sidebar-link>
                    <x-sidebar-link :href="route('organization.invoice-design')" :active="request()->routeIs('organization.invoice-design')" icon="invoice-design" wire:navigate.hover>تصميم الفاتورة</x-sidebar-link>
                    <x-sidebar-link :href="route('zatca.devices')" :active="request()->routeIs('zatca.*')" icon="zatca" wire:navigate.hover>الزكاة والضريبة</x-sidebar-link>
                    <x-sidebar-link :href="route('profile')" :active="request()->routeIs('profile')" icon="profile" wire:navigate.hover>الملف الشخصي</x-sidebar-link>
                </div>
            </div>
        </nav>

        <div class="shrink-0 border-t border-slate-100 p-3">
            <div class="mb-2 rounded-lg bg-slate-50 px-2.5 py-2">
                <div class="truncate text-xs font-semibold text-slate-800">{{ auth()->user()->name }}</div>
                <div class="truncate text-[11px] text-slate-500">{{ auth()->user()->email }}</div>
            </div>
            <button
                type="button"
                wire:click="logout"
                class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border-2 border-rose-200 bg-rose-50 px-2.5 py-2 text-xs font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 hover:text-rose-800"
            >
                <x-icon name="logout" class="h-3.5 w-3.5" />
                تسجيل الخروج
            </button>
        </div>
    </aside>
</div>
