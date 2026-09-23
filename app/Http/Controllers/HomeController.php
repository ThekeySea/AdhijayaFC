<?php

namespace App\Http\Controllers;

use App\Lib\WhatsApp;
use App\Models\Service;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->active()
            ->orderBy('name')
            ->limit(6)
            ->get();

        return view('home', [
            'services' => $services,
            'whatsappUrl' => WhatsApp::isConfigured()
                ? WhatsApp::url('Halo Admin Fotocopy Adhijaya, saya ingin bertanya soal layanan.')
                : null,
        ]);
    }
}
