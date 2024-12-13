<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MCPCustomerModel extends Model
{
    protected $table = 'm_cp_customer';

    protected $guarded = ['id'];

    public function customerRelation()
    {
        return $this->belongsTo('App\Models\MCustomerModel','customer','id');
    }
}
