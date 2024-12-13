<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;
use App\Models\WareHouseModel;
use App\Models\DWareHouseModel;
use App\Models\MGudangModel;
use App\Models\MProdukModel;
use App\Models\MStokProdukModel;


class TransferWareHouseController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        // dd('aa');
        $dataWareHouse = WareHouseModel::select('t_transfer_warehouse.*','gudang_asal.name as gudang_asal','gudang_tujuan.name as gudang_tujuan')
        ->join('m_gudang as gudang_asal','gudang_asal.id','t_transfer_warehouse.gudang_asal')
        ->join('m_gudang as gudang_tujuan','gudang_tujuan.id','t_transfer_warehouse.gudang_tujuan')
        ->orderBy('id','DESC')
        ->get();
        // dd($dataWareHouse);
        return view('admin.inventory.transfer-warehouse.index',compact('dataWareHouse'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        $twcode = $this->setcode();

        $gudangAsal = MGudangModel::orderBy('name','ASC')->get();

        $gudangTujuan = MGudangModel::orderBy('name','ASC')->get();

        return view('admin.inventory.transfer-warehouse.create',compact('gudangAsal','gudangTujuan','twcode'));

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

        $twcode = $this->setcode();
        $twdate = date('Y-m-d',strtotime($request->tw_date));
        $sendDate = date('Y-m-d');
        // dd($twdate);
        $array = [];
        $i = 0 ;


        foreach($request->id_produk as $rawidproduk)
        {
            $array[$i]['tw_code'] = $twcode;
            $array[$i]['produk'] = $rawidproduk;
            $i++;
        }

        $i = 0 ;
        foreach($request->jumlah as $rawjumlah)
        {
            $array[$i]['qty'] = $rawjumlah;
            $i++;
        }

        DB::beginTransaction();

        try{

            //header
            $insert = new WareHouseModel;
            $insert->tw_code = $twcode;
            $insert->gudang_asal = $request->gudang_asal;
            $insert->gudang_tujuan = $request->gudang_tujuan;
            $insert->tw_date = $twdate;
            $insert->send_date = $sendDate;
            $insert->description = $request->description;
            $insert->user_input = auth()->user()->id;
            $insert->save();

            //detail
            DWareHouseModel::insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::roolback();
            dd($e);
        }

        return redirect()->route('warehouse.index');
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($twcode)
    {
        $header = WareHouseModel::select('t_transfer_warehouse.*','gudang_asal.name as gudang_asal','gudang_tujuan.name as gudang_tujuan','user_input.name as user_input')
        ->join('m_gudang as gudang_asal','gudang_asal.id','t_transfer_warehouse.gudang_asal')
        ->join('m_gudang as gudang_tujuan','gudang_tujuan.id','t_transfer_warehouse.gudang_tujuan')
        ->join('m_user as user_input','user_input.id','t_transfer_warehouse.user_input')
        ->orderBy('id','DESC')
        ->where('t_transfer_warehouse.tw_code',$twcode)
        ->first();

        $detail = DWareHouseModel::join('m_produk','m_produk.id','d_transfer_warehouse.produk')
        ->select('m_produk.code','m_produk.name','d_transfer_warehouse.qty')
        ->where('d_transfer_warehouse.tw_code',$twcode)
        ->get();

        return view('admin.inventory.transfer-warehouse.detail',compact('header','detail'));
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($twcode)
    {
        $gudangAsal = MGudangModel::orderBy('name','ASC')->get();

        $gudangTujuan = MGudangModel::orderBy('name','ASC')->get();


        $header = WareHouseModel::select('t_transfer_warehouse.*','gudang_asal.name as gudang_asal',
            'gudang_asal.id as gudang_asal_id','gudang_tujuan.name as gudang_tujuan','gudang_tujuan.id as gudang_tujuan_id',
            'user_input.name as user_input')
            ->join('m_gudang as gudang_asal','gudang_asal.id','t_transfer_warehouse.gudang_asal')
            ->join('m_gudang as gudang_tujuan','gudang_tujuan.id','t_transfer_warehouse.gudang_tujuan')
            ->join('m_user as user_input','user_input.id','t_transfer_warehouse.user_input')
            ->orderBy('id','DESC')
            ->where('t_transfer_warehouse.tw_code',$twcode)
            ->first();
        $barang = DB::table('m_stok_produk')
            ->select('m_produk.id','m_produk.name')
            ->join('m_produk','m_produk.code','m_stok_produk.produk_code')
            ->where('m_stok_produk.gudang',$header->gudang_asal_id)
            // ->where('m_stok_produk.stok','!=','0')
            ->groupBy('m_produk.id','m_produk.name')
            ->get();

        $detail = DWareHouseModel::join('m_produk','m_produk.id','d_transfer_warehouse.produk')
            ->select('m_produk.id as produk_id','m_produk.code','m_produk.name','d_transfer_warehouse.qty','d_transfer_warehouse.id as id_dtlwarehouse','m_produk.satuan_terkecil')
            ->where('d_transfer_warehouse.tw_code',$twcode)
            ->get();

        $getAllQtyUseInRetur = DWareHouseModel::join('t_transfer_warehouse','t_transfer_warehouse.tw_code','d_transfer_warehouse.tw_code')
            ->where('d_transfer_warehouse.tw_code',$twcode)
            ->where('t_transfer_warehouse.status_aprove','!=','post')
            ->sum('qty');

        $date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));
        foreach( $detail as $dtl ){

            // $stok = DB::table('m_stok_produk')
            //     ->where('m_stok_produk.produk_code', $dtl->code)
            //     ->where('m_stok_produk.gudang', $header->gudang_asal_id)
            //     ->groupBy('m_stok_produk.produk_code')
            //     ->sum('stok');

            $balance = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $dtl->code)
                ->where('m_stok_produk.gudang', $header->gudang_asal_id)
                ->where('type', 'closing')
                ->whereMonth('periode',date('m', strtotime($date_last_month)))
                ->whereYear('periode',date('Y', strtotime($date_last_month)))
                ->sum('balance');

            $stok = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $dtl->code)
                ->where('m_stok_produk.gudang', $header->gudang_asal_id)
                ->whereMonth('created_at',date('m', strtotime($date_now)))
                ->whereYear('created_at',date('Y', strtotime($date_now)))
                ->groupBy('m_stok_produk.produk_code')
                ->sum('stok');

             $stok = $stok + $balance;

            $dtl->stok = $stok;
            $dtl->maxwarehoue = $stok - ( $getAllQtyUseInRetur - $dtl->qty );
        }
        // dd($header,$detail);
        return view('admin.inventory.transfer-warehouse.update',compact('header','detail','gudangAsal','gudangTujuan','barang'));
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        //dd($request->all());

        $twdate = date('Y-m-d',strtotime($request->tw_date));
        $sendDate = date('Y-m-d',strtotime($request->send_date));

        $array = [];
        $i = 0 ;


        foreach($request->id_produk as $rawidproduk)
        {
            $array[$i]['tw_code'] = $request->tw_code;
            $array[$i]['produk'] = $rawidproduk;
            $i++;
        }

        $i = 0 ;
        foreach($request->jumlah as $rawjumlah)
        {
            $array[$i]['qty'] = $rawjumlah;
            $i++;
        }

        DB::beginTransaction();

        try{

            //header
            $update = WareHouseModel::find($id);
            $update->gudang_asal = $request->gudang_asal;
            $update->gudang_tujuan = $request->gudang_tujuan;
            $update->tw_date = $twdate;
            $update->send_date = $sendDate;
            $update->description = $request->description;
            $update->save();

            //delete-detail
            DWareHouseModel::where('tw_code',$request->tw_code)->delete();

            //insert-new
            DWareHouseModel::insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect()->route('warehouse.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($twcode)
    {
        WareHouseModel::where('tw_code',$twcode)->delete();

        DWareHouseModel::where('tw_code',$twcode)->delete();

        return redirect()->route('warehouse.index');
    }

    protected function setCode()
    {
        $getLastCode = DB::table('t_transfer_warehouse')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'TW'.$dataDate.$nol.$getLastCode;
    }

    public function getProdukByGudang($idGudang)
    {
        // $data = DB::table('m_stok_produk')
        //         ->select('m_produk.id','m_produk.name')
        //         ->join('m_produk','m_produk.code','m_stok_produk.produk_code')
        //         ->where('m_stok_produk.gudang',$idGudang)
        //         ->where('m_stok_produk.stok','!=','0')
        //         ->groupBy('m_produk.id','m_produk.name')
        //         ->get();

        $data = DB::table('m_stok_produk')
                ->select('m_produk.id','m_produk.name',DB::raw('sum(m_stok_produk.stok + m_stok_produk.balance) as stok_produk'))
                ->join('m_produk','m_produk.code','m_stok_produk.produk_code')
                ->where('m_stok_produk.gudang',$idGudang)
                // ->where('stok_produk','<>',"")
                ->groupBy('m_produk.id','m_produk.name')
                // ->having('stok_produk','>',0)
                ->orderBy('m_produk.name','asc')
                ->get();

        return Response::json($data);
    }

    public function getProduk(Request $request)
    {

        //validasi-jika-barang sama
        $cekproduk = 0;
        if ($request->produk != null || $request->produk != '') {
            foreach ($request->produk as $i => $raw_produk) {
                if ($request->id == $request->produk [$i]) {
                    $cekproduk = 1;
                }
            }
        }

        $date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

        $isi = null;
        if ($cekproduk == 0) {

            // $result = MProdukModel::find($request->id);

            // $stok = DB::table('m_stok_produk')
            // ->where('m_stok_produk.produk_code', $result->code)
            // ->where('m_stok_produk.gudang', $request->gudang)
            // ->groupBy('m_stok_produk.produk_code')
            // ->sum('stok');

            $result = MProdukModel::find($request->id);

            $balance = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $result->code)
            ->where('m_stok_produk.gudang', $request->gudang)
            ->where('type', 'closing')
            ->where('type', 'closing')
            ->whereMonth('periode',date('m', strtotime($date_last_month)))
            ->whereYear('periode',date('Y', strtotime($date_last_month)))
            ->sum('balance');

            $stok = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $result->code)
                ->where('m_stok_produk.gudang', $request->gudang)
                ->whereMonth('created_at',date('m', strtotime($date_now)))
                ->whereYear('created_at',date('Y', strtotime($date_now)))
                ->groupBy('m_stok_produk.produk_code')
                ->sum('stok');

            $stok = $stok + $balance;

            $isi = "<tr id='tr_".$request->id."'>";

            $isi .= "<input type='hidden' value='". $request->id ."'name='id_produk[".$request->length."]' id='produk_id_". $request->id."'>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$result->code."' ></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' data-toggle='tooltip' data-placement='top' title='".$result->name."' readonly value='".$result->name."' ></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$result->satuan_terkecil."' ></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$stok."' name='stok[".$request->length."]' id='". $request->id."_stok'></td>";

            $isi .= "<td> <input type='number' min='1' max='".$stok."' id='".$request->id."_jumlah' class='form-control input-sm' name='jumlah[".$request->length."]' value='1'></td>";


            $isi .= "<td> <button type='button' value='".$request->length."' class='btn btn-danger btn-sm btn-delete' onclick='hapusBaris(". $request->id.")'><span class='fa fa-trash'></span></button></td>";

            $isi .= "</tr>";
        }

        return $isi;
    }

    public function posting($twcode)
    {
        // dd($twcode);
        $dataTransfer = DB::table('t_transfer_warehouse')
        ->join('d_transfer_warehouse','d_transfer_warehouse.tw_code','t_transfer_warehouse.tw_code')
        ->join('m_produk','m_produk.id','d_transfer_warehouse.produk')
        ->where('t_transfer_warehouse.tw_code',$twcode)
        ->get();
        $arrayGudangAsal = [];
        $arrayGudangTujuan = [];
        $i=0;

        foreach( $dataTransfer as  $key =>$data){

            $stokAwalGudangAsal = DB::table('m_stok_produk')->where('produk_code',$data->code)
            ->where('gudang',$data->gudang_asal)
            ->sum('stok');

            $stokAwalGudangTujuan = DB::table('m_stok_produk')->where('produk_code',$data->code)
            ->where('gudang',$data->gudang_tujuan)
            ->sum('stok');
            //gudang-asal
            $arrayGudangAsal[$i]['produk_code'] = $data->code;
            $arrayGudangAsal[$i]['produk_id'] = $data->produk;
            $arrayGudangAsal[$i]['transaksi']   = $data->tw_code;
            $arrayGudangAsal[$i]['tipe_transaksi']   =  'Transfer Warehouse';
            $arrayGudangAsal[$i]['stok_awal']   = $stokAwalGudangAsal;
            $arrayGudangAsal[$i]['gudang']      = $data->gudang_asal;
            $arrayGudangAsal[$i]['stok']        = -$data->qty;
            $arrayGudangAsal[$i]['type']        = 'out';
            $arrayGudangAsal[$i]['gudang']      = $data->gudang_asal;

            //gudang-asal
            $arrayGudangTujuan[$i]['produk_code'] = $data->code;
            $arrayGudangTujuan[$i]['produk_id'] = $data->produk;
            $arrayGudangTujuan[$i]['transaksi']   = $data->tw_code;
            $arrayGudangTujuan[$i]['tipe_transaksi']   =  'Transfer Warehouse';
            $arrayGudangTujuan[$i]['stok_awal']   = $stokAwalGudangTujuan;
            $arrayGudangTujuan[$i]['gudang']      = $data->gudang_tujuan;
            $arrayGudangTujuan[$i]['stok']        = $data->qty;
            $arrayGudangTujuan[$i]['type']        = 'in';
            $arrayGudangTujuan[$i]['gudang']      = $data->gudang_tujuan;

            $i++;
        }
        // echo "<pre>";
        //     print_r($arrayGudangAsal);
        // echo "</pre>"."<br>";

        // echo "<pre>";
        //     print_r($arrayGudangTujuan);
        // echo "</pre>"."<br>";
        // die();

        DB::beginTransaction();
        try {

            //insertstokkeluar
            MStokProdukModel::insert($arrayGudangAsal);

            //insertstokmasuk
            MStokProdukModel::insert($arrayGudangTujuan);

            //update-flag
            DB::table('t_transfer_warehouse')->where('tw_code',$twcode)->update([ 'status_aprove' => 'post' ]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect()->route('warehouse.index');

    }

    public function laporan()
    {
        $data = DB::table('t_transfer_warehouse')
                ->join('m_gudang','m_gudang.id','t_transfer_warehouse.gudang_asal')
                ->select('m_gudang.id','m_gudang.name')

                ->get();

        // dd($data);
        return view('admin.inventory.transfer-warehouse.laporan',compact('data'));
    }

    public function getTwAjax($gudang,$type,$periode)
    {
        $tglmulai = substr($periode,0,11);
        $tglsampai = substr($periode,13,11);
        $result = DB::table('t_transfer_warehouse')
        ->select('tw_code');
        if($type == 'out'){
            $result->where('gudang_asal',$gudang);
        }
        if($type == 'in'){
            $result->where('gudang_tujuan',$gudang);

        }
        $result->where('tw_date','>=', date('Y-m-d', strtotime($tglmulai)));
        $result->where('tw_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

        $result=$result->get();

        return Response::json($result);
    }

    public function getBarangByTwAjax($tw_code)
    {
        $result = DB::table('d_transfer_warehouse')
        ->join('m_produk','m_produk.id','=','d_transfer_warehouse.produk')
        ->select('m_produk.name','m_produk.id')
        ->where('d_transfer_warehouse.tw_code',$tw_code)
        ->groupBy('m_produk.id')
        ->get();

        return Response::json($result);
    }

    public function getBarangByGudangAjax($gudang,$type)
    {
        $result = DB::table('d_transfer_warehouse')
        ->join('m_produk','m_produk.id','=','d_transfer_warehouse.produk')
        ->join('t_transfer_warehouse','t_transfer_warehouse.tw_code','d_transfer_warehouse.tw_code')
        ->select('m_produk.name','m_produk.id');
        if($type == 'out'){
            $result->where('t_transfer_warehouse.gudang_asal',$gudang);
        }
        if($type == 'in'){
            $result->where('t_transfer_warehouse.gudang_tujuan',$gudang);
        }
        $result->groupBy('m_produk.id');
        $result=$result->get();

        return Response::json($result);
    }

    public function getGudangAjax($request,$type)
    {

        $tglmulai = substr($request,0,11);
        $tglsampai = substr($request,13,11);

        $result = DB::table('t_transfer_warehouse');
        if($type == 'out'){
            $result->join('m_gudang','m_gudang.id','t_transfer_warehouse.gudang_asal');

        }
        if($type == 'in'){
            $result->join('m_gudang','m_gudang.id','t_transfer_warehouse.gudang_tujuan');

        }
        $result->select('m_gudang.id','m_gudang.name');
        $result->where('tw_date','>=', date('Y-m-d', strtotime($tglmulai)));
        $result->where('tw_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

        $result->groupBy('m_gudang.id','m_gudang.name');
        $data = $result->get();

        return Response::json($data);
    }

}
