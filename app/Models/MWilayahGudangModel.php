<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MWilayahGudangModel extends Model
{
    protected $table = 'm_wilayah_gudang';

    protected $guarded = ['id'];

    public function gudangRelation()
    {
        return $this->belongsTo('App\Models\MGudangModel','gudang','id');
    }

    public function kotaRelation()
    {
        return $this->belongsTo('App\Models\MKotaKabModel','kota_kab','id');
    }
}
