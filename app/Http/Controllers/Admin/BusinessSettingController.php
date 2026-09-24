<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\BusinessSetting;
use App\Services\OpeningHours;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.business-settings.edit', [
            'settings' => BusinessSetting::current(),
            'hours' => OpeningHours::all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'about' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'hours_weekday' => ['nullable', 'string', 'max:50'],
            'hours_sunday' => ['nullable', 'string', 'max:50'],
            'delivery_rate_per_km' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'delivery_min_fee' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'delivery_discount_per_100k' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'delivery_max_radius_km' => ['nullable', 'integer', 'min:1', 'max:500'],
            'business_hours' => ['nullable', 'array'],
            'business_hours.*.day_of_week' => ['required_with:business_hours', 'integer', 'between:0,6'],
            'business_hours.*.is_open' => ['nullable'],
            'business_hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'business_hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        $hoursPayload = $data['business_hours'] ?? [];
        unset($data['business_hours']);

        BusinessSetting::current()->update($data + [
            'delivery_rate_per_km' => $data['delivery_rate_per_km'] ?? 3000,
            'delivery_min_fee' => $data['delivery_min_fee'] ?? 5000,
            'delivery_discount_per_100k' => $data['delivery_discount_per_100k'] ?? 5000,
            'delivery_max_radius_km' => $data['delivery_max_radius_km'] ?? 20,
        ]);
        BusinessSetting::flushCurrent();

        $this->syncHours($hoursPayload);
        OpeningHours::flush();

        return redirect()
            ->route('admin.business-settings.edit')
            ->with('status', 'Info usaha berhasil diperbarui.');
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $payload
     */
    private function syncHours(array $payload): void
    {
        foreach ($payload as $row) {
            if (! is_array($row) || ! isset($row['day_of_week'])) {
                continue;
            }

            $day = (int) $row['day_of_week'];
            $isOpen = ! empty($row['is_open']);
            $opens = $isOpen ? (($row['opens_at'] ?? '') !== '' ? $row['opens_at'].':00' : null) : null;
            $closes = $isOpen ? (($row['closes_at'] ?? '') !== '' ? $row['closes_at'].':00' : null) : null;

            BusinessHour::updateOrCreate(
                ['day_of_week' => $day],
                [
                    'is_open' => $isOpen && $opens !== null && $closes !== null,
                    'opens_at' => $opens,
                    'closes_at' => $closes,
                ],
            );
        }
    }
}
