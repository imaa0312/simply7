<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MProdukModel;

class LaporanHppController extends Controller
{
    public function index()
    {
        $products = MProdukModel::orderBy('name')->get();

        return view('admin/accounting/cash-bank/laporan-hpp',compact('products'));
    }

    public function detail()
    {
        $tglmulai = date('d-m-Y');
        $tglsampai = date('d-m-Y',strtotime('+ 1 day'));

        return view('admin/report/laporan-hpp',compact('tglmulai','tglsampai'));
    }
}
