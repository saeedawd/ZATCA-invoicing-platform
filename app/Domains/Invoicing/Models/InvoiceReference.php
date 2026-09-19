<?php

namespace App\Domains\Invoicing\Models;

use App\Domains\Tenancy\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceReference extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'referenced_invoice_id',
        'reason',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function referencedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'referenced_invoice_id');
    }
}
