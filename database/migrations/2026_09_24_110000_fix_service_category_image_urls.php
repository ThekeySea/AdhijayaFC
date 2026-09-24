<?php

use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CATEGORY_IMAGES = [
        'digital-print' => 'images/services/digital-print.svg',
        'cetak-buku' => 'images/services/cetak-buku.svg',
        'undangan-event' => 'images/services/undangan-event.svg',
        'alat-tulis-stationery' => 'images/services/alat-tulis-stationery.svg',
        'media-promosi-uv' => 'images/services/media-promosi-uv.svg',
        'lain-lain' => 'images/services/lain-lain.svg',
    ];

    private const DEFAULT_IMAGES = [
        'images/services/digital-print.svg',
        'images/services/cetak-buku.svg',
        'images/services/undangan-event.svg',
        'images/services/alat-tulis-stationery.svg',
        'images/services/media-promosi-uv.svg',
        'images/services/lain-lain.svg',
        'images/services/atk.svg',
        '',
    ];

    public function up(): void
    {
        $services = DB::table('services')
            ->leftJoin('service_categories', 'services.category_id', '=', 'service_categories.id')
            ->select('services.id', 'services.type', 'services.image_url', 'service_categories.slug')
            ->where('services.type', Service::TYPE_JASA)
            ->get();

        foreach ($services as $row) {
            $target = self::CATEGORY_IMAGES[$row->slug] ?? null;

            if ($target === null) {
                continue;
            }

            $current = (string) ($row->image_url ?? '');
            $isBlank = trim($current) === '';
            $isDefault = in_array($current, self::DEFAULT_IMAGES, true);
            $isWrongLainlain = $current === 'images/services/lain-lain.svg' && $row->slug !== 'lain-lain';

            if ($isBlank || $isDefault || $isWrongLainlain) {
                if ($current !== $target) {
                    DB::table('services')->where('id', $row->id)->update(['image_url' => $target]);
                }
            }
        }

        DB::table('services')
            ->where('type', Service::TYPE_JUAL)
            ->where(function ($q) {
                $q->whereNull('image_url')
                    ->orWhere('image_url', '')
                    ->orWhere('image_url', 'images/services/lain-lain.svg');
            })
            ->update(['image_url' => 'images/services/atk.svg']);
    }

    public function down(): void
    {
        // Intentionally left blank: image_url corrections are safe to keep.
    }
};
