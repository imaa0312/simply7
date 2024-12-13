<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TSalesOrderModel extends Model
{
    protected $table = 't_sales_order';

    protected $guarded = ['id'];

    protected $dates = ['so_date','sending_date','created_at','updated_at'];

    public function customerRelation()
    {
        return $this->belongsTo('App\Models\MCustomerModel','customer','id');
    }

    public function salesRelation()
    {
        return $this->belongsTo('App\Models\MUserModel','sales','id');
    }
}
