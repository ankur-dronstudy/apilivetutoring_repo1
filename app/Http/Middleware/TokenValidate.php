<?php

namespace App\Http\Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use JWTAuth;

use Closure;

class TokenValidate {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $token = $request->get('token');
        if (!$token) {
            return response('Token not provided', 401);
        }else{
            $user = JWTAuth::parseToken()->authenticate();
            if(!$user){
                return response('Unauthorized.', 404);                
            }
            return $next($request);
        }
        
    }

}
