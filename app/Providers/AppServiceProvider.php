<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
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
        Model::preventLazyLoading(true);
        Model::unguard();
        DB::prohibitDestructiveCommands(app()->isProduction());
        Date::use(CarbonImmutable::class);
        Vite::useAggressivePrefetching();

        if (app()->environment(['production'])) {
            URL::forceHttps();
            URL::forceScheme('https');
            request()->server->set('HTTPS', request()->header('X-Forwarded-Proto', 'https') == 'https' ? 'on' : 'off');
        }

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        // if( ! app()->runningInConsole()) {
        //     config(['site.meta.description' => settings('meta_description')]);
        //     config(['site.meta.keyword' => settings('meta_keyword')]);
        // }
    }
}
