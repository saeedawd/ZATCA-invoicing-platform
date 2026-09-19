@if ($showQr ?? true)
    <div class="qr-wrap">
        @if ($qrDataUri)
            <img src="{{ $qrDataUri }}" alt="ZATCA QR">
            <div class="tiny bold" style="margin-top:5px; color: {{ $brandPrimary }};">رمز الاستجابة السريعة لهيئة الزكاة والضريبة والجمارك</div>
        @else
            <div class="small muted">رمز الزكاة غير متاح</div>
        @endif
    </div>
@endif

<div class="footer ar">
    {{ $invoiceFooter }}
</div>
