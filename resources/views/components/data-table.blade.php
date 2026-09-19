@props([
    'empty' => null,
    'search' => false,
    'searchModel' => 'search',
    'searchPlaceholder' => 'بحث...',
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-soft']) }}>
    @if ($search || isset($filters) || isset($toolbar))
        <div class="flex flex-wrap items-end gap-3 border-b border-slate-100 bg-slate-50/50 px-4 py-4 sm:px-5">
            @if ($search)
                <div class="relative min-w-[14rem] flex-1">
                    <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <x-text-input
                        wire:model.live.debounce.300ms="{{ $searchModel }}"
                        :placeholder="$searchPlaceholder"
                        class="w-full ps-10"
                    />
                </div>
            @endif

            @isset($filters)
                <div class="flex flex-wrap items-end gap-3">
                    {{ $filters }}
                </div>
            @endisset

            @isset($toolbar)
                <div class="ms-auto flex flex-wrap items-center gap-2">
                    {{ $toolbar }}
                </div>
            @endisset
        </div>
    @endif

    <div class="overflow-x-auto">
        {{ $slot }}
    </div>

    @isset($empty)
        {{-- Empty slot handled by caller inside table/body; keep footer pagination separate --}}
    @endisset

    @isset($footer)
        <div class="border-t border-slate-100 px-4 py-3 sm:px-5">
            {{ $footer }}
        </div>
    @endisset
</div>
