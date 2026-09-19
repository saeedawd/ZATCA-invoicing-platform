<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <x-seo.head
            :title="$title ?? null"
            description="سجّل دخولك أو أنشئ حسابًا مجانيًا في فواتير زاتكا — منصة فوترة إلكترونية سعودية بدون أي رسوم."
            robots="noindex, nofollow"
            :schema="[]"
        />

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-surface font-sans text-slate-800 antialiased">
        <div class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0">
            <div class="text-center">
                <a href="{{ route('home') }}" wire:navigate class="inline-flex flex-col items-center gap-2">
                    <img src="{{ asset('logo.png') }}" alt="فواتير زاتكا" width="56" height="56" class="h-14 w-14 object-contain">
                    <span class="text-2xl font-bold text-brand-700">{{ config('seo.name', 'فواتير زاتكا') }}</span>
                </a>
                <p class="mt-1 text-sm text-slate-500">فوترة مجانية ومتوافقة مع الزكاة والضريبة والجمارك</p>
            </div>

            <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-soft sm:max-w-md sm:rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
