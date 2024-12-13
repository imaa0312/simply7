<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use App\Models\MCustomerModel;
use App\Models\TSalesOrderModel;
use App\Models\TPurchaseOrderModel;
use App\Models\MProdukModel;
use App\Models\MKonfirmasiPembayaran;
use DB;



class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $topDay             = DB::table('t_faktur')->where('jatuh_tempo',date('Y-m-d'))->count();
        $roleSales          = MRoleModel::where('name', 'Sales')->first();
        $getAllSales        = MUserModel::where('role', '=', $roleSales->id)->count();
        $getAllCustomers    = MCustomerModel::count();
        $waitinglist        = TSalesOrderModel::where('status_aprove', 'in approval')->count();
        $waitinglistkirim   = TSalesOrderModel::where('status_aprove', 'in process')->count();
        $powaitinglist      = TPurchaseOrderModel::where('status_aprove', 'in approval')->count();
        $powaitinglistkirim = TPurchaseOrderModel::where('status_aprove', 'in process')->count();
        $waitingassetorder     = DB::table('t_fixed_asset_po')->where('status_aprove', 'in approval')->count();
        $waitingassetordersend = DB::table('t_fixed_asset_po')->where('status_aprove', 'in process')->count();
        $konfirmasipembayarantransferwaiting = MKonfirmasiPembayaran::where('status_pembayaran', 'pending')->count();

        return view('adminlte::home', compact('getAllSales', 'getAllCustomers', 'waitinglist','powaitinglist','powaitinglistkirim','waitinglistkirim','topDay','waitingassetorder','waitingassetordersend','konfirmasipembayarantransferwaiting'));
    }

}
