<?php

namespace App\Domains\Invoicing\Models;

use App\Domains\Catalog\Models\Party;
use App\Domains\Tenancy\Models\Concerns\BelongsToTenant;
use App\Domains\Zatca\Models\ZatcaDevice;
use App\Domains\Zatca\Models\ZatcaSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'uuid',
        'direction',
        'invoice_type',
        'document_type_code',
        'invoice_number',
        'uuid_zatca',
        'issue_date',
        'issue_time',
        'supply_date',
        'party_id',
        'quote_id',
        'currency',
        'payment_means_code',
        'note',
        'line_extension_amount',
        'discount_amount',
        'taxable_amount',
        'tax_amount',
        'total_amount',
        'prepaid_amount',
        'payable_amount',
        'amount_paid',
        'balance_due',
        'payment_status',
        'previous_hash',
        'invoice_hash',
        'qr_tlv_base64',
        'xml_path',
        'pdf_path',
        'status',
        'zatca_status',
        'counter_value',
        'zatca_device_id',
        'created_by',
        'posted_at',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'supply_date' => 'date',
            'line_extension_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'prepaid_amount' => 'decimal:2',
            'payable_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'posted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice): void {
            $invoice->uuid ??= (string) Str::uuid();
            $invoice->uuid_zatca ??= (string) Str::uuid();
            $invoice->currency ??= 'SAR';
            $invoice->status ??= 'draft';
            $invoice->zatca_status ??= 'draft';
            $invoice->payment_means_code ??= '10';
            $invoice->amount_paid ??= 0;
            $invoice->payment_status ??= 'unpaid';
            if ($invoice->balance_due === null) {
                $invoice->balance_due = round((float) ($invoice->total_amount ?? 0), 2);
            }
        });
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('line_no');
    }

    public function taxTotals(): HasMany
    {
        return $this->hasMany(InvoiceTaxTotal::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(InvoiceReference::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ZatcaSubmission::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderByDesc('paid_at')->orderByDesc('id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(ZatcaDevice::class, 'zatca_device_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isLocked(): bool
    {
        return ! $this->isDraft();
    }

    public function isSales(): bool
    {
        return $this->direction === 'sales';
    }

    public function isPurchase(): bool
    {
        return $this->direction === 'purchase';
    }

    public function isSimplified(): bool
    {
        return $this->invoice_type === 'simplified';
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partial';
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === 'unpaid';
    }
}
