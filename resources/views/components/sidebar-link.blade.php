@props([
    'href',
    'active' => false,
    'icon' => null,
])

@php
$classes = $active
    ? 'flex items-center gap-2 rounded-lg bg-brand-50 px-2.5 py-1.5 text-xs font-semibold text-brand-800'
    : 'flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900';
@endphp

<a href="{{ $href }}" @click="$dispatch('close-sidebar')" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <span @class([
            'inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-md',
            'bg-brand-100 text-brand-700' => $active,
            'bg-slate-100 text-slate-500' => ! $active,
        ])>
            <x-icon :name="$icon" class="h-3.5 w-3.5" />
        </span>
    @endif
    <span class="truncate">{{ $slot }}</span>
</a>
