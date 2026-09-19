@extends('layouts.marketing')

@php
    $seo = app(\App\Support\Seo\SeoManager::class);
    $crumbs = $seo->breadcrumbsFor('electronic_invoicing');
    $faqs = [
        [
            'question' => 'ما هي الفوترة الإلكترونية؟',
            'answer' => 'الفوترة الإلكترونية هي إصدار الفواتير وحفظها وتبادلها بصيغة إلكترونية منظمة بدل الورق التقليدي، وفق المتطلبات المعتمدة في المملكة العربية السعودية.',
        ],
        [
            'question' => 'كيف أبدأ الفوترة الإلكترونية مع فواتير زاتكا؟',
            'answer' => 'تنشئ حسابًا مجانيًا، تضيف بيانات منشأتك، ثم تبدأ بإصدار الفواتير وعروض الأسعار من لوحة التحكم.',
        ],
        [
            'question' => 'هل فواتير زاتكا موقع رسمي للهيئة؟',
            'answer' => 'لا. فواتير زاتكا منصة برمجية خاصة مستقلة تساعد المنشآت على الفوترة الإلكترونية، وليست تابعة لهيئة الزكاة والضريبة والجمارك.',
        ],
    ];
@endphp

@push('head')
    <x-seo.head page="electronic_invoicing" :faqs="$faqs" />
@endpush

@section('content')
    <article>
        <section class="mkt-hero-bg border-b border-slate-200/70">
            <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-16">
                <x-marketing.breadcrumbs :items="$crumbs" />
                <h1 class="text-3xl font-bold leading-snug text-slate-900 sm:text-4xl">الفوترة الإلكترونية في السعودية</h1>
                <p class="mt-5 text-lg leading-8 text-slate-600">
                    دليل عملي مبسّط عن الفوترة الإلكترونية في المملكة، وكيف تساعدك منصة فواتير زاتكا على إصدار الفواتير الإلكترونية يوميًا بدون تعقيد وبدون رسوم.
                </p>
            </div>
        </section>

        <section class="bg-white py-12 sm:py-16">
            <div class="mx-auto max-w-3xl space-y-8 px-4 text-slate-600 sm:px-6 leading-8">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">لماذا الفوترة الإلكترونية مهمة؟</h2>
                    <p class="mt-3">كثير من المنشآت في السعودية تحتاج اليوم نظامًا واضحًا لإصدار الفاتورة الإلكترونية، متابعة الضريبة، ومشاركة المستند مع العميل. الورق والتعديل اليدوي يستهلك وقتًا ويزيد احتمال الأخطاء.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">ماذا تقدّم فواتير زاتكا؟</h2>
                    <p class="mt-3">من خلال حساب واحد تقدر تصدر فواتير المبيعات والمشتريات، تجهّز عروض الأسعار، تدير العملاء والمنتجات، وتطلع على تقارير تساعدك تراجع أرقامك. المنصة مجانية 100٪ ومبنية لواجهة عربية واتجاه RTL.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">خطوات بسيطة للبدء</h2>
                    <ol class="mt-3 list-decimal space-y-2 pe-6">
                        <li>أنشئ حسابًا مجانيًا على فواتير زاتكا.</li>
                        <li>أدخل بيانات منشأتك الأساسية.</li>
                        <li>أضف العملاء والمنتجات عند الحاجة.</li>
                        <li>أصدر فاتورة إلكترونية أو عرض سعر وشاركه مع عميلك.</li>
                    </ol>
                </div>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white hover:bg-brand-700" wire:navigate>ابدأ مجانًا</a>
                    <a href="{{ route('marketing.tax-invoice') }}" class="inline-flex rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:border-brand-300" wire:navigate>إنشاء فاتورة ضريبية</a>
                </div>
            </div>
        </section>

        <x-marketing.faq :items="$faqs" />
        <x-marketing.related-links :links="[
            ['label' => 'إنشاء فاتورة ضريبية إلكترونية', 'url' => route('marketing.tax-invoice')],
            ['label' => 'إنشاء عرض سعر إلكتروني', 'url' => route('marketing.quotations')],
            ['label' => 'فوترة متوافقة مع متطلبات زاتكا', 'url' => route('marketing.zatca-e-invoicing')],
            ['label' => 'برنامج فواتير مجاني', 'url' => route('marketing.free-invoice-software')],
        ]" />
    </article>
@endsection
