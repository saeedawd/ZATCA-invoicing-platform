<?php

namespace App\Domains\Invoicing\Models;

use App\Domains\Catalog\Models\Product;
use App\Domains\Tenancy\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteLine extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'quote_id',
        'line_no',
        'product_id',
        'description',
        'quantity',
        'unit_code',
        'unit_price',
        'discount',
        'tax_category',
        'tax_rate',
        'line_net',
        'line_tax',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'line_net' => 'decimal:2',
            'line_tax' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
