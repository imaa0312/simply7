<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;
use App\Models\MSupplierModel;
use App\Models\TRrturSjmModel;
use App\Models\DRrturSjmModel;
use App\Models\MStokProdukModel;

class TReturSuratJalanMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd('aa');
        $dataRetur = TRrturSjmModel::join('m_supplier','m_supplier.id','t_retur_sjm.supplier')
                    ->select('t_retur_sjm.*','m_supplier.name')
                    ->orderBy('id','DESC')
                    ->get();

                    $supplier = TRrturSjmModel::join('m_supplier','m_supplier.id','t_retur_sjm.supplier')
                                ->select('m_supplier.name as name','m_supplier.id as supplier')
                                ->orderBy('supplier','ASC')
                                ->groupBy('name','m_supplier.id')
                                ->get();

                    // ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','t_retur_sjm.sjm_code')
                    // ->whereRaw('d_surat_jalan_masuk.qty != d_surat_jalan_masuk.retur_qty')
        return view('admin.purchasing.surat-jalan.retur.index',compact('dataRetur','supplier'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $returCode = $this->setCodeRetur();

        $supplier = DB::table('m_supplier')
                    ->select('m_supplier.name','m_supplier.id')
                    ->join('t_surat_jalan_masuk','t_surat_jalan_masuk.supplier','m_supplier.id')
                    ->where('t_surat_jalan_masuk.status','post')
                    ->groupBy('m_supplier.name','m_supplier.id')
                    ->get();
                foreach( $supplier as $keysupplier => $valuesupplier ){
                    $dataSj  = DB::table('t_surat_jalan_masuk')
                            ->select('t_surat_jalan_masuk.sj_masuk_code')
                            ->where('t_surat_jalan_masuk.status','post')
                            ->where('supplier',$valuesupplier->id)
                            ->get();

                    foreach($dataSj as $keysj => $value){
                        $getQtyDsjm = DB::table('d_surat_jalan_masuk')->where('sj_masuk_code',$value->sj_masuk_code)->sum('qty');

                        $getQtyRetur = DB::table('d_retur_sjm')
                                    ->join('t_retur_sjm','t_retur_sjm.rt_code','d_retur_sjm.rt_code')
                                    ->where('sjm_code',$value->sj_masuk_code)->sum('qty');

                        if(  $getQtyRetur >= $getQtyDsjm){
                            unset($dataSj[$keysj]);
                        }

                    }

                    if( count($dataSj) < 1 ){
                        unset($supplier[$keysupplier]);
                    }
                }

        return view('admin.purchasing.surat-jalan.retur.create',compact('supplier','returCode'));
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
            //mencari nilai PI
            $dataPI = DB::table('t_purchase_invoice')
                ->where('sj_masuk_code',$request->sjm_code)
                ->first();

            $dataPO = DB::table('t_purchase_order')
                ->join('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
                ->select('t_purchase_order.*')
                ->where('po_code',$request->po_code)
                ->first();

            $dataSJM = DB::table('t_surat_jalan_masuk')
                ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','=','t_surat_jalan_masuk.sj_masuk_code')
                ->where('t_surat_jalan_masuk.sj_masuk_code',$request->sjm_code)
                ->get();

            //get diskon header po
            $totalheader = $dataPO->grand_total;

            $totaldetail = DB::table('d_purchase_order')
                ->where('po_code',$request->po_code)
                ->sum('total_neto');

            $qtybarang = DB::table('d_purchase_order')
                ->where('po_code',$request->po_code)
                ->sum('qty');

            $diskonheader = $totaldetail - $totalheader;
            $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);

            $grand_total = 0;
            foreach ($dataSJM as $raw_data) {
                //getdata d po
                $dataDPO = DB::table('d_purchase_order')
                    ->where('po_code',$raw_data->po_code)
                    ->where('produk',$raw_data->produk_id)
                    ->first();

                //get harga per barang
                $hargabarang = $dataDPO->total_neto / $dataDPO->qty;
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

                //$grand_total = $grand_total + (($raw_data->qty - $qty_retur) * ($hargabarang - $diskonheaderperbarang));
                $grand_total = $grand_total + (($qty_retur) * ($hargabarang - $diskonheaderperbarang));
            }

            //insert
            $insert = new TRrturSjmModel;
            $insert->rt_code  = $this->setCodeRetur();
            $insert->sjm_code  = $request->sjm_code;
            $insert->po_code  = $request->po_code;
            $insert->supplier = $request->supplier;
            $insert->gudang    = $request->gudang;
            $insert->grand_total    = $grand_total;
            $insert->description  = $request->description;
            $insert->user_input   = auth()->user()->id;
            $insert->save();

            for($x=0; $x<count($array); $x++){

                DRrturSjmModel::insert([
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

        return redirect()->route('sjm-retur.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($code)
    {
        $dataheader = DB::table('t_retur_sjm')
                    ->join('m_supplier','m_supplier.id','=','t_retur_sjm.supplier')
                    ->join('m_user','m_user.id','=','t_retur_sjm.user_input')
                    ->select('t_retur_sjm.*','m_supplier.name as supplier','m_user.name as user_input')
                    ->where('rt_code',$code)
                    ->first();

        $datadetail = DB::table('d_retur_sjm')
                    ->select('*','m_satuan_unit.code as satuan_kemasan')
                    ->join('m_produk','m_produk.id','=','d_retur_sjm.produk_id')
                    ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
                    ->where('rt_code',$code)
                    ->get();

        // dd($dataheader,$datadetail);

        return view('admin.purchasing.surat-jalan.retur.detail',compact('dataheader','datadetail'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dataReturSJm = TRrturSjmModel::join('m_user','m_user.id','t_retur_sjm.user_input')->select('t_retur_sjm.*','m_user.name as user_input')->where('t_retur_sjm.id',$id)->first();

        $supplier = DB::table('m_supplier')
                    ->select('m_supplier.name','m_supplier.id')
                    ->join('t_surat_jalan_masuk','t_surat_jalan_masuk.supplier','m_supplier.id')
                    ->where('t_surat_jalan_masuk.status','post')
                    ->get();

        $dataSj  = DB::table('t_surat_jalan_masuk')
                    ->select('t_surat_jalan_masuk.sj_masuk_code')
                    ->where('t_surat_jalan_masuk.status','post')
                    ->where('supplier',$dataReturSJm->supplier)
                    ->get();

        $deatilSJ = DB::table('d_retur_sjm')
                ->join('m_produk','m_produk.id','=','d_retur_sjm.produk_id')
                ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.id','=','d_retur_sjm.detail_sj_id')
                ->join('t_retur_sjm','t_retur_sjm.rt_code','=','d_retur_sjm.rt_code')
                ->select('d_retur_sjm.*','m_produk.id as produk_id','m_produk.code','m_produk.name as produk','d_surat_jalan_masuk.qty as qty_received','d_surat_jalan_masuk.id as id_dsj','t_retur_sjm.gudang')
                ->where('d_retur_sjm.rt_code',$dataReturSJm->rt_code)
                ->get();
                // DB::raw('(qty - save_qty) as maxDeviverQty'  )
        //add-index-stok
        foreach ($deatilSJ as $raw_sj) {

            $getQtyRetur = DB::table('d_retur_sjm')->where('detail_sj_id',$raw_sj->id_dsj)->sum('qty');

            $getSisaQtyRetur = $raw_sj->qty_received - $getQtyRetur;

            $stok = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_sj->code)
                    ->where('m_stok_produk.gudang', $raw_sj->gudang)
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('stok');

            $raw_sj->stok = $stok;
            $raw_sj->maxqtyretur = $getSisaQtyRetur + $raw_sj->qty;
        }
        // dd($deatilSJ);
        return view('admin.purchasing.surat-jalan.retur.update',compact('dataReturSJm','dataSj','deatilSJ','supplier'));


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
            //mencari nilai PI
            $dataPI = DB::table('t_purchase_invoice')
                ->where('sj_masuk_code',$request->sjm_code)
                ->first();

            $dataPO = DB::table('t_purchase_order')
                ->join('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
                ->select('t_purchase_order.*')
                ->where('po_code',$request->po_code)
                ->first();

            $dataSJM = DB::table('t_surat_jalan_masuk')
                ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','=','t_surat_jalan_masuk.sj_masuk_code')
                ->where('t_surat_jalan_masuk.sj_masuk_code',$request->sjm_code)
                ->get();

            //get diskon header po
            $totalheader = $dataPO->grand_total;

            $totaldetail = DB::table('d_purchase_order')
                ->where('po_code',$request->po_code)
                ->sum('total_neto');

            $qtybarang = DB::table('d_purchase_order')
                ->where('po_code',$request->po_code)
                ->sum('qty');

            $diskonheader = $totaldetail - $totalheader;
            $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);

            $grand_total = 0;
            foreach ($dataSJM as $raw_data) {
                //getdata d po
                $dataDPO = DB::table('d_purchase_order')
                    ->where('po_code',$raw_data->po_code)
                    ->where('produk',$raw_data->produk_id)
                    ->first();

                //get harga per barang
                $hargabarang = $dataDPO->total_neto / $dataDPO->qty;
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

                //$grand_total = $grand_total + (($raw_data->qty - $qty_retur) * ($hargabarang - $diskonheaderperbarang));
                $grand_total = $grand_total + (($qty_retur) * ($hargabarang - $diskonheaderperbarang));

            }

            $update = TRrturSjmModel::find($id);
            $update->sjm_code  = $request->sjm_code;
            $update->po_code  = $request->po_code;
            $update->gudang    = $request->gudang;
            $update->grand_total    = $grand_total;
            $update->description  = $request->description;
            $update->save();

        // dd($update->rt_code);

            //delete
            DRrturSjmModel::where('rt_code', $update->rt_code)->delete();

            for($x=0; $x<count($array); $x++){

                DRrturSjmModel::insert([
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

        return redirect()->route('sjm-retur.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $dataRetur = TRrturSjmModel::find($id);

        DRrturSjmModel::where('rt_code',$dataRetur->rt_code)->delete();

        TRrturSjmModel::where('rt_code',$dataRetur->rt_code)->delete();

        return redirect()->route('sjm-retur.index');
    }


    public function approve($id)
    {
        // dd($id);
        $dataTRetur = TRrturSjmModel::find($id);
        $dataDetailRetur = DRrturSjmModel::where('rt_code',$dataTRetur->rt_code)->get();

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
                $insertStokModel->tipe_transaksi   =  'Retur PD';
                $insertStokModel->stok_awal   =  $jumlahStok;
                $insertStokModel->gudang      =  $dataTRetur->gudang;
                $insertStokModel->stok        =  -$value->qty;
                $insertStokModel->type        =  'out';
                $insertStokModel->save();

                //update-header-status
                TRrturSjmModel::where('id',$id)->update(['status' => 'post']);
            }

            //mengembalikan nilai PI
            $dataPI = DB::table('t_purchase_invoice')
                ->where('sj_masuk_code',$dataTRetur->sjm_code)
                ->first();

            $dataPO = DB::table('t_purchase_order')
                ->join('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
                ->select('t_purchase_order.*')
                ->where('po_code',$dataPI->po_code)
                ->first();

            $dataSJM = DB::table('t_surat_jalan_masuk')
                ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','=','t_surat_jalan_masuk.sj_masuk_code')
                ->where('t_surat_jalan_masuk.sj_masuk_code',$dataPI->sj_masuk_code)
                ->get();

            //get diskon header po
            $totalheader = $dataPO->grand_total;

            $totaldetail = DB::table('d_purchase_order')
                ->where('po_code',$dataPI->po_code)
                ->sum('total_neto');

            $qtybarang = DB::table('d_purchase_order')
                ->where('po_code',$dataPI->po_code)
                ->sum('qty');

            $diskonheader = $totaldetail - $totalheader;
            $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);

            $total_baru = 0;
            foreach ($dataSJM as $raw_data) {
                //getdata d po
                $dataDPO = DB::table('d_purchase_order')
                    ->where('po_code',$raw_data->po_code)
                    ->where('produk',$raw_data->produk_id)
                    ->first();

                //get harga per barang
                $hargabarang = $dataDPO->total_neto / $dataDPO->qty;
                $hargabarang = (int) round($hargabarang);

                //get qty retur jika ada
                $qty_retur = DB::table('d_retur_sjm')
                    ->join('t_retur_sjm','t_retur_sjm.rt_code','=','d_retur_sjm.rt_code')
                    ->where('detail_sj_id',$raw_data->id)
                    ->where('status','post')
                    ->sum('qty');

                $total_baru = $total_baru + (($raw_data->qty - $qty_retur) * ($hargabarang - $diskonheaderperbarang));
            }

                //auto jurnal;
             $id_gl = DB::table('t_general_ledger')
                ->insertGetId([
                    'general_ledger_date' => date('Y-m-d'),
                    'general_ledger_periode' => date('Ym'),
                    'general_ledger_keterangan' => 'PD Retur No.'.$dataTRetur->rt_code,
                    'general_ledger_status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','2010101')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 1,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'debet',
                    'total' => $dataTRetur->grand_total,
                    'ref' => $dataTRetur->rt_code,
                    'type_transaksi' => 'PDR',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','101050101')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 2,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'credit',
                    'total' => $dataTRetur->grand_total,
                    'ref' => $dataTRetur->rt_code,
                    'type_transaksi' => 'PDR',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::table('t_purchase_invoice')
                ->where('sj_masuk_code',$dataTRetur->sjm_code)
                ->update([
                    'total' => $total_baru,
                ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect()->route('sjm-retur.index');

    }

    protected function setCodeRetur()
    {
        $dataDate = date("ym");

        $getLastCode = DB::table('t_retur_sjm')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";
        }else{$nol = null;}

        return 'RTSJM'.$dataDate.$nol.$getLastCode;
    }

    public function getSJBySupplier($id)
    {
        $dataSj  = DB::table('t_surat_jalan_masuk')
                    ->select('t_surat_jalan_masuk.sj_masuk_code')
                    ->where('t_surat_jalan_masuk.status','post')
                    ->where('supplier',$id)
                    ->get();

                foreach($dataSj as $keysj => $value){
                    $getQtyDsjm = DB::table('d_surat_jalan_masuk')->where('sj_masuk_code',$value->sj_masuk_code)->sum('qty');

                    $getQtyRetur = DB::table('d_retur_sjm')
                                ->join('t_retur_sjm','t_retur_sjm.rt_code','d_retur_sjm.rt_code')
                                ->where('sjm_code',$value->sj_masuk_code)->sum('qty');

                    if(  $getQtyRetur >= $getQtyDsjm){
                        unset($dataSj[$keysj]);
                    }

                }

        return Response::json($dataSj);
    }

    public function getProdukSjm(Request $request)
    {
        // return $request->all();

        $result = DB::table('d_surat_jalan_masuk')
                ->join('t_surat_jalan_masuk','t_surat_jalan_masuk.sj_masuk_code','d_surat_jalan_masuk.sj_masuk_code')
                ->join('m_produk','m_produk.id','=','d_surat_jalan_masuk.produk_id')
                ->select('d_surat_jalan_masuk.id','m_produk.id as produk_id','m_produk.code','m_produk.name as produk','d_surat_jalan_masuk.qty','t_surat_jalan_masuk.gudang','t_surat_jalan_masuk.po_code')
                ->where('t_surat_jalan_masuk.sj_masuk_code',$request->sjm_code)
                ->get();
                // DB::raw('(qty - save_qty) as maxDeviverQty'  )
        //add-index-stok
        foreach ($result as $raw_sj) {
            $getQtyRetur = DB::table('d_retur_sjm')->where('detail_sj_id',$raw_sj->id)->sum('qty');

            $stok = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_sj->code)
                    ->where('m_stok_produk.gudang', $raw_sj->gudang)
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('stok');

            $raw_sj->stok = $stok;
            $raw_sj->maxqtyretur = $raw_sj->qty - $getQtyRetur;
        }

        return Response::json($result);
    }

    public function laporanReturSJM(){
        $dataRetur = TRrturSjmModel::join('m_supplier','m_supplier.id','t_retur_sjm.supplier')
                    ->select('t_retur_sjm.*','m_supplier.name')
                    ->orderBy('id','DESC')
                    ->get();

                    $supplier = TRrturSjmModel::join('m_supplier','m_supplier.id','t_retur_sjm.supplier')
                                ->select('m_supplier.name as name','m_supplier.id as supplier')
                                ->orderBy('supplier','ASC')
                                ->groupBy('name','m_supplier.id')
                                ->get();

                    // ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','t_retur_sjm.sjm_code')
                    // ->whereRaw('d_surat_jalan_masuk.qty != d_surat_jalan_masuk.retur_qty')

        return view('admin.purchasing.surat-jalan.retur.laporan',compact('dataRetur','supplier'));
    }
    public function getSupplierByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        // return $tglmulai;

        $dataCustomer = DB::table('m_supplier')
            ->join('t_retur_sjm', 'm_supplier.id', '=', 't_retur_sjm.supplier')
            ->select('m_supplier.id as supplier_id','m_supplier.name','m_supplier.main_address')
            ->where('t_retur_sjm.retur_dates','>=',date('Y-m-d', strtotime($tglmulai)))
            ->where('t_retur_sjm.retur_dates','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('m_supplier.id','m_supplier.main_address','m_supplier.name')
            ->get();

        return Response::json($dataCustomer);
    }
    public function getReturajax($supplierID)
    {
        $dataSJ = DB::table('t_retur_sjm')
            ->where('supplier',$supplierID)
            ->get();

        return Response::json($dataSJ);
    }
}
