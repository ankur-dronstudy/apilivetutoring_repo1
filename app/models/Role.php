<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {

    public $timestamps = false;
    protected $table = 'roles';
    protected $fillable = [
        'role',
    ];

    public function users() {
        return $this->belongsToMany('App\User');
    }

}
