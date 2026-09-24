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

    public function up(): void
    {
        $services = DB::table('services')
            ->leftJoin('service_categories', 'services.category_id', '=', 'service_categories.id')
            ->select('services.id', 'services.type', 'services.image_url', 'service_categories.slug')
            ->whereNull('services.image_url')
            ->orWhere('services.image_url', '')
            ->get();

        foreach ($services as $row) {
            $image = $row->type === Service::TYPE_JUAL
                ? 'images/services/atk.svg'
                : (self::CATEGORY_IMAGES[$row->slug] ?? 'images/services/lain-lain.svg');

            DB::table('services')->where('id', $row->id)->update(['image_url' => $image]);
        }
    }

    public function down(): void
    {
        // Intentionally left blank: backfill data is safe to keep.
    }
};
