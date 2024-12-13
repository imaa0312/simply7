<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;
use App\Models\TAdjusmentModel;
use App\Models\DAdjusmentModel;
use App\Models\MStokProdukModel;
use App\Models\MProdukModel;
use App\Models\MGudangModel;

class TAdjusmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataAdjusment = TAdjusmentModel::with('gudangRelation')->orderBy('id','DESC')->get();
        // dd($dataAdjusment);
        return view('admin.inventory.adjusment.index',compact('dataAdjusment'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tacode = $this->setcode();

        $gudang = MGudangModel::orderBy('name','ASC')->get();

        return view('admin.inventory.adjusment.create',compact('gudang','tacode'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $ta_code = $this->setcode();

        $ta_date = date('Y-m-d',strtotime($request->ta_date));

        $array = [];
        $i = 0 ;


        foreach($request->id_produk as $rawidproduk)
        {
            $array[$i]['ta_code'] = $ta_code;
            $array[$i]['produk'] = $rawidproduk;
            $i++;
        }

        $i = 0;
        foreach($request->jumlah as $rawjumlah)
        {
            $array[$i]['qty'] = $rawjumlah;
            $i++;
        }

        $i = 0;
        foreach($request->stok as $rawstok)
        {
            $array[$i]['qty_awal'] = $rawstok;
            $i++;
        }

        for($y=0; $y<count($array); $y++){

            $selisih = $array[$y]['qty'] - $array[$y]['qty_awal'];

                // if( $selisih < 0 ){ $selisih =  $selisih * -1; }

            $array[$y]['qty_selisih']  = $selisih;
        }

        // echo "<pre>";
        //     print_r($array);
        // dd($request->all());

        DB::beginTransaction();

        try{

            $insert = new TAdjusmentModel;
            $insert->ta_code =  $ta_code;
            $insert->ta_date =  $ta_date;
            $insert->gudang =  $request->gudang;
            $insert->user_input =  auth()->user()->id;
            $insert->description =  $request->description;
            $insert->save();

            DAdjusmentModel::insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect()->route('adjusment.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($tacode)
    {
        $header = TAdjusmentModel::with('userRelation','gudangRelation')->where('ta_code',$tacode)->first();

        $detail = DAdjusmentModel::with('produkRelation')->where('ta_code',$tacode)->get();

        return view('admin.inventory.adjusment.detail',compact('header','detail'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($tacode)
    {
        $gudang = MGudangModel::orderBy('name','ASC')->get();

        $header = TAdjusmentModel::with('userRelation')->where('ta_code',$tacode)->first();

        $produk = DB::table('m_stok_produk')
                ->select('m_produk.id','m_produk.name')
                ->join('m_produk','m_produk.code','m_stok_produk.produk_code')
                ->where('m_stok_produk.gudang',$header->gudang)
                // ->where('m_stok_produk.stok','!=','0')
                ->groupBy('m_produk.id')
                ->get();

        $detail = DAdjusmentModel::with('produkRelation')->where('ta_code',$tacode)->get();

        foreach( $detail as $value){

            // $stok = DB::table('m_stok_produk')
            //         ->where('m_stok_produk.produk_code', $value->produkRelation->code)
            //         ->where('m_stok_produk.gudang', $header->gudang)
            //         ->groupBy('m_stok_produk.produk_code')
            //         ->sum('stok');

            $balance = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $value->produkRelation->code)
            ->where('m_stok_produk.gudang', $header->gudang)
            ->where('type', 'closing')
            // ->whereMonth('periode',date('m', strtotime($date_last_month)))
            // ->whereYear('periode',date('Y', strtotime($date_last_month)))
            ->sum('balance');

            $stok = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $value->produkRelation->code)
                ->where('m_stok_produk.gudang', $header->gudang)
                ->groupBy('m_stok_produk.produk_code')
                ->sum('stok');

            $stok = $stok + $balance;

            $value->produkRelation->stok = $stok;

            // dd($detail);
        }

        return view('admin.inventory.adjusment.update',compact('gudang','header','detail','produk'));
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
        $ta_date = date('Y-m-d',strtotime($request->ta_date));

        $array = [];
        $i = 0 ;


        foreach($request->id_produk as $rawidproduk)
        {
            $array[$i]['ta_code'] = $request->ta_code;
            $array[$i]['produk'] = $rawidproduk;
            $i++;
        }

        $i = 0;
        foreach($request->jumlah as $rawjumlah)
        {
            $array[$i]['qty'] = $rawjumlah;
            $i++;
        }

        $i = 0;
        foreach($request->stok as $rawstok)
        {
            $array[$i]['qty_awal'] = $rawstok;
            $i++;
        }

        for($y=0; $y<count($array); $y++){

            $selisih = $array[$y]['qty'] - $array[$y]['qty_awal'];

                // if( $selisih < 0 ){ $selisih =  $selisih * -1; }

            $array[$y]['qty_selisih']  = $selisih;
        }

        // echo "<pre>";
        //     print_r($array);
        // dd($request->all());

        DB::beginTransaction();

        try{

            $update = TAdjusmentModel::find($id);
            $update->ta_date =  $ta_date;
            $update->gudang =  $request->gudang;
            $update->description =  $request->description;
            $update->save();

            DAdjusmentModel::where('ta_code',$request->ta_code)->delete();

            DAdjusmentModel::insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect()->route('adjusment.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($tacode)
    {
        TAdjusmentModel::where('ta_code',$tacode)->delete();

        DAdjusmentModel::where('ta_code',$tacode)->delete();

        return redirect()->route('adjusment.index');
    }

    protected function setCode()
    {
        $getLastCode = DB::table('t_adjusment')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'ADJ'.$dataDate.$nol.$getLastCode;
    }

    public function getProdukByGudang($idGudang)
    {
        $data = DB::table('m_stok_produk')
                ->select('m_produk.id','m_produk.name')
                ->join('m_produk','m_produk.code','m_stok_produk.produk_code')
                ->where('m_stok_produk.gudang',$idGudang)
                // ->where('m_stok_produk.stok','!=','0')
                ->groupBy('m_produk.id','m_produk.name')
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

        $isi = null;
        if ($cekproduk == 0) {

            $result = MProdukModel::find($request->id);

            $balance = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $result->code)
            ->where('m_stok_produk.gudang', $request->gudang)
            ->where('type', 'closing')
            // ->whereMonth('periode',date('m', strtotime($date_last_month)))
            // ->whereYear('periode',date('Y', strtotime($date_last_month)))
            ->sum('balance');

            $stok = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $result->code)
                ->where('m_stok_produk.gudang', $request->gudang)
                ->groupBy('m_stok_produk.produk_code')
                ->sum('stok');

            $stok = $stok + $balance;

            $isi = "<tr id='tr_".$request->id."'>";

            $isi .= "<input type='hidden' value='". $request->id ."'name='id_produk[".$request->length."]' id='produk_id_". $request->id."'>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$result->code."' ></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' data-toggle='tooltip' data-placement='top' title='".$result->name."' readonly value='".$result->name."' ></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$result->satuan_kemasan."' ></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$stok."' name='stok[".$request->length."]' id='". $request->id."_stok'></td>";

            $isi .= "<td> <input type='number' min='0'  id='".$request->id."_jumlah' class='form-control input-sm' name='jumlah[".$request->length."]' value='0'></td>";


            $isi .= "<td> <button type='button' value='".$request->length."' class='btn btn-danger btn-sm btn-delete' onclick='hapusBaris(". $request->id.")'><span class='fa fa-trash'></span></button></td>";

            $isi .= "</tr>";
        }

        return $isi;
    }


    public function posting($tacode)
    {
        // dd($tacode);
        $dataAdjusment = DB::table('d_adjusment')
                        ->select("d_adjusment.*","m_produk.*","m_produk.id as produk_id","t_adjusment.*")
                        ->join('t_adjusment','t_adjusment.ta_code','d_adjusment.ta_code')
                        ->join('m_produk','m_produk.id','d_adjusment.produk')
                        ->where('d_adjusment.ta_code',$tacode)
                        ->get();
        $arrayInsert = [];
        $i = 0;

        foreach( $dataAdjusment as $result){
            //stok-awal
            $stokAwal   = DB::table('m_stok_produk')->where('produk_code',$result->code)
                            ->where('gudang',$result->gudang)
                            ->sum('stok');

            ( $result->qty_selisih <= 0 ) ? $type = 'out' : $type = 'in';

            $arrayInsert[$i]['produk_code'] = $result->code;
            $arrayInsert[$i]['produk_id'] = $result->produk_id;
            $arrayInsert[$i]['transaksi']   = $result->ta_code;
            $arrayInsert[$i]['tipe_transaksi']   =  'Stok Adjusment';
            $arrayInsert[$i]['stok_awal']   = $stokAwal;
            $arrayInsert[$i]['gudang']      = $result->gudang;
            $arrayInsert[$i]['stok']        = $result->qty_selisih;
            $arrayInsert[$i]['type']        = $type;

            $i++;
        }

        // echo "<pre>";
        //  print_r($arrayInsert);
        DB::beginTransaction();
        try {

            //insert
            MStokProdukModel::insert($arrayInsert);

            //update-flag
            TAdjusmentModel::where('ta_code',$tacode)->update([ 'status_aprove' => 'post' ]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect()->route('adjusment.index');
    }

    public function getGudangByPeriode($request){
        $tglmulai = substr($request,0,11);
        $tglsampai = substr($request,13,11);

        $result = DB::table('t_adjusment')
        ->join('m_gudang','m_gudang.id','t_adjusment.gudang')
        ->select('m_gudang.id','m_gudang.name')
        ->where('ta_date','>=', date('Y-m-d', strtotime($tglmulai)))
        ->where('ta_date','<=', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
        ->groupBy('m_gudang.id')
        ->get();

        return Response::json($result);
    }

    public function getBarangByGudangAjax($gudang)
    {
        $result = DB::table('d_adjusment')
        ->join('m_produk','m_produk.id','=','d_adjusment.produk')
        ->join('t_adjusment','t_adjusment.ta_code','d_adjusment.ta_code')
        ->select('m_produk.name','m_produk.id')
        ->where('t_adjusment.gudang',$gudang)
        ->groupBy('m_produk.id')

        ->get();

        return Response::json($result);
    }

    public function getBarangByTaAjax($ta_code)
    {
        $result = DB::table('d_adjusment')
        ->join('m_produk','m_produk.id','=','d_adjusment.produk')
        ->select('m_produk.name','m_produk.id')
        ->where('d_adjusment.ta_code',$ta_code)
        ->groupBy('m_produk.id')
        ->get();

        return $result;
    }

    public function getTaByGudang($gudang,$periode){
        $tglmulai = substr($periode,0,11);
        $tglsampai = substr($periode,13,11);
        $result = DB::table('t_adjusment')
        ->select('ta_code')
        ->where('gudang',$gudang);
        $result->where('ta_date','>=', date('Y-m-d', strtotime($tglmulai)));
        $result->where('ta_date','<=', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
        $result=$result->get();


        return Response::json($result);
    }
    public function laporan(){
        return view('admin.inventory.adjusment.laporan');
    }

}
