<?php

namespace App\Providers;

use App\Repositories\Interfaces\ISingleEventRepository;
use App\Repositories\SingleEventRepository;
use App\Services\EventService;
use App\Services\Interfaces\IEventService;
use App\Services\Interfaces\ITimeService;
use App\Services\TimeService;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ITimeService::class, function () {
            return new TimeService();
        });
        $this->app->bind(IEventService::class, function (Application $app) {
            return new EventService($app->make(ISingleEventRepository::class), $app->make(ITimeService::class));
        });
        $this->app->bind(ISingleEventRepository::class, function () {
            return new SingleEventRepository();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
