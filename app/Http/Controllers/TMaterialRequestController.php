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
use App\Models\TMaterialRequest;
use Yajra\Datatables\Datatables;

class TMaterialRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
     {
         return view('admin.produksi.materialrequest.index');
     }

     /**
      * Show the form for creating a new resource.
      *
      * @return \Illuminate\Http\Response
      */
     public function create()
     {

       $codeWo = $this->setCode();

       $dataPo = DB::table('t_work_order')
           ->join('d_work_order','d_work_order.wo_code','=','t_work_order.wo_code')
           ->select('t_work_order.wo_code','t_work_order.id')
           ->whereRaw('d_work_order.material_save_qty != d_work_order.material_request_qty')
           ->where('t_work_order.status','post')
           ->orderBy('t_work_order.wo_code','DESC')
           ->groupBy('t_work_order.wo_code','t_work_order.id')
           ->get();

        $gudang = DB::table('m_gudang')->get();

     // $stokGudang = DB::table('')

       $barang_good = DB::table('m_produk')->where('type_barang','finish good')->get();

       $barang_material = DB::table('m_produk')->where('type_barang','raw material')->get();

       $barang = MProdukModel::where('type_asset','asset')->orderBy('name','ASC')->get();

       return view('admin.produksi.materialrequest.create', compact('dataPo','gudang','barang_good','barang_material','barang','codeWo'));
     }

     /**
      * Store a newly created resource in storage.
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function inApprove(Request $request, $id)
      {
          $asset = DB::table('t_material_request')
              ->join('d_material_request','d_material_request.mr_code','t_material_request.mr_code')
              ->select('d_material_request.produk_id')
              ->orderBy('t_material_request.mr_code', 'desc')
              ->get();


          DB::beginTransaction();
          try{
              foreach($asset as $as){
              $qty_sebelum = DB::table('d_material_request')
              ->where('mr_code',$request->mr_code)
              ->first();
              $qty_setelah = $qty_sebelum->qty_request + $request->qty_save;
              DB::table('d_material_request')
              ->where('mr_code',$request->mr_code)
              ->where('produk_id',$as->produk_id)
              ->update([
                  'qty_save' => $qty_setelah,
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

            DB::table('t_material_request')
                  ->where('mr_code',$request->mr_code)
                  ->update(['status' => 'post']);
            return redirect('admin/produksi/mr');
      }

     public function store(Request $request)
     {
         // dd($request->all());
         $array = [];
         $i = 0;
         $success = null;
         $produk_code = $request->produk_code;
         $produk_id = $request->produk_id;
         $deliver = $request->deliver;

         $setCode = $this->setCode();
         $mr_date = date('Y-m-d', strtotime($request->mr_date));

         //arrayProdukID
         foreach($produk_id as $raw_produk_id){
             $array[$i]['id_produk'] = $raw_produk_id;
             $array[$i]['mr_code'] = $request->mr_code;
             // ($i <= count($raw_produk_id)) ? $i++ : $i = 0;
             $i++;
         }
         $i=0;
         foreach($request->dwo_id as $rawdwo_id){
             $array[$i]['dwo_id'] = $rawdwo_id;
             $i++;
         }
         $i=0;
         //arrayProdukCode
         foreach($produk_code as $raw_produk){
             $array[$i]['produk'] = $raw_produk;
             // ($i <= count($raw_produk)) ? $i++ : $i = 0;
             $i++;
         }
         // $i=0;
         // //arrayProdukCode
         // foreach($request->last_qty as $raw_last_qty){
         //     $array[$i]['last_wo_qty'] = $raw_last_qty;
         //     // ($i <= count($raw_produk)) ? $i++ : $i = 0;
         //     $i++;
         // }
         $i=0;
         //arrayQtyDeliver
         foreach($deliver as $raw_deliver){
             $array[$i]['qty_request'] = $raw_deliver;
             $array[$i]['material_request_qty'] =$raw_deliver;


             // ($i <= count($raw_deliver)) ? $i++ : $i = 0;
             $i++;
         }
         //
         // echo "<pre>";
         //     print_r($array);
         // echo "</pre>";
         // dd($request->all());
         // die();
         DB::beginTransaction();
         try{

             for($x=0; $x<count($array); $x++){
                 $qty_sebelum = DB::table('d_work_order')
                 ->where('wo_code',$request->wo_code)
                 ->where('material_id',$array[$x]['id_produk'])
                 ->first();

                 $qty_setelah = $qty_sebelum->material_request_qty + $array[$x]['material_request_qty'];
                 $qty_setelah_material = $qty_sebelum->material_save_qty - $qty_setelah;

                 DB::table('d_work_order')
                 ->where('wo_code',$request->wo_code)
                 ->where('material_id',$array[$x]['id_produk'])
                 ->update([
                     'material_request_qty' => $qty_setelah,
                     'material_qty' => $qty_setelah_material,
                 ]);
             }
             // dd($qty_setelah_material);

             //insert to t surat-jalan
             $id_mr = DB::table('t_material_request')
                 ->insert([
                     'mr_code' => $setCode,
                     'wo_code' => $request->wo_code,
                     'mr_date' => $mr_date,
                     'description' => $request->description,
                     'gudang' => $request->gudang,
                     'user_input'=> auth()->user()->id,
                 ]);

             //insert detail surat-jalan
             for($x=0; $x<count($array); $x++){
                 DB::table('d_material_request')
                 ->insert([
                     'mr_code' => $setCode,
                     'mr_id' => $array[$x]['dwo_id'],
                     'dwo_id' => $array[$x]['dwo_id'],
                     'produk_id' => $array[$x]['id_produk'],
                     'qty_request' => $array[$x]['qty_request'],
                     // 'last_wo_qty' => $array[$x]['last_wo_qty'],
                 ]);

                 DB::table('d_work_order')
                 ->where('wo_code',$request->wo_code)
                 ->where('material_id',$array[$x]['id_produk'])
                 ->update([
                     'wo_id' => $array[$x]['dwo_id'],
                 ]);
                 // dd($id_mr);
             }

             // update d sales order
             DB::commit();
             $success = true;
         }catch(\Exception $e){
             dd($e);
             $success = false;
             DB::rollback();
         }

         return redirect('admin/produksi/mr');
     }

     public function getProdukWO(Request $request)
     {
         $result = DB::table('d_work_order')
                     ->join('m_produk','m_produk.id','=','d_work_order.material_id')
                     ->join('t_work_order','t_work_order.wo_code','=','d_work_order.wo_code')
                     ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
                     ->select('m_produk.code','m_produk.id as produk_id','m_produk.name','d_work_order.*'
                      ,DB::raw('(d_work_order.material_save_qty - d_work_order.material_request_qty) as maxdeviverqty'),'m_satuan_unit.code as code_unit')
                     ->where('d_work_order.wo_code','=',$request->wo_code)
                     // ->where('d_work_order.material_id','=',$request->produk_id)
                     ->get();
                     // dd($result);

                 foreach ($result as $raw_so) {
                     $cekSj = 0;

                     $cekSj = DB::table('d_work_order')
                             ->join('t_work_order','t_work_order.wo_code','d_work_order.wo_code')
                             ->where('d_work_order.id','=',$raw_so->id)
                             ->where('t_work_order.status','!=','cancel')
                             ->get();
                     $raw_so->cek = $cekSj;
                 }

         return Response::json($result);
     }

     /**
      * Display the specified resource.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
     public function show($code)
     {
         $header = DB::table('t_material_request')
             ->join('m_produk','m_produk.id','t_material_request.id')
             ->select('t_material_request.*','m_produk.name')
             ->where('t_material_request.mr_code',$code)
             ->first();
             // dd($header);
         $barang_material = DB::table('d_material_request')
                 ->join('m_produk','m_produk.id','d_material_request.produk_id')
                 ->select('d_material_request.*','m_produk.*')
                 ->where('mr_code',$code)
                 ->get();
             // dd($barang_material);

         return view('admin.produksi.materialrequest.detail',compact('header','barang_material'));
     }

     /**
      * Show the form for editing the specified resource.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
      public function edit($code)
      {
         $dataAsset = DB::table('t_material_request')
             ->join('m_produk','m_produk.id','t_material_request.id')
             ->select('t_material_request.*','m_produk.*')
             ->where('t_material_request.mr_code',$code)
             ->first();


            $detail =  DB::table('d_material_request')
            ->join('d_work_order','d_work_order.id','d_material_request.dwo_id')
            ->join('m_produk','d_material_request.produk_id','m_produk.id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->select('d_material_request.*','m_produk.name','m_produk.code','m_satuan_unit.code as code_unit',
            DB::raw('(d_material_request.qty_request + d_work_order.material_qty)as maxdeviverqty'),
            'd_work_order.material_save_qty','d_work_order.wo_code','d_work_order.material_id')
            ->where('d_material_request.mr_code',$code)
            ->get();
        // dd($detail);
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

        $gudang = DB::table('m_gudang')->get();

        $barang_good = DB::table('m_produk')->where('type_barang','finish good')->get();

        $barang_material = DB::table('m_produk')->where('type_barang','raw material')->get();

        // dd($dataAsset);



        return view('admin.produksi.materialrequest.update', compact('gudang','detail','dataAsset','barang_good','barang_material'));
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
         $mr_date = date('Y-m-d', strtotime($request->mr_date));
         //arrayProdukID
         $array = [];
         $i = 0;
         // dd($request->all());
         foreach($request->id_produk as $raw_id_produk){
             $array[$i]['id_produk'] = $raw_id_produk;
             $array[$i]['mr_code'] = $request->mr_code;
             $i++;
         }
         $i=0;
         foreach($request->dwo_id as $rawdwo_id){
             $array[$i]['dwo_id'] = $rawdwo_id;
             $i++;
         }
         $i=0;
         foreach($request->deliver as $raw_deliver){
             $array[$i]['qty_request'] = $raw_deliver;
             // $array[$i]['material_request_qty'] =$raw_deliver;

             $i++;
         }

         $detailMrLama = DB::table('t_material_request')
                     ->join('d_material_request','d_material_request.mr_code','=','t_material_request.mr_code')
                     ->where('d_material_request.mr_code', $request->mr_code)
                     ->where('d_material_request.dwo_id',$rawdwo_id)
                     ->select('*','d_material_request.qty_request')
                     ->get();
                    //  dd($detailMrLama);

         DB::beginTransaction();
         try{

             $id_mr = DB::table('t_material_request')
                 ->where('mr_code',$request->mr_code)
                 ->update([
                     'mr_date' => $mr_date,
                     'description' => $request->description,
                     'gudang' => $request->gudang,
                     'user_input'=> auth()->user()->id,
                 ]);

                 // dd($qty_setelah_last_wo);
             DB::table('d_material_request')->where('mr_code',$request->mr_code)->delete();

             //insert detail MR
             for($x=0; $x<count($array); $x++){
                 DB::table('d_material_request')
                 ->insert([
                     'mr_code' => $request->mr_code,
                     'dwo_id' => $array[$x]['dwo_id'],
                     'mr_id' => $id_mr,
                     'produk_id' => $array[$x]['id_produk'],
                     'qty_request' => $array[$x]['qty_request'],
                 ]);
             }

             foreach($detailMrLama as $key =>$dt){
                 for($x=0; $x<count($array); $x++){

                 $detailWOId       = $array[$x]['dwo_id'];
                 $produk           = $array[$x]['id_produk'];

                 $qty_sebelum = DB::table('d_work_order')
                 ->where('id',$detailWOId)
                 ->first();
                //  dd($qty_sebelum);

                //  $allSaveQtyWithoutMe = $qty_sebelum->material_request_qty - $dt->qty_request;
                //  dd($allSaveQtyWithoutMe);
                        // 3-((3-1)-2)
                //  $qty_setelah = $qty_sebelum->material_save_qty - ($qty_sebelum->material_save_qty - $allSaveQtyWithoutMe - $array[$x]['qty_request']);
                 $qty_setelah = $qty_sebelum->material_request_qty - $dt->qty_request + $array[$x]['qty_request'];
                //  $qty_setelah_material = $qty_sebelum->material_save_qty - $qty_setelah;
                 DB::table('d_work_order')
                 ->where('wo_id',$detailWOId)
                 ->where('material_id',$produk)
                 ->update([
                     'material_request_qty' => $qty_setelah,
                     'material_qty' => $qty_sebelum->material_save_qty - $qty_setelah,
                 ]);
                //  dd($qty_setelah);
                //  dd($qty_sebelum->material_save_qty - (($qty_sebelum->material_save_qty - $allSaveQtyWithoutMe) - $array[$x]['qty_request']));

             }
         }
             DB::commit();
             $success = true;
         }catch(\Exception $e){
             dd($e);
             $success = false;
             DB::rollback();
         }
         return redirect('admin/produksi/mr');
     }
     /**
      * Remove the specified resource from storage.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
     public function destroy(Request $request)
     {
        $asset = DB::table('t_work_order')
        ->join('d_work_order','d_work_order.wo_code','t_work_order.wo_code')
        ->join('t_material_request','t_material_request.wo_code','t_work_order.wo_code')
        ->join('d_material_request','d_material_request.mr_code','t_material_request.mr_code')
        ->select('d_work_order.material_id','d_work_order.wo_code','d_material_request.qty_request','d_work_order.material_qty',
        'd_material_request.produk_id','d_work_order.material_save_qty','d_material_request.dwo_id','d_work_order.wo_id')
        ->where('d_material_request.mr_code',$request->mr_code)
        // ->where('d_material_request.dwo_id',$request->dwo_id)
        ->get();
         // dd($asset);
         DB::beginTransaction();
         try{
             foreach($asset as $as){
                 $qty_sebelum_hapus = DB::table('d_material_request')
                 ->where('mr_code',$request->mr_code)
                 ->where('produk_id',$as->produk_id)
                 ->where('dwo_id',$as->dwo_id)
                 ->first();
                 // dd($qty_sebelum_hapus);
                $qty_sebelum = DB::table('d_work_order')
                ->where('material_id',$as->material_id)
                ->where('wo_id',$as->wo_id)
                 ->where('wo_code',$as->wo_code)
                 ->first();
                 // dd($qty_sebelum);

                 $qty_setelah_hapus = $qty_sebelum_hapus->qty_request + $as->material_qty;
                 $data_setelah = $qty_sebelum->material_save_qty - $qty_setelah_hapus;

                 DB::table('d_work_order')
                 ->where('material_id',$as->material_id)
                 ->where('wo_id',$as->wo_id)
                 ->where('wo_code',$as->wo_code)
                 ->update([
                     'material_qty' => $qty_setelah_hapus,
                     'material_request_qty' => $data_setelah,
                 ]);
                 // dd($qty_setelah_hapus);
         }
             DB::commit();
             $success = true;
         }catch(\Exception $e){
             dd($e);
             $success = false;
             DB::rollback();
         }
         $detail = DB::table('t_material_request')->where('mr_code',$request->mr_code)->delete();
         if($detail){
         DB::table('d_material_request')->where('mr_code',$request->mr_code)->delete();
        }

         return redirect('admin/produksi/mr');
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
         $getLastCode = DB::table('t_material_request')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

         $dataDate = date('ym');

         $getLastCode = $getLastCode +1;

         $nol = null;

         if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

         return 'MR'.$dataDate.$nol.$getLastCode;
     }


     public function cancelMRPost(Request $request)
     {
         // dd($request->all());
         DB::table('t_material_request')
             ->where('mr_code',$request->mr_code)
             ->update([
             'status' => 'cancel',
             'cancel_reason' => $request->cancel_reason,
             'cancel_description' => $request->cancel_description,
             'user_cancel' => auth()->user()->id,]);

         return redirect('admin/produksi/mr');
     }

     public function cancelMR($pocode)
     {
         $dataMR = DB::table('t_material_request')->where('mr_code',$pocode)
         ->first();
         $reason = DB::table('m_reason')->orderBy('id','DESC')->get();

         return view('admin.produksi.materialrequest.cancel',compact('dataMR','reason'));
     }

     public function dataWo($codeWo)
     {

             $result = DB::table('t_work_order')
             ->select('t_work_order.*')
             ->where('t_work_order.wo_code','=',$codeWo)
             ->first();

             return Response::json($result);
         }

         public function apiMr(){

             $asset = DB::table('t_material_request')
                 ->select('t_material_request.*')
                 ->orderBy('t_material_request.mr_code', 'desc')
                 ->groupBy('t_material_request.id')
                 ->get();

                 foreach ($asset as $dataMU) {
                     $mu = true;
                     $cekMu = DB::table('t_material_usage')
                             ->where('mr_code',$dataMU->mr_code)
                             ->first();
                     // dd($cekSj);
                     if (count($cekMu) > 0 ) {
                         $mu = false; // jika ada false
                     }
                     $dataMU->mu = $mu;
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
                         // '<a href="'. url('admin/asset/report-asset/'.$asset->wo_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                         '<a href="'. url('admin/produksi-edit/mr/'.$asset->mr_code) .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $asset->mr_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                         '<a href="'. url('admin/produksi/delete/mr/'.$asset->mr_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->mr_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                         '<a href="'. url('admin/produksi/produksi-posting/mr/'.$asset->mr_code) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->mr_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                         '</td>'.
                         '</tr>'.
                         '</table>';
                     }
                 }if( $asset->status == 'post'){
                     if( $asset->mu == true){
                         return '<table id="tabel-in-opsi">'.
                         '<tr>'.
                             '<td>'.
                                 '<a href="'. url('admin/report-mr/'.$asset->mr_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->mr_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                 '<a href="'. url('admin/produksi/cancel/mr/'.$asset->mr_code) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $asset->mr_code .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
                             '</td>'.
                         '</tr>'.
                     '</table>';
                 }
                     if( $asset->mu == false){
                         return '<table id="tabel-in-opsi">'.
                             '<tr>'.
                                 '<td>'.
                                     '<a href="'. url('admin/report-mr/'.$asset->mr_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->mr_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                     '<a href="'. url('admin/puchase-order/close/'.$asset->mr_code) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Close '. $asset->mr_code .'" ><span class="fa fa-hand-paper-o"></span></a>'.'&nbsp;'.
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
                            '<a href="'. url('admin/report-mr/'.$asset->mr_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->mr_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                            '<a href="'. url('admin/produksi/delete/mr/'.$asset->mr_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->mr_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                         '</td>'.
                     '</tr>'.
                 '</table>';
                 }
             })
             ->editColumn('wo_code', function($asset){
                 return '<medium>'. $asset->wo_code .'</medium> ';
             })
             ->editColumn('mr_code', function($asset){
                 return '<a href="'. url('admin/produksi/detail/mr/'.$asset->mr_code) .'">'. $asset->mr_code .'</a> ';
             })
             ->editColumn('mr_date', function($asset){
                 return date('d-m-Y',strtotime($asset->mr_date));
             })
             // ->editColumn('type_asset', function($asset){
             //       return ucfirst($asset->asset_type);
             // })
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
         ->rawColumns(['wo_code','mr_code','qty_request2','mr_date','status','action'])
         ->make(true);
         }

         public function laporanMr()
         {
             $dataSupplier = DB::table('d_material_request')
             ->select('d_material_request.*')
             ->groupBy('d_material_request.id')
             ->get();

             $dataBarang = DB::table('m_produk')
             ->select('id as barang_id','name')
             ->groupBy('id')
             ->get();
             // dd($dataSupplier);

             return view('admin.produksi.materialrequest.laporan',compact('dataSupplier','dataBarang'));
         }

         public function getBarangByMr($code)
         {

             $dataBarang = DB::table('m_produk')
             ->rightjoin('d_material_request', 'd_material_request.produk_id', '=', 'm_produk.id')
             ->join('t_material_request', 't_material_request.id', '=', 'd_material_request.id')
             ->select('m_produk.id as barang_id','m_produk.name')
             ->where('d_material_request.mr_code',$code)
             ->groupBy('m_produk.id')
             ->get();

             return Response::json($dataBarang);
       }

       public function getMrcodeByPeriode($periode)
       {
           $tglmulai = substr($periode,0,10);
           $tglsampai = substr($periode,13,10);

           $dataSupplier = DB::table('t_material_request')
           ->join('d_material_request', 't_material_request.id', '=', 'd_material_request.id')
           ->select('t_material_request.id as mr_id','t_material_request.mr_code')
           ->where('t_material_request.mr_date','>=',date('Y-m-d', strtotime($tglmulai)))
           ->where('t_material_request.mr_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
           ->groupBy('t_material_request.id')
           ->get();
           return Response::json($dataSupplier);
       }

     }
