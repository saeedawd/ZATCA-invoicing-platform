<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @include('pdf.templates.partials.base-styles')
        @page { margin: 0; }

        .page-pad { padding: 12mm 11mm 12mm 14mm; }
        .sidebar {
            width: 10px;
            background: {{ $brandPrimary }};
        }
        .header-band {
            background: {{ $brandPrimary }};
            color: #fff;
            padding: 16px 18px;
        }
        .header-band .sub { color: #ffffffcc; font-size: 10px; margin-top: 3px; }
        .secondary-strip {
            height: 4px;
            background: {{ $brandSecondary }};
            margin-bottom: 14px;
        }

        .meta td { padding: 3px 0; font-size: 11px; }
        .meta .label { color: #607069; width: 34%; }
        .meta .value { font-weight: bold; width: 66%; }

        .box {
            border: 1px solid #d1e5e2;
            padding: 10px 12px;
            background: #f8fffe;
        }
        .box-title {
            color: {{ $brandPrimary }};
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
            border-bottom: 1px solid #dcefee;
            padding-bottom: 4px;
        }

        .items thead th {
            background: {{ $brandPrimary }};
            color: #fff;
            font-size: 10px;
            padding: 8px 5px;
            border: 1px solid {{ $brandPrimary }};
        }
        .items tbody td {
            border: 1px solid #dce8e2;
            padding: 7px 5px;
            font-size: 10px;
        }
        .items tbody tr:nth-child(even) td { background: #f3fbfa; }

        .totals td {
            padding: 7px 10px;
            font-size: 11px;
            border-bottom: 1px solid #e0efec;
        }
        .totals .totals-head td {
            background: #f0f7f6;
            color: {{ $brandPrimary }};
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px solid #d1e5e2;
            padding: 8px 10px;
        }
        .totals .totals-row .label { color: #6d7a73; }
        .totals .totals-row .amount { font-weight: bold; color: #1c2621; }
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
            border: 1px solid {{ $brandSecondary }};
            background: #fff;
            padding: 10px;
            text-align: center;
        }
        .qr-wrap img { width: 105px; height: 105px; }

        .footer {
            margin-top: 10px;
            border-top: 1px solid #d1e5e2;
            padding-top: 6px;
            color: #6d7a73;
            font-size: 8px;
        }

        .accent { color: {{ $brandPrimary }}; }
    </style>
</head>
<body>
    <table style="width:100%;">
        <tr>
            <td class="sidebar"></td>
            <td>
                <div class="header-band ar">
                    <table>
                        <tr>
                            <td width="72%" class="ar middle">
                                <div class="h1" style="color:#fff;">{{ $sellerNameAr }}</div>
                                <div class="sub">الرقم الضريبي: {{ $organization?->vat_number ?: '—' }}
                                    @if ($organization?->cr_number)
                                        &nbsp;|&nbsp; السجل التجاري: {{ $organization->cr_number }}
                                    @endif
                                </div>
                                <div class="sub">{{ $sellerAddressAr }}</div>
                            </td>
                            <td width="28%" class="center middle">
                                @if ($logoDataUri)
                                    <img class="logo-img" src="{{ $logoDataUri }}" alt="logo" style="background:#fff;padding:4px;">
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="secondary-strip"></div>

                <div class="page-pad" style="padding-top:0;">
                    <table class="ar" style="margin-bottom:12px;">
                        <tr>
                            <td width="58%" class="middle">
                                <div class="h2 accent">{{ $documentTitleAr }}</div>
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
                    @include('pdf.templates.partials.totals', ['borderColor' => '#d1e5e2'])
                    <div class="gap"></div>
                    @include('pdf.templates.partials.qr-footer')
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
