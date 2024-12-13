<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MTargetSalesModel extends Model
{
    protected $table = 'm_target_sales';

    protected $guarded = ['id'];

    protected $dates = ['month','created_at', 'updated_at'];
    
    public function salesTargetRelation()
    {
        return $this->belongsTo('App\Models\MUserModel', 'sales','id');
    }
    
        
}
