<?php

namespace App\Domains\Zatca\Models;

use App\Domains\Invoicing\Models\Invoice;
use App\Domains\Tenancy\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ZatcaSubmission extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'zatca_device_id',
        'type',
        'request_uuid',
        'status',
        'request_xml_path',
        'response_json',
        'clearance_status',
        'reporting_status',
        'errors_json',
        'warnings_json',
        'idempotency_key',
        'submitted_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'response_json' => 'array',
            'errors_json' => 'array',
            'warnings_json' => 'array',
            'submitted_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ZatcaSubmission $submission): void {
            $submission->request_uuid ??= (string) Str::uuid();
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(ZatcaDevice::class, 'zatca_device_id');
    }
}
