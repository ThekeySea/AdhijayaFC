<?php

namespace App\Http\Controllers;

use App\Lib\WhatsApp;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->active()
            ->where('type', Service::TYPE_JASA)
            ->with('category')
            ->orderBy('name')
            ->limit(6)
            ->get();

        $atkServices = Service::query()
            ->active()
            ->where('type', Service::TYPE_JUAL)
            ->orderBy('name')
            ->get();

        $digitalPrintServices = Service::query()
            ->active()
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
            'whatsappUrl' => WhatsApp::isConfigured()
                ? WhatsApp::url('Halo Admin Fotocopy Adhijaya, saya ingin bertanya soal layanan.')
                : null,
        ]);
    }
}
