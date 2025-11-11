<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\Property;
use App\Observers\PropertyObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        Paginator::useBootstrap();

        DB::listen(function ($query) {
            $sql = $query->sql;
            $time = $query->time;
            foreach ($query->bindings as $binding) {
                $value = is_numeric($binding) ? $binding : "'{$binding}'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            Log::channel('sqllog')->info("\n SQL: {$sql} \n At Time: {$time}ms \n");
        });

        View::composer('*', function ($view) {
            $view->with([
                'currentUserInfo'   => auth()->user(),
            ]);
        });

        Schema::defaultStringLength(191);
        Property::observe(PropertyObserver::class);
    }
}
