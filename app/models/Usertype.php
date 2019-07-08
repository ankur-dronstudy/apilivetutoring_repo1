<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\models\User;

class Usertype extends Model {

    protected $table = 'usertypes';
    protected $fillable = ['type'];

    public function users() {
        return $this->hasMany('App\models\User');
    }

}
