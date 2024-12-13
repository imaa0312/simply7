<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Response;
use Illuminate\Http\Request;
use App\Models\MProdukModel;
use App\Models\MSupplierModel;
use App\Models\MJangkaWaktu;
use App\Models\MReasonModel;
use App\Models\TFixedAssetPoModel;
use Yajra\Datatables\Datatables;

class TWorkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.produksi.wo.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

      $codeWo = $this->setCode();

      $dataPo = DB::table('m_routing')
            ->select('m_routing.id', 'm_routing.code')
            ->orderBy('m_routing.code','DESC')
            ->groupBy('m_routing.code','m_routing.id')
            ->get();

      $barang_good = DB::table('m_produk')->where('type_barang','finish good')->get();

      $barang_material = DB::table('m_produk')->where('type_barang','raw material')->get();

      $barang = MProdukModel::where('type_asset','asset')->orderBy('name','ASC')->get();

      return view('admin.produksi.wo.create', compact('dataPo','barang_good','barang_material','barang','codeWo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function store(Request $request)
     {
         $setCode = $this->setCode();
         $wo_date = date('Y-m-d', strtotime($request->wo_date));


         $routing = DB::table('m_routing')->where('id',$request->routing)->first();
         // dd($request->finish_good);

         $array = [];
         $i = 0;
         foreach($request->id_produk as $rawid){
             $array[$i]['wo_code'] = $setCode;
             $array[$i]['material_id'] = $rawid;
             $i++;
         }
         $i = 0;
         foreach($request->qty as $rawqty){
             $array[$i]['material_qty'] = $rawqty;
             $i++;
         }

         DB::beginTransaction();
         try{
             $id_wo = DB::table('t_work_order')
                 ->insert([
                     'wo_code' => $setCode,
                     'wo_date' => $wo_date,
                     'routing_code' => $routing->code,
                     'routing_id' => $request->routing,
                     'fg_id' => $request->finish_good,
                     'fg_qty' => $request->qty_result,
                     'fg_qty_save' => $request->qty_result,
                     'sdlc' => $routing->sdlc,
                     'soc' => $routing->soc,
                     'user_input'=> auth()->user()->id,
                 ]);

             //detail-po
             for($n=0; $n<count($array); $n++){
                 DB::table('d_work_order')
                     ->insert([
                         'wo_code' => $setCode,
                         'wo_id' => $id_wo,
                         'material_id'=> $array[$n]['material_id'],
                         'material_qty'=> $array[$n]['material_qty'],
                         'material_request_qty'=> 0,
                         'material_save_qty'=> 0,
                     ]);
             }

             DB::commit();
         }catch(\Exception $e){
             DB::rollback();
             dd($e);
         }

         return redirect('admin/produksi/wo');
     }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $header = DB::table('t_work_order')
            ->join('m_produk','m_produk.id','t_work_order.fg_id')
            ->select('t_work_order.*','m_produk.name')
            ->where('t_work_order.wo_code',$request->wo_code)
            ->first();
            // dd($header);
        $barang_material = DB::table('d_work_order')
            ->join('m_stok_produk','m_stok_produk.id','d_work_order.id')
            ->join('m_produk','m_produk.id','d_work_order.material_id')
            ->select('d_work_order.*','m_produk.*','m_stok_produk.balance')
            ->where('wo_code',$request->wo_code)
            ->get();
            // dd($barang_material);

        return view('admin.produksi.wo.detail',compact('header','barang_material'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function edit(Request $request,$code)
     {

        $dataAsset = DB::table('t_work_order')
            ->join('m_produk','m_produk.id','t_work_order.fg_id')
            ->select('t_work_order.*','m_produk.*')
            ->where('t_work_order.wo_code',$code)
            ->first();
        // dd($dataAsset);

        $detail =  DB::table('d_work_order')
            ->join('m_produk','d_work_order.material_id','m_produk.id')
            ->select('d_work_order.*','m_produk.code','m_produk.name')
            ->where('d_work_order.wo_code',$code)
            ->get();

        $gudang = 1;

            foreach ($detail as $raw_data) {
                $balance = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $gudang)
                    ->where('type', 'closing')
                    ->sum('balance');

                $stok = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $gudang)
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('stok');

                $stok = $stok + $balance;

                $raw_data->stok = $stok;
            }
            // dd($detail);

            $barang_good = DB::table('m_produk')->where('type_barang','finish good')->get();

            $barang_material = DB::table('m_produk')->where('type_barang','raw material')->get();

       // dd($dataAsset);



       return view('admin.produksi.wo.update', compact('dataAsset','barang_good','barang_material','detail'));
     }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {

        // dd($request->all());
        $wo_date = date('Y-m-d', strtotime($request->wo_date));


        $array = [];
        $i = 0;
        foreach($request->id_produk as $rawid){
            $array[$i]['wo_code'] = $request->wo_code;
            $array[$i]['material_id'] = $rawid;
            $i++;
        }
        $i = 0;
        foreach($request->qty as $rawqty){
            $array[$i]['material_qty'] = $rawqty;
            $i++;
        }

        DB::beginTransaction();
        try{
            $id_wo = DB::table('t_work_order')
                ->where('wo_code',$request->wo_code)
                ->update([
                    'wo_date' => $wo_date,
                    'routing_code' => $request->routing,
                    'fg_id' => $request->finish_good,
                    'fg_qty' => $request->qty_result,
                    'fg_qty_save' => $request->qty_result,
                    'sdlc' => $request->sdlc,
                    'soc' => $request->soc,
                    'user_input'=> auth()->user()->id,
                ]);

        DB::table('d_work_order')->where('wo_code',$request->wo_code)->delete();

            //detail-po
    for($n=0; $n<count($array); $n++){
        DB::table('d_work_order')
            ->insert([
                'wo_code' => $request->wo_code,
                'wo_id' => $id_wo,
                'material_id'=> $array[$n]['material_id'],
                'material_qty'=> $array[$n]['material_qty'],
                'material_request_qty'=> 0,
            ]);
        }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/produksi/wo');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteWo = DB::table('t_work_order')->where('wo_code',$id)->delete();

        if($deleteWo){
            DB::table('d_work_order')->where('wo_code',$id)->delete();
        }

        return view('admin.produksi.wo.index',compact('deleteWo'));
    }

    // public function getRouting($code)
    // {
    //     $dataPo = DB::table('m_routing')
    //         ->select('m_routing.sdlc','m_routing.soc')
    //         ->where('m_routing.code','=',$code)
    //         ->first();
    //
    //     return Response::json($dataPo);
    // }

    public function getRow(Request $request)
    {
        $result = DB::table('m_produk')
            ->select('m_produk.*','m_satuan_unit.code as code_unit')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->where('m_produk.id', $request->id)
            ->first();

        //ambil stok
        $gudang = 1;

        $date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

        $balance = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $result->code)
            ->where('m_stok_produk.gudang', $gudang)
            ->where('type', 'closing')
            // ->whereMonth('periode',date('m', strtotime($date_last_month)))
            // ->whereYear('periode',date('Y', strtotime($date_last_month)))
            ->sum('balance');

        $stok = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $result->code)
            ->where('m_stok_produk.gudang', $gudang)
            // ->whereMonth('created_at',date('m', strtotime($date_now)))
            // ->whereYear('created_at',date('Y', strtotime($date_now)))
            ->groupBy('m_stok_produk.produk_code')
            ->sum('stok');

        $stok = $stok + $balance;

        $result->stok=$stok;

        return Response::json($result);
    }

    protected function setCode()
    {
        $getLastCode = DB::table('t_work_order')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'WO'.$dataDate.$nol.$getLastCode;
    }

    public function apiWo(){

        $asset = DB::table('t_work_order')
            ->join('d_work_order','d_work_order.wo_code','t_work_order.wo_code')
            ->select('t_work_order.*')
            ->orderBy('t_work_order.wo_code', 'desc')
            ->groupBy('t_work_order.id')
            ->get();

            foreach ($asset as $dataWO) {
                $wo = true;
                $cekMr = DB::table('t_material_request')
                        ->where('wo_code',$dataWO->wo_code)
                        ->first();
                // dd($cekSj);
                if (count($cekMr) > 0 ) {
                    $wo = false; // jika ada false
                }
                $dataWO->wo = $wo;
            }

        $roleSuperAdmin = DB::table('m_role')
            ->where('name','Super Admin')
            ->first();

        $i=0;
        // dd($asset);
        return Datatables::of($asset)
        ->addColumn('action', function ($asset) use ($i){
            if(  $asset->status == 'in process'){
                if(auth()->user()->role == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    '<a href="'. url('admin/report-wo/'.$asset->wo_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->wo_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/produksi-edit/'.$asset->wo_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $asset->wo_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/produksi/delete/'.$asset->wo_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->wo_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/produksi/produksi-posting/'.$asset->wo_code) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->wo_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                }else {
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    '<a href="'. url('admin/report-wo/'.$asset->wo_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->wo_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/asset/asset-delete/'.$asset->wo_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->wo_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    // '<a href="'. url('admin/asset/asset-posting/'.$asset->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->wo_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                // }
                }
            }elseif($asset->status == 'post'){
                if( $asset->wo == true){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'. url('admin/report-wo/'.$asset->wo_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->wo_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                            '<a href="'. url('admin/produksi/cancel/wo/'.$asset->wo_code) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $asset->wo_code .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
                        '</td>'.
                    '</tr>'.
                '</table>';
            }

                if( $asset->wo == false){
                    return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-wo/'.$asset->wo_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->wo_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/produksi/close/'.$asset->wo_code) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Close '. $asset->wo_code .'" ><span class="fa fa-hand-paper-o"></span></a>'.'&nbsp;'.
                            '</td>'.
                        '</tr>'.
                    '</table>';
                }
                else{
                    return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-mr/'.$asset->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$asset->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/transaksi-purchasing-order/copy/'.$asset->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'. '</tr>'.
                            '</td>'.
                        '</tr>'.
                    '</table>';
                }
            }else{
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                '<td>'.
                '<a href="'. url('admin/report-wo/'.$asset->wo_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->wo_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                '<a href="'. url('admin/produksi/delete/'.$asset->wo_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->wo_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                '</td>'.
                '</tr>'.
                '</table>';
            }
        })
        ->editColumn('wo_code', function($asset){
            return  '<a href="'. url('admin/produksi/detail/'.$asset->wo_code) .'">'. $asset->wo_code .'</a> ';
        })
        ->editColumn('wo_date', function($asset){
            return date('d-m-Y',strtotime($asset->wo_date));
        })
        ->editColumn('status', function($asset){
            if( $asset->status == 'in process' ){
                return '<span class="label label-default">in process</span>';
            }
            elseif ($asset->status == 'post'){
                return '<span class="label label-success">post</span>';
            }
            elseif ($asset->status == 'close'){
                return '<span class="label label-danger">close</span>';
            }
            elseif ($asset->status == 'cancel'){
                return '<span class="label label-danger">cancel</span>';
            }
        })
    ->addIndexColumn()
    ->rawColumns(['wo_code','routing_code','wo_date','status','action'])
    ->make(true);
    }

    public function inApprove(Request $request,$pocode)
    {
        $asset = DB::table('t_work_order')
            ->join('d_work_order','d_work_order.wo_code','t_work_order.wo_code')
            ->select('d_work_order.material_id','t_work_order.fg_id','t_work_order.fg_qty')
            ->orderBy('t_work_order.wo_code', 'desc')
            ->get();
        // dd($request->all());
        DB::beginTransaction();
        try{
            foreach($asset as $as){
            $qty_sebelum = DB::table('d_work_order')
            ->where('wo_code',$request->wo_code)
            ->first();

            $qty_setelah = $qty_sebelum->material_qty + $request->material_save_qty;

            DB::table('d_work_order')
            ->where('wo_code',$request->wo_code)
            ->where('material_id',$as->material_id)
            ->update([
                'material_save_qty' => $qty_setelah,
            ]);
        }
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }
        // dd($qty_setelah);

        DB::table('t_work_order')
            ->where('wo_code',$pocode)
            ->update(['status' => 'post']);
        return redirect('admin/produksi/wo');
    }

    public function cancelWOPost(Request $request)
    {
        dd($request->all());

        DB::beginTransaction();
        try {
            DB::table('t_work_order')->where('wo_code',$request->wo_code)->update([
                'cancel_reason' => $request->cancel_reason,
                'cancel_description' => $request->cancel_description,
                'user_cancel' => auth()->user()->id,
                'status' => 'cancel',
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('admin/produksi/wo');
    }

    public function cancelWO($pocode)
    {
        $dataWO = DB::table('t_work_order')->where('wo_code',$pocode)
        ->first();
        $reason = DB::table('m_reason')->orderBy('id','DESC')->get();

        return view('admin.produksi.wo.cancel',compact('dataWO','reason'));
    }

    public function closeWO($wocode)
    {
            DB::table('t_work_order')->where('wo_code',$wocode)->update([
                'status' => 'close',
            ]);

            return redirect('admin/produksi/wo');
    }

    public function laporanWo()
    {
        $dataSupplier = DB::table('d_work_order')
        ->select('d_work_order.*')
        ->groupBy('d_work_order.id')
        ->get();

        $dataBarang = DB::table('m_produk')
        ->select('id as barang_id','name')
        ->groupBy('id')
        ->get();
        // dd($dataSupplier);

        return view('admin.produksi.wo.laporan',compact('dataSupplier','dataBarang'));
    }

    public function getBarangByWo($code)
    {

        $dataBarang = DB::table('m_produk')
        ->rightjoin('d_work_order', 'd_work_order.material_id', '=', 'm_produk.id')
        ->join('t_work_order', 't_work_order.id', '=', 'd_work_order.id')
        ->select('m_produk.id as barang_id','m_produk.name')
        ->where('d_work_order.wo_code',$code)
        ->groupBy('m_produk.id')
        ->get();

        return Response::json($dataBarang);
  }

  public function getWocodeByPeriode($periode)
  {
      $tglmulai = substr($periode,0,10);
      $tglsampai = substr($periode,13,10);

      $dataSupplier = DB::table('t_work_order')
      ->join('d_work_order', 't_work_order.id', '=', 'd_work_order.id')
      ->select('t_work_order.id as wo_id','t_work_order.wo_code')
      ->where('t_work_order.wo_date','>=',date('Y-m-d', strtotime($tglmulai)))
      ->where('t_work_order.wo_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
      ->groupBy('t_work_order.id')
      ->get();
      return Response::json($dataSupplier);
  }

}
