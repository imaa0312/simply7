<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TPurchaseOrderModel;
use App\Models\TSuratJalanMasukModel;
use App\Models\DSuratJalanMasukModel;
use App\Models\MStokProdukModel;
use App\Models\MReasonModel;
use Yajra\Datatables\Datatables;



class TSuratJalanMasukController extends Controller
{
    public function index()
    {
        $dataSuratJalan = DB::table('t_surat_jalan_masuk')
            ->join('m_supplier','m_supplier.id','=','t_surat_jalan_masuk.supplier')
            ->select('t_surat_jalan_masuk.*','m_supplier.name as supplier','m_supplier.id as supplier_id')
            ->orderBy('t_surat_jalan_masuk.id', 'desc')
            ->get();

        return view('admin.purchasing.surat-jalan.index', compact('dataSuratJalan'));
    }

    public function create()
    {
        $dataPo = DB::table('t_purchase_order')
            ->join('d_purchase_order','d_purchase_order.po_code','=','t_purchase_order.po_code')
            ->select('t_purchase_order.po_code','t_purchase_order.id')
            ->whereRaw('d_purchase_order.qty != d_purchase_order.save_qty')
            ->where('status_aprove','approved')
            ->orderBy('t_purchase_order.po_code','DESC')
            ->groupBy('t_purchase_order.po_code','t_purchase_order.id')
            ->get();

        $dataSupplier = DB::table('t_purchase_order')
            ->join('d_purchase_order','d_purchase_order.po_code','=','t_purchase_order.po_code')
            ->leftjoin('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
            ->select('t_purchase_order.supplier','m_supplier.name')
            ->whereRaw('d_purchase_order.qty != d_purchase_order.save_qty')
            ->where('status_aprove','approved')
            ->orderBy('t_purchase_order.supplier','DESC')
            ->groupBy('t_purchase_order.supplier','m_supplier.name')
            ->get();

        //dd($dataSupplier);

        //dd($dataPo);
            $gudang = DB::table('m_gudang')->get();
            $setSj = $this->setCodeSJ();
        //'dataPo',
        return view('admin.purchasing.surat-jalan.create', compact('dataPo','setSj','gudang','dataSupplier'));
    }

    public function dataPo($codePo)
    {
        $result = DB::table('t_purchase_order')
        ->join('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
        ->select('t_purchase_order.*','m_supplier.name as supplier','m_supplier.id as supplier_id')
        ->where('t_purchase_order.po_code','=',$codePo)
        ->first();

        return Response::json($result);
    }
    //
    public function getProdukPO(Request $request)
    {
        // $produk = $request->produk;
        // $id_product = $request->id;

        $result = DB::table('d_purchase_order')
                ->join('m_produk','m_produk.id','=','d_purchase_order.produk')
                ->join('t_purchase_order','t_purchase_order.po_code','=','d_purchase_order.po_code')
                ->join('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
                ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
                ->select('m_produk.code','m_produk.id as produk_id','m_produk.name','m_produk.berat','d_purchase_order.*',DB::raw('(qty - save_qty) as maxdeviverqty'),'m_satuan_unit.code as code_unit')
                ->where('d_purchase_order.po_code','=',$request->po_code)
                ->get();

            foreach ($result as $raw_so) {
                $cekSj = 0;

                $cekSj = DB::table('d_surat_jalan_masuk')
                        ->join('t_surat_jalan_masuk','t_surat_jalan_masuk.sj_masuk_code','d_surat_jalan_masuk.sj_masuk_code')
                        ->where('d_surat_jalan_masuk.dpo_id','=',$raw_so->id)
                        ->where('t_surat_jalan_masuk.status','!=','cancel')
                        ->get();

                if( count($cekSj) > 0 ){
                    $raw_so->free_qty = 0;
                }

                $raw_so->cek = $cekSj;
            }


        return Response::json($result);
    }

    public function getPOBySupplier($supplier)
    {
        $dataPo = DB::table('t_purchase_order')
            ->join('d_purchase_order','d_purchase_order.po_code','=','t_purchase_order.po_code')
            ->select('t_purchase_order.po_code','t_purchase_order.id')
            ->whereRaw('d_purchase_order.qty != d_purchase_order.save_qty')
            ->where('status_aprove','approved')
            ->where('t_purchase_order.supplier','=',$supplier)
            ->orderBy('t_purchase_order.po_code','DESC')
            ->groupBy('t_purchase_order.po_code','t_purchase_order.id')
            ->get();

        return Response::json($dataPo);
    }


    //
    public function store(Request $request)
    {
        //dd($request->all());
        $array = [];
        $i = 0;
        $success = null;
        $produk_code = $request->produk_code;
        $produk_id = $request->produk_id;
        $deliver = $request->deliver;
        //$supplier_price = $request->supplier_price;

        $setSj = $this->setCodeSJ();
        $receive_date = date('Y-m-d', strtotime($request->alternative_receive_date));

        //arrayProdukID
        foreach($produk_id as $raw_produk_id){
            $array[$i]['id_produk'] = $raw_produk_id;
            $array[$i]['po_code'] = $request->po_code;
            // ($i <= count($raw_produk_id)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;
        foreach($request->dpo_id as $rawdpo_id){
            $array[$i]['dpo_id'] = $rawdpo_id;
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
            $array[$i]['qty_received'] = $raw_deliver;

            // ($i <= count($raw_deliver)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;
        //arrayQtyDeliver
        foreach($request->free_qty as $rawfree){
            $array[$i]['free_qty'] = $rawfree;
            $i++;
        }
        //
        // $i=0;
        // foreach($supplier_price as $raw_supplier_price){
        //     $array[$i]['supplier_price'] = $raw_supplier_price;
        //
        //     // ($i <= count($raw_supplier_price)) ? $i++ : $i = 0;
        //     $i++;
        // }
        $i=0;
        //
        // echo "<pre>";
        //     print_r($array);
        // echo "</pre>";
        // dd($request->all());
        // die();
        DB::beginTransaction();
        try{
            //insert to t surat-jalan masuk
            $store = new TSuratJalanMasukModel;
            // $store->sj_masuk_code = $request->sj_masuk_code;
            $store->sj_masuk_code = $setSj;
            $store->sj_masuk_date = $receive_date;
            $store->po_code = $request->po_code;
            $store->supplier = $request->supplier;
            $store->description = $request->description;
            $store->gudang = $request->gudang;
            $store->alternative_receive_date = $receive_date;
            $store->type_asset = $request->type_asset;
            $store->user_input = auth()->user()->id;
            $store->save();

            //insert detail surat-jalan masuk
            for($x=0; $x<count($array); $x++){
                DSuratJalanMasukModel::insert([
                    'sj_masuk_code' => $store->sj_masuk_code,
                    'dpo_id' =>$array[$x]['dpo_id'],
                    'produk_id' => $array[$x]['id_produk'],
                    'qty' => $array[$x]['qty_received'],
                    'free_qty' => $array[$x]['free_qty'],
                ]);
            }

            // update di purchase order
            for($n=0; $n<count($array); $n++){
                //select
                $getsaveQty = DB::table('d_purchase_order')->where('po_code',$array[$n]['po_code'])
                ->where('produk',$array[$n]['id_produk'])->first();
                if( $getsaveQty->save_qty != 0 ){
                    //update
                    DB::table('d_purchase_order')->where('po_code',$array[$n]['po_code'])->where('produk',$array[$n]['id_produk'])
                    ->update([
                        'save_qty' => $getsaveQty->save_qty + $array[$n]['qty_received'],
                    ]);
                }else{
                    DB::table('d_purchase_order')->where('po_code',$array[$n]['po_code'])->where('produk',$array[$n]['id_produk'])
                    ->update([
                        'save_qty' => $array[$n]['qty_received'],
                    ]);
                }

            }
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }

        // if($success == true){
        return redirect('admin/transaksi-surat-jalan-masuk');
        // }else{
        // 	return redirect()->back()->with('message', 'Code Order Sudah Ada Atau Produk Belum Terisi, Coba Lagi');
        // }
    }

    public function show($sjcode,$status)
    {
        $dataSuratJalan = DB::table('d_surat_jalan_masuk')
                        ->select('*','m_satuan_unit.code as code_unit','m_produk.code as code_produk')
    					->join('t_surat_jalan_masuk','t_surat_jalan_masuk.sj_masuk_code','=','d_surat_jalan_masuk.sj_masuk_code')
                        ->join('m_produk','m_produk.id','=','d_surat_jalan_masuk.produk_id')
                        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
    					->where('d_surat_jalan_masuk.sj_masuk_code',$sjcode)
                        ->where('t_surat_jalan_masuk.status',$status)
    					->get();

         $detailSuratJalan = DB::table('d_surat_jalan_masuk')
    					->join('t_surat_jalan_masuk','t_surat_jalan_masuk.sj_masuk_code','=','d_surat_jalan_masuk.sj_masuk_code')
                         ->join('m_supplier','m_supplier.id','=','t_surat_jalan_masuk.supplier')
                         ->leftjoin('m_user as user_cancel','user_cancel.id','t_surat_jalan_masuk.user_cancel')
                         ->select('d_surat_jalan_masuk.*','t_surat_jalan_masuk.*','m_supplier.name as supplier','user_cancel.name as user_cancel')
    					 ->where('d_surat_jalan_masuk.sj_masuk_code',$sjcode)
                         ->where('t_surat_jalan_masuk.status',$status)
                         ->first();
        $gudang= DB::table('t_surat_jalan_masuk')
        ->join('m_gudang','m_gudang.id','=','t_surat_jalan_masuk.gudang')
        ->where('sj_masuk_code',$sjcode)
        ->first();

        // dd($dataSuratJalan);

    	return view('admin.purchasing.surat-jalan.detail', compact('dataSuratJalan','detailSuratJalan','gudang'));
    }

    public function posting($id)
    {
        //definition variable

        $dataSuratJalan = DB::table('t_surat_jalan_masuk')
            ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','=','t_surat_jalan_masuk.sj_masuk_code')
            ->where('t_surat_jalan_masuk.id',$id)
            ->get();

        // dd($dataSuratJalan);

        $totalqtysj = DB::table('t_surat_jalan_masuk')
            ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','=','t_surat_jalan_masuk.sj_masuk_code')
            ->where('t_surat_jalan_masuk.id',$id)
            ->sum('qty');

        //get-t-so
        $dataPOfromSJ = DB::table('t_purchase_order')
            ->join('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
            ->select('t_purchase_order.*','m_supplier.top')
            ->where('po_code',$dataSuratJalan[0]->po_code)
            ->first();

        //get top_hari and top_toleransi from so
        //dd($dataPOfromSJ);
        // $top = $dataPOfromSJ->top;
        //($dataPOfromSJ->top_toleransi != null ) ? $top_toleransi = $dataPOfromSJ->top_toleransi : $top_toleransi = 0;

        //$jatuh_tempo = $top_hari + $top_toleransi;
        // $jatuh_tempo = $top_hari;
        $jatuh_tempo = $dataPOfromSJ->jatuh_tempo;

        // dd($dataSuratJalan,$dataPOfromSJ,$jatuh_tempo);

        //get diskon header po
        $totalheader = $dataPOfromSJ->grand_total;

        $totaldetail = DB::table('d_purchase_order')
            ->where('po_code',$dataSuratJalan[0]->po_code)
            ->sum('total_neto');

        $qtybarang = DB::table('d_purchase_order')
            ->where('po_code',$dataSuratJalan[0]->po_code)
            ->sum('qty');

        $diskonheader = $totaldetail - $totalheader;

        $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);

        //dd($diskonheader/$qtybarang);

        $total = 0;
        DB::beginTransaction();
        try{
            // update sales order sj_qty
            foreach ($dataSuratJalan as $value) {
                //get sj_qty
                $oldDSuratjalan = DB::table('d_purchase_order')->where('id',$value->dpo_id)->first();

                //update d sales order sj_qty
                DB::table('d_purchase_order')->where('id',$value->dpo_id)->update([
                    'sj_qty' => $oldDSuratjalan->sj_qty + $value->qty,
                ]);

                //getdata d po
                $dataDPO = DB::table('d_purchase_order')
                    ->where('po_code',$value->po_code)
                    ->where('produk',$value->produk_id)
                    ->first();

                //insert stok out
                $getCodeProduk = DB::table('m_produk')->where('id',$value->produk_id)->first();
                // $getGudangSupplier = DB::table('m_supplier')->where('m_supplier.id','=',$value->supplier)
                // ->first();

                $jumlahStok = DB::table('m_stok_produk')->where('produk_code',$getCodeProduk->code)
                    ->where('gudang',$dataSuratJalan[0]->gudang)
                    ->sum('stok');

                //total qty and free qty
                $inBarang = $value->qty + $value->free_qty;
                // dd($outBarang);

                $insertStokModel = new MStokProdukModel;
                $insertStokModel->produk_code =  $getCodeProduk->code;
                $insertStokModel->produk_id =  $getCodeProduk->id;
                $insertStokModel->transaksi   =  $value->sj_masuk_code;
                $insertStokModel->tipe_transaksi   = 'Purchase Delivery';
                $insertStokModel->person   =  $dataSuratJalan[0]->supplier;
                $insertStokModel->stok_awal   =  $jumlahStok;
                $insertStokModel->gudang      =  $dataSuratJalan[0]->gudang;
                $insertStokModel->stok        =  $inBarang;
                $insertStokModel->type        =  'in';
                $insertStokModel->save();

                //get harga per barang
                $hargabarang = $dataDPO->total_neto / $dataDPO->qty;
                $hargabarang = (int) round($hargabarang);
                //
                // //dd($hargabarang);
                //
                //hitung total price per faktur
                $total = $total + ($value->qty * ($hargabarang - $diskonheaderperbarang));

            }
            // dd($total);
            //cek so close
            $detailSuratJalan = DB::table('d_purchase_order')->where('po_code',$dataSuratJalan[0]->po_code)->get();
            $cekClose = 1 ;
            foreach ($detailSuratJalan as $key => $value) {
                if( $value->sj_qty < $value->qty ){
                    $cekClose = 0;
                }
            }

            if($cekClose == 1){
                DB::table('t_purchase_order')->where('po_code',$dataSuratJalan[0]->po_code)->update([
                    'status_aprove' => 'closed'
                ]);
            }

            if ($totalqtysj == $qtybarang) {
                $total = $totalheader;
            }

            if ($dataPOfromSJ->jatuh_tempo == 0) {
                $jatuh_tempo = 0;
            }

            $getCode = substr($dataSuratJalan['0']->sj_masuk_code,2,10);
            $pi_code = 'PI'.$getCode;

            //create purchase invoice
            DB::table('t_purchase_invoice')
                ->insert([
                    'pi_code' => $pi_code,
                    'sj_masuk_code' => $dataSuratJalan[0]->sj_masuk_code,
                    'po_code' => $dataSuratJalan[0]->po_code,
                    'supplier' => $dataSuratJalan[0]->supplier,
                    'type' => 'pi',
                    'jatuh_tempo' => date('Y-m-d', strtotime(date('d-m-Y'). ' + '.$jatuh_tempo.' days')),
                    'total_sesuai_pd' => $total,
                    'total' => $total,
                ]);

            //update status
            DB::table('t_surat_jalan_masuk')->where('t_surat_jalan_masuk.id',$id)->update([
                'status' => 'post',
            ]);

            //AUTO JURNAL
            $id_gl = DB::table('t_general_ledger')
                ->insertGetId([
                    'general_ledger_date' => date('Y-m-d'),
                    'general_ledger_periode' => date('Ym'),
                    'general_ledger_keterangan' => 'PD|PI No.'.$dataSuratJalan[0]->sj_masuk_code,
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
                    'total' => $total,
                    'ref' => $pi_code,
                    'type_transaksi' => 'PI',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            // $id_coa = DB::table('m_coa')
            //     ->where('code','7150301')
            //     ->first();

            // DB::table('d_general_ledger')
            //     ->insert([
            //         't_gl_id' => $id_gl,
            //         'sequence' => 2,
            //         'id_coa' => $id_coa->id,
            //         'debet_credit' => 'debet',
            //         'total' => 0,
            //         'ref' => $pi_code,
            //         'type_transaksi' => 'PI',
            //         'status' => 'post',
            //         'user_confirm' => auth()->user()->id,
            //         'confirm_date' => date('Y-m-d'),
            // ]);

            $id_coa = DB::table('m_coa')
                ->where('code','2010101')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 3,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'credit',
                    'total' => $total,
                    'ref' => $pi_code,
                    'type_transaksi' => 'PI',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi-surat-jalan-masuk');
    }

    // //old
    public function edit($sjCode)
    {
        $gudang = DB::table('m_gudang')->get();

        $headerSJM = DB::table('t_surat_jalan_masuk')
                    ->join('m_supplier','m_supplier.id','=','t_surat_jalan_masuk.supplier')
                    ->join('m_user','m_user.id','=','t_surat_jalan_masuk.user_input')
                    ->select('t_surat_jalan_masuk.*','m_user.name as user_input','m_supplier.name as supplier')
                    ->where('t_surat_jalan_masuk.sj_masuk_code',$sjCode)
                    ->first();

        $detailSJM  = DB::table('d_surat_jalan_masuk')
                    ->join('m_produk','m_produk.id','d_surat_jalan_masuk.produk_id')
                    ->join('d_purchase_order','d_purchase_order.id','d_surat_jalan_masuk.dpo_id')
                    ->select('*','d_surat_jalan_masuk.id as id','d_surat_jalan_masuk.qty as qty_received','d_surat_jalan_masuk.produk_id as id_produk')
                    ->where('sj_masuk_code',$sjCode)
                    ->get();
        //add-ondex-max-deliverqty
        foreach ($detailSJM as $sj) {
            $sj->maxdeliverqty = ($sj->save_qty == 0 ) ? $sj->qty : $sj->qty - ($sj->save_qty - $sj->qty_received);
        }

        // dd($headerSJM,$detailSJM);
        return view('admin.purchasing.surat-jalan.update',compact('headerSJM','detailSJM','gudang'));
    }
    //
    // //old
    public function update(Request $request)
    {
        // dd($request->all());
        $array = [];
        $i = 0;
        //ambil data baru dimasukkan array
        foreach ($request->id_produk as $raw_produk_id) {
            $array[$i]['id_produk'] = $raw_produk_id;
            $array[$i]['sj_masuk_code'] = $request->sj_masuk_code;
            $i++;
        }
        $i = 0;
        foreach ($request->save_qty as $rawsaveqty) {
            $array[$i]['save_qty'] = $rawsaveqty;
            $i++;
        }

        $i = 0;
        foreach ($request->free_qty as $rawfreeqty) {
            $array[$i]['free_qty'] = $rawfreeqty;
            $i++;
        }

        $i = 0;
        foreach ($request->id_sj as $rawidsj) {
            $array[$i]['id_sj'] = $rawidsj;
            $i++;
        }
        $i = 0;
        foreach ($request->dpo_id as $rawdpoid) {
            $array[$i]['dpo_id'] = $rawdpoid;
            $i++;
        }

        //ambil data lama
        $detailSjLama = DB::table('t_surat_jalan_masuk')
                        ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','=','t_surat_jalan_masuk.sj_masuk_code')
                        ->where('t_surat_jalan_masuk.sj_masuk_code', $request->sj_masuk_code)
                        ->select('*','d_surat_jalan_masuk.qty as qty_received')
                        ->get();
            // echo "<pre>";
            // print_r($array);
            // dd($detailSjLama);
            // die();
        DB::beginTransaction();
        try {

             //update t_sj
             DB::table('t_surat_jalan_masuk')->where('t_surat_jalan_masuk.sj_masuk_code',$request->sj_masuk_code)
             ->where('t_surat_jalan_masuk.po_code',$request->po_code)
             ->update([
                 'gudang'     => $request->gudang,
                 'description' => $request->description,
                 'alternative_receive_date' => date('Y-m-d',strtotime($request->alternative_receive_date)),
             ]);

            foreach( $detailSjLama as $key => $sj ){

                $cekIdSj = 0; //flag id

                 for ($x=0; $x <count($array) ; $x++) {

                     //cekid detailSjLama dan detailSjBaru (yang dilempar dari view)
                     if ( $sj->id == $array[$x]['id_sj'] ){
                        //  dd('update');

                         $cekIdSj = 1;
                        //  $soCodeForUpdate = $array[$x]['po_code'];
                         $sjCodeForUpdate = $array[$x]['sj_masuk_code'];
                         $idProdukForUpdate = $array[$x]['id_produk'];
                         $saveQtyForUpdate = $array[$x]['save_qty'];
                         $detailPOId       = $array[$x]['dpo_id'];

                         // echo $saveQtyForUpdate."<br>";
                         // echo $x."<br>";
                         // echo count($array);
                         // die();

                         $oldPo = DB::table('d_purchase_order')->where('id',$detailPOId)->first();
                        //  dd($oldPo);
                         $allSaveQtyWithoutMe = $oldPo->save_qty -  $sj->qty_received;
                         // dd($allSaveQtyWithoutMe);

                         //  $data->qty - ($data->save_qty - $sj->qty_received)
                         DB::table('d_purchase_order')->where('id',$detailPOId)->update([
                               'save_qty' => $oldPo->qty - ( ($oldPo->qty - $allSaveQtyWithoutMe) -  $saveQtyForUpdate ),
                               //   'save_qty' => $saveQtyForUpdate,
                           ]);
                           // dd($oldPo->qty - ( ($oldPo->qty - $allSaveQtyWithoutMe) -  $saveQtyForUpdate ));

                           //update d surat-jalan
                           DB::table('d_surat_jalan_masuk')->where('sj_masuk_code',$sjCodeForUpdate)
                           ->where('produk_id',$idProdukForUpdate)->update([
                               'qty' => $saveQtyForUpdate,
                           ]);
                     }
                     //$saveQtyForDelete = $array[$x]['save_qty'];
                 } //endfor

                 //kondisi hapus
                 if( $cekIdSj == 0 ){
                    //  dd('hapus');
                     $oldPurchaseOrder = DB::table('d_purchase_order')->where('id',$sj->dpo_id)->first();

                     $oldDSuratjalan = DB::table('d_surat_jalan_masuk')->select('*','qty as qty_received')->where('id', '=', $sj->id)->first();

                     // echo "<pre>";
                     //    print_r($array);
                     // die();
                     //update d sales order sj_qty
                     DB::table('d_purchase_order')->where('id',$sj->dpo_id)->update([
                         'save_qty' => $oldPurchaseOrder->save_qty - $oldDSuratjalan->qty_received
                     ]);
                     dd($oldPurchaseOrder->save_qty - $oldDSuratjalan->qty_received);
                     //delete
                     DB::table('d_surat_jalan_masuk')->where('id', '=', $sj->id)->delete();

                 }
             } //endforeach
             DB::commit();
         } catch (\Exception $e) {

             DB::rollback();
             dd($e);
         }
         return redirect('admin/transaksi-surat-jalan-masuk');
    }
    // ====================================^^komenanku
    // public function edit($sjCode)
    // {
    //     //data-sj-yang-di-copy
    //     $dataSJ = TSuratJalanMasukModel::join('m_supplier','m_supplier.id','=','d_surat_jalan_masuk.supplier')
    //             ->join('m_wilayah_sales','m_wilayah_sales.id','=','m_supplier.wilayah_sales')
    //             ->join('m_user as sales','sales.id','=','d_surat_jalan_masuk.sales')
    //             ->join('m_user as user_input','user_input.id','=','d_surat_jalan_masuk.user_input')
    //             ->select('d_surat_jalan_masuk.*','m_supplier.name as supplier','m_supplier.id as supplier_id','m_wilayah_sales.name as wilayah',
    //             'sales.name as sales','sales.id as sales_id','user_input.name as user_input')
    //             ->where('sj_masuk_code',$sjCode)
    //             ->first();

    //     //semua-data-so
    //     $dataSo = TSalesOrderModel::where('status_aprove','approved')->orderBy('po_code','DESC')->get();

    //     //barang-so
    //     $barangSoFromSJCopy = DB::table('d_surat_jalan_masuk')
    //                     ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','=','d_surat_jalan_masuk.sj_masuk_code')
    //                     ->join('m_produk','m_produk.id','=','d_surat_jalan_masuk.produk_id')
    //                     ->select('m_produk.id as produk','m_produk.code','m_produk.name','m_produk.berat',
    //                     'm_produk.satuan_kemasan','m_produk.berat',
    //                     'd_surat_jalan_masuk.*','d_surat_jalan_masuk.gudang','d_surat_jalan_masuk.po_code')
    //                     ->where('d_surat_jalan_masuk.sj_masuk_code',$sjCode)
    //                     ->get();
    //                  //    d_surat_jalan_masuk.qty_so as maxDeviverQty'

    //     foreach ($barangSoFromSJCopy as $raw_so) {
    //         $stok = DB::table('m_stok_produk')
    //                 ->where('m_stok_produk.produk_code', $raw_so->code)
    //                 ->where('m_stok_produk.gudang', $raw_so->gudang)
    //                 ->groupBy('m_stok_produk.produk_code')
    //                 ->sum('stok');
    //         $data = DB::table('d_purchase_order')->where('po_code',$raw_so->po_code)
    //                         ->where('produk',$raw_so->produk)->first();
    //         $raw_so->stok = $stok;
    //         $raw_so->qty =  $data->qty;
    //         $raw_so->maxdeviverqty = ( $data->qty == $data->save_qty) ? $data->qty : $data->qty - $data->save_qty;
    //      //    DB::raw('(qty - save_qty) as maxDeviverQty'
    //     }
    //  //    dd($barangSoFromSJCopy);

    //     return view('admin.transaksi.surat-jalan.update-new',compact('dataSJ','barangSoFromSJCopy','dataSo'));
    // }
    //
    // public function update(Request $request)
    // {
    //     {
    //         $array = [];
    //         $i = 0;
    //         $success = null;
    //         $produk_code = $request->produk_code;
    //         $produk_id = $request->produk_id;
    //         $deliver = $request->deliver;
    //         $supplier_price = $request->supplier_price;
    //
    //         $setSj = $this->setCodeSJ();
    //          $sending_date = date('Y-m-d', strtotime($request->alternative_sending_date));
    //
    //          //arrayProdukID
    //          foreach($produk_id as $raw_produk_id){
    //              $array[$i]['id_produk'] = $raw_produk_id;
    //              $array[$i]['po_code'] = $request->po_code;
    //              // ($i <= count($raw_produk_id)) ? $i++ : $i = 0;
    //              $i++;
    //          }
    //          $i=0;
    //          //arrayProdukCode
    //          foreach($produk_code as $raw_produk){
    //              $array[$i]['produk'] = $raw_produk;
    //              // ($i <= count($raw_produk)) ? $i++ : $i = 0;
    //              $i++;
    //          }
    //          $i=0;
    //          //arrayQtyDeliver
    //          foreach($deliver as $raw_deliver){
    //              $array[$i]['qty_received'] = $raw_deliver;
    //
    //              // ($i <= count($raw_deliver)) ? $i++ : $i = 0;
    //              $i++;
    //          }
    //          $i=0;
    //          foreach($supplier_price as $raw_supplier_price){
    //              $array[$i]['supplier_price'] = $raw_supplier_price;
    //
    //              // ($i <= count($raw_supplier_price)) ? $i++ : $i = 0;
    //              $i++;
    //          }
    //          $i=0;
    //
    //          // echo "<pre>";
    //          //     print_r($array);
    //          // echo "</pre>";
    //          // dd($request->all());
    //          // die();
    //         DB::beginTransaction();
    //         try{
    //              //update surat-jalan
    //             $update = TSuratJalanMasukModel::find($request->id);
    //             $update->driver_name = $request->driver_name;
    //             $update->license_plate = $request->license_plate;
    //             $update->alternative_sending_date = $sending_date;
    //             $update->name_car = $request->name_car;
    //             $update->description = $request->description;
    //             $update->gudang = $request->gudang;
    //             $update->cod = $request->cod;
    //             $update->user_input = auth()->user()->id;
    //             $update->save();
    //
    //             //delete sj-lama
    //             DSuratJalanModel::where('sj_masuk_code',$request->sj_masuk_code)->delete();
    //
    //
    //              //insert detail surat-jalan baru
    //             for($x=0; $x<count($array); $x++){
    //                 DSuratJalanModel::insert([
    //                     'sj_masuk_code' => $request->sj_masuk_code,
    //                     'produk_id' => $array[$x]['id_produk'],
    //                     'qty_received' => $array[$x]['qty_received'],
    //                     'supplier_price' => $array[$x]['supplier_price'],
    //                 ]);
    //             }
    //
    //             for($d=0; $d<count($array); $d++){
    //                 //select
    //                 $getsaveQty = DB::table('d_purchase_order')->where('po_code',$array[$d]['po_code'])
    //                     ->where('produk',$array[$d]['id_produk'])->delete();
    //              }
    //
    //              // update d sales order
    //              for($n=0; $n<count($array); $n++){
    //                  //select
    //                  $getsaveQty = DB::table('d_purchase_order')->where('po_code',$array[$n]['po_code'])
    //                      ->where('produk',$array[$n]['id_produk'])->first();
    //                  if( $getsaveQty->save_qty != 0 ){
    //                      //update
    //                      DB::table('d_purchase_order')->where('po_code',$array[$n]['po_code'])->where('produk',$array[$n]['id_produk'])
    //                      ->update([
    //                          'save_qty' => $getsaveQty->save_qty + $array[$n]['qty_received'],
    //                      ]);
    //                  }else{
    //                      DB::table('d_purchase_order')->where('po_code',$array[$n]['po_code'])->where('produk',$array[$n]['id_produk'])
    //                      ->update([
    //                          'save_qty' => $array[$n]['qty_received'],
    //                      ]);
    //                  }
    //
    //             }
    //              DB::commit();
    //              $success = true;
    //         }catch(\Exception $e){
    //             dd($e);
    //             $success = false;
    //             DB::rollback();
    //         }
    //
    //         return redirect('admin/transaksi-surat-jalan');
    //     }
    // }

    //==================VVkomenanku
    public function delete($sjcode)
    {
     $dataSJ = DB::table('t_surat_jalan_masuk')
                         ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','=','t_surat_jalan_masuk.sj_masuk_code')
                         ->where('t_surat_jalan_masuk.sj_masuk_code',$sjcode)
                         ->get();
     //dd($dataSJ);
     foreach ($dataSJ as $key => $value) {
       //get sj_qty
       $oldDSuratjalan = DB::table('d_purchase_order')->where('po_code',$value->po_code)
       ->where('produk',$value->produk_id)->first();

       //update d sales order sv_qty
     //   if( $oldDSuratjalan->save_qty != $oldDSuratjalan->qty ){
           DB::table('d_purchase_order')->where('po_code',$value->po_code)
           ->where('produk',$value->produk_id)->update([
               'save_qty' => $oldDSuratjalan->save_qty - $value->qty
           ]);
     //   }
     }
        DB::table('t_surat_jalan_masuk')->where('sj_masuk_code', '=',$sjcode)->where('status','in process')->delete();
        DB::table('d_surat_jalan_masuk')->where('sj_masuk_code', '=', $sjcode)->delete();
        return redirect()->back();

    }

    public function laporanSJ()
     {
       $dataSupplier = DB::table('m_supplier')
             ->join('t_surat_jalan_masuk', 'm_supplier.id', '=', 't_surat_jalan_masuk.supplier')
             ->select('m_supplier.id as supplier_id','name')
             ->groupBy('m_supplier.id')
             ->get();

       $dataBarang = DB::table('m_produk')
             ->rightjoin('d_surat_jalan_masuk', 'd_surat_jalan_masuk.produk_id', '=', 'm_produk.id')
             ->select('m_produk.id as barang_id','m_produk.name')
             ->groupBy('m_produk.id')
             ->get();

       return view('admin.purchasing.surat-jalan.laporan',compact('dataSupplier','dataBarang'));
     }

     public function getSupplierByPeriode($periode)
     {
         $tglmulai = substr($periode,0,10);
         $tglsampai = substr($periode,13,10);

         $dataSupplier = DB::table('m_supplier')
             ->join('t_surat_jalan_masuk', 'm_supplier.id', '=', 't_surat_jalan_masuk.supplier')
             ->select('m_supplier.id as supplier_id','name','main_address')
             ->where('t_surat_jalan_masuk.sj_masuk_date','>=',date('Y-m-d', strtotime($tglmulai)))
             ->where('t_surat_jalan_masuk.sj_masuk_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
             ->groupBy('m_supplier.id')
             ->get();

         return Response::json($dataSupplier);
     }
    //
    //  public function getSJBySupplier($supplierID)
    //  {
    //      $dataSJ = DB::table('d_surat_jalan_masuk')
    //          ->where('supplier',$supplierID)
    //          ->get();
    //
    //      return Response::json($dataSJ);
    //  }
    //
     public function getSJByPo($idSo)
     {
         $dataSJ = DB::table('t_surat_jalan_masuk')
             ->where('po_code',$idSo)
             ->get();

         return Response::json($dataSJ);
     }
    //
     public function getBarangBySj($sjId)
     {
         if ($sjId == '0') {
             $dataBarang = DB::table('m_produk')
                 ->select('id as barang_id','name')
                 ->groupBy('id')
                 ->get();
         }else{
             $dataBarang = DB::table('m_produk')
                 ->rightjoin('d_surat_jalan_masuk', 'd_surat_jalan_masuk.produk_id', '=', 'm_produk.id')
                 ->select('m_produk.id as barang_id','m_produk.name')
                 ->where('d_surat_jalan_masuk.sj_masuk_code',$sjId)
                 ->groupBy('m_produk.id')
                 ->get();
         }
         //$dataBarang = 0;

         return Response::json($dataBarang);
     }

     public function getBarangBySupplier($supplier)
     {
         if ($supplier == '0') {
             $dataBarang = DB::table('m_produk')
                 ->rightjoin('d_surat_jalan_masuk', 'd_surat_jalan_masuk.produk_id', '=', 'm_produk.id')
                 ->select('m_produk.id as barang_id','m_produk.name')
                 ->groupBy('m_produk.id')
                 ->get();
         }else{
             $dataBarang = DB::table('m_produk')
                 ->rightjoin('d_surat_jalan_masuk', 'd_surat_jalan_masuk.produk_id', '=', 'm_produk.id')
                 ->join('t_surat_jalan_masuk', 'd_surat_jalan_masuk.sj_masuk_code', '=', 't_surat_jalan_masuk.sj_masuk_code')
                 ->select('m_produk.id as barang_id','m_produk.name')
                 ->where('supplier',$supplier)
                 ->groupBy('m_produk.id')
                 ->get();
         }

         return Response::json($dataBarang);
     }
    //
    //  public function cancelSJ($id)
    //  {
    //      $dataSj = TSuratJalanMasukModel::findOrFail($id);
    //      $reason = MReasonModel::orderBy('id','DESC')->get();
    //
    //      return view('admin.transaksi.surat-jalan.cancel',compact('dataSj','reason'));
    //  }
    //
    //  public function cancelSJPost(Request $request)
    //  {
    //      $detailSJ = DSuratJalanModel::select('d_surat_jalan_masuk.po_code','d_surat_jalan_masuk.gudang','d_surat_jalan_masuk.*')
    //              ->join('d_surat_jalan_masuk','d_surat_jalan_masuk.sj_masuk_code','d_surat_jalan_masuk.sj_masuk_code')
    //              ->where('d_surat_jalan_masuk.sj_masuk_code',$request->sj_masuk_code)
    //              ->get();
    //      // dd($detailSJ);
    //      DB::beginTransaction();
    //      try{
    //
    //          foreach ($detailSJ as $key => $value) {
    //
    //              //get-produk-code
    //              $produkCode = DB::table('m_produk')->where('id',$value->produk_id)->first();
    //
    //              //get-stok-awal-produk
    //              $jumlahStok = DB::table('m_stok_produk')->where('produk_code',$value->gudang)
    //              ->where('gudang',$value->gudang)
    //              ->sum('stok');
    //
    //              //qty dan free_qty
    //              $inStok = $value->qty_received + $value->free_qty;
    //              //update-stok
    //              $insertStokModel = new MStokProdukModel;
    //              $insertStokModel->produk_code =  $produkCode->code;
    //              $insertStokModel->transaksi   =  $value->sj_masuk_code;
    //              $insertStokModel->stok_awal   =  $jumlahStok;
    //              $insertStokModel->gudang      =  $value->gudang;
    //              $insertStokModel->stok        =  $inStok;
    //              $insertStokModel->type        =  'in';
    //              $insertStokModel->save();
    //
    //              //get-old-s0
    //              $oldSo = DB::table('d_purchase_order')->where('po_code',$value->po_code)->first();
    //
    //              //update-detail-so-mengurangi-sj_qty dan save_qty
    //              DB::table('d_purchase_order')
    //                  ->where('po_code',$value->po_code)
    //                  ->where('produk',$value->produk_id)
    //                  ->update([
    //                      'sj_qty' => $oldSo->sj_qty - $value->qty_received,
    //                      'save_qty' => $oldSo->save_qty - $value->qty_received,
    //                  ]);
    //          }
    //
    //          //hapus-t-faktur
    //          DB::table('t_faktur')->where('sj_masuk_code',$request->sj_masuk_code)->delete();
    //
    //          //update-t-surat-jalan
    //          DB::table('d_surat_jalan_masuk')->where('sj_masuk_code',$request->sj_masuk_code)->update([
    //              'cancel_reason' => $request->cancel_reason,
    //              'cancel_description' => $request->cancel_description,
    //              'user_cancel' => auth()->user()->id,
    //              'status' => 'cancel',
    //          ]);
    //
    //          //update-t-so
    //          DB::table('t_purchase_order')->where('po_code',$detailSJ[0]->po_code)->update([
    //              'status_aprove' => 'approved'
    //          ]);
    //
    //
    //          DB::commit();
    //      }catch(\Exception $e){
    //          DB::rollback();
    //          dd($e);
    //      }
    //
    //      return redirect('admin/transaksi-surat-jalan');
    //  }
    //
    //  public function copySj($sjCode)
    //  {
    //      //data-sj-yang-di-copy
    //      $dataSJ = TSuratJalanMasukModel::join('m_supplier','m_supplier.id','=','d_surat_jalan_masuk.supplier')
    //              ->join('m_wilayah_sales','m_wilayah_sales.id','=','m_supplier.wilayah_sales')
    //              ->join('m_user as sales','sales.id','=','d_surat_jalan_masuk.sales')
    //              ->join('m_user as user_input','user_input.id','=','d_surat_jalan_masuk.user_input')
    //              ->select('d_surat_jalan_masuk.*','m_supplier.name as supplier','m_supplier.id as supplier_id','m_wilayah_sales.name as wilayah',
    //              'sales.name as sales','sales.id as sales_id','user_input.name as user_input')
    //              ->where('sj_masuk_code',$sjCode)
    //              ->first();
    //
    //      //semua-data-so
    //      $dataSo = TSalesOrderModel::where('status_aprove','approved')->orderBy('po_code','DESC')->get();
    //
    //      //barang-so
    //      $barangSoFromSJCopy = DB::table('d_purchase_order')
    //             ->join('m_produk','m_produk.id','=','d_purchase_order.produk')
    //             ->join('t_purchase_order','t_purchase_order.po_code','=','d_purchase_order.po_code')
    //             ->join('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
    //              ->select('m_produk.code','m_produk.name','m_produk.berat','d_purchase_order.*',DB::raw('(qty - save_qty) as maxDeviverQty'  ),'m_supplier.gudang','m_produk.satuan_kemasan')
    //             ->where('d_purchase_order.po_code','=',$dataSJ->po_code)
    //             ->get();
    //
    //      foreach ($barangSoFromSJCopy as $raw_so) {
    //          $stok = DB::table('m_stok_produk')
    //                  ->where('m_stok_produk.produk_code', $raw_so->code)
    //                  ->where('m_stok_produk.gudang', $raw_so->gudang)
    //                  ->groupBy('m_stok_produk.produk_code')
    //                  ->sum('stok');
    //          $raw_so->stok = $stok;
    //      }
    //
    //      $setSj = $this->setCodeSJ();
    //
    //      // dd($dataSJ,$barangSoFromSJCopy,$setSj);
    //      return view('admin.transaksi.surat-jalan.copy-sj',compact('dataSJ','barangSoFromSJCopy','dataSo','setSj'));
    //  }
    //
    protected function setCodeSJ()
    {
        $dataDate = date("ym");

        $getLastCode = DB::table('t_surat_jalan_masuk')
        ->select('id')
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";
        }else{$nol = null;}

        return $setSj = 'PDTK'.$dataDate.$nol.$getLastCode;
    }

    public function apiPd()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);

        $suratJalan = DB::table('t_surat_jalan_masuk')
        ->join('m_supplier','m_supplier.id','=','t_surat_jalan_masuk.supplier')
        ->select('t_surat_jalan_masuk.*','m_supplier.name as supplier','m_supplier.id as supplier_id')
        ->orderBy('t_surat_jalan_masuk.id', 'desc')
        ->get();

        $roleSuperAdmin = DB::table('m_role')
        ->where('name','Super Admin')
        ->first();
        $i=0;
        //dd(auth()->user()->role);
        return Datatables::of($suratJalan)
        ->addColumn('action', function ($suratJalan) use ($i){
            if(  $suratJalan->status == 'in process'){
                if(auth()->user()->role == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    '<a href="'. url('admin/report-pd/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                    '<a href="'. url('admin/surat-jalan-masuk/'.$suratJalan->sj_masuk_code.'/update') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $suratJalan->sj_masuk_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/transaksi-sjm/'.$suratJalan->sj_masuk_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $suratJalan->sj_masuk_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/surat-jalan-masuk/posting/'.$suratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $suratJalan->sj_masuk_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                }else {
                    if($suratJalan->print == false){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        '<a href="  '. url('admin/report-pd/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                        '<a href="'. url('admin/surat-jalan-masuk/'.$suratJalan->sj_masuk_code.'/update') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $suratJalan->sj_masuk_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/transaksi-sjm/'.$suratJalan->sj_masuk_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $suratJalan->sj_masuk_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/surat-jalan-masuk/posting/'.$suratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $suratJalan->sj_masuk_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                    else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        '<a href="'. url('admin/surat-jalan-masuk/'.$suratJalan->sj_masuk_code.'/update') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $suratJalan->sj_masuk_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/transaksi-sjm/'.$suratJalan->sj_masuk_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $suratJalan->sj_masuk_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/surat-jalan-masuk/posting/'.$suratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $suratJalan->sj_masuk_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                }
            }else{
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                '<td>'.
                '<a href="'. url('admin/report-pd/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                '</td>'.
                '</tr>'.
                '</table>';
            }
        })
        ->editColumn('sj_masuk_code', function($suratJalan){
            $statuspd=($suratJalan->status == 'save') ? '-' : $suratJalan->sj_masuk_code;
            return '<a href="'. url('admin/transaksi-surat-jalan-masuk/'.$suratJalan->sj_masuk_code.'/'.$suratJalan->status) .'">'. $statuspd .'</a> ';
        })
        ->editColumn('sj_masuk_date', function($suratJalan){
            return date('d-m-Y',strtotime($suratJalan->sj_masuk_date));
        })
        ->editColumn('status', function($suratJalan){
            if( $suratJalan->status == 'in process' ){
                return '<span class="label label-default">in process</span>';}
                elseif ($suratJalan->status == 'post'){
                    return '<span class="label label-success">post</span>';}

                    elseif ($suratJalan->status == 'cancel'){
                        return '<span class="label label-danger">cancel</span>';}

                    })
                    ->addIndexColumn()
                    ->rawColumns(['sj_masuk_code','action','status','sj_date'])
                    ->make(true);
                }
}
