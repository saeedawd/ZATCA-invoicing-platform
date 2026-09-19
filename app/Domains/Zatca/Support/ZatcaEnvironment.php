<?php

namespace App\Domains\Zatca\Support;

class ZatcaEnvironment
{
    public static function baseUrl(string $environment): string
    {
        return match ($environment) {
            'production' => (string) config('zatca.production_base_url'),
            'simulation' => (string) config('zatca.simulation_base_url'),
            default => (string) config('zatca.sandbox_base_url'),
        };
    }

    public static function csrTemplate(string $environment): string
    {
        $templates = config('zatca.csr_templates', []);

        return (string) ($templates[$environment] ?? $templates['sandbox'] ?? 'TSTZATCA-Code-Signing');
    }

    public static function label(string $environment): string
    {
        return match ($environment) {
            'production' => 'الإنتاج',
            'simulation' => 'محاكاة فاتورة',
            default => 'بيئة المطورين',
        };
    }

    public static function otpPortalHint(string $environment): string
    {
        return match ($environment) {
            'production' => 'أنشئ رمز التحقق من بوابة فاتورة.',
            'simulation' => 'أنشئ رمز التحقق من بوابة المحاكاة وليس من بوابة الإنتاج.',
            default => 'استخدم رمز التحقق من بيئة المطورين إن كانت متاحة لحسابك.',
        };
    }

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return ['sandbox', 'simulation', 'production'];
    }
}
