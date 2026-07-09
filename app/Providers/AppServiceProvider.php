<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['layouts.app', 'layouts.guest', 'layouts.navigation', 'courses.index', 'admin.settings'], function ($view) {
            try {
                $view->with('siteName', Setting::siteName());
                $view->with('siteLogoUrl', Setting::logoUrl());
            } catch (\Throwable) {
                $view->with('siteName', config('app.name', 'LearnHost'));
                $view->with('siteLogoUrl', asset('images/logo-new.png'));
            }
        });
    }
}
