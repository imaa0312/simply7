<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MAlamatCustomerModel extends Model
{
    protected $table = 'm_alamat_customer';

    protected $guarded = ['id'];

    public function customerRelation()
    {
        return $this->belongsTo('App\Models\MCustomerModel','customer','id');
    }
}
