<?php

namespace App\Http\Middleware;

use Closure;
//use Illuminate\Support\Facades\Auth;
use App\library\Functions;
use App\models\Adminuser;

class SSAuthenticate {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public $attributes;

    public function handle($request, Closure $next, $guard = null) {
        $response = [ 'status' => false];

        $aheader = $request->header('Authorization');

        if (!isset($aheader) || is_null($aheader)) {
            $response['message'] = 'Missing data!';
        } 
        else {
            $creds = explode('Bearer ', $aheader);
            
            if (isset($creds[1]) && ($creds[1] !== 'null')) {
                $token = $creds[1];
                $user = null;
                $flag = false;

                $tokenuser = null;
                try {
                    Functions::verifyToken($token, $tokenuser);
                } catch (\Exception $e) {
                    $response['message'] = 'ERROR : Invalid token';
                    $response['invalid'] = true;
                    return response($response)->header('Content-Type', 'application/json');
                }

                if (!is_null($tokenuser)) {
                    try {
                        $currTime = time();
                        if (($currTime - $tokenuser->created) > $tokenuser->expiry) {
                            $response['message'] = 'ERROR : expired token';
                            $response['invalid'] = true;
                            return response($response)->header('Content-Type', 'application/json');
                        } else {
                            //dd($tokenuser);
                            return $next($request);
                        }
                    } catch (\Exception $e) {
                        $response['message'] = 'Message: ' .$e->getMessage().' on '.$e->getFile().' at line no '.$e->getLine();
                        $response['invalid'] = true;
                        return response($response)->header('Content-Type', 'application/json');
                    }
                }
            } else {
                $response['message'] = 'ERROR : Format not supported';
                $response['invalid'] = true;
                return response($response)->header('Content-Type', 'application/json');
            }
        }
        return response($response)->header('Content-Type', 'application/json');
    }
}