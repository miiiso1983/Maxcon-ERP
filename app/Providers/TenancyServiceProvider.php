<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
    }

    protected function bootEvents()
    {
        // For now, we'll keep this simple and add event listeners later
        // when we implement the full tenant management system
    }
}
