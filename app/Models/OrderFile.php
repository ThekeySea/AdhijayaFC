<?php

namespace App\Models;

use App\Support\OrderFileStorage;
use Database\Factories\OrderFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'file_name',
    'storage_path',
    'mime_type',
    'file_size',
])]
class OrderFile extends Model
{
    /** @use HasFactory<OrderFileFactory> */
    use HasFactory;

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function formattedSize(): string
    {
        $bytes = (float) $this->file_size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.').' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0, ',', '.').' KB';
        }

        return round($bytes).' B';
    }

    public function existsOnDisk(): bool
    {
        return app(OrderFileStorage::class)->exists((string) $this->storage_path);
    }
}
