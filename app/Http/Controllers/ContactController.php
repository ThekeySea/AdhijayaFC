<?php

namespace App\Http\Controllers;

use App\Lib\WhatsApp;
use App\Models\BusinessSetting;
use App\Services\OpeningHours;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $settings = BusinessSetting::current();

        return view('kontak', [
            'settings' => $settings,
            'hours' => OpeningHours::summary(),
            'whatsappUrl' => WhatsApp::buildWhatsAppUrl(
                $settings->whatsapp_number ?? WhatsApp::number(),
                'Halo Admin Fotocopy Adhijaya, saya ingin menanyakan layanan.'
            ),
        ]);
    }
}
