<?php

namespace Shoutz0r\AcoustId\Providers;

use Illuminate\Support\ServiceProvider;
use Shoutz0r\AcoustId\Subscribers\UploadSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PackageServiceProvider extends ServiceProvider
{

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        //Register our event subscriber
        app(EventDispatcher::class)->addSubscriber(new UploadSubscriber());
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('shoutzor_acoustid.php')
        ], 'config');
    }
}