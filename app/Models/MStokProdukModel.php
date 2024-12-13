<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MStokProdukModel extends Model
{
    protected $table = 'm_stok_produk';

    protected $guarded = ['id'];

    public function produkRelation()
    {
        return $this->belongsTo('\App\Models\MProdukModel','produk_code','code');
    }

    public function gudangRelation()
    {
        return $this->belongsTo('\App\Models\MGudangModel','gudang','id');
    }
}
