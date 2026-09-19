<?php

namespace App\Domains\Invoicing\Services;

use App\Domains\Invoicing\Models\DocumentSequence;
use App\Domains\Tenancy\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceNumberGenerator
{
    public function next(string $key, string $prefix = ''): string
    {
        $tenantId = TenantContext::id() ?? auth()->user()?->tenant_id;

        if (! $tenantId) {
            throw new RuntimeException('Tenant context is required to generate document numbers.');
        }

        return DB::transaction(function () use ($tenantId, $key, $prefix) {
            $sequence = DocumentSequence::query()
                ->where('tenant_id', $tenantId)
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = DocumentSequence::create([
                    'tenant_id' => $tenantId,
                    'key' => $key,
                    'prefix' => $prefix,
                    'next_number' => 1,
                    'padding' => 6,
                ]);
                $sequence = DocumentSequence::query()
                    ->whereKey($sequence->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $number = $sequence->next_number;
            $sequence->next_number = $number + 1;
            $sequence->save();

            $padded = str_pad((string) $number, (int) $sequence->padding, '0', STR_PAD_LEFT);

            return ($sequence->prefix ?: $prefix).$padded;
        });
    }
}
