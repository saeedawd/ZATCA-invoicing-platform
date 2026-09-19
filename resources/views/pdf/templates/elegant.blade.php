<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @include('pdf.templates.partials.base-styles')
        @page { margin: 13mm 12mm; }

        .header {
            background: #faf7f2;
            border: 1px solid #eadfcf;
            padding: 14px 16px;
        }
        .gold-line {
            height: 2px;
            background: {{ $brandSecondary }};
            margin: 0 0 14px;
        }
        .brand-name { color: {{ $brandPrimary }}; font-size: 17px; font-weight: bold; }
        .doc-title { color: {{ $brandSecondary }}; }

        .meta td { padding: 3px 0; font-size: 11px; }
        .meta .label { color: #78716c; width: 34%; }
        .meta .value { font-weight: bold; width: 66%; }

        .box {
            border: 1px solid #eadfcf;
            padding: 10px 12px;
            background: #fffdf9;
        }
        .box-title {
            color: {{ $brandSecondary }};
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
            border-bottom: 1px solid #f0e6d8;
            padding-bottom: 4px;
        }

        .items thead th {
            background: #faf7f2;
            color: {{ $brandPrimary }};
            font-size: 10px;
            padding: 8px 5px;
            border: 1px solid #eadfcf;
        }
        .items tbody td {
            border: 1px solid #f0e6d8;
            padding: 7px 5px;
            font-size: 10px;
        }
        .items tbody tr:nth-child(even) td { background: #fcfaf7; }

        .totals td {
            padding: 7px 10px;
            font-size: 11px;
            border-bottom: 1px solid #f0e6d8;
        }
        .totals .totals-head td {
            background: #faf7f2;
            color: {{ $brandSecondary }};
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px solid #eadfcf;
            padding: 8px 10px;
        }
        .totals .totals-row .label { color: #78716c; }
        .totals .totals-row .amount { font-weight: bold; color: #292524; }
        .totals .payable td {
            background: {{ $brandPrimary }};
            color: #fff;
            font-weight: bold;
            font-size: 12px;
            border: none;
            padding: 9px 10px;
        }
        .totals .amount-paid td {
            background: #ecfdf5;
            color: #047857;
            font-weight: bold;
            border-bottom: 1px solid #d1fae5;
            padding: 8px 10px;
        }
        .totals .balance-due td {
            background: #fff7ed;
            color: #c2410c;
            font-weight: bold;
            border: none;
            padding: 9px 10px;
        }

        .qr-wrap {
            border: 1px dashed {{ $brandSecondary }};
            background: #fffdf9;
            padding: 10px;
            text-align: center;
        }
        .qr-wrap img { width: 105px; height: 105px; }

        .footer {
            margin-top: 10px;
            border-top: 1px solid #eadfcf;
            padding-top: 6px;
            color: #78716c;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header ar">
        <table>
            <tr>
                <td width="70%" class="ar middle">
                    <div class="brand-name">{{ $sellerNameAr }}</div>
                    <div class="small muted" style="margin-top:4px;">الرقم الضريبي: {{ $organization?->vat_number ?: '—' }}
                        @if ($organization?->cr_number)
                            | السجل التجاري: {{ $organization->cr_number }}
                        @endif
                    </div>
                    <div class="small muted">{{ $sellerAddressAr }}</div>
                </td>
                <td width="30%" class="center middle">
                    @if ($logoDataUri)
                        <img class="logo-img" src="{{ $logoDataUri }}" alt="logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <div class="gold-line"></div>

    <table class="ar" style="margin-bottom:12px;">
        <tr>
            <td width="58%" class="middle">
                <div class="h2 doc-title">{{ $documentTitleAr }}</div>
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
            <td width="42%">
                <div class="box ar">
                    <div class="box-title">المشتري</div>
                    <div class="bold" style="font-size:12px;">{{ $invoice->party?->name ?: 'عميل نقدي' }}</div>
                    @if ($invoice->party?->vat_number)
                        <div class="small muted" style="margin-top:4px;">الرقم الضريبي: {{ $invoice->party->vat_number }}</div>
                    @endif
                    @if ($buyerAddress !== '—')
                        <div class="small muted">{{ $buyerAddress }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    @include('pdf.templates.partials.items')
    <div class="gap"></div>
    @include('pdf.templates.partials.totals', ['borderColor' => '#eadfcf'])
    <div class="gap"></div>
    @include('pdf.templates.partials.qr-footer')
</body>
</html>
