@extends('layouts.marketing')

@push('head')
    <x-seo.head page="about" />
@endpush

@section('content')
    @php
        $crumbs = app(\App\Support\Seo\SeoManager::class)->breadcrumbsFor('about');
    @endphp

    <section class="mkt-hero-bg border-b border-slate-200/70">
        <div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 sm:py-20">
            <x-marketing.breadcrumbs :items="$crumbs" />
            <p class="mkt-fade-up text-sm font-semibold text-brand-700">من نحن</p>
            <h1 class="mkt-fade-up-delay mt-3 text-3xl font-bold leading-snug text-slate-900 sm:text-4xl">
                عن فواتير زاتكا — منصة الفوترة الإلكترونية السعودية
            </h1>
            <p class="mkt-fade-up-delay-2 mt-6 text-lg leading-8 text-slate-600">
                فواتير زاتكا (ZATCA Invoices) منصة برمجية خاصة تساعد المنشآت الصغيرة والمتوسطة على إصدار الفواتير الإلكترونية وعروض الأسعار في المملكة العربية السعودية — مجانًا وبدون اشتراك.
            </p>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-3xl space-y-10 px-4 sm:px-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">إيش نقدّم؟</h2>
                <p class="mt-3 leading-8 text-slate-600">
                    حساب واحد يجمع إعداد المنشأة، ربط الأجهزة، فواتير المبيعات والمشتريات، عروض الأسعار، الأطراف والمنتجات، والتقارير. دورة الفوترة كاملة في مكان واحد.
                </p>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900">منصة خاصة — ليست جهة حكومية</h2>
                <p class="mt-3 leading-8 text-slate-600">
                    فواتير زاتكا ليست تابعة لهيئة الزكاة والضريبة والجمارك وليست موقعًا رسميًا للهيئة. نحن نوفر برنامجًا يساعد المنشآت على العمل بما يوافق متطلبات الفوترة الإلكترونية المعتمدة في السعودية.
                </p>
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-900">ليه مجانية؟</h2>
                <p class="mt-3 leading-8 text-slate-600">
                    لأن الالتزام بالفوترة الإلكترونية صار أساسيًا، وما المفروض يكون مكلفًا على كل منشأة. الاستخدام مجاني 100٪ بدون رسوم على التسجيل أو إصدار الفواتير.
                </p>
            </div>
            <div class="flex flex-wrap gap-3 pt-2">
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-brand-700" wire:navigate>ابدأ مجانًا</a>
                <a href="{{ route('contact') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:text-brand-800" wire:navigate>تواصل معنا</a>
            </div>
        </div>
    </section>

    <x-marketing.related-links :links="[
        ['label' => 'الفوترة الإلكترونية في السعودية', 'url' => route('marketing.electronic-invoicing')],
        ['label' => 'برنامج فواتير مجاني', 'url' => route('marketing.free-invoice-software')],
        ['label' => 'التوافق مع متطلبات زاتكا', 'url' => route('marketing.zatca-e-invoicing')],
    ]" />
@endsection
