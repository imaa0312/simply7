<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MTemporarySalesOrderModel extends Model
{
    protected $table = 'temp_t_sales_order';

    protected $guarded = ['id'];
}
