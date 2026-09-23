<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id',
    'min_qty',
    'max_qty',
    'unit_price',
    'sort_order',
])]
class ServicePriceTier extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_qty' => 'integer',
            'max_qty' => 'integer',
            'unit_price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function quantityLabel(): string
    {
        if ($this->max_qty === null) {
            return '>= '.number_format($this->min_qty);
        }

        return number_format($this->min_qty).'-'.number_format($this->max_qty);
    }

    public function formattedUnitPrice(): string
    {
        return 'Rp '.number_format((float) $this->unit_price, 0, ',', '.');
    }
}
