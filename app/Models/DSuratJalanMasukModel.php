<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DSuratJalanMasukModel extends Model
{
    protected $table = 'd_surat_jalan_masuk';

    protected $guarded = ['id'];

    // public function soRelation()
    // {
    //     return $this->belongsTo('App\Models\TSalesOrderModel','so_code','so_code');
    // }
    //
    // public function sjRelation()
    // {
    //     return $this->belongsTo('App\Models\TSuratJalanModel','sj_code','sj_code');
    // }
}
