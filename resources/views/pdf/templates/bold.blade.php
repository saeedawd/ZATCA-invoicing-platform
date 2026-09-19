<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @include('pdf.templates.partials.base-styles')
        @page { margin: 10mm 10mm; }

        .header {
            background: {{ $brandPrimary }};
            color: #fff;
            padding: 18px 16px;
        }
        .header .sub { color: #ffffffcc; font-size: 10px; margin-top: 4px; }
        .accent-bar {
            height: 6px;
            background: {{ $brandSecondary }};
            margin-bottom: 12px;
        }

        .meta td { padding: 3px 0; font-size: 11px; }
        .meta .label { color: #4b5563; width: 34%; }
        .meta .value { font-weight: bold; width: 66%; }

        .box {
            border: 2px solid {{ $brandPrimary }};
            padding: 10px 12px;
            background: #fff;
        }
        .box-title {
            color: {{ $brandPrimary }};
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
            border-bottom: 2px solid {{ $brandSecondary }};
            padding-bottom: 4px;
        }

        .items thead th {
            background: {{ $brandPrimary }};
            color: #fff;
            font-size: 10px;
            padding: 9px 5px;
            border: 1px solid {{ $brandPrimary }};
        }
        .items tbody td {
            border: 1px solid #cfd8d3;
            padding: 8px 5px;
            font-size: 10px;
        }
        .items tbody tr:nth-child(even) td { background: #eef6f1; }

        .totals td {
            padding: 8px 10px;
            font-size: 11px;
            border-bottom: 1px solid #d7e4dd;
        }
        .totals .totals-head td {
            background: {{ $brandPrimary }};
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            border: none;
            padding: 8px 10px;
        }
        .totals .totals-row .label { color: #4b5563; }
        .totals .totals-row .amount { font-weight: bold; color: #1c1917; }
        .totals .payable td {
            background: {{ $brandSecondary }};
            color: #1c1917;
            font-weight: bold;
            font-size: 13px;
            border: none;
            padding: 10px;
        }
        .totals .amount-paid td {
            background: #d1fae5;
            color: #065f46;
            font-weight: bold;
            border-bottom: 1px solid #a7f3d0;
            padding: 9px 10px;
        }
        .totals .balance-due td {
            background: #ffedd5;
            color: #9a3412;
            font-weight: bold;
            border: none;
            padding: 10px;
        }

        .qr-wrap {
            border: 2px solid {{ $brandPrimary }};
            background: #f4faf7;
            padding: 12px;
            text-align: center;
        }
        .qr-wrap img { width: 120px; height: 120px; }

        .footer {
            margin-top: 10px;
            border-top: 2px solid {{ $brandPrimary }};
            padding-top: 6px;
            color: #4b5563;
            font-size: 8px;
        }

        .accent { color: {{ $brandPrimary }}; }
    </style>
</head>
<body>
    <div class="header ar">
        <table>
            <tr>
                <td width="68%" class="ar middle">
                    <div class="h1" style="color:#fff; font-size:20px;">{{ $sellerNameAr }}</div>
                    <div class="sub">الرقم الضريبي: {{ $organization?->vat_number ?: '—' }}
                        @if ($organization?->cr_number)
                            &nbsp;|&nbsp; السجل التجاري: {{ $organization->cr_number }}
                        @endif
                    </div>
                    <div class="sub">{{ $sellerAddressAr }}</div>
                </td>
                <td width="32%" class="center middle">
                    @if ($logoDataUri)
                        <img class="logo-img" src="{{ $logoDataUri }}" alt="logo" style="background:#fff;padding:6px;max-height:60px;">
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div class="accent-bar"></div>

    <table class="ar" style="margin-bottom:12px;">
        <tr>
            <td width="55%" class="middle">
                <div class="h2 accent" style="font-size:15px;">{{ $documentTitleAr }}</div>
                <div class="gap-sm"></div>
                <table class="meta">
                    <tr>
                        <td class="label">{{ $documentNumberLabel ?? 'رقم الفاتورة' }}</td>
                        <td class="value" style="text-align:right;">{{ $invoice->invoice_number ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="label">التاريخ</td>
                        <td class="value" style="text-align:right;">{{ optional($invoice->issue_date)->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td class="label">{{ $documentExtraLabel ?? 'الوقت' }}</td>
                        <td class="value" style="text-align:right;">{{ $documentExtraValue ?? ($invoice->issue_time ?: '—') }}</td>
                    </tr>
                </table>
            </td>
            <td width="45%">
                <div class="box ar">
                    <div class="box-title">المشتري</div>
                    <div class="bold" style="font-size:12px;">{{ $invoice->party?->name ?: 'عميل نقدي' }}</div>
                    @if ($invoice->party?->vat_number)
                        <div class="small muted" style="margin-top:4px;">الرقم الضريبي: {{ $invoice->party->vat_number }}</div>
                    @endif
                    @if ($buyerAddress !== '—')
                        <div class="small muted">{{ $buyerAddress }}</div>
                    @endif
                    @if ($invoice->party?->phone)
                        <div class="small muted">{{ $invoice->party->phone }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @include('pdf.templates.partials.items')
    <div class="gap"></div>
    @include('pdf.templates.partials.totals', ['borderColor' => $brandPrimary])
    <div class="gap"></div>
    @include('pdf.templates.partials.qr-footer')
</body>
</html>
