<?php

namespace App\Domains\Zatca\Services;

use App\Domains\Organization\Models\Organization;
use App\Domains\Zatca\Models\ZatcaDevice;
use App\Domains\Zatca\Support\ZatcaEnvironment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ZatcaOnboardingService
{
    public function __construct(
        protected ZatcaCsrGenerator $csrGenerator,
    ) {}

    public function createDevice(Organization $organization, string $otp, string $environment = 'simulation'): ZatcaDevice
    {
        if (! $organization->isProfileComplete()) {
            throw new RuntimeException('أكمل بيانات المنشأة قبل ربط الجهاز.');
        }

        if (! in_array($environment, ZatcaEnvironment::options(), true)) {
            throw new RuntimeException('بيئة الربط غير صحيحة.');
        }

        $serialUuid = (string) Str::uuid();
        $serial = sprintf(
            '1-%s|2-%s|3-%s',
            $this->sanitizeToken((string) config('zatca.solution_name'), 20),
            $this->sanitizeToken((string) config('zatca.solution_version'), 10),
            $serialUuid
        );

        if (config('zatca.simulation_mode')) {
            $privateKey = "-----BEGIN EC PRIVATE KEY-----\n".chunk_split(base64_encode('sim-key-'.$serial), 64, "\n")."-----END EC PRIVATE KEY-----";
            $csrPem = "-----BEGIN CERTIFICATE REQUEST-----\n".chunk_split(base64_encode($serial.'|'.$organization->vat_number), 64, "\n")."-----END CERTIFICATE REQUEST-----";
        } else {
            [$privateKey, $csrPem] = $this->csrGenerator->generate($organization, $serial, $environment);
        }

        $device = ZatcaDevice::create([
            'tenant_id' => $organization->tenant_id,
            'organization_id' => $organization->id,
            'environment' => $environment,
            'device_serial' => $serial,
            'solution_name' => config('zatca.solution_name'),
            'version' => config('zatca.solution_version'),
            'otp_used_at' => now(),
            'csr' => $csrPem,
            'private_key_encrypted' => $privateKey,
            'status' => 'pending',
            'last_invoice_hash' => ZatcaHashService::INITIAL_HASH,
            'invoice_counter' => 0,
        ]);

        try {
            // مرّر PEM مباشرة لتفادي أي التباس بعد التشفير في الـ model
            return $this->requestComplianceCsid($device, $otp, $csrPem);
        } catch (Throwable $e) {
            $device->update(['status' => 'failed']);
            throw $e;
        }
    }

    public function requestComplianceCsid(ZatcaDevice $device, string $otp, ?string $csrPem = null): ZatcaDevice
    {
        if (config('zatca.simulation_mode')) {
            $token = base64_encode('SIM-CSID-'.$device->id.'-'.$device->organization_id);
            $secret = Str::random(32);

            $device->update([
                'csid_compliance' => $token,
                'secret_encrypted' => $secret,
                'binary_security_token' => $token,
                'public_cert' => "SIMULATED-CERT-{$device->device_serial}",
                'status' => 'onboarded',
            ]);

            return $device->fresh();
        }

        $csrPem ??= (string) $device->csr;
        $csrPayload = $this->csrGenerator->toApiPayload($csrPem);

        $baseUrl = ZatcaEnvironment::baseUrl((string) $device->environment);
        $endpoint = rtrim($baseUrl, '/').'/compliance';

        $response = Http::timeout(60)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'OTP' => $otp,
                'Accept-Version' => 'V2',
                'Accept-Language' => 'en',
            ])
            ->post($endpoint, [
                'csr' => $csrPayload,
            ]);

        if (! $response->successful()) {
            Log::warning('ZATCA compliance CSID rejected', [
                'device_id' => $device->id,
                'environment' => $device->environment,
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'csr_payload_len' => strlen($csrPayload),
                'csr_decodes_to_pem' => str_contains((string) base64_decode($csrPayload, true), 'BEGIN CERTIFICATE REQUEST'),
                'body' => Str::limit($response->body(), 1000),
            ]);

            $message = $this->extractErrorMessage($response->json(), $response->body());
            throw new RuntimeException('رفضت هيئة الزكاة والضريبة والجمارك طلب الشهادة: '.$message);
        }

        $payload = $response->json() ?: [];
        $token = $payload['binarySecurityToken'] ?? null;
        $secret = $payload['secret'] ?? null;

        if (! $token || ! $secret) {
            throw new RuntimeException('استجابة الهيئة ناقصة (binarySecurityToken / secret).');
        }

        $device->update([
            'csid_compliance' => $token,
            'secret_encrypted' => $secret,
            'binary_security_token' => $token,
            'public_cert' => base64_decode($token, true) ?: $token,
            'status' => 'onboarded',
        ]);

        return $device->fresh();
    }

    protected function extractErrorMessage(mixed $json, string $rawBody): string
    {
        if (is_array($json)) {
            foreach (['message', 'error', 'title', 'detail', 'errorMessage'] as $key) {
                if (! empty($json[$key]) && is_string($json[$key])) {
                    return $json[$key];
                }
            }

            if (! empty($json['errors']) && is_array($json['errors'])) {
                return json_encode($json['errors'], JSON_UNESCAPED_UNICODE) ?: $rawBody;
            }
        }

        $trimmed = trim($rawBody);

        return $trimmed !== '' ? Str::limit($trimmed, 500) : 'استجابة غير معروفة من هيئة الزكاة والضريبة والجمارك';
    }

    protected function sanitizeToken(string $value, int $max): string
    {
        $clean = preg_replace('/[^A-Za-z0-9._-]+/', '', $value) ?: 'EGS';

        return Str::limit($clean, $max, '');
    }
}
