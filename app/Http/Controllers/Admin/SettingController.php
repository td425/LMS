<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'siteName' => Setting::siteName(),
            'logoUrl' => Setting::getValue('site_logo', 'images/logo-new.png'),
            'logoPreview' => Setting::logoUrl(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'logo_file' => ['nullable', 'image', 'max:2048'],
        ]);

        Setting::setValue('site_name', $data['site_name']);

        if ($request->hasFile('logo_file')) {
            $path = $request->file('logo_file')->store('logos', 'public');
            Setting::setValue('site_logo', 'storage/'.$path);
        } elseif (filled($data['logo_url'] ?? null)) {
            Setting::setValue('site_logo', $data['logo_url']);
        }

        return back()->with('status', 'Settings saved. Logo and site name updated.');
    }

    public function resetLogo(): RedirectResponse
    {
        $current = Setting::getValue('site_logo');

        if ($current && str_starts_with($current, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $current));
        }

        Setting::setValue('site_logo', 'images/logo-new.png');

        return back()->with('status', 'Logo reset to the default Mwasalat logo.');
    }
}
