<?php

namespace App\Providers;

use App\Repositories\EventLocationRepository;
use App\Repositories\FileUploadRepository;
use App\Repositories\Interfaces\IEventLocationRepository;
use App\Repositories\Interfaces\IFileUploadRepository;
use App\Repositories\Interfaces\IRecurringEventRepository;
use App\Repositories\Interfaces\ISingleEventRepository;
use App\Repositories\RecurringEventRepository;
use App\Repositories\SingleEventRepository;
use App\Services\Interfaces\IRecurringEventService;
use App\Services\Interfaces\RecurringEventService;
use App\Services\SingleEventService;
use App\Services\Interfaces\ISingleEventService;
use App\Services\Interfaces\ITimeService;
use App\Services\TimeService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
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
        $this->app->bind(ISingleEventService::class, function (Application $app) {
            return new SingleEventService($app->make(ISingleEventRepository::class), $app->make(ITimeService::class));
        });
        $this->app->bind(ISingleEventRepository::class, function () {
            return new SingleEventRepository();
        });
        $this->app->bind(IEventLocationRepository::class, function () {
            return new EventLocationRepository();
        });
        $this->app->bind(IFileUploadRepository::class, function () {
            return new FileUploadRepository();
        });
        $this->app->bind(IRecurringEventRepository::class, function () {
            return new RecurringEventRepository();
        });
        $this->app->bind(IRecurringEventService::class, function (Application $app) {
            return new RecurringEventService(
                $app->make(ITimeService::class),
                $app->make(IRecurringEventRepository::class),
                $app->make(ISingleEventRepository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Schema::defaultStringLength(191);
    }
}
