<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MKelurahanDesaModel extends Model
{
    protected $table = 'm_kelurahan_desa';

    protected $guarded = ['id'];

    public function kecamatanRelation()
    {
        return $this->belongsTo('App\Models\MKecamatanModel','kecamatan','id');
    }
}
