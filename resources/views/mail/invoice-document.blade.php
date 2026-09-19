<x-mail::message>
# {{ $invoice->direction === 'purchase' ? 'فاتورة مشتريات' : 'فاتورة' }}

مرحباً {{ $invoice->party?->name ?: '' }}،

مرفق لكم {{ $invoice->direction === 'purchase' ? 'فاتورة المشتريات' : 'الفاتورة' }} رقم **{{ $invoice->invoice_number }}** من **{{ $organizationName }}**.

- التاريخ: {{ optional($invoice->issue_date)->format('Y-m-d') }}
- الإجمالي: {{ number_format((float) $invoice->total_amount, 2) }} ر.س
@if ($invoice->status === 'issued')
- المدفوع: {{ number_format((float) $invoice->amount_paid, 2) }} ر.س
- المتبقي: {{ number_format((float) $invoice->balance_due, 2) }} ر.س
@endif

ملف PDF مرفق مع هذه الرسالة.

شكراً لتعاملكم معنا،  
{{ $organizationName }}
</x-mail::message>
