@props([
    'icon',
    'label',
    'variant' => 'muted',
    'href' => null,
])

@php
$variants = [
    'view' => 'bg-sky-100 text-sky-700 hover:bg-sky-200 hover:text-sky-900 ring-1 ring-sky-200',
    'edit' => 'bg-brand-100 text-brand-700 hover:bg-brand-200 hover:text-brand-900 ring-1 ring-brand-200',
    'delete' => 'bg-rose-100 text-rose-700 hover:bg-rose-200 hover:text-rose-900 ring-1 ring-rose-200',
    'muted' => 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-800 ring-1 ring-slate-200',
];

$classes = 'inline-flex h-9 w-9 items-center justify-center rounded-xl transition focus:outline-none focus:ring-2 focus:ring-brand-600 focus:ring-offset-1 ' . ($variants[$variant] ?? $variants['muted']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes, 'title' => $label, 'aria-label' => $label]) }}>
        <x-icon :name="$icon" class="h-[1.125rem] w-[1.125rem]" />
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => $classes, 'title' => $label, 'aria-label' => $label]) }}>
        <x-icon :name="$icon" class="h-[1.125rem] w-[1.125rem]" />
    </button>
@endif
