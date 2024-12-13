<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MProdukModel extends Model
{
	protected $table = 'm_produk';

    protected $guarded = ['id'];

	public function jenisRelation()
	{
		return $this->belongsTo('App\Models\MJenisProdukModel','jenis','id');
	}

	public function bahanRelation()
	{
		return $this->belongsTo('App\Models\MBahanProdukModel','bahan','id');
	}

	public function merekRelation()
	{
		return $this->belongsTo('App\Models\MMerekProdukModel','merek','id');
	}

	public function imagedetail()
	{
		return $this->hasMany(\App\Models\MProdukImage::class, 'produk_id', 'id');
	}
}
