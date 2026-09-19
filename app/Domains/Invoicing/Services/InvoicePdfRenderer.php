<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Catalog\Models\Party;
use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Invoicing\Models\InvoiceLine;
use App\Domains\Invoicing\Support\InvoiceTemplateCatalog;
use App\Domains\Organization\Models\Organization;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoicePdfRenderer
{
    public function render(Invoice $invoice): string
    {
        $invoice->loadMissing(['party', 'lines', 'taxTotals', 'tenant.organization']);

        $organization = $invoice->tenant?->organization;
        $pdfBinary = $this->makePdfBinary($invoice, $organization);

        $path = sprintf('tenants/%d/invoices/%s.pdf', $invoice->tenant_id, $invoice->uuid);
        Storage::disk('local')->put($path, $pdfBinary);
        $invoice->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * @param  array{invoice_template?: string, show_logo?: bool, brand_primary?: string, brand_secondary?: string, invoice_footer?: ?string, logo_path?: ?string}  $brandingOverrides
     */
    public function previewBinary(Organization $organization, array $brandingOverrides = []): string
    {
        $invoice = $this->sampleInvoice($organization);

        return $this->makePdfBinary($invoice, $organization, $brandingOverrides);
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  array<int, array<string, mixed>>  $lines
     */
    public function previewDraftBinary(Organization $organization, array $header, array $lines, ?Party $party = null): string
    {
        $calculated = app(InvoiceCalculator::class)->calculate(
            $lines,
            (float) ($header['discount_amount'] ?? 0)
        );

        $totals = $calculated['totals'];

        $invoice = new Invoice([
            'tenant_id' => $organization->tenant_id,
            'uuid' => (string) Str::uuid(),
            'direction' => $header['direction'] ?? 'sales',
            'invoice_type' => $header['invoice_type'] ?? 'simplified',
            'document_type_code' => $header['document_type_code'] ?? '388',
            'invoice_number' => 'معاينة',
            'issue_date' => Carbon::parse($header['issue_date'] ?? now()->toDateString()),
            'issue_time' => now()->format('H:i:s'),
            'currency' => 'SAR',
            'note' => $header['note'] ?? null,
            'line_extension_amount' => $totals['line_extension_amount'],
            'discount_amount' => $totals['discount_amount'],
            'taxable_amount' => $totals['taxable_amount'],
            'tax_amount' => $totals['tax_amount'],
            'total_amount' => $totals['total_amount'],
            'prepaid_amount' => $totals['prepaid_amount'],
            'payable_amount' => $totals['payable_amount'],
            'amount_paid' => 0,
            'balance_due' => $totals['payable_amount'],
            'payment_status' => 'unpaid',
            'qr_tlv_base64' => base64_encode('DRAFT-PREVIEW'),
            'status' => 'draft',
        ]);

        $invoiceLines = collect($calculated['lines'])->map(function (array $line) {
            return new InvoiceLine([
                'line_no' => $line['line_no'],
                'description' => $line['description'] ?? '',
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'discount' => $line['discount'] ?? 0,
                'tax_rate' => $line['tax_rate'],
                'tax_category' => $line['tax_category'] ?? 'S',
                'unit_code' => $line['unit_code'] ?? 'PCE',
                'line_net' => $line['line_net'],
                'line_tax' => $line['line_tax'],
                'line_total' => $line['line_total'],
            ]);
        });

        $invoice->setRelation('party', $party);
        $invoice->setRelation('lines', $invoiceLines);
        $invoice->setRelation('taxTotals', collect());

        return $this->makePdfBinary($invoice, $organization);
    }

    /**
     * @param  array{invoice_template?: string, show_logo?: bool, brand_primary?: string, brand_secondary?: string, invoice_footer?: ?string, logo_path?: ?string}  $brandingOverrides
     */
    protected function makePdfBinary(Invoice $invoice, ?Organization $organization, array $brandingOverrides = []): string
    {
        $html = view(
            InvoiceTemplateCatalog::viewName($this->resolveTemplateKey($organization, $brandingOverrides)),
            $this->viewData($invoice, $organization, $brandingOverrides)
        )->render();

        $html = $this->shapeArabicHtml($html);

        return Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'tahoma')
            ->setOption('fontDir', storage_path('fonts'))
            ->setOption('fontCache', storage_path('fonts'))
            ->output();
    }

    /**
     * @param  array{invoice_template?: string, show_logo?: bool, brand_primary?: string, brand_secondary?: string, invoice_footer?: ?string, logo_path?: ?string}  $brandingOverrides
     * @return array<string, mixed>
     */
    protected function viewData(Invoice $invoice, ?Organization $organization, array $brandingOverrides = []): array
    {
        $showLogo = array_key_exists('show_logo', $brandingOverrides)
            ? (bool) $brandingOverrides['show_logo']
            : (bool) ($organization?->show_logo ?? true);

        $brandPrimary = $brandingOverrides['brand_primary']
            ?? $organization?->brand_primary
            ?? '#0b5c41';

        $brandSecondary = $brandingOverrides['brand_secondary']
            ?? $organization?->brand_secondary
            ?? '#c9a24b';

        $footer = array_key_exists('invoice_footer', $brandingOverrides)
            ? $brandingOverrides['invoice_footer']
            : ($organization?->invoice_footer);

        $logoPath = array_key_exists('logo_path', $brandingOverrides)
            ? $brandingOverrides['logo_path']
            : $organization?->logo_path;

        return [
            'invoice' => $invoice,
            'organization' => $organization,
            'qrDataUri' => ($brandingOverrides['show_qr'] ?? true)
                ? $this->buildQrDataUri($invoice->qr_tlv_base64, $brandPrimary)
                : null,
            'showQr' => (bool) ($brandingOverrides['show_qr'] ?? true),
            'hidePaymentSummary' => (bool) ($brandingOverrides['hide_payment_summary'] ?? false),
            'documentTitleAr' => $brandingOverrides['document_title_ar'] ?? $this->documentTitleAr($invoice),
            'documentTitleEn' => $brandingOverrides['document_title_en'] ?? $this->documentTitleEn($invoice),
            'documentNumberLabel' => $brandingOverrides['document_number_label'] ?? 'رقم الفاتورة',
            'documentExtraLabel' => $brandingOverrides['document_extra_label'] ?? 'الوقت',
            'documentExtraValue' => $brandingOverrides['document_extra_value'] ?? ($invoice->issue_time ?: '—'),
            'fontRegular' => $this->fontPath('tahoma.ttf'),
            'fontBold' => $this->fontPath('tahomabd.ttf'),
            'sellerNameAr' => $organization?->legal_name_ar ?: 'المنشأة',
            'sellerNameEn' => $organization?->legal_name_en ?: ($organization?->legal_name_ar ?: 'Company'),
            'sellerAddressAr' => $this->addressAr($organization),
            'sellerAddressEn' => $this->addressEn($organization),
            'buyerAddress' => $this->partyAddress($invoice),
            'brandPrimary' => $brandPrimary,
            'brandSecondary' => $brandSecondary,
            'showLogo' => $showLogo,
            'logoDataUri' => $showLogo ? $this->logoDataUri($logoPath) : null,
            'invoiceFooter' => array_key_exists('invoice_footer', $brandingOverrides) && filled($brandingOverrides['invoice_footer'])
                ? $brandingOverrides['invoice_footer']
                : (filled($footer)
                    ? $footer
                    : 'مستند إلكتروني صادر عبر منصة فواتير زاتكا ومتوافق مع متطلبات هيئة الزكاة والضريبة والجمارك.'),
        ];
    }

    /**
     * @param  array<string, mixed>  $brandingOverrides
     */
    public function renderBinary(Invoice $invoice, ?Organization $organization = null, array $brandingOverrides = []): string
    {
        return $this->makePdfBinary($invoice, $organization, $brandingOverrides);
    }

    /**
     * @param  array{invoice_template?: string}  $brandingOverrides
     */
    protected function resolveTemplateKey(?Organization $organization, array $brandingOverrides = []): string
    {
        $key = $brandingOverrides['invoice_template']
            ?? $organization?->invoice_template
            ?? InvoiceTemplateCatalog::DEFAULT;

        return InvoiceTemplateCatalog::resolve((string) $key);
    }

    protected function sampleInvoice(Organization $organization): Invoice
    {
        $invoice = new Invoice([
            'tenant_id' => $organization->tenant_id,
            'uuid' => (string) Str::uuid(),
            'direction' => 'sales',
            'invoice_type' => 'simplified',
            'document_type_code' => '388',
            'invoice_number' => 'PREVIEW-001',
            'issue_date' => Carbon::today(),
            'issue_time' => now()->format('H:i:s'),
            'currency' => 'SAR',
            'note' => 'هذه معاينة لتصميم الفاتورة فقط.',
            'line_extension_amount' => 200,
            'discount_amount' => 0,
            'taxable_amount' => 200,
            'tax_amount' => 30,
            'total_amount' => 230,
            'prepaid_amount' => 0,
            'payable_amount' => 230,
            'amount_paid' => 100,
            'balance_due' => 130,
            'payment_status' => 'partial',
            'qr_tlv_base64' => base64_encode('ZATCA-PREVIEW'),
            'status' => 'issued',
        ]);

        $party = new Party([
            'name' => 'عميل تجريبي',
            'vat_number' => '300000000000003',
            'phone' => '0500000000',
            'city' => 'الرياض',
            'street' => 'طريق الملك',
            'district' => 'العليا',
            'building_number' => '1234',
            'postal_code' => '12345',
        ]);

        $lines = new Collection([
            new InvoiceLine([
                'line_no' => 1,
                'description' => 'خدمة استشارية',
                'quantity' => 1,
                'unit_price' => 120,
                'tax_rate' => 15,
                'line_total' => 138,
            ]),
            new InvoiceLine([
                'line_no' => 2,
                'description' => 'منتج تجريبي',
                'quantity' => 2,
                'unit_price' => 40,
                'tax_rate' => 15,
                'line_total' => 92,
            ]),
        ]);

        $invoice->setRelation('party', $party);
        $invoice->setRelation('lines', $lines);
        $invoice->setRelation('taxTotals', new Collection);

        return $invoice;
    }

    protected function logoDataUri(?string $logoPath): ?string
    {
        if (! filled($logoPath) || ! Storage::disk('public')->exists($logoPath)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($logoPath) ?: 'image/png';
        $contents = Storage::disk('public')->get($logoPath);

        if ($contents === null || $contents === false || $contents === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    protected function addressAr(?Organization $organization): string
    {
        if (! $organization) {
            return '—';
        }

        return collect([
            $organization->building_number ? 'مبنى '.$organization->building_number : null,
            $organization->street,
            $organization->district,
            $organization->city,
            $organization->postal_code,
            $organization->country ?: 'SA',
        ])->filter()->implode('، ') ?: '—';
    }

    protected function addressEn(?Organization $organization): string
    {
        if (! $organization) {
            return '—';
        }

        return collect([
            $organization->building_number ? 'Bldg '.$organization->building_number : null,
            $organization->street,
            $organization->district,
            $organization->city,
            $organization->postal_code,
            $organization->country ?: 'SA',
        ])->filter()->implode(', ') ?: '—';
    }

    protected function partyAddress(Invoice $invoice): string
    {
        $party = $invoice->party;

        if (! $party) {
            return '—';
        }

        return collect([
            $party->building_number,
            $party->street,
            $party->district,
            $party->city,
            $party->postal_code,
        ])->filter()->implode(' - ') ?: '—';
    }

    protected function fontPath(string $filename): string
    {
        return str_replace('\\', '/', storage_path('fonts/'.$filename));
    }

    protected function shapeArabicHtml(string $html): string
    {
        $arabic = new Arabic;
        $positions = $arabic->arIdentify($html);

        for ($i = count($positions) - 1; $i >= 1; $i -= 2) {
            $start = $positions[$i - 1];
            $length = $positions[$i] - $positions[$i - 1];
            $segment = substr($html, $start, $length);
            $shaped = $arabic->utf8Glyphs($segment, 500, false, true);
            $html = substr_replace($html, $shaped, $start, $length);
        }

        return $html;
    }

    protected function buildQrDataUri(?string $payload, string $brandPrimary = '#0b5c41'): ?string
    {
        if (! filled($payload)) {
            return null;
        }

        [$r, $g, $b] = $this->hexToRgb($brandPrimary);

        $result = (new Builder(
            writer: new PngWriter,
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 240,
            margin: 6,
            foregroundColor: new Color($r, $g, $b),
            backgroundColor: new Color(255, 255, 255),
        ))->build();

        return 'data:image/png;base64,'.base64_encode($result->getString());
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return [11, 92, 65];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    protected function documentTitleAr(Invoice $invoice): string
    {
        return match ($invoice->document_type_code) {
            '381' => 'إشعار دائن',
            '383' => 'إشعار مدين',
            default => $invoice->isSimplified() ? 'فاتورة ضريبية مبسطة' : 'فاتورة ضريبية',
        };
    }

    protected function documentTitleEn(Invoice $invoice): string
    {
        return match ($invoice->document_type_code) {
            '381' => 'Credit Note',
            '383' => 'Debit Note',
            default => $invoice->isSimplified() ? 'Simplified Tax Invoice' : 'Tax Invoice',
        };
    }
}
