<?php

namespace App\Models;

use Database\Factories\ServiceOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id',
    'group_id',
    'name',
    'price',
    'pricing',
    'is_active',
    'sort_order',
])]
class ServiceOption extends Model
{
    /** @use HasFactory<ServiceOptionFactory> */
    use HasFactory;

    public const PRICING_PER_UNIT = 'per_unit';

    public const PRICING_PER_ORDER = 'per_order';

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ServiceOptionGroup::class, 'group_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format((float) $this->price, 0, ',', '.');
    }
}
