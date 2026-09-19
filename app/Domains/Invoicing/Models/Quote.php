<?php

namespace App\Domains\Invoicing\Models;

use App\Domains\Catalog\Models\Party;
use App\Domains\Tenancy\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quote extends Model
{
    use BelongsToTenant;

    public const STATUSES = ['draft', 'sent', 'accepted', 'rejected', 'converted', 'expired'];

    protected $fillable = [
        'tenant_id',
        'uuid',
        'quote_number',
        'party_id',
        'issue_date',
        'valid_until',
        'currency',
        'note',
        'line_extension_amount',
        'discount_amount',
        'taxable_amount',
        'tax_amount',
        'total_amount',
        'status',
        'pdf_path',
        'converted_invoice_id',
        'created_by',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'valid_until' => 'date',
            'line_extension_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'sent_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Quote $quote): void {
            $quote->uuid ??= (string) Str::uuid();
            $quote->currency ??= 'SAR';
            $quote->status ??= 'draft';
        });
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class)->orderBy('line_no');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isEditable(): bool
    {
        return $this->isDraft();
    }

    public function canSend(): bool
    {
        return $this->isDraft();
    }

    public function canAccept(): bool
    {
        return $this->status === 'sent';
    }

    public function canReject(): bool
    {
        return $this->status === 'sent';
    }

    public function canConvert(): bool
    {
        return in_array($this->status, ['sent', 'accepted'], true) && ! $this->converted_invoice_id;
    }
}
