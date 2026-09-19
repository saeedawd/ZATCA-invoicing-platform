@props([
    'status' => null,
    'type' => 'invoice',
])

@php
$value = strtolower((string) $status);

if ($type === 'zatca') {
    $labels = [
        'onboarded' => 'مربوط',
        'pending' => 'قيد الانتظار',
        'failed' => 'فشل',
        'draft' => 'مسودة',
        'signed' => 'موقّعة',
        'cleared' => 'مقبولة',
        'reported' => 'مبلّغ',
        'accepted' => 'مقبول',
        'warning' => 'مقبول مع تنبيه',
        'rejected' => 'مرفوض',
        'submitted' => 'مُرسل',
    ];

    $map = match (true) {
        in_array($value, ['cleared', 'reported', 'accepted', 'onboarded', 'warning'], true) => ['label' => $labels[$value] ?? $status, 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/15'],
        in_array($value, ['rejected', 'failed'], true) => ['label' => $labels[$value] ?? $status, 'class' => 'bg-rose-50 text-rose-700 ring-rose-600/15'],
        in_array($value, ['pending', 'queued', 'submitted', 'signed'], true) => ['label' => $labels[$value] ?? $status, 'class' => 'bg-amber-50 text-amber-800 ring-amber-600/15'],
        default => ['label' => $labels[$value] ?? ($status ?: '—'), 'class' => 'bg-slate-50 text-slate-600 ring-slate-500/15'],
    };
} elseif ($type === 'payment') {
    $map = match ($value) {
        'paid' => ['label' => 'مدفوعة', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/15'],
        'partial' => ['label' => 'مدفوعة جزئياً', 'class' => 'bg-amber-50 text-amber-800 ring-amber-600/15'],
        'unpaid' => ['label' => 'غير مدفوعة', 'class' => 'bg-rose-50 text-rose-700 ring-rose-600/15'],
        default => ['label' => $status ?: '—', 'class' => 'bg-slate-50 text-slate-600 ring-slate-500/15'],
    };
} elseif ($type === 'quote') {
    $map = match ($value) {
        'sent' => ['label' => 'مُرسل', 'class' => 'bg-sky-50 text-sky-700 ring-sky-600/15'],
        'accepted' => ['label' => 'مقبول', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/15'],
        'converted' => ['label' => 'محوّل لفاتورة', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/15'],
        'rejected', 'expired' => ['label' => \App\Support\Labels::quoteStatus($value), 'class' => 'bg-rose-50 text-rose-700 ring-rose-600/15'],
        'draft' => ['label' => 'مسودة', 'class' => 'bg-slate-100 text-slate-600 ring-slate-500/15'],
        default => ['label' => $status ?: '—', 'class' => 'bg-slate-50 text-slate-600 ring-slate-500/15'],
    };
} else {
    $map = match ($value) {
        'issued' => ['label' => 'صادرة', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/15'],
        'draft' => ['label' => 'مسودة', 'class' => 'bg-slate-100 text-slate-600 ring-slate-500/15'],
        'cancelled', 'canceled' => ['label' => 'ملغاة', 'class' => 'bg-rose-50 text-rose-700 ring-rose-600/15'],
        default => ['label' => $status ?: '—', 'class' => 'bg-slate-50 text-slate-600 ring-slate-500/15'],
    };
}
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ' . $map['class']]) }}>
    {{ $map['label'] }}
</span>
