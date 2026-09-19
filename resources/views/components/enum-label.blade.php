@props([
    'type',
    'value' => null,
])

@php
    use App\Support\Labels;
@endphp

<span {{ $attributes }}>{{ Labels::resolve($type, is_scalar($value) ? (string) $value : null) }}</span>
