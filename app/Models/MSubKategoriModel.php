<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MSubKategoriModel extends Model
{
    protected $table = 'm_sub_kategori_produk';

    protected $guarded = ['id'];

    public function kategoriRelation()
    {
    	return $this->belongsTo('\App\Models\MKategoriModel','kategori_id','id');
    }
}
