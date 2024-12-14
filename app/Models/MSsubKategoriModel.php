<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MSsubKategoriModel extends Model
{
    protected $table = 'm_ssub_kategori_produk';

    protected $guarded = ['id'];

    public function kategoriRelation()
    {
    	return $this->belongsTo('\App\Models\MSubKategoriModel','sub_kategori_id','id');
    }
}
