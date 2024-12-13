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
use App\Models\TProductionResult;
use App\Models\TFixedAssetPoModel;
use App\Models\MStokProdukModel;
use Yajra\Datatables\Datatables;

class ProductionResultController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.produksi.productionresult.index');
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
            // ->join('d_work_order','d_work_order.wo_code','=','t_work_order.wo_code')
            ->select('t_work_order.wo_code','t_work_order.id')
            ->whereRaw('fg_qty != 0')
            ->where('t_work_order.status','post')
            ->orderBy('t_work_order.wo_code','DESC')
            ->groupBy('t_work_order.wo_code','t_work_order.id')
            ->get();
            // dd($dataPo);
         $gudang = DB::table('m_gudang')->get();

      // $stokGudang = DB::table('')

        $barang_good = DB::table('m_produk')->where('type_barang','finish good')->get();

        $barang_material = DB::table('m_produk')->where('type_barang','raw material')->get();

        $barang = MProdukModel::where('type_asset','asset')->orderBy('name','ASC')->get();


      return view('admin.produksi.productionresult.create', compact('dataPo','barang_good','barang_material','barang','codeWo','gudang'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $array = [];
        $i = 0;
        $success = null;
        $produk_code = $request->produk_code;
        $produk_id = $request->produk_id;
        $deliver = $request->deliver;

        $setSj = $this->setCode();
        $pr_date = date('Y-m-d', strtotime($request->pr_date));

        //arrayProdukID
        foreach($produk_id as $raw_produk_id){
            $array[$i]['id_produk'] = $raw_produk_id;
            $array[$i]['pr_code'] = $request->pr_code;
            // ($i <= count($raw_produk_id)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;
        //arrayProdukCode
        foreach($produk_code as $raw_produk){
            $array[$i]['produk'] = $raw_produk;
            // ($i <= count($raw_produk)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;
        //arrayQtyDeliver
        foreach($deliver as $raw_deliver){
            $array[$i]['qty_result'] = $raw_deliver;

            // ($i <= count($raw_deliver)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;
        //
        // echo "<pre>";
        //     print_r($array);
        // echo "</pre>";
        // dd($request->all());
        // die();
        DB::beginTransaction();
        try{
            //insert to t surat-jalan
            $store = new TProductionResult;
            // $store->sj_code = $request->sj_code;
            $store->pr_code = $setSj;
            $store->wo_code = $request->wo_code;
            $store->pr_date = $pr_date;
            $store->description = $request->description;
            $store->gudang = $request->gudang;
            $store->user_input = auth()->user()->id;
            $store->save();

            //insert detail surat-jalan
            $data = DB::table('t_work_order')
                ->select('t_work_order.*')
                ->where('wo_code',$request->wo_code)
                ->get();

            for($x=0; $x<count($array); $x++){
                DB::table('d_production_result')
                ->insert([
                    'pr_code' => $store->pr_code,
                    'produk_id' => $array[$x]['id_produk'],
                    'description' => $store->description,
                    'qty_result' => $array[$x]['qty_result'],
                    'last_pr_qty' => 0,
                ]);
                foreach($data as $dt){
                $qty_work_order = DB::table('t_work_order')
                    ->where('wo_code',$dt->wo_code)
                    ->first();

                $qty_produksi = $array[$x]['qty_result'];

                $qty_save = $qty_work_order->fg_qty_save - $qty_produksi;
                $qty_sudah_produksi = $qty_work_order->fg_qty_production + $qty_produksi;

                DB::table('t_work_order')
                ->where('wo_code',$dt->wo_code)
                // ->where('fg_id',$dt->fg_id)
                ->update([
                    'fg_qty_save' => $qty_save,
                    'fg_qty_production' => $qty_sudah_produksi,
                ]);
                }
            }

            // update d sales order
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }
      return redirect('admin/produksi/pr');
    }

    public function apiPr(){

        $asset = DB::table('t_production_result')
            ->select('t_production_result.*')
            ->orderBy('t_production_result.pr_code', 'desc')
            ->get();

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
                    '<a href="'. url('admin/produksi-edit/pr/'.$asset->pr_code) .'" class="btn btn-sm btn-primary" data-toggle="tooltip" title="Ubah '. $asset->pr_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/produksi/delete/pr/'.$asset->pr_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->pr_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/produksi/produksi-posting/pr/'.$asset->pr_code) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->pr_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                }else {
                    // if($asset->print == false){
                    //     return '<table id="tabel-in-opsi">'.
                    //     '<tr>'.
                    //     '<td>'.
                    //     // '<a href="  '. url('admin/asset/report-asset/'.$asset->wo_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                    //     '<a href="'. url('admin/asset/asset-edit/'.$asset->wo_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $asset->wo_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    //     '<a href="'. url('admin/asset/asset-delete/'.$asset->wo_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->wo_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    //     '<a href="'. url('admin/asset/asset-posting/'.$asset->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->wo_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    //     '</td>'.
                    //     '</tr>'.
                    //     '</table>';
                    // }
                    // else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        // '<a href="'. url('admin/asset/asset-edit/'.$asset->wo_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $asset->wo_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/asset-delete/'.$asset->pr_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->pr_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                        // '<a href="'. url('admin/asset/asset-posting/'.$asset->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->wo_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    // }
                }
            }elseif($asset->status == 'post' ){
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                '<td>'.
                '<a href="'. url('admin/produksi/report-pr/'.$asset->pr_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                '<a href="'. url('admin/produksi/cancel/pr/'.$asset->pr_code) .'" class="btn btn-sm btn-danger" data-toggle="tooltip"  title="Cancel '. $asset->pr_code  .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
                '</td>'.
                '</tr>'.
                '</table>';
            }else{
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                '<td>'.
                '<a href="'. url('admin/produksi/report-pr/'.$asset->pr_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                '<a href="'. url('admin/asset/asset-delete/'.$asset->pr_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->pr_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                '</td>'.
                '</tr>'.
                '</table>';
            }
        })
        ->editColumn('pr_code', function($asset){
            return '<a href="'. url('admin/produksi/detail/pr/'.$asset->pr_code) .'">'. $asset->pr_code .'</a> ';
        })
        ->editColumn('pr_date', function($asset){
            return date('d-m-Y',strtotime($asset->pr_date));
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
    ->rawColumns(['pr_code','routing_code','pr_date','status','action'])
    ->make(true);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($code)
    {
        $header = DB::table('t_production_result')
            ->select('t_production_result.*','m_gudang.name')
            ->join('m_gudang','m_gudang.id','t_production_result.gudang')
            ->where('t_production_result.pr_code',$code)
            ->first();
            // dd($header);

        $barang_material = DB::table('d_production_result')
            ->join('m_stok_produk','m_stok_produk.id','d_production_result.id')
            ->join('m_produk','m_produk.id','d_production_result.produk_id')
            ->select('d_production_result.*','m_produk.*','m_stok_produk.balance')
            ->where('pr_code',$code)
            ->get();

        return view('admin.produksi.productionresult.detail',compact('header','barang_material'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($code)
    {
        $dataAsset = DB::table('t_production_result')
            ->join('m_produk','m_produk.id','t_production_result.id')
            ->join('t_work_order','t_work_order.wo_code','t_production_result.wo_code')
            ->join('d_production_result','d_production_result.pr_code','t_production_result.pr_code')
            ->join('m_routing','m_routing.code','t_work_order.routing_code')
            ->select('t_production_result.*','m_produk.name','t_work_order.routing_code','m_routing.name as routing_name')
            ->where('t_production_result.pr_code',$code)
            ->first();
            // dd($dataAsset);

           $detail =  DB::table('d_production_result')
           ->join('m_produk','d_production_result.produk_id','m_produk.id')
           ->join('t_production_result','t_production_result.pr_code','d_production_result.pr_code')
           ->join('t_work_order','t_work_order.wo_code','t_production_result.wo_code')
           ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
           ->select('d_production_result.*','m_produk.name','m_produk.code',
           DB::raw('(t_work_order.fg_qty_save + d_production_result.qty_result )as maxdeviverqty'),'m_satuan_unit.code as code_unit','t_work_order.wo_code','t_work_order.fg_qty_save','t_work_order.fg_qty')
           ->where('d_production_result.pr_code',$code)
           ->get();

       // dd($detail);
       // $gudang = 1;
       //
       // foreach ($detail as $raw_data) {
       //  $balance = DB::table('t_work_order')
       //      ->where('t_work_order.wo_code', $raw_data->wo_code)
       //      ->groupBy('t_work_order.wo_code')
       //      ->where('status', 'post')
       //      ->sum('fg_qty');
       //
       //  $stok = DB::table('d_production_result')
       //      ->join('t_production_result','t_production_result.pr_code','d_production_result.pr_code')
       //      ->join('t_work_order','t_work_order.wo_code','t_production_result.wo_code')
       //      ->where('t_production_result.wo_code', $raw_data->wo_code)
       //      ->groupBy('t_production_result.wo_code')
       //      ->sum('qty_result');
       //
       //  $stok = $balance - $stok + $raw_data->qty_result;
       //
       //  $raw_data->fg_qty = $stok;
       // }

       $gudang = DB::table('m_gudang')->get();

       $barang_good = DB::table('m_produk')->where('type_barang','finish good')->get();

       $barang_material = DB::table('m_produk')->where('type_barang','raw material')->get();

       // dd($dataAsset);



       return view('admin.produksi.productionresult.update', compact('gudang','detail','dataAsset','barang_good','barang_material'));
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
        $prcode = $request->pr_code;
        $pr_date = date('Y-m-d', strtotime($request->pr_date));
        $array = [];
        $i = 0;

        foreach($request->id_produk as $raw_id_produk){
            $array[$i]['id_produk'] = $raw_id_produk;
            $array[$i]['pr_code'] = $prcode;
            // ($i <= count($raw_produk_id)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;
        //arrayQtyDeliver
        foreach($request->deliver as $raw_deliver){
            $array[$i]['qty_result'] = $raw_deliver;

            // ($i <= count($raw_deliver)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;

        DB::beginTransaction();
        try{
            $id_pr = DB::table('t_production_result')
                ->where('pr_code',$prcode)
                ->update([
                    'pr_date' => $pr_date,
                    'description' => $request->description,
                    'gudang' => $request->gudang,
                    'user_input'=> auth()->user()->id,
                ]);

            for($x=0; $x<count($array); $x++){
                $data = DB::table('t_production_result')
                ->join('d_production_result','d_production_result.pr_code','t_production_result.pr_code')
                ->join('t_work_order','t_work_order.wo_code','t_production_result.wo_code')
                ->select('d_production_result.*','t_work_order.wo_code as code','t_work_order.fg_qty_save','t_work_order.fg_qty_production')
                ->where('d_production_result.pr_code',$request->pr_code)
                ->get();
                // dd($data);

                foreach($data as $raw_data){
                    $qty_work_order = DB::table('t_work_order')
                        ->where('wo_code',$raw_data->code)
                        ->first();

                    $qty_production = DB::table('d_production_result')
                        ->where('pr_code',$request->pr_code)
                        ->first();

                    $qty_produksi_baru = $array[$x]['qty_result'];

                    $qty_save_baru = $qty_work_order->fg_qty_save + $qty_production->qty_result - $qty_produksi_baru;
                    $qty_sudah_produksi_baru = $qty_work_order->fg_qty_production - $qty_production->qty_result + $qty_produksi_baru;

                    // $qty_setelah_update = $qty_sebelum_update->fg_qty_save - $raw_data->maxdeliver + $array[$x]['qty_result'];
                    // $qty_produksi = $qty_sebelum_update->fg_qty_save - $qty_setelah_update;

                    DB::table('t_work_order')
                       ->where('wo_code',$raw_data->code)
                       ->update([
                           'fg_qty_save' => $qty_save_baru,
                           'fg_qty_production' => $qty_sudah_produksi_baru,
                       ]);
                   // dd($qty_setelah_update);
                }
            }

            //insert detail pr
            DB::table('d_production_result')->where('pr_code',$prcode)->delete();

            for($x=0; $x<count($array); $x++){
                DB::table('d_production_result')
                ->insert([
                    'pr_code' => $prcode,
                    'produk_id' => $array[$x]['id_produk'],
                    'qty_result' => $array[$x]['qty_result'],
                    'description' => $request->description,
                    'last_pr_qty' => 0,
                ]);
            }

            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }
      return redirect('admin/produksi/pr');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$prCode)
    {
        $asset = DB::table('t_production_result')
        ->join('d_production_result','d_production_result.pr_code','t_production_result.pr_code')
        ->join('t_work_order','t_work_order.wo_code','t_production_result.wo_code')
        ->select('t_work_order.fg_qty','d_production_result.produk_id','t_work_order.wo_code','t_work_order.fg_qty_save')
        ->where('d_production_result.pr_code',$request->pr_code)
        ->get();

        DB::beginTransaction();
        try{
            foreach($asset as $as){
                $qty_production = DB::table('d_production_result')
                    ->where('pr_code',$request->pr_code)
                    ->first();

                $qty_work_order = DB::table('t_work_order')
                    ->where('wo_code',$as->wo_code)
                    ->first();

                $qty_setelah_hapus = $qty_work_order->fg_qty_save + $qty_production->qty_result;
                $qty_sudah_produksi = $qty_work_order->fg_qty_production - $qty_production->qty_result;

                DB::table('t_work_order')
                ->where('t_work_order.wo_code',$as->wo_code)
                // ->where('material_id',$as->material_id)
                // ->where('wo_id',$as->wo_id)
                ->update([
                    'fg_qty_save' => $qty_setelah_hapus,
                    'fg_qty_production' => $qty_sudah_produksi,
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
        $data = DB::table('t_production_result')->where('pr_code',$prCode)->delete();
        if($data){
            DB::table('d_production_result')->where('pr_code',$prCode)->delete();
        }
        return redirect('admin/produksi/pr');
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

    protected function setCode()
    {
        $getLastCode = DB::table('t_production_result')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'PR'.$dataDate.$nol.$getLastCode;
    }

    public function getProdukWOPr(Request $request)
    {
        $result = DB::table('t_work_order')
            ->join('m_produk','m_produk.id','=','t_work_order.fg_id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->select('m_produk.code','m_produk.id as produk_id','m_produk.name',
            DB::raw('(t_work_order.fg_qty - t_work_order.fg_qty_production)as maxdeviverqty')
            ,'m_satuan_unit.code as code_unit','t_work_order.*')
            ->where('t_work_order.wo_code','=',$request->wo_code)
            ->get();

        foreach ($result as $raw_so) {
            $cekSj = 0;

            $cekSj = DB::table('t_work_order')
                    ->where('t_work_order.id','=',$raw_so->id)
                    ->where('t_work_order.status','!=','cancel')
                    ->get();
            $raw_so->cek = $cekSj;

            $balance = DB::table('t_work_order')
                ->where('t_work_order.wo_code', $raw_so->wo_code)
                ->groupBy('t_work_order.wo_code')
                ->where('status', 'post')
                ->sum('fg_qty');

            $stok = DB::table('d_production_result')
                ->join('t_production_result','t_production_result.pr_code','d_production_result.pr_code')
                ->join('t_work_order','t_work_order.wo_code','t_production_result.wo_code')
                ->where('t_production_result.wo_code', $raw_so->wo_code)
                ->groupBy('t_production_result.wo_code')
                ->sum('qty_result');

            $stok = $balance - $stok;

            $raw_so->qty_fg = $stok;

        }

        return Response::json($result);
    }

    public function dataWo($codeWo)
    {
        $result = DB::table('t_work_order')
            ->select('t_work_order.*')
            ->where('t_work_order.wo_code','=',$codeWo)
            ->first();

        return Response::json($result);
    }

    public function getRoutingbyWo($code)
    {
        $dataRouting = DB::table('t_work_order')
        ->join('d_work_order','d_work_order.id','=','t_work_order.id')
        ->join('m_routing','m_routing.code','t_work_order.routing_code')
        ->select('t_work_order.routing_code','t_work_order.id as wo_id','m_routing.name')
        ->where('t_work_order.wo_code',$code)
        ->groupBy('t_work_order.id')
        ->groupBy('m_routing.id')
        ->get();

        return Response::json($dataRouting);
    }

    public function inApprove($prcode)
    {
        $data_pr = DB::table('t_production_result')
            ->join('d_production_result','d_production_result.pr_code','t_production_result.pr_code')
            ->join('m_produk','m_produk.id','d_production_result.produk_id')
            ->select('t_production_result.pr_code','t_production_result.gudang','d_production_result.produk_id','m_produk.code as produk_code','d_production_result.qty_result')
            ->orderBy('t_production_result.pr_code', 'desc')
            ->where('t_production_result.pr_code',$prcode)
            ->get();

        // dd($data_pr);

        $success = false;
        DB::beginTransaction();
        try{
            foreach($data_pr as $raw_data){
                $insertStokModel = new MStokProdukModel;
                $insertStokModel->produk_code       =  $raw_data->produk_code;
                $insertStokModel->transaksi         =  $raw_data->pr_code;
                $insertStokModel->tipe_transaksi    =  'Production Result';
                $insertStokModel->person            =  1;
                $insertStokModel->stok_awal         =  0;
                $insertStokModel->gudang            =  $raw_data->gudang;
                $insertStokModel->stok              =  $raw_data->qty_result;
                $insertStokModel->type              =  'in';
                $insertStokModel->save();
            }
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }

        if ($success == true) {
            DB::table('t_production_result')
                ->where('pr_code',$prcode)
                ->update([
                    'status' => 'post',
                ]);
        }
        
        return redirect('admin/produksi/pr');
    }

    public function getPrcodeByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataSupplier = DB::table('t_production_result')
        ->join('d_production_result', 't_production_result.id', '=', 'd_production_result.id')
        ->select('t_production_result.id as pr_id','t_production_result.pr_code')
        ->where('t_production_result.pr_date','>=',date('Y-m-d', strtotime($tglmulai)))
        ->where('t_production_result.pr_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
        ->groupBy('t_production_result.id')
        ->get();
        return Response::json($dataSupplier);
    }

    public function laporanPr()
    {
        $dataSupplier = DB::table('d_production_result')
        ->select('d_production_result.*')
        ->groupBy('d_production_result.id')
        ->get();

        $dataBarang = DB::table('m_produk')
        ->select('id as barang_id','name')
        ->groupBy('id')
        ->get();
        // dd($dataSupplier);

        return view('admin.produksi.productionresult.laporan',compact('dataSupplier','dataBarang'));
    }

    public function cancelPRPost(Request $request)
    {
        // dd($request->all());
        DB::table('t_production_result')
            ->where('pr_code',$request->pr_code)
            ->update([
            'status' => 'cancel',
            'cancel_reason' => $request->cancel_reason,
            'cancel_description' => $request->cancel_description,
            'user_cancel' => auth()->user()->id,]);

        return redirect('admin/produksi/pr');
    }

    public function cancelPR($prcode)
    {
        $dataPR = DB::table('t_production_result')->where('pr_code',$prcode)
        ->first();
        $reason = DB::table('m_reason')->orderBy('id','DESC')->get();

        return view('admin.produksi.productionresult.cancel',compact('dataPR','reason'));
    }
}
