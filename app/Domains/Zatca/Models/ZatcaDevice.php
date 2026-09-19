<?php

namespace App\Domains\Zatca\Models;

use App\Domains\Organization\Models\Organization;
use App\Domains\Tenancy\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ZatcaDevice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'environment',
        'device_serial',
        'solution_name',
        'version',
        'otp_used_at',
        'csr',
        'private_key_encrypted',
        'public_cert',
        'csid_compliance',
        'csid_production',
        'secret_encrypted',
        'binary_security_token',
        'status',
        'last_invoice_hash',
        'invoice_counter',
    ];

    protected function casts(): array
    {
        return [
            'otp_used_at' => 'datetime',
            'private_key_encrypted' => 'encrypted',
            'secret_encrypted' => 'encrypted',
            'csr' => 'encrypted',
            'public_cert' => 'encrypted',
            'binary_security_token' => 'encrypted',
            'csid_compliance' => 'encrypted',
            'csid_production' => 'encrypted',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ZatcaSubmission::class);
    }

    public function isOnboarded(): bool
    {
        return $this->status === 'onboarded';
    }

    public function nextCounter(): int
    {
        return ((int) $this->invoice_counter) + 1;
    }
}
