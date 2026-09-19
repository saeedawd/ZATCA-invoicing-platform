<x-mail::message>
# عرض سعر

مرحباً {{ $quote->party?->name ?: '' }}،

مرفق لكم عرض السعر رقم **{{ $quote->quote_number }}** من **{{ $organizationName }}**.

- تاريخ العرض: {{ optional($quote->issue_date)->format('Y-m-d') }}
- صالح حتى: {{ optional($quote->valid_until)->format('Y-m-d') ?: '—' }}
- الإجمالي: {{ number_format((float) $quote->total_amount, 2) }} ر.س

ملف PDF مرفق مع هذه الرسالة.

شكراً لاهتمامكم،  
{{ $organizationName }}
</x-mail::message>
