<?php

namespace App\Services;

use App\Models\BusinessHour;
use Illuminate\Support\Carbon;

/**
 * Jam buka per hari + generator slot checkout (pickup / delivery scheduled).
 * Slot format sama dengan Booking::TIME_SLOTS: "09.00-10.00".
 */
class OpeningHours
{
    private const SLOT_MINUTES = 60;

    /** @var array<int, BusinessHour>|null */
    private static ?array $hours = null;

    public static function flush(): void
    {
        self::$hours = null;
    }

    /**
     * @return array<int, BusinessHour>
     */
    public static function all(): array
    {
        if (self::$hours !== null) {
            return self::$hours;
        }

        $rows = BusinessHour::query()->orderBy('day_of_week')->get();

        if ($rows->isEmpty()) {
            $rows = collect(range(0, 6))->map(fn (int $day) => new BusinessHour([
                'day_of_week' => $day,
                'is_open' => true,
                'opens_at' => '08:00:00',
                'closes_at' => '20:00:00',
            ]));
        }

        return self::$hours = $rows->all();
    }

    public static function forDate(Carbon|string $date): ?BusinessHour
    {
        $day = Carbon::parse($date)->dayOfWeek;

        foreach (self::all() as $hour) {
            if ($hour->day_of_week === $day) {
                return $hour;
            }
        }

        return null;
    }

    public static function isOpenOn(Carbon|string $date): bool
    {
        $hour = self::forDate($date);

        return $hour !== null
            && $hour->is_open
            && $hour->opens_at !== null
            && $hour->closes_at !== null;
    }

    /**
     * Slot per jam dalam rentang jam buka hari itu, difilter min_ready.
     *
     * @return list<string>
     */
    public static function slotsForDate(Carbon|string $date, int $minReadyMinutes = 0): array
    {
        $day = Carbon::parse($date)->startOfDay();
        $hour = self::forDate($day);

        if ($hour === null || ! $hour->is_open || $hour->opens_at === null || $hour->closes_at === null) {
            return [];
        }

        $open = $day->copy()->setTimeFromTimeString(substr($hour->opens_at, 0, 5));
        $close = $day->copy()->setTimeFromTimeString(substr($hour->closes_at, 0, 5));

        if ($close->lessThanOrEqualTo($open)) {
            return [];
        }

        $earliest = now()->addMinutes(max(0, $minReadyMinutes));
        $slots = [];

        for ($start = $open->copy(); $start->copy()->addMinutes(self::SLOT_MINUTES)->lessThanOrEqualTo($close); $start->addMinutes(self::SLOT_MINUTES)) {
            if ($start->lessThan($earliest)) {
                continue;
            }

            $end = $start->copy()->addMinutes(self::SLOT_MINUTES);
            $slots[] = $start->format('H.i').'-'.$end->format('H.i');
        }

        return $slots;
    }

    public static function isValidSlot(Carbon|string $date, string $slot, int $minReadyMinutes = 0): bool
    {
        return in_array($slot, self::slotsForDate($date, $minReadyMinutes), true);
    }

    /**
     * Tanggal yang bisa dipilih (hari ini + n ke depan) — buka saja.
     *
     * @return list<string>
     */
    public static function bookableDates(int $daysAhead = 14): array
    {
        $dates = [];

        for ($i = 0; $i <= $daysAhead; $i++) {
            $date = now()->addDays($i);

            if (self::isOpenOn($date)) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates;
    }

    /**
     * Label jam per hari untuk halaman Kontak / admin.
     *
     * @return list<array{day: string, label: string, is_open: bool, range: string}>
     */
    public static function summary(): array
    {
        $ordered = collect(self::all())->sortBy('day_of_week')->values();

        return $ordered->map(fn (BusinessHour $hour) => [
            'day' => (string) $hour->day_of_week,
            'label' => $hour->dayLabel(),
            'is_open' => (bool) $hour->is_open,
            'range' => $hour->formattedRange(),
        ])->all();
    }
}
