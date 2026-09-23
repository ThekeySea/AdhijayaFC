<?php

namespace App\Support;

use App\Models\Service;
use App\Models\ServiceOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Cart
{
    private const SESSION_KEY = 'cart.items';

    /**
     * Raw cart items stored in the session, keyed by service id.
     *
     * @return array<int, array{service_id: int, name: string, price: float, quantity: int, detail: string, option_ids: list<int>, files: list<array{path: string, name: string, mime: string, size: int}>}>
     */
    public static function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /**
     * @param  list<int>  $optionIds
     * @param  list<array{path: string, name: string, mime: string, size: int}>  $files
     */
    public static function add(Service $service, int $quantity, string $detail = '', array $optionIds = [], array $files = []): void
    {
        $items = self::items();
        $id = $service->id;
        $optionIds = self::validOptionIds($service, $optionIds);

        if (isset($items[$id])) {
            $items[$id]['quantity'] = min($items[$id]['quantity'] + $quantity, 9999);
            $items[$id]['detail'] = $detail !== '' ? $detail : $items[$id]['detail'];
            $items[$id]['option_ids'] = $optionIds !== [] ? $optionIds : ($items[$id]['option_ids'] ?? []);
            $items[$id]['files'] = array_values(array_merge($items[$id]['files'] ?? [], $files));
        } else {
            $items[$id] = [
                'service_id' => $id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'quantity' => $quantity,
                'detail' => $detail,
                'option_ids' => $optionIds,
                'files' => $files,
            ];
        }

        session([self::SESSION_KEY => $items]);
    }

    /**
     * @param  list<int>  $optionIds
     * @param  list<array{path: string, name: string, mime: string, size: int}>|null  $files
     */
    public static function update(Service $service, int $quantity, ?string $detail = null, ?array $optionIds = null, ?array $files = null): void
    {
        $items = self::items();

        if (! isset($items[$service->id])) {
            return;
        }

        $items[$service->id]['quantity'] = $quantity;

        if ($detail !== null) {
            $items[$service->id]['detail'] = $detail;
        }

        if ($optionIds !== null) {
            $items[$service->id]['option_ids'] = self::validOptionIds($service, $optionIds);
        }

        if ($files !== null) {
            $items[$service->id]['files'] = $files;
        }

        session([self::SESSION_KEY => $items]);
    }

    public static function remove(int $serviceId): void
    {
        $items = self::items();

        if (isset($items[$serviceId])) {
            self::deleteStoredFiles($items[$serviceId]['files'] ?? []);
        }

        unset($items[$serviceId]);
        session([self::SESSION_KEY => $items]);
    }

    public static function clear(): void
    {
        foreach (self::items() as $item) {
            self::deleteStoredFiles($item['files'] ?? []);
        }

        session()->forget(self::SESSION_KEY);
    }

    /**
     * Cart lines with prices refreshed from the database.
     *
     * Inactive or deleted services are pruned from the session.
     *
     * @return Collection<int, array{service: Service, price: float, unit_price: float, quantity: int, detail: string, options: Collection<int, ServiceOption>, option_surcharge: float, fixed_option_total: float, subtotal: float}>
     */
    public static function lines(): Collection
    {
        $items = self::items();

        if ($items === []) {
            return collect();
        }

        $services = Service::query()
            ->active()
            ->whereIn('id', array_keys($items))
            ->with([
                'activeOptions' => fn ($q) => $q->whereIn('id', collect($items)->flatMap(fn ($i) => $i['option_ids'] ?? [])->unique()),
                'priceTiers',
            ])
            ->get()
            ->keyBy('id');

        $lines = collect($items)
            ->map(function (array $item) use ($services) {
                $service = $services->get($item['service_id']);

                if ($service === null) {
                    return null;
                }

                $optionIds = array_map('intval', $item['option_ids'] ?? []);
                $options = $service->activeOptions
                    ->filter(fn (ServiceOption $option) => in_array($option->id, $optionIds, true))
                    ->values();

                $unitSurcharge = (float) $options
                    ->filter(fn (ServiceOption $option) => $option->pricing === ServiceOption::PRICING_PER_UNIT)
                    ->sum('price');
                $fixedTotal = (float) $options
                    ->filter(fn (ServiceOption $option) => $option->pricing === ServiceOption::PRICING_PER_ORDER)
                    ->sum('price');

                $quantity = max(1, (int) $item['quantity']);
                $price = $service->priceForQuantity($quantity);
                $unitPrice = $price + $unitSurcharge;

                return [
                    'service' => $service,
                    'price' => $price,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'detail' => (string) ($item['detail'] ?? ''),
                    'options' => $options,
                    'option_surcharge' => $unitSurcharge,
                    'fixed_option_total' => $fixedTotal,
                    'files' => array_values($item['files'] ?? []),
                    'subtotal' => ($unitPrice * $quantity) + $fixedTotal,
                ];
            })
            ->filter()
            ->values();

        $pruned = array_intersect_key($items, $services->all());

        if (count($pruned) !== count($items)) {
            foreach (array_diff_key($items, $services->all()) as $removed) {
                self::deleteStoredFiles($removed['files'] ?? []);
            }

            session([self::SESSION_KEY => $pruned]);
        }

        return $lines;
    }

    public static function count(): int
    {
        return (int) self::lines()->sum('quantity');
    }

    public static function formatAmount(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    /**
     * @param  list<mixed>  $optionIds
     * @return list<int>
     */
    private static function validOptionIds(Service $service, array $optionIds): array
    {
        if ($optionIds === []) {
            return [];
        }

        $valid = $service->activeOptions()
            ->whereIn('id', array_map('intval', $optionIds))
            ->pluck('id')
            ->all();

        return array_map('intval', $valid);
    }

    /**
     * @param  list<array{path?: string}>  $files
     */
    private static function deleteStoredFiles(array $files): void
    {
        foreach ($files as $file) {
            $path = $file['path'] ?? '';

            if ($path !== '' && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }
    }
}
