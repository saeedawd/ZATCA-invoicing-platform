<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <x-seo.head
            :title="$title ?? 'لوحة التحكم'"
            description="منطقة خاصة بمستخدمي فواتير زاتكا — غير مخصصة للفهرسة."
            robots="noindex, nofollow"
            :schema="[]"
        />

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cairo:400,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="min-h-screen bg-surface font-sans text-slate-800 antialiased"
        x-data="{ sidebarOpen: false }"
        :class="{ 'sidebar-open': sidebarOpen }"
        @close-sidebar.window="sidebarOpen = false"
        @keydown.escape.window="sidebarOpen = false"
    >
        {{-- هيكل LTR لتثبيت السايدبار يمين الشاشة، والمحتوى RTL --}}
        <div class="min-h-screen lg:flex" dir="ltr">
            <div class="flex min-w-0 flex-1 flex-col" dir="rtl">
                <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                        <div class="min-w-0 flex-1">
                            @isset($header)
                                <div class="truncate text-base font-semibold text-slate-900 sm:text-lg">
                                    {{ $header }}
                                </div>
                            @else
                                <div class="truncate text-sm font-medium text-slate-500">
                                    {{ $title ?? config('app.name', 'فواتير زاتكا') }}
                                </div>
                            @endisset
                        </div>

                        <div class="hidden items-center gap-2 text-sm text-slate-500 sm:flex">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-700">
                                <x-icon name="profile" class="h-4 w-4" />
                            </span>
                            <span class="max-w-[10rem] truncate font-medium text-slate-700">{{ auth()->user()->name }}</span>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border-2 border-brand-200 bg-brand-50 text-brand-700 transition hover:bg-brand-100 lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="فتح القائمة"
                            aria-controls="app-sidebar"
                        >
                            <x-icon name="menu" class="h-5 w-5" />
                        </button>
                    </div>
                </header>

                <main class="flex-1">
                    <div class="px-4 py-6 sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>

            <livewire:layout.navigation />
        </div>

        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
            @click="sidebarOpen = false"
            style="display: none;"
        ></div>
    </body>
</html>
