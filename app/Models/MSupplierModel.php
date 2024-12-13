<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class MSupplierModel extends Model
{
    //use SoftDeletes;

    protected $table = 'm_supplier';

    //protected $dates = ['deleted_at'];

    protected $guarded = ['id'];
}
