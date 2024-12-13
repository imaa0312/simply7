<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MProdukModel;

class DAdjusmentModel extends Model
{
    protected $table = 'd_adjusment';

    protected $guarded = ['id'];

    public function produkRelation()
    {
        return $this->belongsTo('\App\Models\MProdukModel','produk','id');
    }
}
