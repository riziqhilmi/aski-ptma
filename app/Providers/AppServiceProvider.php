<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $publicUrl = env('DEMO_PUBLIC_URL');

        if ($publicUrl) {
            $publicUrl = rtrim($publicUrl, '/');

            config(['app.asset_url' => $publicUrl]);
            URL::forceRootUrl($publicUrl);
            URL::forceScheme('https');

            $this->app->make(Vite::class)->createAssetPathsUsing(
                fn (string $path): string => $publicUrl.'/'.ltrim($path, '/')
            );
        }
    }
}
