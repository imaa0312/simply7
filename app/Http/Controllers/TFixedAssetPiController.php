<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class TFixedAssetPiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
     {
         $dataSupplier = DB::table('m_supplier')
             ->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
             ->select('m_supplier.id as supplier_id','name')
             ->where('t_purchase_invoice.status','unpaid')
             ->groupBy('m_supplier.id')
             ->get();

         $dataPI = DB::table('t_purchase_invoice')
             ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
             ->where('t_purchase_invoice.status', '!=', 'unpaid')
             ->where('t_purchase_invoice.type','pifa')
             ->select('t_purchase_invoice.po_code','t_purchase_invoice.pi_code','t_purchase_invoice.sj_masuk_code','t_purchase_invoice.jumlah_yg_dibayarkan','t_purchase_invoice.status','m_supplier.name as supplier','m_supplier.id as id_supplier','t_purchase_invoice.id as pi_id','t_purchase_invoice.total','t_purchase_invoice.print')
             ->orderBy('t_purchase_invoice.id','DESC')
             ->get();

         return view('admin.fixed-asset.purchase-invoice.index', compact('dataPI','dataSupplier'));
     }

     public function waiting()
     {
         $dataSupplier = DB::table('m_supplier')
             ->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
             ->select('m_supplier.id as supplier_id','name')
             ->where('t_purchase_invoice.status','unpaid')
             ->groupBy('m_supplier.id')
             ->get();

         $dataPI = DB::table('t_purchase_invoice')
             ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
             ->where('t_purchase_invoice.status', '=', 'unpaid')
             ->where('t_purchase_invoice.type','pifa')
             ->select('t_purchase_invoice.po_code','t_purchase_invoice.pi_code','t_purchase_invoice.sj_masuk_code','t_purchase_invoice.jumlah_yg_dibayarkan','t_purchase_invoice.status','m_supplier.name as supplier','m_supplier.id as id_supplier','t_purchase_invoice.id as pi_id','t_purchase_invoice.total','t_purchase_invoice.print')
             ->orderBy('t_purchase_invoice.id','DESC')
             ->get();

         return view('admin.fixed-asset.purchase-invoice.waiting', compact('dataPI','dataSupplier'));
     }
 public function detail($pi_code)
     {
         $dataPI = DB::table('t_purchase_invoice')
             ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
             ->select('*','t_purchase_invoice.status as status_payment')
             ->where('pi_code',$pi_code)
             ->first();

         $dataPembayaran = DB::table('d_pi_pembayaran')
             ->join('t_pi_pembayaran', 'd_pi_pembayaran.pembayaran_code', '=', 't_pi_pembayaran.pembayaran_code')
             ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pi_pembayaran.type')
             ->leftjoin('m_bank', 'm_bank.id', '=', 't_pi_pembayaran.bank')
             ->join('m_supplier', 'm_supplier.id', '=', 't_pi_pembayaran.supplier')
             ->select('*','t_pi_pembayaran.type as type_payment','m_bank.name as bank_name','t_pi_pembayaran.status as status_pembayaran')
             ->where('pi_code',$pi_code)
             ->orderBy('t_pi_pembayaran.pembayaran_code')
             ->get();

         return view('admin.fixed-asset.purchase-invoice.detail', compact('dataPI','dataPembayaran'));
     }

}
