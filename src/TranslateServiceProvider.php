<?php

namespace iscms\Translate;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class TranslateServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('translate', function () {
            return new TranslateApi(new Client(['verify'=>false]), config('services.baidu'));
        });
    }
}
