<?php

namespace App\Http\Controllers;

use DB;
use App\Models\MGudangModel;
use Illuminate\Http\Request;
use App\Models\MStokProdukModel;
use Yajra\Datatables\Datatables;



class MStokProdukController extends Controller
{

    public function index()
    {
        $dataGudang = MGudangModel::all();
        return view('admin.transaksi.stok.all-gudang',compact('dataGudang'));
    }

    public function stokProdukGudang($id)
    {
        $gudang = MGudangModel::where('id',$id)->first();

        $dataProduk = DB::table('m_stok_produk')
                    ->leftjoin('m_produk', 'm_produk.code', '=', 'm_stok_produk.produk_code')
                    ->select(DB::raw('SUM(stok) as jmlh_stok'), 'm_stok_produk.gudang', 'm_stok_produk.produk_code', 'm_produk.name as produk','m_produk.id as produk_id','m_produk.type_barang')
                    ->groupBy('produk_code')
                    ->orderBy('produk_code', 'DESC')
                    ->where('gudang', $id)
                    ->get();

        
        // dd($gudang);
        // return view('admin.transaksi.stok.index', compact('dataProduk','id'));
        return view('admin.transaksi.stok.index-server-side', compact('dataProduk','id','gudang'));
    }

    public function updateStokProduk(Request $request, $id)
    {
        $code  = $request->code;
        $type = $request->type;
        $dataProduk = DB::table('m_stok_produk')
                    ->join('m_produk', 'm_produk.code', '=', 'm_stok_produk.produk_code')
                    ->select('m_stok_produk.*', 'm_produk.name as produk')
                    ->where('produk_code', '=', $code)
                    ->where('gudang', $id)
                    ->first();
        // dd($dataProduk);
        return view('admin.transaksi.stok.update',compact('dataProduk','type','code', 'id'));
    }

    public function update(Request $request)
    {
        $this->validate($request,[
            'code' => 'required',
            'stok' => 'required|numeric',
            'type' => 'required'
        ]);

        $stok_awal = DB::table('m_stok_produk')
            ->select('m_stok_produk.produk_code','m_stok_produk.type_barang')
            ->where('m_stok_produk.produk_code', $request->code)
            ->where('m_stok_produk.gudang', $request->id_gudang)
            ->groupBy('m_stok_produk.produk_code','m_stok_produk.type_barang')
            ->sum('m_stok_produk.stok');

        $cekstok = null;
        ($request->type == 'in') ? $cekstok = $request->stok : $cekstok = -$request->stok;

        $stok = new MStokProdukModel;
        $stok->produk_code = $request->code;
        $stok->transaksi = "Stok Produk (+)";
        $stok->tipe_transaksi = "Edit Stok";
        $stok->stok_awal = $stok_awal;
        $stok->stok = $cekstok;
        $stok->gudang = $request->id_gudang;
        $stok->type = $request->type;
        $stok->save();

        return redirect('admin/transaksi-stok/'.$request->id_gudang);
    }

    public function laporanStok()
    {
        $dataGudang = DB::table('m_gudang')
                ->select('id as gudang_id','name')
                ->groupBy('id','name')
                ->get();

        // $dataBarang = DB::table('m_produk')
        //         ->select('code as barang_code','name')
        //         ->groupBy('id')
        //         ->get();

        $dataBarang = DB::table('m_stok_produk')
            ->join('m_produk','m_produk.code','=','m_stok_produk.produk_code')
            ->select('m_produk.id','m_produk.code as barang_code','m_produk.name',DB::raw('SUM(m_stok_produk.stok) as stok'))
            ->groupBy('m_produk.id','m_produk.code','name')
            // ->where('m_stok_produk.stok','!=', 0)
            ->where(function ($query) {
                $query->where('m_stok_produk.stok','!=', 0)
                ->orWhere('m_stok_produk.balance','!=',0);
            })
            ->get();

        return view('admin.transaksi.stok.laporan',compact('dataGudang','dataBarang'));
    }

    public function apiStokProduk($id)
    {
        $dataProduk = DB::table('m_stok_produk')
                    ->leftjoin('m_produk', 'm_produk.code', '=', 'm_stok_produk.produk_code')
                    ->select(DB::raw('SUM(stok) as jmlh_stok'), 'm_stok_produk.gudang', 'm_stok_produk.produk_code', 'm_produk.name as produk','m_produk.id as produk_id','m_produk.type_barang')
                    ->groupBy('produk_code')
                    ->orderBy('produk_code', 'DESC')
                    ->where('gudang', $id)
                    ->get();

        $date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

        foreach ($dataProduk as $raw_data) {
            $balance = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $raw_data->produk_code)
                ->where('m_stok_produk.gudang', $id)
                ->where('type', 'closing')
                ->whereMonth('periode',date('m', strtotime($date_last_month)))
                ->whereYear('periode',date('Y', strtotime($date_last_month)))
                ->sum('balance');

            $stok = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $raw_data->produk_code)
                ->where('m_stok_produk.gudang', $id)
                ->whereMonth('created_at',date('m', strtotime($date_now)))
                ->whereYear('created_at',date('Y', strtotime($date_now)))
                ->groupBy('m_stok_produk.produk_code')
                ->sum('stok');

            $stok_akhir = $stok + $balance;

            $raw_data->jmlh_stok = $stok_akhir;
        }


        return Datatables::of($dataProduk)
            ->addColumn('action',function($dataProduk){
                return '<a href="'.route("produk.stok.update",["id" => $dataProduk->gudang ,"code" => $dataProduk->produk_code, "type" => "in"]).'"  class="btn btn-success btn-sm"  data-toggle="tooltip" data-placement="top" title="Tambah Stok Produk '.$dataProduk->produk.'"> <span class="fa fa-plus"></span></a> </a>';
            })
            ->editColumn('type_barang', function($dataProduk){
                return ucwords($dataProduk->type_barang);
            })
            ->addIndexColumn()
            ->rawColumns(['action','type_barang'])
            ->make(true);
    }
}
