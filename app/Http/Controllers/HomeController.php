<?php

namespace App\Http\Controllers;

use App\Lib\WhatsApp;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $base = Service::query()->active();

        $services = (clone $base)
            ->where('type', Service::TYPE_JASA)
            ->with('category')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $atkServices = (clone $base)
            ->where('type', Service::TYPE_JUAL)
            ->orderBy('name')
            ->get();

        $digitalPrintServices = (clone $base)
            ->whereHas('category', fn ($q) => $q->where('slug', 'digital-print'))
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = ServiceCategory::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('home', [
            'services' => $services,
            'atkServices' => $atkServices,
            'digitalPrintServices' => $digitalPrintServices,
            'categories' => $categories,
            'serviceCount' => (clone $base)->count(),
            'transactionCount' => Order::query()->count(),
            'whatsappUrl' => WhatsApp::isConfigured()
                ? WhatsApp::url('Halo Admin Fotocopy Adhijaya, saya ingin bertanya soal layanan.')
                : null,
        ]);
    }
}
