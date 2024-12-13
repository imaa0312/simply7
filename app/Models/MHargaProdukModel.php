<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MHargaProdukModel extends Model
{
    protected $table = 'm_harga_produk';

    protected $guarded = ['id'];

    public function produkRelation()
    {
        return $this->belongsTo('App\Models\MProdukModel','produk','id');
    }
}
