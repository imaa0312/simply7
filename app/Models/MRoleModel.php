<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MRoleModel extends Model
{
    protected $table = "m_role";
    protected $guarded = ['id'];

    public function users()
    {
      	return $this->belongsToMany('App\Models\MUserModel')->withTimestamps();
    }
}
