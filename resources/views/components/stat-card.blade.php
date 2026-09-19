@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'default',
])

@php
$tones = [
    'default' => 'border-slate-200/80',
    'success' => 'border-emerald-200/80',
    'warning' => 'border-amber-200/80',
    'danger' => 'border-rose-200/80',
];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border bg-white p-5 shadow-soft ' . ($tones[$tone] ?? $tones['default'])]) }}>
    <div class="text-sm font-medium text-slate-500">{{ $label }}</div>
    <div class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</div>
    @if ($hint)
        <div class="mt-2 text-sm text-slate-500">{{ $hint }}</div>
    @endif
    @isset($footer)
        <div class="mt-3 text-sm">
            {{ $footer }}
        </div>
    @endisset
</div>
