<?php

namespace App\Domains\Organization\Models;

use App\Domains\Tenancy\Models\Concerns\BelongsToTenant;
use App\Domains\Tenancy\Models\Tenant;
use App\Domains\Zatca\Models\ZatcaDevice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'legal_name_ar',
        'legal_name_en',
        'vat_number',
        'cr_number',
        'building_number',
        'street',
        'district',
        'city',
        'postal_code',
        'additional_no',
        'plot_identification',
        'country',
        'seller_id_type',
        'seller_id_value',
        'is_vat_registered',
        'phone',
        'email',
        'logo_path',
        'show_logo',
        'invoice_template',
        'brand_primary',
        'brand_secondary',
        'invoice_footer',
    ];

    protected function casts(): array
    {
        return [
            'is_vat_registered' => 'boolean',
            'show_logo' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function zatcaDevices(): HasMany
    {
        return $this->hasMany(ZatcaDevice::class);
    }

    public function isProfileComplete(): bool
    {
        return filled($this->legal_name_ar)
            && filled($this->vat_number)
            && filled($this->cr_number)
            && filled($this->city)
            && filled($this->building_number)
            && filled($this->postal_code);
    }
}
