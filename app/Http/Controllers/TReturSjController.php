<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;
use App\Models\TReturModel;
use App\Models\DReturModel;
use App\Models\MStokProdukModel;

class TReturSjController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $dataRetur = TReturModel::join('m_customer','m_customer.id','t_retur_sj.customer')
                    ->select('t_retur_sj.*','m_customer.name as name')
                    ->orderBy('id','DESC')
                    //->groupBy('t_retur_sj.id','','m_customer.name')
                    ->get();

        // $customer = TReturModel::join('m_customer','m_customer.id','t_retur_sj.customer')
        //             ->select('m_customer.id','m_customer.name')
        //             ->orderBy('m_customer.name','ASC')
        //             ->groupBy('m_customer.id','m_customer.name')
        //             ->get();
        // dd($dataRetur);
        return view('admin.transaksi.surat-jalan.retur.index',compact('dataRetur'));
    }

    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        $returCode = $this->setCodeRetur();

        $dataRetur = DB::table('m_customer')
        ->select('m_customer.name','m_customer.id')
        ->join('t_surat_jalan','t_surat_jalan.customer','m_customer.id')
        ->groupBy('m_customer.name','m_customer.id')
        ->where('t_surat_jalan.status','post')
        ->get();

        foreach( $dataRetur as $keyretur => $value ){

            $dataSj  = DB::table('t_surat_jalan')
            ->select('t_surat_jalan.sj_code')
            ->join('m_customer','m_customer.id','t_surat_jalan.customer')
            ->where('t_surat_jalan.status','post')
            ->where('m_customer.id',$value->id)
            ->get();

            foreach($dataSj as $key => $retursj){

                $getQtyDsj = DB::table('d_surat_jalan')->where('sj_code',$retursj->sj_code)->sum('qty_delivery');

                $getQtyRetur = DB::table('d_retur_sj')
                ->join('t_retur_sj','t_retur_sj.rt_code','d_retur_sj.rt_code')
                ->where('sj_code',$retursj->sj_code)->sum('qty');
                if(  $getQtyRetur >= $getQtyDsj){
                    unset($dataSj[$key]);
                }
            }

            if( count($dataSj) < 1 ){
                unset($dataRetur[$keyretur]);
            }
        }
        // dd($dataRetur);

        return view('admin.transaksi.surat-jalan.retur.create',compact('dataRetur','returCode'));

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

        foreach ($request->produk_id as $raw_produk) {
            $array[$i]['produk'] = $raw_produk;
            $i++;
        }

        $i = 0;

        foreach ($request->retur as $raw_retur) {
            $array[$i]['retur'] = $raw_retur;
            $i++;
        }
        $i = 0;
        foreach ($request->dsjm_id as $raw_dsjm_id) {
            $array[$i]['dsjm_id'] = $raw_dsjm_id;
            $i++;
        }
        // $getDetailIdSj = DB::table('d_surat_jalan')->where('sj_code',$request->sj_code)->first();

        DB::beginTransaction();

        try {
            //mencari nilai faktur
            $dataFaktur = DB::table('t_faktur')
            ->where('sj_code',$request->sj_code)
            ->first();

            $dataSO = DB::table('t_sales_order')
            ->join('m_customer','m_customer.id','=','t_sales_order.customer')
            ->select('t_sales_order.*','m_customer.credit_limit_days')
            ->where('so_code',$request->so_code)
            ->first();

            $dataSJ = DB::table('t_surat_jalan')
            ->join('d_surat_jalan','d_surat_jalan.sj_code','=','t_surat_jalan.sj_code')
            ->where('t_surat_jalan.sj_code',$request->sj_code)
            ->get();

            //get diskon header so
            $totalheader = $dataSO->grand_total;

            $totaldetail = DB::table('d_sales_order')
            ->where('so_code',$request->so_code)
            ->sum('total');

            $qtybarang = DB::table('d_sales_order')
            ->where('so_code',$request->so_code)
            ->sum('qty');

            $diskonheader = $totaldetail - $totalheader;
            $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);

            $grand_total = 0;
            foreach ($dataSJ as $raw_data) {
                //getdata d so
                $dataDSO = DB::table('d_sales_order')
                ->where('so_code',$raw_data->so_code)
                ->where('produk',$raw_data->produk_id)
                ->first();

                //get harga per barang
                $hargabarang = $dataDSO->total / $dataDSO->qty;
                $hargabarang = (int) round($hargabarang);

                //hitung grand total
                for($x=0; $x<count($array); $x++){
                    if ($array[$x]['dsjm_id'] == $raw_data->id) {
                        $qty_retur = $array[$x]['retur'];

                        $array[$x]['harga'] = $hargabarang - $diskonheaderperbarang;
                        $array[$x]['total'] = $qty_retur * ($hargabarang - $diskonheaderperbarang);
                    }else{
                        $qty_retur = 0;
                    }
                }

                $grand_total = $grand_total + ($qty_retur * ($hargabarang - $diskonheaderperbarang));
            }

            //insert retur
            $insert = new TReturModel;
            $insert->rt_code  = $this->setCodeRetur();
            $insert->sj_code  = $request->sj_code;
            $insert->so_code  = $request->so_code;
            $insert->customer = $request->customer;
            $insert->sales    = $request->sales;
            $insert->gudang    = $request->gudang;
            $insert->grand_total    = $grand_total;
            $insert->description  = $request->description;
            $insert->user_input   = auth()->user()->id;
            $insert->save();

            for($x=0; $x<count($array); $x++){
                DReturModel::insert([
                    'detail_sj_id' => $array[$x]['dsjm_id'],
                    'rt_code' => $insert->rt_code,
                    'produk_id' => $array[$x]['produk'],
                    'harga' => $array[$x]['harga'],
                    'qty' => $array[$x]['retur'],
                    'total' => $array[$x]['total'],
                ]);
            }


            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect()->route('sj-retur.index');
    }

    /**
    * Display the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($code)
    {
        $dataheader=DB::table('t_retur_sj')
        ->join('m_customer','m_customer.id','=','t_retur_sj.customer')
        ->join('m_user','m_user.id','=','t_retur_sj.user_input')
        ->select('t_retur_sj.*','m_customer.name as customer','m_user.name as user_input')
        ->where('rt_code',$code)
        ->first();

        $datadetail=DB::table('d_retur_sj')
        ->select('*','m_satuan_unit.code as satuan_kemasan')
        ->join('m_produk','m_produk.id','=','d_retur_sj.produk_id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
        ->where('rt_code',$code)
        ->get();

        return view('admin.transaksi.surat-jalan.retur.detail',compact('dataheader','datadetail'));
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($id)
    {
        $dataTRetur = TReturModel::find($id);

        $customer = DB::table('m_customer')->orderBy('name','ASC')->get();

        $dataSj  = DB::table('t_surat_jalan')
        ->select('t_surat_jalan.sj_code')
        ->join('m_customer','m_customer.id','t_surat_jalan.customer')
        ->where('t_surat_jalan.status','post')
        ->where('m_customer.id',$dataTRetur->customer)
        ->get();

        $dataDetailRetur = DB::table('d_retur_sj')
        ->join('t_retur_sj','t_retur_sj.rt_code','d_retur_sj.rt_code')
        ->join('m_produk','m_produk.id','=','d_retur_sj.produk_id')
        ->join('d_surat_jalan','d_surat_jalan.id','=','d_retur_sj.detail_sj_id')
        ->select('m_produk.id as produk_id','m_produk.code','m_produk.name as produk','d_retur_sj.*','d_surat_jalan.id as id_dsj','t_retur_sj.gudang','d_surat_jalan.qty_delivery')
        ->where('d_retur_sj.rt_code',$dataTRetur->rt_code)
        ->get();
        //add-index-stok
        foreach ($dataDetailRetur as $raw_retur) {

            $getQtyRetur = DB::table('d_retur_sj')->where('detail_sj_id',$raw_retur->id_dsj)->sum('qty');

            $getSisaQtyRetur = $raw_retur->qty_delivery - $getQtyRetur;

            $stok = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $raw_retur->code)
            ->where('m_stok_produk.gudang', $raw_retur->gudang)
            ->groupBy('m_stok_produk.produk_code')
            ->sum('stok');

            $raw_retur->stok = $stok;
            $raw_retur->getQtyRetur =  $getQtyRetur;
            $raw_retur->maxqtyretur =  $getSisaQtyRetur + $raw_retur->qty;

        }


        // dd($dataDetailRetur);
        return view('admin.transaksi.surat-jalan.retur.update',compact('dataTRetur','customer','returCode','dataSj','dataDetailRetur'));

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
        // dd($request->all());
        $array = [];
        $i = 0;

        foreach ($request->produk_id as $raw_produk) {
            $array[$i]['produk'] = $raw_produk;
            $i++;
        }

        $i = 0;

        foreach ($request->retur as $raw_retur) {
            $array[$i]['retur'] = $raw_retur;
            $i++;
        }
        $i = 0;
        foreach ($request->dsjm_id as $raw_dsjm_id) {
            $array[$i]['dsjm_id'] = $raw_dsjm_id;
            $i++;
        }

        DB::beginTransaction();

        try {
            //mencari nilai faktur
            $dataFaktur = DB::table('t_faktur')
            ->where('sj_code',$request->sj_code)
            ->first();

            $dataSO = DB::table('t_sales_order')
            ->join('m_customer','m_customer.id','=','t_sales_order.customer')
            ->select('t_sales_order.*','m_customer.credit_limit_days')
            ->where('so_code',$request->so_code)
            ->first();

            $dataSJ = DB::table('t_surat_jalan')
            ->join('d_surat_jalan','d_surat_jalan.sj_code','=','t_surat_jalan.sj_code')
            ->where('t_surat_jalan.sj_code',$request->sj_code)
            ->get();

            //get diskon header so
            $totalheader = $dataSO->grand_total;

            $totaldetail = DB::table('d_sales_order')
            ->where('so_code',$request->so_code)
            ->sum('total');

            $qtybarang = DB::table('d_sales_order')
            ->where('so_code',$request->so_code)
            ->sum('qty');

            $diskonheader = $totaldetail - $totalheader;
            $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);

            $grand_total = 0;
            foreach ($dataSJ as $raw_data) {
                //getdata d so
                $dataDSO = DB::table('d_sales_order')
                ->where('so_code',$raw_data->so_code)
                ->where('produk',$raw_data->produk_id)
                ->first();

                //get harga per barang
                $hargabarang = $dataDSO->total / $dataDSO->qty;
                $hargabarang = (int) round($hargabarang);

                //hitung grand total
                for($x=0; $x<count($array); $x++){
                    if ($array[$x]['dsjm_id'] == $raw_data->id) {
                        $qty_retur = $array[$x]['retur'];

                        $array[$x]['harga'] = $hargabarang - $diskonheaderperbarang;
                        $array[$x]['total'] = $qty_retur * ($hargabarang - $diskonheaderperbarang);
                    }else{
                        $qty_retur = 0;
                    }
                }

                $grand_total = $grand_total + ($qty_retur * ($hargabarang - $diskonheaderperbarang));
            }

            //insert retur
            $update = TReturModel::find($id);
            $update->sj_code  = $request->sj_code;
            $update->so_code  = $request->so_code;
            $update->customer = $request->customer;
            $update->sales    = $request->sales;
            $update->gudang    = $request->gudang;
            $update->grand_total    = $grand_total;
            $update->description  = $request->description;
            $update->save();

            //delete-old-retur
            DReturModel::where('rt_code',$update->rt_code)->delete();

            for($x=0; $x<count($array); $x++){

                DReturModel::insert([
                    'detail_sj_id' => $array[$x]['dsjm_id'],
                    'rt_code' => $update->rt_code,
                    'produk_id' => $array[$x]['produk'],
                    'harga' => $array[$x]['harga'],
                    'qty' => $array[$x]['retur'],
                    'total' => $array[$x]['total'],
                ]);
            }


            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect()->route('sj-retur.index');
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        $dataRetur = TReturModel::find($id);

        DReturModel::where('rt_code',$dataRetur->rt_code)->delete();

        TReturModel::where('rt_code',$dataRetur->rt_code)->delete();

        return redirect()->route('sj-retur.index');
    }

    protected function setCodeRetur()
    {
        $dataDate = date("ym");

        $getLastCode = DB::table('t_retur_sj')
        ->select('id')
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";
        }else{$nol = null;}

        return 'RT'.$dataDate.$nol.$getLastCode;
    }


    public function approve($id)
    {
        // dd($id);
        $dataTRetur = TReturModel::find($id);
        $dataDetailRetur = DReturModel::where('rt_code',$dataTRetur->rt_code)->get();

        DB::beginTransaction();
        try {

            foreach ($dataDetailRetur as $key => $value) {

                //get-produk-code
                $produkCode = DB::table('m_produk')->where('id',$value->produk_id)->first();

                //get-stok-awal-produk
                $jumlahStok = DB::table('m_stok_produk')
                ->where('produk_code',$produkCode->code)
                ->where('gudang',$dataTRetur->gudang)
                ->sum('stok');

                $insertStokModel = new MStokProdukModel;
                $insertStokModel->produk_code =  $produkCode->code;
                $insertStokModel->transaksi   =  $value->rt_code;
                $insertStokModel->tipe_transaksi   = 'Retur SJ';
                $insertStokModel->stok_awal   =  $jumlahStok;
                $insertStokModel->gudang      =  $dataTRetur->gudang;
                $insertStokModel->stok        =  $value->qty;
                $insertStokModel->type        =  'in';
                $insertStokModel->save();

                //update-header-status
                TReturModel::where('id',$id)->update(['status' => 'post']);
            }

            //mengembalikan nilai faktur
            $dataFaktur = DB::table('t_faktur')
            ->where('sj_code',$dataTRetur->sj_code)
            ->first();

            $dataSO = DB::table('t_sales_order')
            ->join('m_customer','m_customer.id','=','t_sales_order.customer')
            ->select('t_sales_order.*','m_customer.credit_limit_days')
            ->where('so_code',$dataFaktur->so_code)
            ->first();

            $dataSJ = DB::table('t_surat_jalan')
            ->join('d_surat_jalan','d_surat_jalan.sj_code','=','t_surat_jalan.sj_code')
            ->where('t_surat_jalan.sj_code',$dataFaktur->sj_code)
            ->get();

            //get diskon header so
            $totalheader = $dataSO->grand_total;

            $totaldetail = DB::table('d_sales_order')
            ->where('so_code',$dataFaktur->so_code)
            ->sum('total');

            $qtybarang = DB::table('d_sales_order')
            ->where('so_code',$dataFaktur->so_code)
            ->sum('qty');

            $diskonheader = $totaldetail - $totalheader;
            $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);

            $total_baru = 0;
            foreach ($dataSJ as $raw_data) {
                //getdata d so
                $dataDSO = DB::table('d_sales_order')
                ->where('so_code',$raw_data->so_code)
                ->where('produk',$raw_data->produk_id)
                ->first();

                //get harga per barang
                $hargabarang = $dataDSO->total / $dataDSO->qty;
                $hargabarang = (int) round($hargabarang);

                //get qty retur jika ada
                $qty_retur = DB::table('d_retur_sj')
                ->join('t_retur_sj','t_retur_sj.rt_code','=','d_retur_sj.rt_code')
                ->where('detail_sj_id',$raw_data->id)
                ->where('status','post')
                ->sum('qty');

                $total_baru = $total_baru + (($raw_data->qty_delivery - $qty_retur) * ($hargabarang - $diskonheaderperbarang));
            }

            //auto jurnal;
             $id_gl = DB::table('t_general_ledger')
                ->insertGetId([
                    'general_ledger_date' => date('Y-m-d'),
                    'general_ledger_periode' => date('Ym'),
                    'general_ledger_keterangan' => 'Sales Retur No.'.$dataTRetur->rt_code,
                    'general_ledger_status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','101050101')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 1,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'debet',
                    'total' => $dataTRetur->grand_total,
                    'ref' => $dataTRetur->rt_code,
                    'type_transaksi' => 'SRT',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','601')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 2,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'credit',
                    'total' => $dataTRetur->grand_total,
                    'ref' => $dataTRetur->rt_code,
                    'type_transaksi' => 'SRT',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','4030101')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 3,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'debet',
                    'total' => $dataTRetur->grand_total,
                    'ref' => $dataTRetur->rt_code,
                    'type_transaksi' => 'SRT',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','101040101')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 4,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'credit',
                    'total' => $dataTRetur->grand_total,
                    'ref' => $dataTRetur->rt_code,
                    'type_transaksi' => 'SRT',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            //update
            DB::table('t_faktur')
            ->where('sj_code',$dataTRetur->sj_code)
            ->update([
                'total' => $total_baru,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('/admin/transaksi/sj-retur');

    }

    public function getSJByCustomer($id)
    {
        $dataSj  = DB::table('t_surat_jalan')
        ->select('t_surat_jalan.sj_code')
        ->join('m_customer','m_customer.id','t_surat_jalan.customer')
        ->where('t_surat_jalan.status','post')
        ->where('m_customer.id',$id)
        ->get();
        foreach($dataSj as $key => $retursj){

            $getQtyDsj = DB::table('d_surat_jalan')->where('sj_code',$retursj->sj_code)->sum('qty_delivery');

            $getQtyRetur = DB::table('d_retur_sj')
            ->join('t_retur_sj','t_retur_sj.rt_code','d_retur_sj.rt_code')
            ->where('sj_code',$retursj->sj_code)->sum('qty');
            if(  $getQtyRetur >= $getQtyDsj){
                unset($dataSj[$key]);
            }

        }

        return Response::json($dataSj);
    }

    public function getProdukSj(Request $request)
    {
        $result = DB::table('d_surat_jalan')
        ->join('t_surat_jalan','t_surat_jalan.sj_code','d_surat_jalan.sj_code')
        ->join('m_produk','m_produk.id','=','d_surat_jalan.produk_id')
        ->select('d_surat_jalan.id','m_produk.id as produk_id','m_produk.code','m_produk.name as produk','d_surat_jalan.qty_delivery','t_surat_jalan.gudang','t_surat_jalan.sales','t_surat_jalan.so_code')
        ->where('d_surat_jalan.sj_code',$request->sj_code)
        ->get();
        //add-index-stok
        foreach ($result as $raw_sj) {
            $getQtyRetur = DB::table('d_retur_sj')->where('detail_sj_id',$raw_sj->id)->sum('qty');

            $stok = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $raw_sj->code)
            ->where('m_stok_produk.gudang', $raw_sj->gudang)
            ->groupBy('m_stok_produk.produk_code')
            ->sum('stok');
            //calculate maxqtyretur
            $raw_sj->stok = $stok;
            $raw_sj->maxqtyretur =  $raw_sj->qty_delivery - $getQtyRetur;
        }

        return Response::json($result);
    }

    public function laporanReturSJ(){
        $dataRetur = TReturModel::join('m_customer','m_customer.id','t_retur_sj.customer')
                    ->select('t_retur_sj.rt_code','t_retur_sj.id','m_customer.name as name')
                    ->orderBy('id','DESC')
                    ->groupBy('t_retur_sj.id','t_retur_sj.rt_code','m_customer.name')
                    ->get();

        $customer = TReturModel::join('m_customer','m_customer.id','t_retur_sj.customer')
                    ->select('m_customer.id','m_customer.name')
                    ->orderBy('m_customer.name','ASC')
                    ->groupBy('m_customer.id','m_customer.name')
                    ->get();
        // dd($dataRetur);
        return view('admin.transaksi.surat-jalan.retur.laporan',compact('dataRetur','customer'));
    }

    public function getCustomerByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        // return $tglmulai;

        $dataCustomer = DB::table('m_customer')
            ->join('t_retur_sj', 'm_customer.id', '=', 't_retur_sj.customer')
            ->select('m_customer.id as customer_id','m_customer.name','m_customer.main_address')
            ->where('t_retur_sj.retur_dates','>=',date('Y-m-d', strtotime($tglmulai)))
            ->where('t_retur_sj.retur_dates','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('m_customer.id','m_customer.main_address','m_customer.name')
            ->get();

        return Response::json($dataCustomer);
    }
    public function getReturajax($customerID)
    {
        $dataSJ = DB::table('t_retur_sj')
            ->where('customer',$customerID)
            ->get();

        return Response::json($dataSJ);
    }
}
