<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\models\User;
use App\models\UserDetail;
use App\models\Usertype;
use Validator;
use DB;
use Illuminate\Support\Facades\Hash;
use App\library\Functions;
use Illuminate\Support\Str;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use App\models\Adminuser;
use App\Classes\SSMail;
use App\models\SocialAuth;
use App\models\RoleUser;
use App\Classes\SSAuth;

class AuthController extends Controller {

    private $signer;

    public function __construct() {
        $this->signer = new Sha256();
    }

    public function login(Request $request) {
        $result['status'] = array('statusCode' => 0, 'message' => '');
	$credentials = $request->all();
        $username = $credentials['username'];
        $password = md5($credentials['password']);
        
	$user_data = User::where('username', $username)->where('password', $password);
        $user = $user_data->first(array('id', 'name','is_active','email','login_count'));

        if (!$user) {
            $result['status'] = array('statusCode' => 0, 'message' => 'Your email or password did not match');
            return response($result)->header('Content-Type', 'application/json');
        }

        if ($user->is_active == 0) {
            $result['status'] = array('statusCode' => 0, 'message' => 'Your account has not been activated yet');
        } else if ($user !== NULL) {
            $uid = $user['id'];
            $current_count = $user['login_count'];
            $count = DB::update("UPDATE users SET login_count = $current_count + 1 WHERE id = '$uid' ");
            $roles = User::user_roles($user->id);
            $result['userDetail']['id'] = $user['id'];
            $result['userDetail']['name'] = $user['name'];
            $result['userDetail']['username'] = $user['email'];
            //$result['userDetail']['login_count'] = $current_count;
          //  $result['userDetail']['roles'] = $roles;
            
            if (in_array('Student', $roles)) {
                $result['token'] = $this->generateUserLoginToken($user);
                $result['status'] = array('statusCode' => 1, 'message' => 'Success');
                $result['userDetail']['role'] = 5;
                
            } else if (in_array('Teacher', $roles)) {
                $result['token'] = $this->generateUserLoginToken($user);
                $result['status'] = array('statusCode' => 1, 'message' => 'Success');
                $result['userDetail']['role'] = 4;
               
            } else if (in_array('Admin', $roles)) {
                $result['token'] = $this->generateUserLoginToken($user);
                $result['status'] = array('statusCode' => 1, 'message' => 'Success');
                $result['userDetail']['role'] = 7;
                
            } else {
                unset($result);
                $result = [];
                $result['status'] = array('statusCode' => 0, 'message' => 'YYou are not authorize to login.');
            }
        }

        return response()->json($result)->header('Content-Type', 'application/json');
    }	       


    private function generateUserLoginToken($u) {
        $user = (object) ['id' => $u['id'], 'email' => $u['email'], 'created' => time(), 'expiry' => 25920000];
        return $this->generateToken($user);
    }

    private function generateToken($user) {
        $jwtCredentials = \Config::get('scholar.jwt');
        $ip = $_SERVER["REMOTE_ADDR"];
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $str = md5(uniqid(rand(), true) . $ip . $ua);

        $token = (new Builder())->setId($str)
                ->setAudience($jwtCredentials['aud'])
                ->setIssuer($jwtCredentials['iss'])
                ->set('user', $user)
                ->setHeader('ssh', $jwtCredentials['ssh'])
                ->sign($this->signer, $jwtCredentials['salt'])
                ->getToken();
        $jwt = $token->__toString();
        return $jwt;
    }

    public function logout(Request $request) {
        $token = JWTAuth::getToken();
        JWTAuth::invalidate($token);
        return response()->json(['success' => 'successfully logout'], 200);
    }
}
