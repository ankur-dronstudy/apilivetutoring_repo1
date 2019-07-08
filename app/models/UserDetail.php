<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model {

    public $timestamps = false;
    protected $table = 'user_details';
    protected $fillable = [
        'user_id', 'college_id','program_id','branch_id','department_id','role_id','start_date','CreatedAt','UpdatedAt'];
    public static $rules = array(
        'user_id' => 'required', 'program_id' => 'required','branch_id'=> 'sometimes', 'role_id' => 'required');    

}
?>
