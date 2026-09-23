<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'service_id',
    'name',
    'selection_type',
    'is_required',
    'sort_order',
    'is_active',
])]
class ServiceOptionGroup extends Model
{
    public const SELECTION_SINGLE = 'single';

    public const SELECTION_MULTIPLE = 'multiple';

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return HasMany<ServiceOption, $this> */
    public function options(): HasMany
    {
        return $this->hasMany(ServiceOption::class, 'group_id')->orderBy('sort_order')->orderBy('name');
    }

    public function isSingle(): bool
    {
        return $this->selection_type === self::SELECTION_SINGLE;
    }
}
