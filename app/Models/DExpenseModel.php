<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DExpenseModel extends Model
{
    protected $table = 'd_expense';

    protected $guarded = ['id'];
}
