@php
    $border = $borderColor ?? '#d7e4dd';
    $hidePayments = (bool) ($hidePaymentSummary ?? false);
@endphp

<table>
    <tr>
        <td width="44%">
            <table class="totals" style="padding:0; border:1px solid {{ $border }}; border-collapse:separate; overflow:hidden;">
                <tr class="totals-head">
                    <td class="ar" colspan="2">ملخص المبالغ</td>
                </tr>
                <tr class="totals-row">
                    <td class="ar amount" width="46%">{{ number_format((float) $invoice->taxable_amount, 2) }} ر.س</td>
                    <td class="ar label" width="54%">قبل الضريبة</td>
                </tr>
                @if ((float) $invoice->discount_amount > 0)
                    <tr class="totals-row">
                        <td class="ar amount">{{ number_format((float) $invoice->discount_amount, 2) }} ر.س</td>
                        <td class="ar label">الخصم</td>
                    </tr>
                @endif
                <tr class="totals-row">
                    <td class="ar amount">{{ number_format((float) $invoice->tax_amount, 2) }} ر.س</td>
                    <td class="ar label">ضريبة القيمة المضافة</td>
                </tr>
                <tr class="payable">
                    <td class="ar amount">{{ number_format((float) $invoice->payable_amount, 2) }} ر.س</td>
                    <td class="ar label">الإجمالي</td>
                </tr>
                @unless ($hidePayments)
                    @php
                        $amountPaid = round((float) ($invoice->amount_paid ?? 0), 2);
                        $balanceDue = round((float) ($invoice->balance_due ?? $invoice->payable_amount ?? $invoice->total_amount ?? 0), 2);
                    @endphp
                    <tr class="amount-paid">
                        <td class="ar amount">{{ number_format($amountPaid, 2) }} ر.س</td>
                        <td class="ar label">المدفوع</td>
                    </tr>
                    <tr class="balance-due">
                        <td class="ar amount">{{ number_format($balanceDue, 2) }} ر.س</td>
                        <td class="ar label">المتبقي</td>
                    </tr>
                @endunless
            </table>
        </td>
        <td width="4%"></td>
        <td width="52%" class="ar">
            @if ($invoice->note)
                <div class="box">
                    <div class="box-title">ملاحظات</div>
                    <div class="small">{{ $invoice->note }}</div>
                </div>
            @endif
        </td>
    </tr>
</table>
