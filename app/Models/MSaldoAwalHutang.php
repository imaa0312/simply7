<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MSaldoAwalHutang extends Model
{
    protected $table = 'm_saldo_awal_hutang';

    protected $guarded = ['id'];

    public function supplierRelation()
    {
    	return $this->belongsTo('\App\Models\MSupplierModel','supplier','id');
    }
}
