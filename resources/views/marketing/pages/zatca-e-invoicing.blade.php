@extends('layouts.marketing')

@php
    $seo = app(\App\Support\Seo\SeoManager::class);
    $crumbs = $seo->breadcrumbsFor('zatca_e_invoicing');
    $faqs = [
        [
            'question' => 'هل فواتير زاتكا تابعة لهيئة الزكاة والضريبة والجمارك؟',
            'answer' => 'لا. فواتير زاتكا منصة برمجية خاصة مستقلة. الاسم يعكس مجال الخدمة (الفوترة الإلكترونية في السعودية) وليس تمثيلًا رسميًا للهيئة.',
        ],
        [
            'question' => 'ماذا تعني «متوافق مع متطلبات زاتكا» هنا؟',
            'answer' => 'تعني أن المنصة مصممة لمساعدة المنشآت على إصدار الفواتير وربط الأجهزة والعمل وفق متطلبات الفوترة الإلكترونية المعتمدة، دون الادعاء بأننا الجهة التنظيمية نفسها.',
        ],
        [
            'question' => 'من أين أحصل على المتطلبات الرسمية؟',
            'answer' => 'المرجع الرسمي دائمًا هو قنوات هيئة الزكاة والضريبة والجمارك. منصتنا أداة مساعدة تشغيلية للمنشآت.',
        ],
    ];
@endphp

@push('head')
    <x-seo.head page="zatca_e_invoicing" :faqs="$faqs" />
@endpush

@section('content')
    <article>
        <section class="mkt-hero-bg border-b border-slate-200/70">
            <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-16">
                <x-marketing.breadcrumbs :items="$crumbs" />
                <h1 class="text-3xl font-bold leading-snug text-slate-900 sm:text-4xl">فوترة إلكترونية بمساعدة متوافقة مع متطلبات زاتكا</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600">
                    فواتير زاتكا منصة خاصة تساعد منشأتك على إصدار الفواتير الإلكترونية في السعودية بما يوافق متطلبات الفوترة الإلكترونية — دون أن تكون موقعًا حكوميًا أو تابعًا للهيئة.
                </p>
            </div>
        </section>

        <section class="bg-white py-12 sm:py-16">
            <div class="mx-auto max-w-3xl space-y-8 px-4 leading-8 text-slate-600 sm:px-6">
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-7 text-amber-950">
                    تنويه مهم: فواتير زاتكا (ZATCA Invoices) منصة برمجية مستقلة. لسنا هيئة الزكاة والضريبة والجمارك، ولسنا جهة حكومية، ولا نمثّل الموقع الرسمي للهيئة.
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">كيف نساعد منشأتك؟</h2>
                    <p class="mt-3">نقدّم أدوات لإصدار الفواتير وعروض الأسعار، وإدارة المبيعات والمشتريات، وربط أجهزة المنشأة من داخل المنصة، بواجهة عربية تسهّل العمل اليومي لفريقك.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">أين تكمل الصورة؟</h2>
                    <p class="mt-3">للفهم العام راجع <a href="{{ route('marketing.electronic-invoicing') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>الفوترة الإلكترونية في السعودية</a>، ولإنشاء المستندات استخدم صفحات <a href="{{ route('marketing.tax-invoice') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>الفاتورة الضريبية</a> و<a href="{{ route('marketing.quotations') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>عرض السعر</a>.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700" wire:navigate>ابدأ مجانًا</a>
                    <a href="{{ route('about') }}" class="inline-flex rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700" wire:navigate>عن المنصة</a>
                </div>
            </div>
        </section>

        <x-marketing.faq :items="$faqs" />
        <x-marketing.related-links :links="[
            ['label' => 'برنامج فواتير مجاني', 'url' => route('marketing.free-invoice-software')],
            ['label' => 'الفوترة الإلكترونية', 'url' => route('marketing.electronic-invoicing')],
            ['label' => 'تواصل معنا', 'url' => route('contact')],
        ]" />
    </article>
@endsection
