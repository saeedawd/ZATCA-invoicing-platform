<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="flex min-h-screen flex-col bg-surface font-sans text-slate-800 antialiased">
        <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/90 backdrop-blur">
            <input type="checkbox" id="mobile-nav-toggle" class="peer/nav sr-only" autocomplete="off">

            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-3 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5 transition-opacity hover:opacity-90" wire:navigate>
                    <img src="{{ asset('logo.png') }}" alt="فواتير زاتكا" width="36" height="36" class="h-9 w-9 shrink-0 object-contain">
                    <span class="truncate text-lg font-bold text-brand-700">{{ config('seo.name') }}</span>
                </a>

                <nav class="hidden items-center gap-5 text-sm font-medium text-slate-600 lg:flex">
                    <a href="{{ route('home') }}" class="transition-colors hover:text-brand-700 {{ request()->routeIs('home') ? 'text-brand-700' : '' }}" wire:navigate>الرئيسية</a>
                    <a href="{{ route('marketing.electronic-invoicing') }}" class="transition-colors hover:text-brand-700 {{ request()->routeIs('marketing.electronic-invoicing') ? 'text-brand-700' : '' }}" wire:navigate>الفوترة الإلكترونية</a>
                    <a href="{{ route('marketing.quotations') }}" class="transition-colors hover:text-brand-700 {{ request()->routeIs('marketing.quotations') ? 'text-brand-700' : '' }}" wire:navigate>عروض الأسعار</a>
                    <a href="{{ route('about') }}" class="transition-colors hover:text-brand-700 {{ request()->routeIs('about') ? 'text-brand-700' : '' }}" wire:navigate>من نحن</a>
                    <a href="{{ route('contact') }}" class="transition-colors hover:text-brand-700 {{ request()->routeIs('contact') ? 'text-brand-700' : '' }}" wire:navigate>تواصل</a>
                </nav>

                <div class="flex shrink-0 items-center gap-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-brand-700">لوحة التحكم</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-brand-700 sm:inline-flex" wire:navigate>دخول</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-brand-700 sm:px-3.5" wire:navigate>ابدأ مجانًا</a>
                    @endauth

                    <label
                        for="mobile-nav-toggle"
                        class="inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-xl border-2 border-brand-200 bg-brand-50 text-brand-700 transition hover:bg-brand-100 lg:hidden"
                        aria-label="القائمة"
                    >
                        <x-icon name="menu" class="h-5 w-5" />
                    </label>
                </div>
            </div>

            <nav class="mx-auto hidden max-w-6xl border-t border-slate-100 px-4 py-3 peer-checked/nav:block lg:!hidden sm:px-6">
                <div class="flex flex-col gap-1">
                    <a href="{{ route('home') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-brand-50 hover:text-brand-800 {{ request()->routeIs('home') ? 'bg-brand-50 font-semibold text-brand-800' : '' }}" wire:navigate>الرئيسية</a>
                    <a href="{{ route('marketing.electronic-invoicing') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-brand-50 hover:text-brand-800 {{ request()->routeIs('marketing.electronic-invoicing') ? 'bg-brand-50 font-semibold text-brand-800' : '' }}" wire:navigate>الفوترة الإلكترونية</a>
                    <a href="{{ route('marketing.tax-invoice') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-brand-50 hover:text-brand-800 {{ request()->routeIs('marketing.tax-invoice') ? 'bg-brand-50 font-semibold text-brand-800' : '' }}" wire:navigate>فاتورة ضريبية</a>
                    <a href="{{ route('marketing.quotations') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-brand-50 hover:text-brand-800 {{ request()->routeIs('marketing.quotations') ? 'bg-brand-50 font-semibold text-brand-800' : '' }}" wire:navigate>عروض الأسعار</a>
                    <a href="{{ route('about') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-brand-50 hover:text-brand-800 {{ request()->routeIs('about') ? 'bg-brand-50 font-semibold text-brand-800' : '' }}" wire:navigate>من نحن</a>
                    <a href="{{ route('contact') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-brand-50 hover:text-brand-800 {{ request()->routeIs('contact') ? 'bg-brand-50 font-semibold text-brand-800' : '' }}" wire:navigate>تواصل</a>
                    @guest
                        <a href="{{ route('login') }}" class="mt-1 rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:hidden" wire:navigate>دخول</a>
                    @endguest
                </div>
            </nav>
        </header>

        <main class="flex-1">
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </main>

        <footer class="border-t border-slate-200/80 bg-white">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-4">
                <div class="sm:col-span-2 lg:col-span-1">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('logo.png') }}" alt="فواتير زاتكا" width="36" height="36" class="h-9 w-9 object-contain" loading="lazy">
                        <div class="font-bold text-brand-700">{{ config('seo.name') }}</div>
                    </div>
                    <p class="mt-3 max-w-sm text-sm leading-7 text-slate-600">
                        منصة خاصة مجانية لإصدار الفواتير الإلكترونية وعروض الأسعار في السعودية، ومساعدة المنشآت على الالتزام بمتطلبات الفوترة الإلكترونية. ليست تابعة لهيئة الزكاة والضريبة والجمارك.
                    </p>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-900">مواضيع مهمة</div>
                    <nav class="mt-3 flex flex-col gap-2 text-sm text-slate-600">
                        <a href="{{ route('marketing.electronic-invoicing') }}" class="transition-colors hover:text-brand-700" wire:navigate>الفوترة الإلكترونية</a>
                        <a href="{{ route('marketing.tax-invoice') }}" class="transition-colors hover:text-brand-700" wire:navigate>الفاتورة الضريبية</a>
                        <a href="{{ route('marketing.quotations') }}" class="transition-colors hover:text-brand-700" wire:navigate>عروض الأسعار</a>
                        <a href="{{ route('marketing.zatca-e-invoicing') }}" class="transition-colors hover:text-brand-700" wire:navigate>التوافق مع زاتكا</a>
                    </nav>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-900">المنصة</div>
                    <nav class="mt-3 flex flex-col gap-2 text-sm text-slate-600">
                        <a href="{{ route('marketing.free-invoice-software') }}" class="transition-colors hover:text-brand-700" wire:navigate>برنامج فواتير مجاني</a>
                        <a href="{{ route('about') }}" class="transition-colors hover:text-brand-700" wire:navigate>من نحن</a>
                        <a href="{{ route('contact') }}" class="transition-colors hover:text-brand-700" wire:navigate>تواصل معنا</a>
                        <a href="{{ route('privacy') }}" class="transition-colors hover:text-brand-700" wire:navigate>الخصوصية</a>
                        <a href="{{ route('terms') }}" class="transition-colors hover:text-brand-700" wire:navigate>شروط الاستخدام</a>
                    </nav>
                </div>
                <div>
                    <div class="text-sm font-semibold text-slate-900">تواصل</div>
                    <p class="mt-3 text-sm leading-7 text-slate-600">بريد الشركة:</p>
                    <a href="mailto:{{ config('seo.contact_to', 'info@zatca.app') }}" class="mt-1 inline-flex text-sm font-semibold text-brand-700 transition hover:text-brand-800" dir="ltr">
                        {{ config('seo.contact_to', 'info@zatca.app') }}
                    </a>
                    @guest
                        <div class="mt-4">
                            <a href="{{ route('register') }}" class="inline-flex text-sm font-semibold text-brand-700 transition hover:text-brand-800" wire:navigate>إنشاء حساب مجاني ←</a>
                        </div>
                    @endguest
                </div>
            </div>
            <div class="border-t border-slate-100 py-4 text-center text-xs text-slate-400">
                © {{ date('Y') }} {{ config('seo.name') }} ({{ config('seo.english_name') }}) — منصة خاصة مستقلة.
            </div>
        </footer>
    </body>
</html>
