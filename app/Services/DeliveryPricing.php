<?php

namespace App\Services;

use App\Models\BusinessSetting;
use InvalidArgumentException;

/**
 * Ongkir relatif jarak (haversine) toko → pelanggan.
 * Diskon per kelipatan belanja 100K; radius maks dari pengaturan admin.
 */
class DeliveryPricing
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * @return array{
     *     distance_km: float,
     *     base_fee: float,
     *     discount: float,
     *     fee: float,
     *     within_radius: bool,
     *     max_radius_km: float,
     *     rate_per_km: float,
     *     min_fee: float,
     *     discount_per_100k: float
     * }
     */
    public function quote(float $latitude, float $longitude, float $subtotal): array
    {
        $settings = BusinessSetting::current();

        if ($settings->latitude === null || $settings->longitude === null) {
            throw new InvalidArgumentException('Lokasi toko belum diatur. Hubungi admin.');
        }

        $distance = $this->distanceKm(
            (float) $settings->latitude,
            (float) $settings->longitude,
            $latitude,
            $longitude,
        );

        $rate = (float) ($settings->delivery_rate_per_km ?? 3000);
        $minFee = (float) ($settings->delivery_min_fee ?? 5000);
        $discountPer100k = (float) ($settings->delivery_discount_per_100k ?? 5000);
        $maxRadius = (float) ($settings->delivery_max_radius_km ?? 20);

        $base = max($minFee, $distance * $rate);
        $discount = floor(max(0, $subtotal) / 100000) * $discountPer100k;
        $fee = max(0, $base - $discount);

        return [
            'distance_km' => round($distance, 2),
            'base_fee' => round($base),
            'discount' => round($discount),
            'fee' => round($fee),
            'within_radius' => $distance <= $maxRadius,
            'max_radius_km' => $maxRadius,
            'rate_per_km' => $rate,
            'min_fee' => $minFee,
            'discount_per_100k' => $discountPer100k,
        ];
    }

    public function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($latFrom) * cos($latTo) * sin($deltaLng / 2) ** 2;

        return 2 * self::EARTH_RADIUS_KM * asin(min(1, sqrt($a)));
    }
}
