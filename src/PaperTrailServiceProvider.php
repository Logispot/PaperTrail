<?php

namespace Logispot\PaperTrail;

use Illuminate\Support\ServiceProvider;

class PaperTrailServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/papertrail.php' => config_path('papertrail.php'),
        ], 'config');

        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__.'/../migrations/create_papertrails_table.php' => database_path("/migrations/{$timestamp}_create_papertrails_table.php"),
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
    }
}
