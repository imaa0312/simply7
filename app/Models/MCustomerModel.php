<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class MCustomerModel extends Model
{
    //use SoftDeletes;

    protected $table = 'm_customer';

    //protected $dates = ['deleted_at'];

    protected $guarded = ['id'];
}
