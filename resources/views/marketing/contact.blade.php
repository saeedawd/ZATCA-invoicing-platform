@extends('layouts.marketing')

@php
    $contactEmail = config('seo.contact_to', 'info@zatca.app');
    $crumbs = app(\App\Support\Seo\SeoManager::class)->breadcrumbsFor('contact');
@endphp

@push('head')
    <x-seo.head page="contact" />
@endpush

@section('content')
    <section class="mkt-hero-bg">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-16">
            <div class="max-w-2xl">
                <x-marketing.breadcrumbs :items="$crumbs" />
                <p class="mkt-fade-up text-sm font-semibold text-brand-700">تواصل معنا</p>
                <h1 class="mkt-fade-up-delay mt-3 text-3xl font-bold leading-snug text-slate-900 sm:text-4xl">
                    فريق فواتير زاتكا جاهز يرد عليك
                </h1>
                <p class="mkt-fade-up-delay-2 mt-5 max-w-xl text-lg leading-8 text-slate-600">
                    استفسار عن التسجيل، إصدار الفواتير، عروض الأسعار، أو استخدام المنصة — راسلنا وبنرد في أقرب وقت.
                </p>
            </div>
        </div>
    </section>

    <section class="border-t border-slate-200/80 bg-white py-12 sm:py-16">
        <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:gap-16">
            <aside class="mkt-fade-up space-y-8 lg:col-span-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">البريد الرسمي</h2>
                    <p class="mt-2 text-sm leading-7 text-slate-600">للاستفسارات والدعم العام — بريد الشركة للتواصل.</p>
                    <a href="mailto:{{ $contactEmail }}" class="mt-4 inline-flex items-center gap-2 text-lg font-semibold text-brand-700 transition hover:text-brand-800" dir="ltr">
                        {{ $contactEmail }}
                    </a>
                </div>
                <div class="border-t border-slate-100 pt-8">
                    <h2 class="text-lg font-bold text-slate-900">إيش نقدر نساعدك فيه؟</h2>
                    <ul class="mt-4 space-y-3 text-sm leading-7 text-slate-600">
                        <li>فتح حساب مجاني وبدء إصدار الفواتير</li>
                        <li>عروض الأسعار والفواتير الضريبية</li>
                        <li>استخدام المنصة وربط الأجهزة</li>
                    </ul>
                </div>
            </aside>

            <div class="mkt-fade-up-delay lg:col-span-8">
                <div class="rounded-2xl border border-slate-200/90 bg-surface p-6 sm:p-8">
                    <h2 class="text-xl font-bold text-slate-900">أرسل رسالة</h2>
                    <p class="mt-2 text-sm text-slate-600">عبّئ البيانات وبنرد عليك على بريدك المسجّل في النموذج.</p>
                    <div class="mt-6">
                        <livewire:marketing.contact-form />
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
