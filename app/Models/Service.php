<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'category_id',
    'type',
    'unit',
    'price',
    'is_active',
    'image_url',
    'min_quantity',
    'file_requirement',
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    public const TYPE_JASA = 'jasa';

    public const TYPE_JUAL = 'jual';

    public const FILE_NONE = 'none';

    public const FILE_OPTIONAL = 'optional';

    public const FILE_REQUIRED = 'required';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'min_quantity' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** @return BelongsTo<ServiceCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    /** @return HasMany<ServiceOption, $this> */
    public function options(): HasMany
    {
        return $this->hasMany(ServiceOption::class);
    }

    /** @return HasMany<ServiceOption, $this> */
    public function activeOptions(): HasMany
    {
        return $this->options()->whereNull('group_id')->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    /** @return HasMany<ServiceOptionGroup, $this> */
    public function optionGroups(): HasMany
    {
        return $this->hasMany(ServiceOptionGroup::class)->orderBy('sort_order')->orderBy('name');
    }

    /** @return HasMany<ServiceOptionGroup, $this> */
    public function activeOptionGroups(): HasMany
    {
        return $this->optionGroups()->where('is_active', true);
    }

    /** @return HasMany<ServicePriceTier, $this> */
    public function priceTiers(): HasMany
    {
        return $this->hasMany(ServicePriceTier::class)->orderBy('min_qty');
    }

    public function priceForQuantity(int $quantity): float
    {
        $quantity = max(1, $quantity);

        foreach ($this->priceTiers as $tier) {
            if ($quantity < $tier->min_qty) {
                continue;
            }

            if ($tier->max_qty === null || $quantity <= $tier->max_qty) {
                return (float) $tier->unit_price;
            }
        }

        return (float) $this->price;
    }

    public function formattedPrice(): string
    {
        return 'Rp '.number_format((float) $this->price, 0, ',', '.');
    }

    public function badgeLabel(): string
    {
        if ($this->category !== null) {
            return $this->category->name;
        }

        return $this->type === self::TYPE_JUAL ? 'ATK' : 'Layanan';
    }

    public function requiresFile(): bool
    {
        return $this->file_requirement === self::FILE_REQUIRED;
    }

    public function allowsFile(): bool
    {
        return in_array($this->file_requirement, [self::FILE_OPTIONAL, self::FILE_REQUIRED], true);
    }

    public function fileIsRequired(): bool
    {
        return $this->requiresFile();
    }
}
