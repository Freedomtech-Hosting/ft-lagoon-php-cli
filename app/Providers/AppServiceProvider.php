<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use FreedomtechHosting\FtLagoonPhp\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
      // Implement if needed
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Client::class, function ($app, $parameters) {
          $config = array_merge(config('ftlagoonphp'), $parameters);
	        return new Client($config);
	      });
    }
}
