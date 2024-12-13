<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MTemporarySalesOrderModel;



class MTemporarySalesOrderController extends Controller
{
    public function index()
    {
        $data = MTemporarySalesOrderModel::join('temp_m_customer','temp_m_customer.code','=','temp_t_sales_order.customer_code')
               ->join('m_user','m_user.id','=','temp_t_sales_order.sales')
               ->select('temp_t_sales_order.*','temp_m_customer.*','m_user.name as sales')
               ->get();
        return view('admin.customer.temp-customer.temp-sales',compact('data'));
    }
}
