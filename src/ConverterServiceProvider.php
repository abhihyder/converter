<?php

namespace Hyder\Converter;

use Hyder\Converter\Services\PdfToImageService;
use Illuminate\Support\ServiceProvider;

class ConverterServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('pdf-to-image-service', function(){
            return new PdfToImageService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/converter.php' => config_path('converter.php'),
        ], 'config');
    }
}