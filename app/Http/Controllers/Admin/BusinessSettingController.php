<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.business-settings.edit', [
            'settings' => BusinessSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:150'],
            'about' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'hours_weekday' => ['nullable', 'string', 'max:50'],
            'hours_sunday' => ['nullable', 'string', 'max:50'],
        ]);

        BusinessSetting::current()->update($data);
        BusinessSetting::flushCurrent();

        return redirect()
            ->route('admin.business-settings.edit')
            ->with('status', 'Info usaha berhasil diperbarui.');
    }
}
