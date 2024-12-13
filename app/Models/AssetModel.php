<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetModel extends Model
{
    protected $table = 't_purchase_order';

    protected $guarded = ['type_asset'];
}
