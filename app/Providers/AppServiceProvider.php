<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Event;
use App\Models\Person;
use App\Observers\CompanyObserver;
use App\Observers\EventObserver;
use App\Observers\PersonObserver;
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
        Person::observe(PersonObserver::class);
        Company::observe(CompanyObserver::class);
        Event::observe(EventObserver::class);
    }
}
