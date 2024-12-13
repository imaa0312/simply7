<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MSaldoAwalPiutang extends Model
{
    protected $table = 'm_saldo_awal_piutang';

    protected $guarded = ['id'];

    public function customerRelation()
    {
    	return $this->belongsTo('\App\Models\MCustomerModel','customer','id');
    }
}
