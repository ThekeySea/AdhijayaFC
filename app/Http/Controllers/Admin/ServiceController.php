<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.services.index', [
            'services' => $services,
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        Service::create($data);

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $this->validated($request, $service);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $service->update($data);

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Layanan berhasil dihapus.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $service->update([
            'is_active' => ! $service->is_active,
        ]);

        return back()->with(
            'status',
            $service->is_active
                ? 'Layanan diaktifkan.'
                : 'Layanan dinonaktifkan.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Service::class, 'name')->ignore($service?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'image_url' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
