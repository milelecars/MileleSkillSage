<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class TimeZoneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(Request $request): void
    {
        if (!Session::has('user_timezone')) {
            $timezone = 'UTC';
            
            if ($request->hasCookie('user_timezone')) {
                $timezone = $request->cookie('user_timezone');
            } else {
                // Attempt to guess the timezone based on IP
                $ip = $request->ip();
                $location = geoip()->getLocation($ip);
                $timezone = $location->timezone;
            }
            
            Session::put('user_timezone', $timezone);
        }

        View::composer('*', function ($view) {
            $view->with('user_timezone', Session::get('user_timezone', 'UTC'));
        });
    }
}