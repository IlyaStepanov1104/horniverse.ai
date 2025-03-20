<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

use Closure;

class Language
{
    public function handle(Request $request, Closure $next)
    {
        $firstSection = $request->segment(1);
        if ($firstSection == 'ru') {
            App::setLocale($firstSection);
        }
        else {
            App::setLocale('en');
        }
        return $next($request);
    }
}