<?php

namespace App\Models;

use App\Enums\ProductKeyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'supplier_id', 'code', 'status', 'request_id', 'used_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductKeyStatus::class,
            'used_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }
}
