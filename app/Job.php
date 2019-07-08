<?php

namespace App\models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Validator;
use App\models\Usertype;
use App\library\Functions;
use App\Classes\SSAuth;
use DB;

class Job extends Model
{
	// public $timestamps = false;
	// protected $table = 'jobs';
	// protected $fillable = [
	// 'company_id', 'user_id', 'title', 'stipend', 'description', 'type'
	// ];
	public static $rules = [
		'company_id' => 'required|unique:jobs',
		'user_id' => 'required',
		'title' => 'sometimes|required|max:50',
		'description' => 'sometimes|required|max:50',
		'type' => 'required|min:1',
		];
}
?>
