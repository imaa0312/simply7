<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MKecamatanModel extends Model
{
    protected $table = 'm_kecamatan';

    protected $guarded = ['id'];

    public function kotaRelation()
    {
        return $this->belongsTo('App\Models\MKotaKabModel','kota_kab','id');
    }
}
