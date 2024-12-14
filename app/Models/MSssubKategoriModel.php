<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MSssubKategoriModel extends Model
{
    protected $table = 'm_sssub_kategori_produk';

    protected $guarded = ['id'];

    public function kategoriRelation()
    {
    	return $this->belongsTo('\App\Models\MSsubKategoriModel','ssub_kategori_id','id');
    }
}
