<?php

namespace App\Http\Controllers;

use App\Lib\WhatsApp;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('kontak', [
            'whatsappUrl' => WhatsApp::isConfigured()
                ? WhatsApp::url('Halo Admin Fotocopy Adhijaya, saya ingin menanyakan layanan.')
                : null,
        ]);
    }
}
