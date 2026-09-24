<?php

namespace App\Models;

use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'sort_order',
    'is_active',
])]
class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory;

    /**
     * Soft badge palette by category slug (distinct, non-neon).
     *
     * @var array<string, string>
     */
    private const BADGE_CLASSES = [
        'digital-print' => 'bg-blue-50 text-blue-800 ring-1 ring-blue-200',
        'cetak-buku' => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
        'undangan-event' => 'bg-violet-50 text-violet-800 ring-1 ring-violet-200',
        'alat-tulis-stationery' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
        'media-promosi-uv' => 'bg-rose-50 text-rose-800 ring-1 ring-rose-200',
        'lain-lain' => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
    ];

    /**
     * Fallback cycle for unknown/new categories.
     *
     * @var list<string>
     */
    private const FALLBACK_BADGE_CLASSES = [
        'bg-blue-50 text-blue-800 ring-1 ring-blue-200',
        'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-200',
        'bg-violet-50 text-violet-800 ring-1 ring-violet-200',
        'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
        'bg-rose-50 text-rose-800 ring-1 ring-rose-200',
        'bg-cyan-50 text-cyan-800 ring-1 ring-cyan-200',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** @return HasMany<Service, $this> */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'category_id');
    }

    public function badgeClass(): string
    {
        if ($this->slug !== null && isset(self::BADGE_CLASSES[$this->slug])) {
            return self::BADGE_CLASSES[$this->slug];
        }

        $index = abs((int) $this->id) % count(self::FALLBACK_BADGE_CLASSES);

        return self::FALLBACK_BADGE_CLASSES[$index];
    }
}
