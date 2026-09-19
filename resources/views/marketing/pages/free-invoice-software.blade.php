@extends('layouts.marketing')

@php
    $seo = app(\App\Support\Seo\SeoManager::class);
    $crumbs = $seo->breadcrumbsFor('free_invoice_software');
    $faqs = [
        [
            'question' => 'هل برنامج فواتير زاتكا مجاني فعلاً؟',
            'answer' => 'نعم. الاستخدام مجاني 100٪ بدون رسوم اشتراك وبدون رسوم على إصدار الفواتير أو عروض الأسعار.',
        ],
        [
            'question' => 'هل يناسب المحلات والشركات الصغيرة؟',
            'answer' => 'نعم. المنصة موجّهة للمنشآت الصغيرة والمتوسطة في السعودية التي تحتاج فوترة يومية واضحة بدون أنظمة معقّدة.',
        ],
        [
            'question' => 'ماذا يشمل البرنامج المجاني؟',
            'answer' => 'إصدار فواتير، عروض أسعار، مبيعات ومشتريات، أطراف ومنتجات، وتقارير أساسية من واجهة عربية.',
        ],
    ];
@endphp

@push('head')
    <x-seo.head page="free_invoice_software" :faqs="$faqs" />
@endpush

@section('content')
    <article>
        <section class="mkt-hero-bg border-b border-slate-200/70">
            <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-16">
                <x-marketing.breadcrumbs :items="$crumbs" />
                <h1 class="text-3xl font-bold leading-snug text-slate-900 sm:text-4xl">برنامج فواتير مجاني في السعودية</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600">
                    فواتير زاتكا برنامج فوترة إلكترونية مجاني للمنشآت السعودية: أصدر فواتيرك وعروض أسعارك بدون اشتراك وبدون رسوم خفية.
                </p>
            </div>
        </section>

        <section class="bg-white py-12 sm:py-16">
            <div class="mx-auto max-w-3xl space-y-8 px-4 leading-8 text-slate-600 sm:px-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">مجاني 100٪ — بدون أي رسوم</h2>
                    <p class="mt-3">ما فيه باقات مدفوعة مخفية داخل الصفحة. التسجيل مجاني، وإصدار الفاتورة وعرض السعر مجاني. هدفنا توفير أداة عملية لكل منشأة تبي تلتزم بالفوترة الإلكترونية بسهولة.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">أكثر من مجرد نموذج فاتورة</h2>
                    <p class="mt-3">تحصل على دورة عمل كاملة: عملاء، منتجات، مبيعات، مشتريات، عروض أسعار، وتقارير. راجع أيضًا صفحات <a href="{{ route('marketing.tax-invoice') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>الفاتورة الضريبية</a> و<a href="{{ route('marketing.quotations') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>عروض الأسعار</a>.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">الفرق بيننا وبين الجهات الرسمية</h2>
                    <p class="mt-3">فواتير زاتكا منصة خاصة (ZATCA Invoices). نحن لسنا هيئة الزكاة والضريبة والجمارك ولسنا موقعًا حكوميًا؛ نقدّم برنامجًا يساعد منشأتك على العمل وفق متطلبات الفوترة الإلكترونية في المملكة.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700" wire:navigate>جرّب البرنامج مجانًا</a>
                    <a href="{{ route('marketing.zatca-e-invoicing') }}" class="inline-flex rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700" wire:navigate>عن التوافق مع زاتكا</a>
                </div>
            </div>
        </section>

        <x-marketing.faq :items="$faqs" />
        <x-marketing.related-links :links="[
            ['label' => 'الفوترة الإلكترونية في السعودية', 'url' => route('marketing.electronic-invoicing')],
            ['label' => 'إنشاء فاتورة ضريبية', 'url' => route('marketing.tax-invoice')],
            ['label' => 'من نحن', 'url' => route('about')],
        ]" />
    </article>
@endsection
