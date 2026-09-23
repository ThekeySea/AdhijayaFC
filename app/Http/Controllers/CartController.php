<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        $lines = Cart::lines();

        return view('cart.index', [
            'lines' => $lines,
            'subtotal' => (float) $lines->sum('subtotal'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'detail' => ['nullable', 'string', 'max:500'],
        ]);

        $service = Service::query()
            ->active()
            ->findOrFail($validated['service_id']);

        Cart::add(
            $service,
            (int) $validated['quantity'],
            trim((string) ($validated['detail'] ?? '')),
        );

        return redirect()
            ->route('cart.index')
            ->with('status', $service->name.' ditambahkan ke keranjang.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'detail' => ['nullable', 'string', 'max:500'],
        ]);

        Cart::update(
            $service,
            (int) $validated['quantity'],
            trim((string) ($validated['detail'] ?? '')),
        );

        return redirect()
            ->route('cart.index')
            ->with('status', 'Keranjang diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        Cart::remove($service->id);

        return redirect()
            ->route('cart.index')
            ->with('status', 'Item dihapus dari keranjang.');
    }
}
