@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-slate-200 bg-white text-sm text-slate-800 shadow-sm focus:border-brand-600 focus:ring-brand-600']) }}>
