<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            'logo_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:2048'],
        ]);

        try {
            Setting::setValue('site_name', $data['site_name']);

            if ($request->hasFile('logo_file')) {
                $path = $request->file('logo_file')->store('logos', 'public');
                Setting::setValue('site_logo', 'storage/'.$path);
            } elseif (array_key_exists('logo_url', $data) && filled($data['logo_url'])) {
                Setting::setValue('site_logo', $data['logo_url']);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to update site settings', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['site_name' => 'Could not save settings. Run migrations and ensure storage is writable.']);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Settings saved. Logo and site name updated.');
    }

    public function resetLogo(): RedirectResponse
    {
        try {
            $current = Setting::getValue('site_logo');

            if ($current && str_starts_with($current, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $current));
            }

            Setting::setValue('site_logo', 'images/logo-new.png');
        } catch (\Throwable $e) {
            Log::error('Failed to reset logo', ['message' => $e->getMessage()]);

            return back()->withErrors(['logo_url' => 'Could not reset logo. Check storage permissions.']);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Logo reset to the default Mwasalat logo.');
    }
}
