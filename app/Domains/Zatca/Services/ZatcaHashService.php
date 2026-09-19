<?php

namespace App\Domains\Zatca\Services;

class ZatcaHashService
{
    public const INITIAL_HASH = 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';

    public function hashXml(string $xml): string
    {
        return base64_encode(hash('sha256', $xml, true));
    }
}
