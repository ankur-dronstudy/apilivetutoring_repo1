<?php

namespace App\models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Validator;
use App\models\Usertype;
use App\library\Functions;
use App\models\RoleUser;
use App\Classes\SSAuth;
use DB;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable,
        CanResetPassword;

    protected $table = 'users';
    public $timestamps = false;
    protected $fillable = [
        'registration_id','unique_id','house','dob','email', 'username', 'first_name', 'last_name', 'password', 'name', 'gender','academic_year', 'category', 'blood_group', 'allergies', 'mobile', 'session','pic', 'shift', 'religion', 'religion','admission_type' , 'parent_category','address', 'college_id', 'is_active', 'onboarded', 'user_details', 'guardian_details', 'health_details', 'login_count','State', 'City', 'Country', 'CreatedAt', 'UpdatedAt'
    ];
    protected $hidden = ['password', 'remember_token'];
    public static $rules = array(
        'name' => 'sometimes|required|max:40',
        'email' => 'email|max:255',
        'username' => 'sometimes|required|max:30|unique:users',
        'registration_id' => 'sometimes|required|unique:users',
        'password' => 'sometimes|required|min:6',
        'mobile' => 'sometimes|required|numeric|digits:10',
    );
    public static $signuprules = array(
        'name' => 'sometimes|required|max:40',
        'email' => 'email|max:255|unique:users',
        'username' => 'sometimes|required|max:30|unique:users',
        'password' => 'sometimes|required|min:6',
        'mobile' => 'sometimes|required|numeric|digits:10',
    );
    // public static $upaterules = array(
    //     'name' => 'sometimes|max:40',
    //     'email' => 'email|max:255',
    //     'username' => 'max:30|unique:users',
    //     'password' => 'min:6',
    //     'mobile' => 'numeric|digits:10',
    // );
    // public static $onboardingRules = array(
    //     'fname' => 'sometimes|required|max:40',
    //     'mobile' => 'sometimes|required|numeric|digits:10',
    //     'email' => 'sometimes|required|email|max:255',
    //     'exam' => 'sometimes|required|numeric|max:30',
    //     'apdate' => 'sometimes|required',
    // );

    public static $updatingRules = array(
        'mobile' => 'sometimes|required|numeric|digits:10',
        'username' => 'sometimes|required',
        'gender' => 'sometimes|required',
    );
    public static $password_rules = array(
        'p' => 'sometimes|required|min:6|different:op',
        'cp' => 'sometimes|required|same:p',
    );
    public static $updateMobileRules = [
        'mobile' => 'sometimes|required|numeric|digits:10|unique:users',
        'otp' => 'sometimes|required'
    ];

    public static function isOnboarded($id) {
        return User::where('id', $id)->pluck('onboarded');
    }

    public static function updateOnboard($id, $onboard) {
        return User::where('id', $id)->update(['onboarded' => $onboard]);
    }

    public function usertype() {

        return $this->belongsTo('App\models\Usertype', 'usertype_id');
    }

    public function quizzes() {
        return $this->hasMany('App\models\Quiz');
    }

    public static function userlist() {
        return User::get();
    }

    public static function generate_activation_code($id) {

        $code = Functions::generating_random_string(12, "App\models\User", "code");
        $user = User::where("id", $id)->first();
        $user->code = $code;
        $user->save();

        return route("account_activation", array($user->email, $code));
    }

    public static function role($id, $type) {
        $roles = RoleUser::join("roles", "roles.id", '=', "role_user.role_id")
                        ->where('role_user.user_id', $id)->get();

        $user_role = [];
        foreach ($roles as $role) {
            $user_role[] = $role->role;
        }
        if (in_array($type, $user_role)) {
            return true;
        } else {
            return false;
        }
    }

    public static function roles($id) {
        $roles = RoleUser::join("roles", "roles.id", '=', "role_user.role_id")
                        ->where('role_user.user_id', $id)->get();

        $user_role = [];
        foreach ($roles as $role) {
            $user_role['role_id'] = $role->role_id;
            $user_role['role_name'] = $role->role;
        }
        return $user_role;
    }

    public static function user_roles($id) {
        $roles = DB::select("Select * FROM roles JOIN role_user ON roles.id = role_user.role_id WHERE role_user.user_id='$id'");
        $user_role = [];
        foreach ($roles as $role) {
            $user_role[] = $role->role;
        }
        return $user_role;
    }

    public static function username($id) {
        $username = User::where('id', $id)->pluck('username');
        return $username;
    }

    public static function email($id) {
        $email = User::where('id', $id)->value('email');
        return $email;
    }

    public static function name($id) {
        $name = User::where('id', $id)->value('name');
        return $name;
    }
}
