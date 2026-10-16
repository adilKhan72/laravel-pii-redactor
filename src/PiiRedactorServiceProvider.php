<?php

namespace AdilKhan\PiiRedactor;

use Illuminate\Support\ServiceProvider;

class PiiRedactorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/pii-redactor.php', 'pii-redactor');

        $this->app->singleton(PiiRedactor::class, fn ($app) => new PiiRedactor(
            $app['config']->get('pii-redactor.types', []),
            $app['config']->get('pii-redactor.custom', []),
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/pii-redactor.php' => config_path('pii-redactor.php')], 'pii-redactor-config');
        }
    }
}
