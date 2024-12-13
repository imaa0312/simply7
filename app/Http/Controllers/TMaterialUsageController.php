<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Response;
use Illuminate\Http\Request;
use App\Models\MProdukModel;
use App\Models\MSupplierModel;
use App\Models\MJangkaWaktu;
use App\Models\TMaterialUsage;
use App\Models\MReasonModel;
use App\Models\TFixedAssetPoModel;
use App\Models\MStokProdukModel;
use Yajra\Datatables\Datatables;

class TMaterialUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.produksi.materialusage.index');
    }

    protected function setCode()
    {
        $getLastCode = DB::table('t_material_usage')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'MU'.$dataDate.$nol.$getLastCode;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $codeWo = $this->setCode();

        $dataPo = DB::table('t_material_request')
            ->join('d_material_request','d_material_request.mr_code','t_material_request.mr_code')
            ->select('t_material_request.mr_code','t_material_request.id')
            ->where('status','post')
            ->whereRaw('d_material_request.qty_request != d_material_request.qty_usage')
            ->orderBy('t_material_request.mr_code','DESC')
            ->groupBy('t_material_request.mr_code','t_material_request.id')
            ->get();

         $gudang = DB::table('m_gudang')->get();

      // $stokGudang = DB::table('')

        $barang_good = DB::table('m_produk')->where('type_barang','finish good')->get();

        $barang_material = DB::table('m_produk')->where('type_barang','raw material')->get();

        $barang = MProdukModel::where('type_asset','asset')->orderBy('name','ASC')->get();

      return view('admin.produksi.materialusage.create', compact('dataPo','barang_good','barang_material','barang','codeWo','gudang'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $array = [];
        $i = 0;
        $success = null;
        $produk_code = $request->produk_code;
        $produk_id = $request->produk_id;
        $deliver = $request->deliver;

        $setSj = $this->setCode();
        $mu_date = date('Y-m-d', strtotime($request->mu_date));

        //arrayProdukID
        foreach($produk_id as $raw_produk_id){
            $array[$i]['id_produk'] = $raw_produk_id;
            $array[$i]['mu_code'] = $request->mu_code;
            // ($i <= count($raw_produk_id)) ? $i++ : $i = 0;
            $i++;
        }

        $i=0;
        foreach($request->dmr_id as $rawdmr_id){
            $array[$i]['dmr_id'] = $rawdmr_id;
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
            $array[$i]['qty_usage'] = $raw_deliver;

            // ($i <= count($raw_deliver)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;
        //
        // echo "<pre>";
        //     print_r($array);
        // echo "</pre>";
        // die();
        DB::beginTransaction();
        try{
            // $asset = DB::table('t_material_request')
            //     ->join('d_material_request','d_material_request.mr_code','t_material_request.mr_code')
            //     ->select('d_material_request.produk_id')
            //     ->orderBy('t_material_request.mr_code', 'desc')
            //     ->get();

            for($x=0; $x<count($array); $x++){
                // foreach($asset as $as){
                $qty_sebelum = DB::table('d_material_request')
                ->where('mr_code',$request->mr_code)
                ->where('produk_id',$array[$x]['id_produk'])
                ->first();

                $qty_setelah = $qty_sebelum->qty_usage + $array[$x]['qty_usage'];
                $qty_setelah_material = $qty_sebelum->qty_request - $qty_setelah;

                DB::table('d_material_request')
                ->where('mr_code',$request->mr_code)
                ->where('produk_id',$array[$x]['id_produk'])
                ->update([
                    'qty_usage' => $qty_setelah,
                    'qty_save' => $qty_setelah_material,
                ]);
                // dd($qty_setelah_material);
            // }
        }

            //insert to t MU
            $store = new TMaterialUsage;
            $store->mu_code = $setSj;
            $store->mr_code = $request->mr_code;
            $store->mu_date = $mu_date;
            $store->description = $request->description;
            // $store->gudang = $request->gudang;
            $store->user_input = auth()->user()->id;
            $store->save();
            // dd($store);
            //insert detail MU
            for($x=0; $x<count($array); $x++){
                DB::table('d_material_usage')
                ->insert([
                    'mu_code' => $store->mu_code,
                    'dmr_id' => $array[$x]['dmr_id'],
                    'produk_id' => $array[$x]['id_produk'],
                    'qty_usage' => $array[$x]['qty_usage'],
                ]);

                DB::table('d_material_request')
                ->where('mr_code',$request->mr_code)
                ->where('produk_id',$array[$x]['id_produk'])
                ->update([
                    'mr_id' => $array[$x]['dmr_id'],
                ]);
            }
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }

      return redirect('admin/produksi/mu');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($code)
    {
        $header = DB::table('t_material_usage')
            ->join('m_produk','m_produk.id','t_material_usage.id')
            ->select('t_material_usage.*','m_produk.name')
            ->where('t_material_usage.mu_code',$code)
            ->first();
            // dd($header);
        $barang_material = DB::table('d_material_usage')
                ->join('m_stok_produk','m_stok_produk.id','d_material_usage.id')
                ->join('m_produk','m_produk.id','d_material_usage.produk_id')
                ->select('d_material_usage.*','m_produk.*','m_stok_produk.balance')
                ->where('mu_code',$code)
                ->get();


            // dd($barang_material);

        return view('admin.produksi.materialusage.detail',compact('header','barang_material'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$code)
    {
        $dataAsset = DB::table('t_material_usage')
            ->select('*')
            ->where('mu_code',$request->mu_code)
            ->first();


        $detail =  DB::table('d_material_usage')
        ->join('d_material_request','d_material_request.id','d_material_usage.dmr_id')
        ->join('m_produk','d_material_usage.produk_id','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
        ->select('d_material_usage.*','m_produk.name','m_produk.code','m_satuan_unit.code as code_unit',
                 DB::raw('(d_material_usage.qty_usage + d_material_request.qty_save) as maxdeviverqty'),
                 'd_material_request.qty_request')
        ->where('d_material_usage.mu_code',$request->mu_code)
        ->get();

        // dd($dataAsset);

        return view('admin.produksi.materialusage.update', compact('dataAsset','detail'));
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
        $mu_date = date('Y-m-d', strtotime($request->mu_date));
        //arrayProdukID
        $array = [];
        $i = 0;
        // dd($request->all());
        foreach($request->id_produk as $raw_id_produk){
            $array[$i]['id_produk'] = $raw_id_produk;
            $array[$i]['mu_code'] = $request->mu_code;
            $i++;
        }
        $i=0;
        foreach($request->deliver as $raw_deliver){
            $array[$i]['qty_usage'] = $raw_deliver;
            $i++;
        }
        $i=0;
        foreach($request->dmr_id as $raw_dmr_id){
            $array[$i]['dmr_id'] = $raw_dmr_id;
            $i++;
        }
        $detailMrLama = DB::table('t_material_usage')
                    ->join('d_material_usage','d_material_usage.mu_code','=','t_material_usage.mu_code')
                    ->where('t_material_usage.mu_code', $request->mu_code)
                    ->where('d_material_usage.dmr_id',$raw_dmr_id)
                    ->select('*','d_material_usage.qty_usage')
                    ->get();

        DB::beginTransaction();
        try{
            DB::table('t_material_usage')
                ->where('mu_code',$request->mu_code)
                ->update([
                    'mu_date' => $mu_date,
                    'description' => $request->description,
                    'user_input'=> auth()->user()->id,
                ]);

                // dd($qty_setelah_last_wo);
            DB::table('d_material_usage')->where('mu_code',$request->mu_code)->delete();

            //insert detail MR
            for($x=0; $x<count($array); $x++){
                DB::table('d_material_usage')
                ->insert([
                    'mu_code' => $request->mu_code,
                    'produk_id' => $array[$x]['id_produk'],
                    'qty_usage' => $array[$x]['qty_usage'],
                    'dmr_id' => $array[$x]['dmr_id']
                ]);
            }

            foreach($detailMrLama as $mr){
                for($x=0; $x<count($array); $x++){

                $detailMRId       = $array[$x]['dmr_id'];

                $oldMr = DB::table('d_material_request')->where('id',$detailMRId)->first();
                // dd($oldMr);

                // $allSaveQtyWithoutMe = $oldMr->qty_request - $mr->qty_received;
                // dd($allSaveQtyWithoutMe);


                $qty_setelah = $oldMr->qty_usage - $mr->qty_usage + $array[$x]['qty_usage'];
                $qty_setelah_material = $oldMr->qty_request - $qty_setelah;
                // $qty_setelah = ($qty_sebelum->material_save_qty - $qty_sebelum_material->qty_request) + ($qty_sebelum_material->qty_request - $array[$x]['qty_request']);

                $setelah = DB::table('d_material_request')
                // ->where('wo_code',$dt->wo_code)
                // ->where('material_id',$array[$x]['id_produk'])
                ->where('id',$detailMRId)
                ->update([
                    'qty_usage' => $qty_setelah,
                    'qty_save' => $qty_setelah_material,
                ]);
                // dd($qty_setelah);
            }
        }
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }
        return redirect('admin/produksi/mu');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$code)
    {
        $asset = DB::table('t_material_request')
            ->join('d_material_request','d_material_request.mr_code','t_material_request.mr_code')
            ->select('d_material_request.*')
            ->get();
        // dd($request->all());
        DB::beginTransaction();
        try{
            foreach($asset as $as){
            $qty_sebelum_hapus = DB::table('d_material_usage')
            ->where('mu_code',$request->mu_code)
            ->where('produk_id',$as->produk_id)
            ->first();
            // ->where('dwo_id',$as->dwo_id)

            $qty_setelah_hapus = $qty_sebelum_hapus->qty_usage + $as->qty_save;
            $data_setelah = $as->qty_request - $qty_setelah_hapus;

            DB::table('d_material_request')
            ->where('mr_code',$as->mr_code)
            ->where('mr_id',$as->mr_id)
            ->update([
                'qty_save' => $qty_setelah_hapus,
                'qty_usage' => $data_setelah,
            ]);
            // dd($data_setelah);
        }
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }

        $deleteMu = DB::table('t_material_usage')->where('mu_code',$code)->delete();
        if($deleteMu){
            DB::table('d_material_usage')->where('mu_code',$code)->delete();
        }

        return view('admin.produksi.materialusage.index',compact('deleteWo'));
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

    $result = DB::table('m_routing')
        ->select('m_produk.*','m_satuan_unit.code as code_unit')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
        ->where('m_produk.id', $request->id)
        ->first();

    $produk = $request->produk;


    $cekproduk = 0;
    if ($produk != null || $produk != '') {
        foreach ($produk as $i => $raw_produk) {
            if ($request->id == $produk[$i]) {
                $cekproduk = 1;
            }
        }
    }

    if( $cekproduk == 0 ){

        $row = "<tr id='tr_".$request->id."'>";

        $row .= "<input type='hidden' value='". $request->id ."'name='id_produk[".$request->length."]' id='produk_id_". $request->id."'>";


        $row .= "<td> <input type='text' class='form-control input-sm' readonly value='".$result->name."' name='produk[".$request->length."]' data-toggle='tooltip' data-placement='top' title='".$result->name."' style='curpor:pointer;'></td>";


        $row .= "<td> <input type='text' class='form-control input-sm text-only-number' id='". $request->id."_persen'
        lass='form-control input-sm' onkeyup='hitungSubTotal(". $request->id.")' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' autocomplete='off' onchange='hitungSubTotal(". $request->id.")' value='0' name='persen[".$request->length."]'></td>";

        $row .= "<td> <input type='text' id='". $request->id."_potongan' class='form-control input-sm text-only-number' onkeyup='hitungSubTotal(". $request->id.")' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' onchange='hitungSubTotal(". $request->id.")' value='0' name='potongan[".$request->length."]'></td>";

        $row .= "<td>
        <input type='number' min='1' class='form-control input-sm ". $request->id."_produkPrice' value='' name='hargaProduk[".$request->length."]' id='". $request->id."_harga' onkeyup='hitungSubTotal(".$request->id.");' onkeypress='hitungSubTotal(". $request->id.");' autocomplete='off' onchange='hitungSubTotal(". $request->id.");' required>
        </td>";

        $row .= "<td> <input type='number' min='1' max='' id='".$request->id."_jumlah' class='form-control input-sm' onkeyup='hitungSubTotal(".$request->id.");' onkeypress='hitungSubTotal(". $request->id.");' autocomplete='off' onchange='hitungSubTotal(". $request->id.");' name='jumlah[".$request->length."]' value='1'></td>";

        $row .= "<td> <input type='number' min='0' max='' class='form-control input-sm' onkeyup='hitungSubTotal(".$request->id.");' name='free[".$request->length."]' value='0'></td>";

        $row .= "<td> <input type='text' class='form-control input-sm' readonly name='satuan[".$request->length."]' value='".$result->code_unit."' ></td>";

        $row .= "<td> <input type='text' readonly class='form-control input-sm ". $request->id."_subTotal' value='' name='subTotal[". $request->id."]' id='". $request->id."_subTotal'></td>";

        $row .= "<td> <button type='button' value='".$request->id."' class='btn btn-danger btn-sm btn-delete' title='Hapus' onclick='hapusBaris(". $request->id.")'><span class='fa fa-trash'></span></button></td>";

        $row .= "</tr>";
    }
    return $row;
    }

    public function getProdukMR(Request $request)
    {
        // $produk = $request->produk;
        // $id_product = $request->id;
        $result = DB::table('d_material_request')
        ->join('m_produk','m_produk.id','=','d_material_request.produk_id')
        ->join('t_material_request','t_material_request.mr_code','=','d_material_request.mr_code')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
        ->select('m_produk.code','m_produk.id as produk_id','m_produk.name','d_material_request.*'
        ,DB::raw('(qty_request - qty_usage) as maxDeviverQty'),'m_satuan_unit.code as code_unit')
        ->where('d_material_request.mr_code','=',$request->mr_code)
        ->get();

        foreach ($result as $raw_so) {
            $cekSj = 0;

            $cekSj = DB::table('d_material_request')
            ->join('t_material_request','t_material_request.mr_code','d_material_request.mr_code')
            ->where('d_material_request.id','=',$raw_so->id)
            ->where('t_material_request.status','!=','cancel')
            ->get();
            $raw_so->cek = $cekSj;
        }

         return Response::json($result);
    }

    public function dataMr($codeWo)
    {
        $result = DB::table('t_material_request')
        ->select('t_material_request.*')
        ->where('t_material_request.mr_code','=',$codeWo)
        ->first();

        return Response::json($result);
    }

    public function apiMu()
    {
        $asset = DB::table('t_material_usage')
            ->select('t_material_usage.*')
            ->orderBy('t_material_usage.mu_code', 'desc')
            ->get();

        $roleSuperAdmin = DB::table('m_role')
            ->where('name','Super Admin')
            ->first();

        $i=0;
        // dd($asset);
        return Datatables::of($asset)
        ->addColumn('action', function ($asset) use ($i){
            if( $asset->status == 'in process'){
                if(auth()->user()->role == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    // '<a href="'. url('admin/asset/report-asset/'.$asset->wo_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                    '<a href="'. url('admin/produksi-edit/mu/'.$asset->mu_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $asset->mu_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/produksi/delete/mu/'.$asset->mu_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->mr_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/produksi/produksi-posting/mu/'.$asset->mu_code) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->mu_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
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
                    '<a href="'. url('admin/produksi/delete/mu/'.$asset->mu_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->mr_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    // '<a href="'. url('admin/asset/asset-posting/'.$asset->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->wo_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                    // }
                }
            }elseif( $asset->status == 'post'){
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                '<td>'.
                '<a href="'. url('admin/report-mu/'.$asset->mu_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->mu_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                '<a href="'. url('admin/produksi/cancel/mu/'.$asset->mu_code) .'" class="btn btn-sm btn-danger" data-toggle="tooltip"  title="Cancel '. $asset->mu_code  .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
                '</td>'.
                '</tr>'.
                '</table>';
            }else{
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                '<td>'.
                '<a href="'. url('admin/report-mu/'.$asset->mu_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $asset->mu_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                '<a href="'. url('admin/produksi/delete/mu/'.$asset->mu_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->mu_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                // '<a href="'. url('admin/produksi/cancel/mu/'.$asset->mu_code) .'" class="btn btn-sm btn-danger" data-toggle="tooltip"  title="Cancel '. $asset->mu_code  .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
                '</td>'.
                '</tr>'.
                '</table>';
            }
            })
            ->editColumn('mu_code', function($asset){
            return '<a href="'. url('admin/produksi/detail/mu/'.$asset->mu_code) .'">'. $asset->mu_code .'</a> ';
            })
            ->editColumn('mu_date', function($asset){
            return date('d-m-Y',strtotime($asset->mu_date));
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
        ->rawColumns(['mu_code','routing_code','mu_date','status','action'])
        ->make(true);
    }

    public function inApprove($mucode)
    {
        $data_mu = DB::table('t_material_usage')
            ->join('d_material_usage','d_material_usage.mu_code','t_material_usage.mu_code')
            ->join('m_produk','m_produk.id','d_material_usage.produk_id')
            ->select('t_material_usage.mu_code','t_material_usage.gudang','d_material_usage.produk_id','m_produk.code as produk_code','d_material_usage.qty_usage')
            ->orderBy('t_material_usage.mu_code', 'desc')
            ->where('t_material_usage.mu_code',$mucode)
            ->get();

        // dd($data_mu);

        $success = false;
        DB::beginTransaction();
        try{
            foreach($data_mu as $raw_data){
                // $qty_sebelum = DB::table('d_material_usage')
                // ->where('mu_code',$request->mu_code)
                // ->first();
        
                // $qty_setelah = $qty_sebelum->qty_usage + $request->qty_save;
        
                // DB::table('d_material_usage')
                // ->where('mu_code',$request->mu_code)
                // ->where('produk_id',$as->produk_id)
                // ->update([
                //     'last_mr_qty' => $qty_setelah,
                // ]);
                // dd($qty_setelah);

                $insertStokModel = new MStokProdukModel;
                $insertStokModel->produk_code       =  $raw_data->produk_code;
                $insertStokModel->transaksi         =  $raw_data->mu_code;
                $insertStokModel->tipe_transaksi    =  'Material Usage';
                $insertStokModel->person            =  1;
                $insertStokModel->stok_awal         =  0;
                $insertStokModel->gudang            =  1;
                $insertStokModel->stok              =  -$raw_data->qty_usage;
                $insertStokModel->type              =  'out';
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
            DB::table('t_material_usage')
                ->where('mu_code',$mucode)
                ->update(['status' => 'post']);
        }

        return redirect('admin/produksi/mu');
    }

    public function laporanMu()
    {
        $dataSupplier = DB::table('d_material_usage')
            ->select('d_material_usage.*')
            ->groupBy('d_material_usage.id')
            ->get();

        $dataBarang = DB::table('m_produk')
            ->select('id as barang_id','name')
            ->groupBy('id')
            ->get();
            // dd($dataSupplier);

        return view('admin.produksi.materialusage.laporan',compact('dataSupplier','dataBarang'));
    }

    public function getMucodeByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataSupplier = DB::table('t_material_usage')
            ->join('d_material_usage', 't_material_usage.id', '=', 'd_material_usage.id')
            ->select('t_material_usage.id as mu_id','t_material_usage.mu_code')
            ->where('t_material_usage.mu_date','>=',date('Y-m-d', strtotime($tglmulai)))
            ->where('t_material_usage.mu_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('t_material_usage.id')
            ->get();
        return Response::json($dataSupplier);
    }

    public function cancelMUPost(Request $request)
    {
        // dd($request->all());
        DB::table('t_material_usage')
        ->where('mu_code',$request->mu_code)
        ->update([
        'status' => 'cancel',
        'cancel_reason' => $request->cancel_reason,
        'cancel_description' => $request->cancel_description,
        'user_cancel' => auth()->user()->id,]);

        return redirect('admin/produksi/mu');
    }

    public function cancelMU($pocode)
    {
        $dataMU = DB::table('t_material_usage')->where('mu_code',$pocode)->first();
        $reason = DB::table('m_reason')->orderBy('id','DESC')->get();

        return view('admin.produksi.materialusage.cancel',compact('dataMU','reason'));
    }
}
