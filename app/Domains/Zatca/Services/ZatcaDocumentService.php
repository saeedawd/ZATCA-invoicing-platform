<?php

namespace App\Domains\Zatca\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Zatca\Models\ZatcaDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ZatcaDocumentService
{
    public function __construct(
        protected ZatcaXmlBuilder $xmlBuilder,
        protected ZatcaSigner $signer,
        protected ZatcaHashService $hashService,
        protected ZatcaQrEncoder $qrEncoder,
    ) {}

    public function prepareIssuedInvoice(Invoice $invoice): Invoice
    {
        $organization = $invoice->tenant?->organization ?? $invoice->tenant()->first()?->organization;

        if (! $organization) {
            $invoice->load('tenant.organization');
            $organization = $invoice->tenant?->organization;
        }

        if (! $organization) {
            throw new RuntimeException('بيانات المنشأة غير مكتملة.');
        }

        $device = ZatcaDevice::query()
            ->where('tenant_id', $invoice->tenant_id)
            ->where('status', 'onboarded')
            ->where('environment', config('zatca.simulation_mode') ? 'sandbox' : 'production')
            ->latest('id')
            ->first();

        if (! $device) {
            $device = ZatcaDevice::query()
                ->where('tenant_id', $invoice->tenant_id)
                ->where('status', 'onboarded')
                ->latest('id')
                ->first();
        }

        return DB::transaction(function () use ($invoice, $organization, $device) {
            $previousHash = $device?->last_invoice_hash ?: ZatcaHashService::INITIAL_HASH;
            $counter = $device ? $device->nextCounter() : 1;

            $xml = $this->xmlBuilder->build($invoice, $organization, $previousHash, $counter);

            if ($device) {
                $xml = $this->signer->sign($xml, $device);
            }

            $hash = $this->hashService->hashXml($xml);
            $qr = $this->qrEncoder->encode([
                'seller_name' => $organization->legal_name_ar,
                'vat_number' => (string) $organization->vat_number,
                'timestamp' => $invoice->issue_date?->format('Y-m-d').'T'.($invoice->issue_time ?: '00:00:00'),
                'total' => number_format((float) $invoice->total_amount, 2, '.', ''),
                'vat_total' => number_format((float) $invoice->tax_amount, 2, '.', ''),
                'hash' => $hash,
            ]);

            $xmlPath = sprintf('tenants/%d/invoices/%s.xml', $invoice->tenant_id, $invoice->uuid);
            Storage::disk('local')->put($xmlPath, $xml);

            $invoice->update([
                'previous_hash' => $previousHash,
                'invoice_hash' => $hash,
                'qr_tlv_base64' => $qr,
                'xml_path' => $xmlPath,
                'counter_value' => $counter,
                'zatca_device_id' => $device?->id,
                'zatca_status' => 'signed',
            ]);

            if ($device) {
                $device->update([
                    'last_invoice_hash' => $hash,
                    'invoice_counter' => $counter,
                ]);
            }

            return $invoice->fresh();
        });
    }
}
