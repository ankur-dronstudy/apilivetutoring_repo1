<?php

namespace App\Classes;

use Illuminate\Http\Request;
use App\models\User;
use App\library\Functions;

class SSAuth {

    public static function user() {
        if (SSAuth::exist()) {
            $user = User::where('id', SSAuth::exist()->id)
                    ->where('email', SSAuth::exist()->email)
                    ->first();
            return $user;
        } else {
            return false;
        }
    }
    
    public static function exist() {
        $response = ['status'=>false];
        $authorization = app('request')->header('Authorization');
        $creds = explode('Bearer ', $authorization);
        $token = $creds[1];
        if (!isset($token)) {
            return false;
        }
        $tokenuser = null;
        //dd($token);
        if (Functions::verifyToken($token, $tokenuser))
            return $tokenuser;
        return response($response)->header('Content-Type', 'application/json');
    }

}
