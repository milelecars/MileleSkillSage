<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class TimeHelper
{
    public static function convertToLocalTime($time)
    {
        $userTimezone = Session::get('user_timezone', 'UTC');
        return Carbon::parse($time)->timezone($userTimezone);
    }
}