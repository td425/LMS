<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_uses_local_logo_by_default(): void
    {
        Setting::setValue('site_logo', 'images/logo-new.png');
        Setting::setValue('site_name', 'LearnHost');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('images/logo-new.png', false)
            ->assertDontSee('https://mwasalat.om/ar/images/logo-new.png', false);
    }

    public function test_admin_can_update_site_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Mwasalat Academy',
                'logo_url' => 'images/logo-new.png',
            ])
            ->assertRedirect();

        $this->assertSame('Mwasalat Academy', Setting::siteName());
        $this->assertStringContainsString('images/logo-new.png', Setting::logoUrl());
    }

    public function test_admin_can_upload_logo_file(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        // Avoid GD dependency in CI/hosting test environments.
        $file = UploadedFile::fake()->createWithContent(
            'brand.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
        );

        $this->actingAs($admin)
            ->put(route('admin.settings.update'), [
                'site_name' => 'LearnHost',
                'logo_file' => $file,
            ])
            ->assertRedirect();

        $stored = Setting::getValue('site_logo');
        $this->assertNotNull($stored);
        $this->assertStringStartsWith('storage/logos/', $stored);
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)
            ->get(route('admin.settings.edit'))
            ->assertForbidden();
    }
}
