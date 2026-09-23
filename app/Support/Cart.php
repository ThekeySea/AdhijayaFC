<?php

namespace App\Support;

use App\Models\Service;
use Illuminate\Support\Collection;

class Cart
{
    private const SESSION_KEY = 'cart.items';

    /**
     * Raw cart items stored in the session, keyed by service id.
     *
     * @return array<int, array{service_id: int, name: string, price: float, quantity: int, detail: string}>
     */
    public static function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public static function add(Service $service, int $quantity, string $detail = ''): void
    {
        $items = self::items();
        $id = $service->id;

        if (isset($items[$id])) {
            $items[$id]['quantity'] = min($items[$id]['quantity'] + $quantity, 9999);
            $items[$id]['detail'] = $detail !== '' ? $detail : $items[$id]['detail'];
        } else {
            $items[$id] = [
                'service_id' => $id,
                'name' => $service->name,
                'price' => (float) $service->price,
                'quantity' => $quantity,
                'detail' => $detail,
            ];
        }

        session([self::SESSION_KEY => $items]);
    }

    public static function update(Service $service, int $quantity, ?string $detail = null): void
    {
        $items = self::items();

        if (! isset($items[$service->id])) {
            return;
        }

        $items[$service->id]['quantity'] = $quantity;

        if ($detail !== null) {
            $items[$service->id]['detail'] = $detail;
        }

        session([self::SESSION_KEY => $items]);
    }

    public static function remove(int $serviceId): void
    {
        $items = self::items();
        unset($items[$serviceId]);
        session([self::SESSION_KEY => $items]);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Cart lines with prices refreshed from the database.
     *
     * Inactive or deleted services are pruned from the session.
     *
     * @return Collection<int, array{service: Service, price: float, quantity: int, detail: string, subtotal: float}>
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
            ->get()
            ->keyBy('id');

        $lines = collect($items)
            ->map(function (array $item) use ($services) {
                $service = $services->get($item['service_id']);

                if ($service === null) {
                    return null;
                }

                $price = (float) $service->price;
                $quantity = max(1, (int) $item['quantity']);

                return [
                    'service' => $service,
                    'price' => $price,
                    'quantity' => $quantity,
                    'detail' => (string) ($item['detail'] ?? ''),
                    'subtotal' => $price * $quantity,
                ];
            })
            ->filter()
            ->values();

        $pruned = array_intersect_key($items, $services->all());

        if (count($pruned) !== count($items)) {
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
}
