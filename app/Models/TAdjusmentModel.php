<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MGudangModel;
use App\Models\MUserModel;

class TAdjusmentModel extends Model
{
    protected $table = 't_adjusment';

    protected $guarded = ['id'];

    public function gudangRelation()
    {
        return $this->belongsTo('\App\Models\MGudangModel','gudang','id');
    }

    public function userRelation()
    {
        return $this->belongsTo('\App\Models\MUserModel','user_input','id');
    }

    public function detailAdjusment()
    {
        return $this->hasMany('\App\Models\DAdjusmentModel','ta_code','ta_code');
    }

}
