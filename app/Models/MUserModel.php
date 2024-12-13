<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
class MUserModel extends Authenticatable
{
    use SoftDeletes;
    protected $table = 'm_user';
    protected $dates = ['deleted_at'];

    public function roles()
    {
        return $this->belongsToMany('App\Models\MRoleModel')->withTimestamps();
    }
}
