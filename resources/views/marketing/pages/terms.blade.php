@extends('layouts.marketing')

@php
    $crumbs = app(\App\Support\Seo\SeoManager::class)->breadcrumbsFor('terms');
@endphp

@push('head')
    <x-seo.head page="terms" />
@endpush

@section('content')
    <article>
        <section class="mkt-hero-bg border-b border-slate-200/70">
            <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-16">
                <x-marketing.breadcrumbs :items="$crumbs" />
                <h1 class="text-3xl font-bold text-slate-900">شروط الاستخدام</h1>
                <p class="mt-4 leading-8 text-slate-600">باستخدامك منصة فواتير زاتكا فإنك توافق على الشروط التالية.</p>
            </div>
        </section>

        <section class="bg-white py-12 sm:py-16">
            <div class="mx-auto max-w-3xl space-y-8 px-4 leading-8 text-slate-600 sm:px-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">طبيعة الخدمة</h2>
                    <p class="mt-3">فواتير زاتكا منصة برمجية خاصة لتسهيل إصدار الفواتير الإلكترونية وعروض الأسعار للمنشآت في السعودية. الخدمة تُقدَّم «كما هي» مع سعينا المستمر لتحسينها.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">عدم التمثيل الحكومي</h2>
                    <p class="mt-3">المنصة ليست تابعة لهيئة الزكاة والضريبة والجمارك وليست موقعًا رسميًا لها. يبقى المستخدم مسؤولًا عن الالتزام بالمتطلبات النظامية السارية على منشأته.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">استخدام مقبول</h2>
                    <p class="mt-3">يلتزم المستخدم بإدخال بيانات صحيحة، وعدم إساءة استخدام الخدمة، وعدم محاولة الوصول غير المصرح به لبيانات منشآت أخرى.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">التواصل</h2>
                    <p class="mt-3">للاستفسارات: <a href="mailto:{{ config('seo.contact_to') }}" class="font-medium text-brand-700" dir="ltr">{{ config('seo.contact_to') }}</a> أو عبر صفحة <a href="{{ route('contact') }}" class="font-medium text-brand-700" wire:navigate>تواصل معنا</a>.</p>
                </div>
            </div>
        </section>
    </article>
@endsection
