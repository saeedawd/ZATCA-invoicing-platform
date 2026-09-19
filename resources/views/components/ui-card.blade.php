@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-soft' . ($padding ? ' p-5 sm:p-6' : '')]) }}>
    {{ $slot }}
</div>
