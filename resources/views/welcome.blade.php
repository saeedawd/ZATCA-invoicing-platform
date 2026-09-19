@extends('layouts.marketing')

@php
    $faqs = [
        [
            'question' => 'هل أقدر أصدر فاتورة إلكترونية مجانًا؟',
            'answer' => 'نعم. فواتير زاتكا منصة مجانية 100٪ لإصدار الفواتير الإلكترونية في المملكة العربية السعودية بدون رسوم اشتراك.',
        ],
        [
            'question' => 'هل المنصة تساعد على الالتزام بمتطلبات هيئة الزكاة والضريبة والجمارك؟',
            'answer' => 'نعم. المنصة مصممة لمساعدة المنشآت على إصدار الفواتير وربط الأجهزة بما يوافق متطلبات الفوترة الإلكترونية. فواتير زاتكا منصة خاصة مستقلة وليست موقعًا حكوميًا.',
        ],
        [
            'question' => 'هل أقدر أصدر عرض سعر ثم أحوله لفاتورة؟',
            'answer' => 'نعم. تقدر تصدر عرض سعر للعميل، ولما يوافق تحوله لفاتورة بسهولة بدون إعادة إدخال البيانات.',
        ],
        [
            'question' => 'هل تدعم المنصة فواتير المبيعات والمشتريات؟',
            'answer' => 'نعم. تقدر تدير فواتير المبيعات والمشتريات وعروض الأسعار والتقارير الضريبية من حساب واحد.',
        ],
    ];
@endphp

@push('head')
    <x-seo.head page="home" :faqs="$faqs" />
@endpush

@section('content')
    <section class="mkt-hero-bg relative overflow-hidden">
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-6xl flex-col justify-center px-4 py-16 sm:px-6 sm:py-24">
            <div class="max-w-2xl">
                <p class="mkt-fade-up text-2xl font-bold tracking-tight text-brand-700 sm:text-3xl">فواتير زاتكا</p>
                <h1 class="mkt-fade-up-delay mt-5 text-3xl font-bold leading-[1.35] text-slate-900 sm:text-5xl sm:leading-[1.3]">
                    فواتير إلكترونية مجانية في السعودية — إصدار فاتورة وعرض سعر من منصة واحدة
                </h1>
                <p class="mkt-fade-up-delay-2 mt-6 max-w-xl text-lg leading-8 text-slate-600">
                    منصة فوترة إلكترونية للمنشآت في المملكة: أصدر فاتورة ضريبية، جهّز عرض سعر، وأدر المبيعات والمشتريات مع مساعدة على الالتزام بمتطلبات الفوترة الإلكترونية. مجاني 100٪ بدون أي رسوم.
                </p>
                <div class="mkt-fade-up-delay-2 mt-9 flex flex-wrap items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-brand-700">لوحة التحكم</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-brand-700" wire:navigate>ابدأ إصدار الفواتير مجانًا</a>
                        <a href="{{ route('marketing.electronic-invoicing') }}" class="inline-flex items-center rounded-lg border border-slate-300/90 bg-white/80 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-brand-300 hover:text-brand-800" wire:navigate>ما هي الفوترة الإلكترونية؟</a>
                    @endauth
                </div>
                <p class="mkt-fade-up-delay-2 mt-5 text-sm text-slate-500">
                    للمؤسسات في كل أنحاء المملكة · منصة خاصة مستقلة · بدون اشتراك
                </p>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200/80 bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">إصدار فاتورة وعرض سعر من منصة واحدة</h2>
            <p class="mt-3 max-w-2xl leading-7 text-slate-600">
                سواء تبي تصدر فاتورة مبيعات، فاتورة مشتريات، أو عرض سعر للعميل — كل شيء جاهز بواجهة عربية ومصمّم لسوق المملكة.
            </p>

            <ul class="mt-12 grid gap-10 sm:grid-cols-2">
                <li class="border-t border-brand-100 pt-6">
                    <h3 class="text-lg font-semibold text-slate-900">إصدار فواتير إلكترونية</h3>
                    <p class="mt-2 leading-7 text-slate-600">أنشئ <a href="{{ route('marketing.tax-invoice') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>فاتورة ضريبية إلكترونية</a>، عدّل المسودة، وتابع الحالة وشاركها PDF أو بالبريد.</p>
                </li>
                <li class="border-t border-brand-100 pt-6">
                    <h3 class="text-lg font-semibold text-slate-900">إصدار عرض سعر</h3>
                    <p class="mt-2 leading-7 text-slate-600">حضّر <a href="{{ route('marketing.quotations') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>عرض السعر الإلكتروني</a>، ولما يعتمد حوّله مباشرة لفاتورة.</p>
                </li>
                <li class="border-t border-brand-100 pt-6">
                    <h3 class="text-lg font-semibold text-slate-900">متطلبات الفوترة الإلكترونية</h3>
                    <p class="mt-2 leading-7 text-slate-600">تعرّف على <a href="{{ route('marketing.zatca-e-invoicing') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>كيف تساعدك المنصة مع متطلبات زاتكا</a> كحل برمجي خاص مستقل.</p>
                </li>
                <li class="border-t border-brand-100 pt-6">
                    <h3 class="text-lg font-semibold text-slate-900">برنامج فواتير مجاني</h3>
                    <p class="mt-2 leading-7 text-slate-600">لا رسوم ولا باقات مخفية. راجع صفحة <a href="{{ route('marketing.free-invoice-software') }}" class="font-medium text-brand-700 hover:underline" wire:navigate>برنامج الفواتير المجاني</a>.</p>
                </li>
            </ul>
        </div>
    </section>

    <section class="border-t border-slate-200/80 bg-surface py-16 sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="max-w-3xl">
                <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">برنامج فواتير مجاني لكل منشأة في السعودية</h2>
                <p class="mt-4 leading-8 text-slate-600">
                    التسجيل مجاني، وإصدار الفواتير وعروض الأسعار مجاني. هدفنا إن أي منشأة في المملكة تقدر تدير فوترتها الإلكترونية بسهولة، بدون ما يكون السعر عائقًا.
                </p>
            </div>
        </div>
    </section>

    <x-marketing.faq :items="$faqs" title="أسئلة شائعة عن إصدار الفواتير وعروض الأسعار" />

    <section class="border-t border-slate-200/80 bg-brand-800 py-16 text-white sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <h2 class="text-2xl font-bold sm:text-3xl">ابدأ الآن: أصدر فاتورتك أو عرض السعر مجانًا</h2>
            <p class="mt-3 max-w-xl text-brand-100 leading-7">
                حساب مجاني خلال دقائق — لمنشآت المملكة العربية السعودية.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                @guest
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-3 text-sm font-semibold text-brand-800 transition hover:bg-brand-50" wire:navigate>إنشاء حساب مجاني</a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10" wire:navigate>تواصل معنا</a>
                @else
                    <a href="{{ url('/dashboard') }}" class="inline-flex items-center rounded-lg bg-white px-5 py-3 text-sm font-semibold text-brand-800 transition hover:bg-brand-50">الانتقال للوحة التحكم</a>
                @endguest
            </div>
        </div>
    </section>
@endsection
