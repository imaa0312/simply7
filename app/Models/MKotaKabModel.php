<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MKotaKabModel extends Model
{
    protected $table = 'm_kota_kab';
    
    protected $guarded = ['id'];
    
    public function provinsiRelation()
    {
        return $this->belongsTo('App\Models\MProvinsiModel','provinsi','id');
    }
}
