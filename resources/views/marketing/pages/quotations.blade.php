@extends('layouts.marketing')

@php
    $seo = app(\App\Support\Seo\SeoManager::class);
    $crumbs = $seo->breadcrumbsFor('quotations');
    $faqs = [
        [
            'question' => 'ما الفرق بين عرض السعر والفاتورة؟',
            'answer' => 'عرض السعر عرض مبدئي للأسعار والكميات يُرسل للعميل قبل الاعتماد. بعد الموافقة يتحول عادة إلى فاتورة رسمية توثق البيع أو الخدمة.',
        ],
        [
            'question' => 'هل أقدر أصدر عرض سعر إلكتروني مجانًا؟',
            'answer' => 'نعم. فواتير زاتكا تتيح إنشاء عروض الأسعار ومشاركتها وتحويلها لفاتورة بدون رسوم.',
        ],
        [
            'question' => 'هل صفحة عروض الأسعار هذه هي شاشة النظام الداخلية؟',
            'answer' => 'لا. هذه صفحة تعريفية عامة. إدارة العروض الفعلية تتم داخل حسابك بعد تسجيل الدخول.',
        ],
    ];
@endphp

@push('head')
    <x-seo.head page="quotations" :faqs="$faqs" />
@endpush

@section('content')
    <article>
        <section class="mkt-hero-bg border-b border-slate-200/70">
            <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-16">
                <x-marketing.breadcrumbs :items="$crumbs" />
                <h1 class="text-3xl font-bold leading-snug text-slate-900 sm:text-4xl">إنشاء عرض سعر إلكتروني مجانًا</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600">
                    جهّز عرض سعر احترافي لعملائك في السعودية، شاركه بسرعة، ولما يعتمد حوّله إلى فاتورة من فواتير زاتكا.
                </p>
            </div>
        </section>

        <section class="bg-white py-12 sm:py-16">
            <div class="mx-auto max-w-3xl space-y-8 px-4 leading-8 text-slate-600 sm:px-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">عرض سعر يختصر وقتك</h2>
                    <p class="mt-3">بدل إعادة كتابة الأصناف في كل مرة، تحفظ منتجاتك وأسعارها وتبني منها عرض السعر، ثم تحدّث الكميات والخصومات حسب كل عميل.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">من العرض إلى الفاتورة الضريبية</h2>
                    <p class="mt-3">بعد موافقة العميل، تحويل العرض إلى <a href="{{ route('marketing.tax-invoice') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>فاتورة ضريبية</a> يقلل الأخطاء ويحافظ على نفس البنود والمبالغ.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">جزء من دورة فوترة كاملة</h2>
                    <p class="mt-3">عروض الأسعار ضمن منظومة تشمل المبيعات والمشتريات والتقارير. ابدأ من <a href="{{ route('marketing.electronic-invoicing') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>الفوترة الإلكترونية</a> أو افتح حسابًا مجانيًا مباشرة.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700" wire:navigate>ابدأ عروض الأسعار مجانًا</a>
                    <a href="{{ route('contact') }}" class="inline-flex rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700" wire:navigate>اسألنا عن المنصة</a>
                </div>
            </div>
        </section>

        <x-marketing.faq :items="$faqs" />
        <x-marketing.related-links :links="[
            ['label' => 'إنشاء فاتورة ضريبية إلكترونية', 'url' => route('marketing.tax-invoice')],
            ['label' => 'برنامج فواتير مجاني', 'url' => route('marketing.free-invoice-software')],
            ['label' => 'الفوترة الإلكترونية في السعودية', 'url' => route('marketing.electronic-invoicing')],
        ]" />
    </article>
@endsection
