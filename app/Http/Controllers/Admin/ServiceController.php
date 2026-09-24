<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOption;
use App\Models\ServiceOptionGroup;
use App\Models\ServicePriceTier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->with(['category', 'priceTiers'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('admin.services.index', [
            'services' => $services,
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', [
            'categories' => $this->categories(),
            'tiers' => $this->defaultTiersPayload(),
            'optionGroups' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->prepareData($this->validated($request), $request);

        $service = Service::create($data);
        $this->syncTiers($service, $this->tierPayload($request));
        $this->syncOptionGroups($service, $this->groupPayload($request));

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service->load(['priceTiers', 'optionGroups.options']),
            'categories' => $this->categories(),
            'tiers' => $this->existingTiersPayload($service),
            'optionGroups' => $this->existingGroupsPayload($service),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $this->prepareData($this->validated($request, $service), $request);

        $service->update($data);
        $this->syncTiers($service, $this->tierPayload($request));
        $this->syncOptionGroups($service, $this->groupPayload($request));

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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareData(array $data, Request $request): array
    {
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['category_id'] = $data['type'] === Service::TYPE_JUAL ? null : $data['category_id'];
        $data['min_quantity'] = ($data['min_quantity'] ?? '') === '' ? null : (int) $data['min_quantity'];

        return $data;
    }

    /**
     * @return Collection<int, ServiceCategory>
     */
    private function categories()
    {
        return ServiceCategory::query()->active()->orderBy('sort_order')->orderBy('name')->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Service $service = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Service::class, 'name')->ignore($service?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'string', Rule::in([Service::TYPE_JASA, Service::TYPE_JUAL])],
            'category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'min_quantity' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'min_ready_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'file_requirement' => ['nullable', 'string', Rule::in([
                Service::FILE_NONE,
                Service::FILE_OPTIONAL,
                Service::FILE_REQUIRED,
            ])],
            'price_tiers' => ['nullable', 'array'],
            'price_tiers.*.min_qty' => ['required', 'integer', 'min:1', 'max:999999'],
            'price_tiers.*.max_qty' => ['nullable', 'integer', 'min:1', 'max:999999'],
            'price_tiers.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'option_groups' => ['nullable', 'array'],
            'option_groups.*.name' => ['required', 'string', 'max:255'],
            'option_groups.*.selection_type' => ['required', 'string', Rule::in([
                ServiceOptionGroup::SELECTION_SINGLE,
                ServiceOptionGroup::SELECTION_MULTIPLE,
            ])],
            'option_groups.*.is_required' => ['nullable'],
            'option_groups.*.options' => ['nullable', 'array'],
            'option_groups.*.options.*.name' => ['required', 'string', 'max:255'],
            'option_groups.*.options.*.price' => ['required', 'numeric', 'min:-999999999', 'max:999999999'],
            'option_groups.*.options.*.pricing' => ['required', 'string', Rule::in([
                ServiceOption::PRICING_PER_UNIT,
                ServiceOption::PRICING_PER_ORDER,
            ])],
        ]);

        if ($data['type'] === Service::TYPE_JASA && empty($data['category_id'])) {
            throw ValidationException::withMessages([
                'category_id' => 'Kategori wajib dipilih untuk layanan jasa.',
            ]);
        }

        $this->assertTiersDoNotOverlap($data['price_tiers'] ?? []);

        $data['file_requirement'] = $data['file_requirement'] ?? Service::FILE_NONE;
        $data['min_quantity'] = $data['min_quantity'] ?? null;
        $data['min_ready_minutes'] = ($data['min_ready_minutes'] ?? '') === '' || $data['min_ready_minutes'] === null
            ? 30
            : (int) $data['min_ready_minutes'];

        return $data;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     */
    private function assertTiersDoNotOverlap(array $tiers): void
    {
        $normalized = [];

        foreach ($tiers as $index => $tier) {
            $min = (int) $tier['min_qty'];
            $max = isset($tier['max_qty']) && $tier['max_qty'] !== '' ? (int) $tier['max_qty'] : null;

            if ($max !== null && $max < $min) {
                throw ValidationException::withMessages([
                    "price_tiers.{$index}.max_qty" => 'Batas akhir harus lebih besar atau sama dengan batas awal.',
                ]);
            }

            foreach ($normalized as $other) {
                $otherMax = $other['max'] ?? PHP_INT_MAX;
                $currentMax = $max ?? PHP_INT_MAX;

                if ($min <= $otherMax && $other['min'] <= $currentMax) {
                    throw ValidationException::withMessages([
                        "price_tiers.{$index}.min_qty" => 'Rentang jumlah tidak boleh tumpang tindih.',
                    ]);
                }
            }

            $normalized[] = ['min' => $min, 'max' => $max];
        }
    }

    /**
     * @return array<int, array{min_qty: int, max_qty: int|null, unit_price: float}>
     */
    private function tierPayload(Request $request): array
    {
        $raw = $request->input('price_tiers', []);
        $payload = [];

        foreach (is_array($raw) ? $raw : [] as $tier) {
            if (! is_array($tier)) {
                continue;
            }

            $payload[] = [
                'min_qty' => (int) ($tier['min_qty'] ?? 1),
                'max_qty' => ($tier['max_qty'] ?? '') === '' ? null : (int) $tier['max_qty'],
                'unit_price' => (float) ($tier['unit_price'] ?? 0),
            ];
        }

        return $payload;
    }

    /**
     * @return array<int, array{name: string, selection_type: string, is_required: bool, options: array<int, array{name: string, price: float, pricing: string}>}>
     */
    private function groupPayload(Request $request): array
    {
        $raw = $request->input('option_groups', []);
        $payload = [];

        foreach (is_array($raw) ? $raw : [] as $group) {
            if (! is_array($group) || trim((string) ($group['name'] ?? '')) === '') {
                continue;
            }

            $options = [];

            foreach (is_array($group['options'] ?? []) ? $group['options'] : [] as $option) {
                if (! is_array($option) || trim((string) ($option['name'] ?? '')) === '') {
                    continue;
                }

                $options[] = [
                    'name' => trim((string) $option['name']),
                    'price' => (float) ($option['price'] ?? 0),
                    'pricing' => ($option['pricing'] ?? ServiceOption::PRICING_PER_UNIT) === ServiceOption::PRICING_PER_ORDER
                        ? ServiceOption::PRICING_PER_ORDER
                        : ServiceOption::PRICING_PER_UNIT,
                ];
            }

            $payload[] = [
                'name' => trim((string) $group['name']),
                'selection_type' => ($group['selection_type'] ?? ServiceOptionGroup::SELECTION_SINGLE) === ServiceOptionGroup::SELECTION_MULTIPLE
                    ? ServiceOptionGroup::SELECTION_MULTIPLE
                    : ServiceOptionGroup::SELECTION_SINGLE,
                'is_required' => ! empty($group['is_required']),
                'options' => $options,
            ];
        }

        return $payload;
    }

    /**
     * @param  array<int, array{min_qty: int, max_qty: int|null, unit_price: float}>  $tiers
     */
    private function syncTiers(Service $service, array $tiers): void
    {
        $service->priceTiers()->delete();

        foreach ($tiers as $index => $tier) {
            ServicePriceTier::create([
                'service_id' => $service->id,
                'min_qty' => $tier['min_qty'],
                'max_qty' => $tier['max_qty'],
                'unit_price' => $tier['unit_price'],
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Replace option groups (and their options) for a service.
     *
     * @param  array<int, array{name: string, selection_type: string, is_required: bool, options: array<int, array{name: string, price: float, pricing: string}>}>  $groups
     */
    private function syncOptionGroups(Service $service, array $groups): void
    {
        ServiceOption::whereIn(
            'group_id',
            $service->optionGroups()->pluck('id')
        )->delete();
        $service->optionGroups()->delete();

        foreach ($groups as $groupIndex => $group) {
            $groupModel = ServiceOptionGroup::create([
                'service_id' => $service->id,
                'name' => $group['name'],
                'selection_type' => $group['selection_type'],
                'is_required' => $group['is_required'],
                'sort_order' => $groupIndex,
                'is_active' => true,
            ]);

            foreach ($group['options'] as $optionIndex => $option) {
                ServiceOption::create([
                    'service_id' => $service->id,
                    'group_id' => $groupModel->id,
                    'name' => $option['name'],
                    'price' => $option['price'],
                    'pricing' => $option['pricing'],
                    'is_active' => true,
                    'sort_order' => $optionIndex,
                ]);
            }
        }
    }

    /**
     * @return list<array{min_qty: string, max_qty: string, unit_price: string}>
     */
    private function defaultTiersPayload(): array
    {
        return [
            ['min_qty' => '1', 'max_qty' => '10', 'unit_price' => ''],
            ['min_qty' => '11', 'max_qty' => '50', 'unit_price' => ''],
            ['min_qty' => '51', 'max_qty' => '100', 'unit_price' => ''],
            ['min_qty' => '101', 'max_qty' => '', 'unit_price' => ''],
        ];
    }

    /**
     * @return array<int, array{min_qty: string, max_qty: string, unit_price: string}>
     */
    private function existingTiersPayload(Service $service): array
    {
        $old = old('price_tiers');

        if (is_array($old) && $old !== []) {
            return array_values(array_map(fn ($tier) => [
                'min_qty' => (string) ($tier['min_qty'] ?? ''),
                'max_qty' => ($tier['max_qty'] ?? '') === '' ? '' : (string) $tier['max_qty'],
                'unit_price' => (string) ($tier['unit_price'] ?? ''),
            ], is_array($old) ? $old : []));
        }

        if ($service->priceTiers->isNotEmpty()) {
            return $service->priceTiers->map(fn (ServicePriceTier $tier) => [
                'min_qty' => (string) $tier->min_qty,
                'max_qty' => $tier->max_qty === null ? '' : (string) $tier->max_qty,
                'unit_price' => (string) (int) round((float) $tier->unit_price),
            ])->values()->all();
        }

        return $this->defaultTiersPayload();
    }

    /**
     * @return array<int, array{name: string, selection_type: string, is_required: bool, options: array<int, array{name: string, price: string, pricing: string}>}>
     */
    private function existingGroupsPayload(Service $service): array
    {
        $old = old('option_groups');

        if (is_array($old) && $old !== []) {
            return array_values(array_map(fn (array $group) => [
                'name' => (string) ($group['name'] ?? ''),
                'selection_type' => (string) ($group['selection_type'] ?? ServiceOptionGroup::SELECTION_SINGLE),
                'is_required' => ! empty($group['is_required']),
                'options' => array_values(array_map(fn (array $option) => [
                    'name' => (string) ($option['name'] ?? ''),
                    'price' => (string) ($option['price'] ?? ''),
                    'pricing' => (string) ($option['pricing'] ?? ServiceOption::PRICING_PER_UNIT),
                ], is_array($group['options'] ?? null) ? $group['options'] : [])),
            ], $old));
        }

        return $service->optionGroups->map(fn (ServiceOptionGroup $group) => [
            'name' => $group->name,
            'selection_type' => $group->selection_type,
            'is_required' => (bool) $group->is_required,
            'options' => $group->options->map(fn (ServiceOption $option) => [
                'name' => $option->name,
                'price' => (string) (int) round((float) $option->price),
                'pricing' => $option->pricing,
            ])->values()->all(),
        ])->values()->all();
    }
}
