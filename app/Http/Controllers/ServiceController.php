<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = (string) $request->query('category', '');
        $type = (string) $request->query('type', '');

        $categories = ServiceCategory::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $query = Service::query()
            ->active()
            ->with(['category', 'activeOptions', 'priceTiers'])
            ->orderBy('name');

        $activeCategory = '';
        $activeType = '';

        if ($type === Service::TYPE_JUAL) {
            $query->where('type', Service::TYPE_JUAL);
            $activeType = Service::TYPE_JUAL;
            $categorySlug = '';
        } elseif ($categorySlug !== '' && $categories->contains('slug', $categorySlug)) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
            $activeCategory = $categorySlug;
        } else {
            $categorySlug = '';
        }

        return view('services.index', [
            'services' => $query->get(),
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'activeType' => $activeType,
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        $service->load([
            'category',
            'activeOptions',
            'priceTiers',
            'activeOptionGroups.options',
        ]);

        $groups = $service->activeOptionGroups->map(fn ($group) => [
            'id' => $group->id,
            'name' => $group->name,
            'selection' => $group->selection_type,
            'required' => (bool) $group->is_required,
            'options' => $group->options->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->name,
                'price' => (float) $option->price,
                'pricing' => $option->pricing,
                'label' => $option->formattedPrice().($option->pricing === 'per_unit' ? ' / '.$service->unit : ' / pesanan'),
            ])->values(),
        ])->values();

        $freeOptions = $service->activeOptions->map(fn ($option) => [
            'id' => $option->id,
            'name' => $option->name,
            'price' => (float) $option->price,
            'pricing' => $option->pricing,
            'label' => $option->formattedPrice().($option->pricing === 'per_unit' ? ' / '.$service->unit : ' / pesanan'),
        ])->values();

        return view('services.show', [
            'service' => $service,
            'optionGroups' => $groups,
            'freeOptions' => $freeOptions,
        ]);
    }
}
