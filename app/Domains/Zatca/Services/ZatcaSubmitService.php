<?php

namespace App\Domains\Zatca\Services;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Zatca\Models\ZatcaSubmission;
use App\Domains\Zatca\Support\ZatcaEnvironment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ZatcaSubmitService
{
    public function submit(Invoice $invoice): ZatcaSubmission
    {
        $invoice->loadMissing(['device', 'tenant.organization']);

        $type = $invoice->isSimplified() ? 'reporting' : 'clearance';
        $idempotencyKey = $invoice->idempotency_key ?: 'zatca-'.$invoice->uuid;

        $existing = ZatcaSubmission::query()
            ->where('invoice_id', $invoice->id)
            ->where('idempotency_key', $idempotencyKey)
            ->whereIn('status', ['accepted', 'warning'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $submission = ZatcaSubmission::create([
            'tenant_id' => $invoice->tenant_id,
            'invoice_id' => $invoice->id,
            'zatca_device_id' => $invoice->zatca_device_id,
            'type' => $type,
            'status' => 'pending',
            'request_xml_path' => $invoice->xml_path,
            'idempotency_key' => $idempotencyKey,
            'submitted_at' => now(),
        ]);

        $invoice->update(['zatca_status' => 'submitted', 'idempotency_key' => $idempotencyKey]);

        if (config('zatca.simulation_mode') || ! $invoice->device) {
            return $this->simulateAcceptance($submission, $invoice, $type);
        }

        return $this->sendToZatca($submission, $invoice, $type);
    }

    protected function simulateAcceptance(ZatcaSubmission $submission, Invoice $invoice, string $type): ZatcaSubmission
    {
        $response = [
            'status' => 'REPORTED',
            'reportingStatus' => 'REPORTED',
            'clearanceStatus' => 'CLEARED',
            'simulation' => true,
            'uuid' => $invoice->uuid_zatca,
            'hash' => $invoice->invoice_hash,
        ];

        $submission->update([
            'status' => 'accepted',
            'response_json' => $response,
            'reporting_status' => $type === 'reporting' ? 'REPORTED' : null,
            'clearance_status' => $type === 'clearance' ? 'CLEARED' : null,
            'responded_at' => now(),
        ]);

        $invoice->update([
            'zatca_status' => $type === 'clearance' ? 'cleared' : 'reported',
        ]);

        return $submission->fresh();
    }

    protected function sendToZatca(ZatcaSubmission $submission, Invoice $invoice, string $type): ZatcaSubmission
    {
        $device = $invoice->device;
        $baseUrl = ZatcaEnvironment::baseUrl((string) $device->environment);

        $endpoint = $type === 'clearance'
            ? '/invoices/clearance/single'
            : '/invoices/reporting/single';

        $xml = Storage::disk('local')->get((string) $invoice->xml_path);

        $response = Http::withBasicAuth(
            (string) $device->binary_security_token,
            (string) $device->secret_encrypted
        )->withHeaders([
            'Accept-Version' => 'V2',
            'Accept-Language' => 'ar',
            'Content-Type' => 'application/json',
        ])->post(rtrim($baseUrl, '/').$endpoint, [
            'invoiceHash' => $invoice->invoice_hash,
            'uuid' => $invoice->uuid_zatca,
            'invoice' => base64_encode((string) $xml),
        ]);

        $payload = $response->json() ?: ['raw' => $response->body()];
        $statusCode = $response->status();

        $status = match (true) {
            $statusCode >= 200 && $statusCode < 300 && empty($payload['validationResults']['errorMessages'] ?? null) => 'accepted',
            $statusCode >= 200 && $statusCode < 300 => 'warning',
            default => 'rejected',
        };

        $submission->update([
            'status' => $status,
            'response_json' => $payload,
            'errors_json' => $payload['validationResults']['errorMessages'] ?? null,
            'warnings_json' => $payload['validationResults']['warningMessages'] ?? null,
            'clearance_status' => $payload['clearanceStatus'] ?? null,
            'reporting_status' => $payload['reportingStatus'] ?? null,
            'responded_at' => now(),
        ]);

        $invoice->update([
            'zatca_status' => match ($status) {
                'accepted' => $type === 'clearance' ? 'cleared' : 'reported',
                'warning' => $type === 'clearance' ? 'cleared' : 'reported',
                default => 'rejected',
            },
        ]);

        return $submission->fresh();
    }
}
