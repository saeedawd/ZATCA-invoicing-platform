@extends('layouts.marketing')

@php
    $seo = app(\App\Support\Seo\SeoManager::class);
    $crumbs = $seo->breadcrumbsFor('tax_invoice');
    $faqs = [
        [
            'question' => 'ما هي الفاتورة الضريبية؟',
            'answer' => 'الفاتورة الضريبية مستند يوضح تفاصيل البيع أو الخدمة وقيمة ضريبة القيمة المضافة، وتُستخدم لتوثيق المعاملة بين المنشأة والعميل وفق الأنظمة في السعودية.',
        ],
        [
            'question' => 'هل أقدر أنشئ فاتورة ضريبية إلكترونية مجانًا؟',
            'answer' => 'نعم عبر فواتير زاتكا بدون رسوم اشتراك، مع إمكانية مشاركة الفاتورة PDF أو بالبريد.',
        ],
        [
            'question' => 'هل تدعم المنصة فواتير المبيعات والمشتريات؟',
            'answer' => 'نعم. تقدر تدير فواتير المبيعات وفواتير المشتريات من نفس الحساب.',
        ],
    ];
@endphp

@push('head')
    <x-seo.head page="tax_invoice" :faqs="$faqs" />
@endpush

@section('content')
    <article>
        <section class="mkt-hero-bg border-b border-slate-200/70">
            <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-16">
                <x-marketing.breadcrumbs :items="$crumbs" />
                <h1 class="text-3xl font-bold leading-snug text-slate-900 sm:text-4xl">إنشاء فاتورة ضريبية إلكترونية</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600">
                    أصدر فاتورة ضريبية إلكترونية لعملائك في السعودية من فواتير زاتكا — بسرعة، بواجهة عربية، وبدون أي رسوم.
                </p>
            </div>
        </section>

        <section class="bg-white py-12 sm:py-16">
            <div class="mx-auto max-w-3xl space-y-8 px-4 leading-8 text-slate-600 sm:px-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">فاتورة ضريبية واضحة ومنظمة</h2>
                    <p class="mt-3">بدل ما تعتمد على جداول متفرقة، تجمع في الفاتورة بيانات العميل والأصناف والضريبة والإجمالي، وتقدر تعدّل المسودة قبل الإصدار وتشارك النسخة النهائية مع الطرف الآخر.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">من عرض السعر إلى الفاتورة</h2>
                    <p class="mt-3">كثير من المعاملات تبدأ بـ <a href="{{ route('marketing.quotations') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>عرض سعر</a>. في فواتير زاتكا تقدر تحوّل العرض المعتمد إلى فاتورة بدون إعادة إدخال كل البنود.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">مناسب للمحلات والشركات الصغيرة</h2>
                    <p class="mt-3">المنصة مصممة للمنشآت اللي تبي حلًا عمليًا لإدارة الفواتير في السعودية، مع تقارير وأطراف ومنتجات — مجانًا بالكامل كـ <a href="{{ route('marketing.free-invoice-software') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>برنامج فواتير</a>.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700" wire:navigate>أنشئ فاتورة الآن مجانًا</a>
                    <a href="{{ route('marketing.electronic-invoicing') }}" class="inline-flex rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700" wire:navigate>عن الفوترة الإلكترونية</a>
                </div>
            </div>
        </section>

        <x-marketing.faq :items="$faqs" />
        <x-marketing.related-links :links="[
            ['label' => 'الفوترة الإلكترونية في السعودية', 'url' => route('marketing.electronic-invoicing')],
            ['label' => 'إنشاء عرض سعر إلكتروني', 'url' => route('marketing.quotations')],
            ['label' => 'التوافق مع متطلبات زاتكا', 'url' => route('marketing.zatca-e-invoicing')],
        ]" />
    </article>
@endsection
