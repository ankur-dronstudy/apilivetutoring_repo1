<?php

namespace App\Http\Middleware;

use Closure;
use App\library\Functions;

class Logging
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = \Request::route()->getName();     
        Functions::logMessage(LOG_INFO,LOG_LOCAL0,"route::".$route);
        
       
        return $next($request);
    }
}
