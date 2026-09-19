<?php

namespace App\Domains\Zatca\Services;

use App\Domains\Organization\Models\Organization;
use App\Domains\Zatca\Support\ZatcaEnvironment;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class ZatcaCsrGenerator
{
    /**
     * @return array{0:string,1:string} [privateKeyPem, csrPem]
     */
    public function generate(Organization $organization, string $serial, string $environment): array
    {
        $context = $this->buildContext($organization, $serial, $environment);

        try {
            return $this->generateWithOpenSslCli($context);
        } catch (Throwable $cliError) {
            try {
                return $this->generateWithPhpOpenSsl($context);
            } catch (Throwable $phpError) {
                throw new RuntimeException(
                    'تعذر توليد CSR متوافق مع متطلبات هيئة الزكاة. '
                    .'CLI: '.$cliError->getMessage()
                    .' | PHP: '.$phpError->getMessage()
                );
            }
        }
    }

    /**
     * حوّل PEM إلى payload الـ API.
     *
     * وفق سكربت زاتكا/مايكروسوفت الرسمي: base64 لملف الـ PEM كاملًا
     * (شامل الترويسات) بعد إزالة المسافات والأسطر الجديدة.
     */
    public function toApiPayload(string $csrPem): string
    {
        $pem = $this->normalizePem($csrPem);
        $this->assertCsrDerContainsRequiredFields($pem);

        $encoded = base64_encode($pem);
        $payload = preg_replace('/\s+/', '', $encoded);

        if (! is_string($payload) || $payload === '' || ! preg_match('/^[A-Za-z0-9+\/=]+$/', $payload)) {
            throw new RuntimeException('تعذر ترميز CSR للإرسال.');
        }

        // تحقق عكسي: فك الترميز يجب أن يعيد PEM صالح
        $decoded = base64_decode($payload, true);
        if ($decoded === false || ! str_contains($decoded, 'BEGIN CERTIFICATE REQUEST')) {
            throw new RuntimeException('ترميز CSR غير متسق.');
        }

        return $payload;
    }

    /**
     * @return array<string, string>
     */
    protected function buildContext(Organization $organization, string $serial, string $environment): array
    {
        $vat = preg_replace('/\D+/', '', (string) $organization->vat_number) ?: (string) $organization->vat_number;

        $english = $this->sanitizeDn($organization->legal_name_en ?: '', 64);
        $arabic = $this->sanitizeDn($organization->legal_name_ar ?: '', 64);
        $orgName = $english !== 'NA' ? $english : ($arabic !== 'NA' ? $this->asciiFallback($arabic) : 'Taxpayer');

        // CN = اسم وحدة الحل (نص حر) — نفضّل اسم الحل + جزء من الرقم الضريبي للتفرد
        $solution = $this->sanitizeDn((string) config('zatca.solution_name', 'ZatcaApp'), 40);
        $commonName = $this->sanitizeDn($solution.'-'.substr($vat, -5), 64);

        return [
            'template' => ZatcaEnvironment::csrTemplate($environment),
            'commonName' => $commonName !== 'NA' ? $commonName : $orgName,
            'orgName' => $orgName,
            'orgUnit' => $this->sanitizeDn($organization->city ?: 'Riyadh', 64),
            'address' => $this->sanitizeDn(trim(implode(' ', array_filter([
                $organization->building_number,
                $organization->street,
                $organization->district,
                $organization->city,
            ]))), 128) ?: 'Riyadh',
            'email' => filled($organization->email) ? (string) $organization->email : 'noreply@zatca.app',
            'invoiceType' => (string) config('zatca.invoice_type_code', '1100'),
            'serial' => $serial,
            'vat' => $vat,
            'category' => 'Supply activities',
        ];
    }

    /**
     * @param  array<string, string>  $context
     * @return array{0:string,1:string}
     */
    protected function generateWithOpenSslCli(array $context): array
    {
        $openssl = $this->resolveOpenSslBinary();
        if ($openssl === null) {
            throw new RuntimeException('أمر openssl غير متاح.');
        }

        $dir = storage_path('app/zatca/tmp');
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new RuntimeException('تعذر إنشاء مجلد ملفات الربط.');
        }

        $id = (string) Str::uuid();
        $configPath = $dir.DIRECTORY_SEPARATOR."csr-{$id}.cnf";
        $keyPath = $dir.DIRECTORY_SEPARATOR."key-{$id}.pem";
        $csrPath = $dir.DIRECTORY_SEPARATOR."req-{$id}.csr";

        file_put_contents($configPath, $this->buildOpenSslConfig($context));

        try {
            $this->runProcess([
                $openssl, 'ecparam',
                '-name', 'secp256k1',
                '-genkey',
                '-noout',
                '-out', $keyPath,
            ]);

            // -reqexts req_ext يضمن تضمين امتدادات زاتكا داخل الـ CSR
            $this->runProcess([
                $openssl, 'req',
                '-new',
                '-sha256',
                '-key', $keyPath,
                '-config', $configPath,
                '-reqexts', 'req_ext',
                '-outform', 'PEM',
                '-out', $csrPath,
            ]);

            $privateKeyPem = (string) file_get_contents($keyPath);
            $csrPem = (string) file_get_contents($csrPath);
            $this->assertCsrDerContainsRequiredFields($csrPem);
            $this->toApiPayload($csrPem);

            return [$privateKeyPem, $csrPem];
        } finally {
            @unlink($configPath);
            @unlink($keyPath);
            @unlink($csrPath);
        }
    }

    /**
     * @param  array<string, string>  $context
     * @return array{0:string,1:string}
     */
    protected function generateWithPhpOpenSsl(array $context): array
    {
        if (! extension_loaded('openssl') || ! function_exists('openssl_pkey_new')) {
            throw new RuntimeException('امتداد OpenSSL غير مفعل.');
        }

        $dir = storage_path('app/zatca/tmp');
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new RuntimeException('تعذر إنشاء مجلد ملفات الربط.');
        }

        $configPath = $dir.DIRECTORY_SEPARATOR.'csr-php-'.Str::uuid().'.cnf';
        file_put_contents($configPath, $this->buildOpenSslConfig($context));

        $previousConf = getenv('OPENSSL_CONF');
        putenv('OPENSSL_CONF='.$configPath);

        try {
            while (openssl_error_string() !== false) {
                // صفّ طابور أخطاء OpenSSL القديمة
            }

            $privateKey = openssl_pkey_new([
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => 'secp256k1',
                'config' => $configPath,
            ]);

            if ($privateKey === false) {
                throw new RuntimeException('تعذر توليد مفتاح secp256k1: '.$this->opensslError());
            }

            $dn = [
                'commonName' => $context['commonName'],
                'organizationalUnitName' => $context['orgUnit'],
                'organizationName' => $context['orgName'],
                'countryName' => 'SA',
            ];

            $csrResource = openssl_csr_new($dn, $privateKey, [
                'digest_alg' => 'sha256',
                'req_extensions' => 'req_ext',
                'config' => $configPath,
            ]);

            if ($csrResource === false) {
                throw new RuntimeException('تعذر توليد CSR: '.$this->opensslError());
            }

            if (! openssl_csr_export($csrResource, $csrPem) || ! is_string($csrPem) || $csrPem === '') {
                throw new RuntimeException('تعذر تصدير CSR.');
            }

            $privateKeyPem = '';
            if (! openssl_pkey_export($privateKey, $privateKeyPem, null, ['config' => $configPath])
                || ! is_string($privateKeyPem)
                || $privateKeyPem === '') {
                throw new RuntimeException('تعذر تصدير المفتاح الخاص: '.$this->opensslError());
            }

            $this->assertCsrDerContainsRequiredFields($csrPem);
            $this->toApiPayload($csrPem);

            return [$privateKeyPem, $csrPem];
        } finally {
            if ($previousConf === false) {
                putenv('OPENSSL_CONF');
            } else {
                putenv('OPENSSL_CONF='.$previousConf);
            }
            @unlink($configPath);
        }
    }

    protected function normalizePem(string $csrPem): string
    {
        $csrPem = trim(str_replace(["\r\n", "\r"], "\n", $csrPem));

        if ($csrPem === '') {
            throw new RuntimeException('ملف CSR فارغ.');
        }

        // لو وصل body فقط (base64 DER) غلّفه كـ PEM
        if (! str_contains($csrPem, 'BEGIN CERTIFICATE REQUEST')) {
            $body = preg_replace('/\s+/', '', $csrPem);
            if (! is_string($body) || $body === '' || ! preg_match('/^[A-Za-z0-9+\/=]+$/', $body)) {
                throw new RuntimeException('CSR غير صالح.');
            }

            $der = base64_decode($body, true);
            if ($der === false || $der === '' || ! str_starts_with($der, "\x30")) {
                throw new RuntimeException('CSR ليس PKCS#10 صالحًا.');
            }

            return "-----BEGIN CERTIFICATE REQUEST-----\n"
                .chunk_split($body, 64, "\n")
                .'-----END CERTIFICATE REQUEST-----';
        }

        if (preg_match('/-----BEGIN (?:NEW )?CERTIFICATE REQUEST-----(.*)-----END (?:NEW )?CERTIFICATE REQUEST-----/s', $csrPem, $matches) !== 1) {
            throw new RuntimeException('تعذر قراءة بنية CSR.');
        }

        $body = preg_replace('/\s+/', '', $matches[1]);
        if (! is_string($body) || $body === '' || ! preg_match('/^[A-Za-z0-9+\/=]+$/', $body)) {
            throw new RuntimeException('محتوى CSR بعد التنظيف غير صالح.');
        }

        $der = base64_decode($body, true);
        if ($der === false || $der === '' || ! str_starts_with($der, "\x30")) {
            throw new RuntimeException('CSR ليس PKCS#10 صالحًا.');
        }

        return "-----BEGIN CERTIFICATE REQUEST-----\n"
            .chunk_split($body, 64, "\n")
            .'-----END CERTIFICATE REQUEST-----';
    }

    protected function assertCsrDerContainsRequiredFields(string $csrPem): void
    {
        $pem = $this->normalizePem($csrPem);

        if (preg_match('/-----BEGIN CERTIFICATE REQUEST-----(.*)-----END CERTIFICATE REQUEST-----/s', $pem, $m) !== 1) {
            throw new RuntimeException('CSR بدون ترويسة صحيحة.');
        }

        $der = base64_decode(preg_replace('/\s+/', '', $m[1]) ?: '', true);
        if ($der === false || $der === '') {
            throw new RuntimeException('تعذر فك CSR.');
        }

        $required = [
            'ZATCA-Code-Signing' => ['ZATCA-Code-Signing', 'PREZATCA-Code-Signing', 'TSTZATCA-Code-Signing'],
            'serial' => ['1-'],
            'title' => ['1100', '1000', '0100'],
        ];

        $hasTemplate = str_contains($der, 'ZATCA-Code-Signing')
            || str_contains($der, 'PREZATCA-Code-Signing')
            || str_contains($der, 'TSTZATCA-Code-Signing');

        if (! $hasTemplate) {
            throw new RuntimeException('CSR لا يحتوي قالب شهادة زاتكا (certificateTemplateName).');
        }

        if (! str_contains($der, '1-')) {
            throw new RuntimeException('CSR لا يحتوي الرقم التسلسلي للجهاز.');
        }

        unset($required);
    }

    /**
     * @param  array<string, string>  $context
     */
    protected function buildOpenSslConfig(array $context): string
    {
        // حقول زاتكا الرسمية (CNF) — بدون x509_extensions لتجنب أقسام ناقصة على بعض بيئات OpenSSL
        return <<<CNF
oid_section = OIDs

[OIDs]
certificateTemplateName = 1.3.6.1.4.1.311.20.2

[req]
default_bits = 2048
emailAddress = {$context['email']}
prompt = no
default_md = sha256
req_extensions = req_ext
distinguished_name = dn

[dn]
C = SA
OU = {$context['orgUnit']}
O = {$context['orgName']}
CN = {$context['commonName']}

[v3_req]
basicConstraints = CA:FALSE
keyUsage = digitalSignature, nonRepudiation, keyEncipherment

[req_ext]
certificateTemplateName = ASN1:PRINTABLESTRING:{$context['template']}
subjectAltName = dirName:alt_names

[alt_names]
SN = {$context['serial']}
UID = {$context['vat']}
title = {$context['invoiceType']}
registeredAddress = {$context['address']}
businessCategory = {$context['category']}
CNF;
    }

    /**
     * @param  list<string>  $command
     */
    protected function runProcess(array $command): void
    {
        $process = new Process($command);
        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput().' '.$process->getOutput());
            throw new RuntimeException($error !== '' ? $error : 'فشل تنفيذ OpenSSL.');
        }
    }

    protected function resolveOpenSslBinary(): ?string
    {
        foreach (['openssl', '/usr/bin/openssl', '/bin/openssl'] as $binary) {
            try {
                $process = new Process([$binary, 'version']);
                $process->setTimeout(10);
                $process->run();
                if ($process->isSuccessful()) {
                    return $binary;
                }
            } catch (Throwable) {
                // continue
            }
        }

        return null;
    }

    protected function opensslError(): string
    {
        $messages = [];
        while ($message = openssl_error_string()) {
            $messages[] = $message;
        }

        return $messages !== [] ? implode(' | ', $messages) : 'خطأ OpenSSL غير معروف';
    }

    protected function sanitizeDn(string $value, int $max): string
    {
        $clean = trim(str_replace(["\n", "\r", ',', '=', '/', '#', '"', "'", '\\'], ' ', $value));
        $clean = preg_replace('/\s+/', ' ', $clean) ?: 'NA';

        return Str::limit($clean, $max, '');
    }

    protected function asciiFallback(string $value): string
    {
        if (preg_match('/^[\x20-\x7E]+$/', $value)) {
            return $value !== '' ? $value : 'NA';
        }

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = is_string($ascii) ? trim(preg_replace('/\s+/', ' ', $ascii) ?: '') : '';

        return $ascii !== '' ? $ascii : 'Taxpayer';
    }
}
