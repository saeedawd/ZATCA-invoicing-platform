@extends('layouts.marketing')

@php
    $crumbs = app(\App\Support\Seo\SeoManager::class)->breadcrumbsFor('privacy');
@endphp

@push('head')
    <x-seo.head page="privacy" />
@endpush

@section('content')
    <article>
        <section class="mkt-hero-bg border-b border-slate-200/70">
            <div class="mx-auto max-w-3xl px-4 py-14 sm:px-6 sm:py-16">
                <x-marketing.breadcrumbs :items="$crumbs" />
                <h1 class="text-3xl font-bold text-slate-900">سياسة الخصوصية</h1>
                <p class="mt-4 leading-8 text-slate-600">آخر تحديث: {{ date('Y') }} — توضح هذه الصفحة كيف تتعامل منصة فواتير زاتكا مع بيانات المستخدمين والمنشآت.</p>
            </div>
        </section>

        <section class="bg-white py-12 sm:py-16">
            <div class="mx-auto max-w-3xl space-y-8 px-4 leading-8 text-slate-600 sm:px-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">البيانات التي نجمعها</h2>
                    <p class="mt-3">بيانات الحساب (مثل الاسم والبريد)، وبيانات المنشأة التي تدخلها لاستخدام الفوترة، ومحتوى المستندات التي تنشئها داخل المنصة، بالإضافة لبيانات تقنية أساسية لتشغيل الخدمة وأمانها.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">كيف نستخدم البيانات</h2>
                    <p class="mt-3">لتشغيل حسابك، إصدار الفواتير وعروض الأسعار، إرسال الإشعارات الضرورية، وتحسين استقرار المنصة. لا نبيع بيانات عملائك لأطراف أخرى لأغراض تسويقية.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">خصوصية بيانات المستأجرين</h2>
                    <p class="mt-3">بيانات المنشآت والفواتير خاصة ولا تُفهرس في محركات البحث. صفحات لوحة التحكم محمية بتسجيل الدخول وغير مخصصة للفهرسة العامة.</p>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">التواصل</h2>
                    <p class="mt-3">لأسئلة الخصوصية راسلنا على <a href="mailto:{{ config('seo.contact_to') }}" class="font-medium text-brand-700" dir="ltr">{{ config('seo.contact_to') }}</a>.</p>
                </div>
            </div>
        </section>
    </article>
@endsection
