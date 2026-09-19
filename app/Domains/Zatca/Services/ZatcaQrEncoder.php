<?php

namespace App\Domains\Zatca\Services;

class ZatcaQrEncoder
{
    /**
     * @param  array{seller_name:string,vat_number:string,timestamp:string,total:string,vat_total:string,hash?:string}  $data
     */
    public function encode(array $data): string
    {
        $tlv = $this->tlv(1, $data['seller_name'])
            .$this->tlv(2, $data['vat_number'])
            .$this->tlv(3, $data['timestamp'])
            .$this->tlv(4, $data['total'])
            .$this->tlv(5, $data['vat_total']);

        if (! empty($data['hash'])) {
            $tlv .= $this->tlv(6, $data['hash']);
        }

        return base64_encode($tlv);
    }

    protected function tlv(int $tag, string $value): string
    {
        return chr($tag).chr(strlen($value)).$value;
    }
}
