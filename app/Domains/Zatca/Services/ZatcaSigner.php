<?php

namespace App\Domains\Zatca\Services;

use App\Domains\Zatca\Models\ZatcaDevice;

class ZatcaSigner
{
    /**
     * Lightweight signing placeholder that stamps digest metadata.
     * Production deployments should replace this with full XAdES-BES signing.
     */
    public function sign(string $xml, ZatcaDevice $device): string
    {
        $digest = base64_encode(hash('sha256', $xml, true));
        $stamp = sprintf(
            '<!-- ZATCA-SIGNATURE device="%s" digest="%s" signed_at="%s" -->',
            htmlspecialchars((string) $device->device_serial, ENT_XML1),
            $digest,
            now()->toIso8601String()
        );

        return str_replace('</Invoice>', $stamp."\n</Invoice>", $xml);
    }
}
