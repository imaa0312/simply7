<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TFixedAssetPoModel;
use App\Models\TSuratJalanMasukModel;
use App\Models\DSuratJalanMasukModel;
use App\Models\MStokProdukModel;
use App\Models\MReasonModel;
use Yajra\Datatables\Datatables;
class TFixedAssetPdController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index()
     {
         $dataSuratJalan = DB::table('t_fixed_asset_pd')
             ->join('m_supplier','m_supplier.id','=','t_fixed_asset_pd.supplier')
             ->select('t_fixed_asset_pd.*','m_supplier.name as supplier','m_supplier.id as supplier_id')
             ->orderBy('t_fixed_asset_pd.id', 'desc')
             ->get();

         return view('admin.fixed-asset.pd.index', compact('dataSuratJalan'));
     }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */



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
        $gudang = 1;
        $success = null;
        $produk_code = $request->produk_code;
        $produk_id = $request->produk_id;
        $deliver = $request->deliver;
        //$supplier_price = $request->supplier_price;

        $setSj = $this->setCodeSJ();
        $receive_date = date('Y-m-d', strtotime($request->alternative_receive_date));

        //arrayProdukID
        foreach($produk_id as $raw_produk_id)
        {
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
        // $i=0;
        // //arrayQtyDeliver
        // foreach($request->free_qty as $rawfree){
        //     $array[$i]['free_qty'] = $rawfree;
        //     $i++;
        // }

        $i=0;

        DB::beginTransaction();
        try{
            DB::table('t_fixed_asset_pd')
                ->insert([
                    'sj_masuk_code' => $setSj,
                    'po_code' => $request->po_code,
                    'supplier'=> $request->supplier,
                    'description'=> $request->description,
                    'gudang'=> $gudang,
                    'alternative_receive_date'=> $receive_date,
                    'type_asset'=> $request->type_asset,
                    'user_input'=> auth()->user()->id,
                ]);

            //insert detail surat-jalan masuk
            for($x=0; $x<count($array); $x++){

                DB::table('d_fixed_asset_pd')
                    ->insert([
                        'sj_masuk_code' => $setSj,
                        'dpo_id' => $array[$x]['dpo_id'],
                        'produk_id'=> $array[$x]['id_produk'],
                        'qty'=> $array[$x]['qty_received'],
                        // 'free_qty'=> $array[$x]['free_qty'],
                    ]);

                    $qty_sebelum = DB::table('d_fixed_asset_po')
                    ->where('po_code',$request->po_code)
                    ->where('produk',$array[$x]['id_produk'])
                    ->first();
                    // dd($qty_sebelum);
                    $qty_setelah = $qty_sebelum->sj_qty + $array[$x]['qty_received'];
                    $qty = $qty_sebelum->save_qty - $qty_setelah;

                    DB::table('d_fixed_asset_po')
                    ->where('po_code',$request->po_code)
                    ->where('produk',$array[$x]['id_produk'])
                    ->update([
                        'sj_qty' => $qty_setelah,
                        'qty' => $qty,
                    ]);
                    // dd($qty_setelah);
            }

            // update di purchase order
            for($n=0; $n<count($array); $n++){
                //select
                $getsaveQty = DB::table('d_fixed_asset_po')->where('po_code',$array[$n]['po_code'])
                    ->where('produk',$array[$n]['id_produk'])->first();

                // if( $getsaveQty->save_qty != 0 ){
                //     //update
                //     DB::table('d_fixed_asset_po')
                //         ->where('po_code',$array[$n]['po_code'])
                //         ->where('produk',$array[$n]['id_produk'])
                //         ->update([
                //             'save_qty' => $getsaveQty->save_qty + $array[$n]['qty_received']
                //         ]);
                // }else{
                //     DB::table('d_fixed_asset_po')
                //         ->where('po_code',$array[$n]['po_code'])
                //         ->where('produk',$array[$n]['id_produk'])
                //         ->update([
                //             'save_qty' => $array[$n]['qty_received']
                //         ]);
                // }

            }
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }

        // if($success == true){
        return redirect('admin/asset/pd');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($sjcode,$status)
    {
        $dataSuratJalan = DB::table('d_fixed_asset_pd')
            ->select('*','m_satuan_unit.code as code_unit')
            ->join('t_fixed_asset_pd','t_fixed_asset_pd.sj_masuk_code','=','d_fixed_asset_pd.sj_masuk_code')
            ->join('m_produk','m_produk.id','=','d_fixed_asset_pd.produk_id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->where('d_fixed_asset_pd.sj_masuk_code',$sjcode)
            ->where('t_fixed_asset_pd.status',$status)
            ->get();

         $detailSuratJalan = DB::table('d_fixed_asset_pd')
            ->join('t_fixed_asset_pd','t_fixed_asset_pd.sj_masuk_code','=','d_fixed_asset_pd.sj_masuk_code')
            ->join('m_supplier','m_supplier.id','=','t_fixed_asset_pd.supplier')
            ->leftjoin('m_user as user_cancel','user_cancel.id','t_fixed_asset_pd.user_cancel')
            ->select('d_fixed_asset_pd.*','t_fixed_asset_pd.*','m_supplier.name as supplier','user_cancel.name as user_cancel')
            ->where('d_fixed_asset_pd.sj_masuk_code',$sjcode)
            ->where('t_fixed_asset_pd.status',$status)
            ->first();

        $gudang= DB::table('t_fixed_asset_pd')
        ->join('m_gudang','m_gudang.id','=','t_fixed_asset_pd.gudang')
        ->where('sj_masuk_code',$sjcode)
        ->first();

        // dd($dataSuratJalan);

        return view('admin.fixed-asset.pd.detail', compact('dataSuratJalan','detailSuratJalan','gudang'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function create()
     {
       $dataPo = DB::table('t_fixed_asset_po')
           ->join('d_fixed_asset_po','d_fixed_asset_po.po_code','=','t_fixed_asset_po.po_code')
           ->select('t_fixed_asset_po.po_code','t_fixed_asset_po.id')
           ->where('status_aprove','approved')
           ->whereRaw('d_fixed_asset_po.save_qty != 0')
           ->orderBy('t_fixed_asset_po.po_code','DESC')
           ->groupBy('t_fixed_asset_po.po_code','t_fixed_asset_po.id')
           ->get();

       $dataSupplier = DB::table('t_fixed_asset_po')
           ->join('d_fixed_asset_po','d_fixed_asset_po.po_code','=','t_fixed_asset_po.po_code')
           ->leftjoin('m_supplier','m_supplier.id','=','t_fixed_asset_po.supplier')
           ->select('t_fixed_asset_po.supplier','m_supplier.name')
           ->whereRaw('d_fixed_asset_po.qty != 0')
           ->where('status_aprove','approved')
           ->orderBy('t_fixed_asset_po.supplier','DESC')
           ->groupBy('t_fixed_asset_po.supplier','m_supplier.name')
           ->get();

       // dd($dataSupplier);

       // dd($dataPo);
           $gudang = DB::table('m_gudang')->get();
           $setSj = $this->setCodeSJ();
       //'dataPo',
       return view('admin.fixed-asset.pd.create', compact('dataPo','setSj','gudang','dataSupplier'));
     }

    public function edit($sjCode)
    {
        $gudang = DB::table('m_gudang')->get();

        $headerSJM = DB::table('t_fixed_asset_pd')
                    ->join('m_supplier','m_supplier.id','=','t_fixed_asset_pd.supplier')
                    ->join('m_user','m_user.id','=','t_fixed_asset_pd.user_input')
                    ->select('t_fixed_asset_pd.*','m_user.name as user_input','m_supplier.name as supplier')
                    ->where('t_fixed_asset_pd.sj_masuk_code',$sjCode)
                    ->first();

        $detailSJM      = DB::table('d_fixed_asset_pd')
                        ->join('m_produk','m_produk.id','d_fixed_asset_pd.produk_id')
                        ->join('d_fixed_asset_po','d_fixed_asset_po.id','d_fixed_asset_pd.dpo_id')
                        ->select('*','d_fixed_asset_pd.id as id','d_fixed_asset_pd.qty as qty_received','d_fixed_asset_pd.produk_id as id_produk',
                                 DB::raw('(d_fixed_asset_po.qty + d_fixed_asset_pd.qty)as maxdeliverqty'))
                        ->where('sj_masuk_code',$sjCode)
                        ->get();
        //add-ondex-max-deliverqty
        // foreach ($detailSJM as $sj) {
        //     $sj->maxdeliverqty = ($sj->save_qty == 0 ) ? $sj->qty : $sj->qty - ($sj->save_qty - $sj->save_qty);
        // }

        // dd($headerSJM,$detailSJM);
        return view('admin.fixed-asset.pd.update',compact('headerSJM','detailSJM','gudang'));
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

        // $i = 0;
        // foreach ($request->free_qty as $rawfreeqty) {
        //     $array[$i]['free_qty'] = $rawfreeqty;
        //     $i++;
        // }

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
        $detailSjLama = DB::table('t_fixed_asset_pd')
                        ->join('d_fixed_asset_pd','d_fixed_asset_pd.sj_masuk_code','=','t_fixed_asset_pd.sj_masuk_code')
                        ->where('t_fixed_asset_pd.sj_masuk_code', $request->sj_masuk_code)
                        ->select('*','d_fixed_asset_pd.qty as qty_received')
                        ->get();

        DB::beginTransaction();
        try {

             //update t_sj
             DB::table('t_fixed_asset_pd')->where('t_fixed_asset_pd.sj_masuk_code',$request->sj_masuk_code)
             ->where('t_fixed_asset_pd.po_code',$request->po_code)
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

                         $oldPo = DB::table('d_fixed_asset_po')->where('id',$detailPOId)->first();

                         $allSaveQtyWithoutMe = $oldPo->save_qty -  $sj->qty_received;

                         //  $data->qty - ($data->save_qty - $sj->qty_received)
                         DB::table('d_fixed_asset_po')->where('id',$detailPOId)->update([
                               'qty' => $oldPo->save_qty - $array[$x]['save_qty'],
                               'sj_qty' => $oldPo->sj_qty - $oldPo->sj_qty + $array[$x]['save_qty'],
                               //   'save_qty' => $saveQtyForUpdate,
                           ]);

                           //update d surat-jalan
                           DB::table('d_fixed_asset_pd')->where('sj_masuk_code',$sjCodeForUpdate)
                           ->where('produk_id',$idProdukForUpdate)->update([
                               'qty' => $saveQtyForUpdate,
                           ]);
                     }
                 } //endfor

                 //kondisi hapus
                 if( $cekIdSj == 0 ){
                    //  dd('hapus');
                     $oldPurchaseOrder = DB::table('d_fixed_asset_po')->where('id',$sj->dpo_id)->first();

                     $oldDSuratjalan = DB::table('d_fixed_asset_pd')->select('*','qty as qty_received')->where('id', '=', $sj->id)->first();

                     DB::table('d_fixed_asset_po')->where('id',$sj->dpo_id)->update([
                         'save_qty' => $oldPurchaseOrder->save_qty - $oldDSuratjalan->qty_received
                     ]);

                     //delete
                     DB::table('d_fixed_asset_pd')->where('id', '=', $sj->id)->delete();

                 }
             } //endforeach
             DB::commit();
         } catch (\Exception $e) {

             DB::rollback();
             dd($e);
         }
         return redirect('admin/asset/pd');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$pd_code)
    {
        $asset = DB::table('t_fixed_asset_po')
        ->join('d_fixed_asset_po','d_fixed_asset_po.po_code','t_fixed_asset_po.po_code')
        ->join('t_fixed_asset_pd','t_fixed_asset_pd.po_code','t_fixed_asset_po.po_code')
        ->join('d_fixed_asset_pd','d_fixed_asset_pd.sj_masuk_code','t_fixed_asset_pd.sj_masuk_code')
        ->select('d_fixed_asset_po.*','d_fixed_asset_pd.qty as qty_received','d_fixed_asset_pd.produk_id as barang',
                 'd_fixed_asset_pd.dpo_id','d_fixed_asset_po.qty')
        ->where('d_fixed_asset_pd.sj_masuk_code',$pd_code)
        ->get();

        DB::beginTransaction();
        try{
            foreach($asset as $as){
                $qty_sebelum_hapus = DB::table('d_fixed_asset_pd')
                ->where('sj_masuk_code',$pd_code)
                ->where('produk_id',$as->barang)
                ->where('dpo_id',$as->dpo_id)
                ->first();
                // dd($qty_sebelum_hapus);

               $qty_sebelum = DB::table('d_fixed_asset_po')
               ->where('produk',$as->produk)
                ->where('po_code',$as->po_code)
                ->first();
                // dd($qty_sebelum);

                $qty_setelah_hapus = $qty_sebelum_hapus->qty + $as->qty;
                $data_setelah = $qty_sebelum->qty - $as->qty_received;

                DB::table('d_fixed_asset_po')
                ->where('produk',$as->produk)
                ->where('po_code',$as->po_code)
                // ->where('wo_id',$as->wo_id)
                ->update([
                    'qty' => $qty_setelah_hapus,
                    'sj_qty' => $data_setelah,
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

        $data = DB::table('t_fixed_asset_pd')->where('sj_masuk_code',$pd_code)->delete();
        if($data){
            DB::table('d_fixed_asset_pd')->where('sj_masuk_code',$pd_code)->delete();
        }
        return redirect('admin/asset/pd');
    }

    public function getPOBySupplier($supplier)
    {
        $dataPo = DB::table('t_fixed_asset_po')
            ->leftjoin('d_fixed_asset_po','d_fixed_asset_po.po_code','=','t_fixed_asset_po.po_code')
            ->select('t_fixed_asset_po.po_code','t_fixed_asset_po.id')
            ->whereRaw('d_fixed_asset_po.qty != 0')
            ->where('status_aprove','approved')
            ->where('t_fixed_asset_po.supplier',$supplier)
            ->orderBy('t_fixed_asset_po.po_code','DESC')
            ->groupBy('t_fixed_asset_po.po_code','t_fixed_asset_po.id')
            ->get();
            // dd($dataPo);

        return Response::json($dataPo);
    }

    public function getProdukPO(Request $request)
    {        $result = DB::table('d_fixed_asset_po')
                ->join('m_produk','m_produk.id','=','d_fixed_asset_po.produk')
                ->join('t_fixed_asset_po','t_fixed_asset_po.po_code','=','d_fixed_asset_po.po_code')
                ->join('m_supplier','m_supplier.id','=','t_fixed_asset_po.supplier')
                ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
                ->select('m_produk.code','m_produk.id as produk_id','m_produk.name','m_produk.berat','d_fixed_asset_po.*'
                 ,DB::raw('(qty - sj_qty) as maxDeviverQty'),'m_satuan_unit.code as code_unit')
                ->whereRaw('d_fixed_asset_po.save_qty != 0')
                ->where('d_fixed_asset_po.po_code','=',$request->po_code)
                ->get();

            foreach ($result as $raw_so) {
                $cekSj = 0;

                $cekSj = DB::table('d_fixed_asset_pd')
                        ->join('t_fixed_asset_pd','t_fixed_asset_pd.sj_masuk_code','d_fixed_asset_pd.sj_masuk_code')
                        ->where('d_fixed_asset_pd.dpo_id','=',$raw_so->id)
                        ->where('t_fixed_asset_pd.status','!=','cancel')
                        ->get();

                if( count($cekSj) > 0 ){
                    $raw_so->free_qty = 0;
                }

                $raw_so->cek = $cekSj;
            }


        return Response::json($result);
    }

    public function posting($id)
    {
        //definition variable
        $dataSuratJalan = DB::table('t_fixed_asset_pd')
            ->join('d_fixed_asset_pd','d_fixed_asset_pd.sj_masuk_code','=','t_fixed_asset_pd.sj_masuk_code')
            ->where('t_fixed_asset_pd.id',$id)
            ->get();
        // dd($dataSuratJalan);
        $totalqtysj = DB::table('t_fixed_asset_pd')
            ->join('d_fixed_asset_pd','d_fixed_asset_pd.sj_masuk_code','=','t_fixed_asset_pd.sj_masuk_code')
            ->where('t_fixed_asset_pd.id',$id)
            ->sum('qty');

        //get-t-so
        $dataPOfromSJ = DB::table('t_fixed_asset_po')
            ->join('m_supplier','m_supplier.id','=','t_fixed_asset_po.supplier')
            ->select('t_fixed_asset_po.*','m_supplier.top')
            ->where('po_code',$dataSuratJalan[0]->po_code)
            ->first();
        $jatuh_tempo = $dataPOfromSJ->jatuh_tempo;
        //get diskon header po
        $totalheader = $dataPOfromSJ->grand_total;
        $totaldetail = DB::table('d_fixed_asset_po')
            ->where('po_code',$dataSuratJalan[0]->po_code)
            ->sum('total_neto');
        $qtybarang = DB::table('d_fixed_asset_po')
            ->where('po_code',$dataSuratJalan[0]->po_code)
            ->sum('save_qty');
        $diskonheader = $totaldetail - $totalheader;
        $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);
        //dd($diskonheader/$qtybarang);
        $total = 0;
        DB::beginTransaction();
        try{
            // update sales order sj_qty
            foreach ($dataSuratJalan as $value) {
                //get sj_qty
                $oldDSuratjalan = DB::table('d_fixed_asset_po')->where('id',$value->dpo_id)->first();

                // update d sales order sj_qty sudah ada waktu membuat PD
                // DB::table('d_fixed_asset_po')->where('id',$value->dpo_id)->update([
                //     'sj_qty' => $oldDSuratjalan->sj_qty + $value->qty,
                // ]);

                // //getdata d po
                $dataDPO = DB::table('d_fixed_asset_po')
                    ->where('po_code',$value->po_code)
                    ->where('produk',$value->produk_id)
                    ->first();

                //insert stok out
                // $getCodeProduk = DB::table('m_produk')->where('id',$value->produk_id)->first();

                // $jumlahStok = DB::table('m_stok_produk')->where('produk_code',$getCodeProduk->code)
                //     ->where('gudang',$dataSuratJalan[0]->gudang)
                //     ->sum('stok');

                // //total qty and free qty
                // $inBarang = $value->qty + $value->free_qty;

                // $insertStokModel = new MStokProdukModel;
                // $insertStokModel->produk_code =  $getCodeProduk->code;
                // $insertStokModel->transaksi   =  $value->sj_masuk_code;
                // $insertStokModel->tipe_transaksi   = 'Purchase Delivery';
                // $insertStokModel->person   =  $dataSuratJalan[0]->supplier;
                // $insertStokModel->stok_awal   =  $jumlahStok;
                // $insertStokModel->gudang      =  $dataSuratJalan[0]->gudang;
                // $insertStokModel->stok        =  $inBarang;
                // $insertStokModel->type        =  'in';
                // $insertStokModel->save();

                //get harga per barang
                $hargabarang = $dataDPO->total_neto / $dataDPO->save_qty;
                $hargabarang = (int) round($hargabarang);

                //hitung total price per faktur
                $total = $total + ($value->qty * ($hargabarang - $diskonheaderperbarang));
            }
            //cek so close
            $detailSuratJalan = DB::table('d_fixed_asset_po')->where('po_code',$dataSuratJalan[0]->po_code)->get();
            $cekClose = 1 ;
            foreach ($detailSuratJalan as $key => $value) {
                if( $value->sj_qty < $value->qty ){
                    $cekClose = 0;
                }
            }
            if($cekClose == 1){
                DB::table('t_fixed_asset_po')->where('po_code',$dataSuratJalan[0]->po_code)->update([
                    'status_aprove' => 'closed'
                ]);
            }
            if ($totalqtysj == $qtybarang) {
                $total = $totalheader;
            }

            if ($dataPOfromSJ->jatuh_tempo == 0) {
                $jatuh_tempo = 0;
            }
            //update status
            DB::table('t_fixed_asset_pd')->where('t_fixed_asset_pd.id',$id)->update([
                'status' => 'post',
            ]);

            // -------------------------------------------------------------------------------------//
            $getCode = substr($dataSuratJalan['0']->sj_masuk_code,2,8);
            $pi_code = 'PIFA'.$getCode;

            // AUTO JURNAL
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

            $id_coa = DB::table('m_coa')
                ->where('code','2010101')
                ->first();

            // --------------------------------------------------------//

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

            // create purchase invoice
            DB::table('t_purchase_invoice')
                ->insert([
                    'pi_code' => $pi_code,
                    'sj_masuk_code' => $dataSuratJalan[0]->sj_masuk_code,
                    'po_code' => $dataSuratJalan[0]->po_code,
                    'supplier' => $dataSuratJalan[0]->supplier,
                    'type' => 'pifa',
                    'jatuh_tempo' => date('Y-m-d', strtotime(date('d-m-Y'). ' + '.$jatuh_tempo.' days')),
                    'total_sesuai_pd' => $total,
                    'total' => $total,
                ]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/asset/pd');
    }

    protected function setCodeSJ()
    {
        $dataDate = date("ym");

        $getLastCode = DB::table('t_fixed_asset_pd')
        ->select('id')
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";
        }else{$nol = null;}

        return $setSj = 'AD'.$dataDate.$nol.$getLastCode;
    }

    public function apiPd()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);

        $suratJalan = DB::table('t_fixed_asset_pd')
            ->join('m_supplier','m_supplier.id','=','t_fixed_asset_pd.supplier')
            ->select('t_fixed_asset_pd.*','m_supplier.name as supplier','m_supplier.id as supplier_id')
            ->orderBy('t_fixed_asset_pd.id', 'desc')
            ->get();

            foreach ($suratJalan as $dataPD) {
                $pd = true;
                $cekPd = DB::table('t_fixed_asset_pd')
                        ->where('sj_masuk_code',$dataPD->sj_masuk_code)
                        ->first();
                // dd($cekSj);
                if (count($cekPd) > 0 ) {
                    $pd = false; // jika ada false
                }
                $dataPD->pd = $pd;
            }


        $roleSuperAdmin = DB::table('m_role')
            ->where('name','Super Admin')
            ->first();

        $i=0;
        // dd($suratJalan);




        return Datatables::of($suratJalan)
        ->addColumn('action', function ($suratJalan) use ($i){
            if(  $suratJalan->status == 'in process'){
                if(auth()->user()->role == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    // '<a href="'. url('admin/asset/report-pd/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                    '<a href="'. url('admin/asset/pd-edit/'.$suratJalan->sj_masuk_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $suratJalan->sj_masuk_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/asset/pd-delete/'.$suratJalan->sj_masuk_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $suratJalan->sj_masuk_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/asset/pd-posting/'.$suratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $suratJalan->sj_masuk_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                }else {
                    if($suratJalan->print == false){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        // '<a href="  '. url('admin/asset/report-pd/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/pd-edit/'.$suratJalan->sj_masuk_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $suratJalan->sj_masuk_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/pd-delete/'.$suratJalan->sj_masuk_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $suratJalan->sj_masuk_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/pd-posting/'.$suratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $suratJalan->sj_masuk_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        // '<a href="'. url('admin/asset/pd-edit/'.$suratJalan->sj_masuk_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $suratJalan->sj_masuk_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/pd-delete/'.$suratJalan->sj_masuk_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $suratJalan->sj_masuk_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/pd-posting/'.$suratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $suratJalan->sj_masuk_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                }
            }else{
              if($suratJalan->status == 'cancel'){
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                '<td>'.
                '<a href="'. url('admin/asset/report-asset-pd/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'. $suratJalan->sj_masuk_code.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                '</td>'.
                '</tr>'.
                '</table>';
            }else{
              return '<table id="tabel-in-opsi">'.
              '<tr>'.
              '<td>'.
              // '<a href="'. url('admin/asset/pd-edit/'.$suratJalan->sj_masuk_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $suratJalan->sj_masuk_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
              '<a href="'. url('admin/asset/pd-cancel/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-danger" data-toggle="tooltip"  title="Cancel '. $suratJalan->sj_masuk_code  .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
              '<a href="'. url('admin/asset/report-asset-pd/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
              '</td>'.
              '</tr>'.
              '</table>';
            }
          }
        })
        ->editColumn('sj_masuk_code', function($suratJalan){
            $statuspd=($suratJalan->status == 'save') ? '-' : $suratJalan->sj_masuk_code;
            return '<a href="'. url('admin/asset/pd-detail/'.$suratJalan->sj_masuk_code.'/'.$suratJalan->status) .'">'. $statuspd .'</a> ';
        })
        ->editColumn('sj_masuk_date', function($suratJalan){
            return date('d-m-Y',strtotime($suratJalan->sj_masuk_date));
        })
        ->editColumn('status', function($suratJalan){
            if( $suratJalan->status == 'in process' ){
                return '<span class="label label-default">In process</span>';
            }
            elseif ($suratJalan->status == 'post'){
                return '<span class="label label-success">Post</span>';
            }
            elseif ($suratJalan->status == 'cancel'){
                return '<span class="label label-danger">Cancel</span>';
            }
        })
        ->addIndexColumn()
        ->rawColumns(['sj_masuk_code','action','status','sj_date'])
        ->make(true);
    }

    public function cancelPDpost(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            DB::table('t_fixed_asset_pd')
            ->where('sj_masuk_code',$request->pd_code)
            ->update([
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

        return redirect('admin/asset/pd');
    }

    public function cancelPD($pocode)
    {
        $dataPD = DB::table('t_fixed_asset_pd')->where('sj_masuk_code',$pocode)->first();
        $reason = DB::table('m_reason')->orderBy('id','DESC')->get();

        return view('admin.fixed-asset.pd.cancel',compact('dataPD','reason'));
    }

    public function laporanPd()
     {
       $dataSupplier = DB::table('m_supplier')
             ->join('t_fixed_asset_pd', 'm_supplier.id', '=', 't_fixed_asset_pd.supplier')
             ->select('m_supplier.id as supplier_id','name')
             ->groupBy('m_supplier.id')
             ->get();

       $dataBarang = DB::table('m_produk')
             ->rightjoin('d_fixed_asset_pd', 'd_fixed_asset_pd.produk_id', '=', 'm_produk.id')
             ->select('m_produk.id as barang_id','m_produk.name')
             ->groupBy('m_produk.id')
             ->get();

       return view('admin.fixed-asset.pd.laporan',compact('dataSupplier','dataBarang'));
     }

     public function getSupplierByPeriode($periode)
     {
         $tglmulai = substr($periode,0,10);
         $tglsampai = substr($periode,13,10);

         $dataSupplier = DB::table('m_supplier')
             ->join('t_fixed_asset_pd', 'm_supplier.id', '=', 't_fixed_asset_pd.supplier')
             ->select('m_supplier.id as supplier_id','name','main_address')
             // ->where('status','post')
             ->where('t_fixed_asset_pd.sj_masuk_date','>=',date('Y-m-d', strtotime($tglmulai)))
             ->where('t_fixed_asset_pd.sj_masuk_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
             ->groupBy('m_supplier.id')
             ->get();

         return Response::json($dataSupplier);
     }

     public function getBarangBySupplier($supplier)
     {
         if ($supplier == '0') {
             $dataBarang = DB::table('m_produk')
                 ->rightjoin('d_fixed_asset_pd', 'd_fixed_asset_pd.produk_id', '=', 'm_produk.id')
                 ->select('m_produk.id as barang_id','m_produk.name')
                 ->groupBy('m_produk.id')
                 ->get();
         }else{
             $dataBarang = DB::table('m_produk')
                 ->rightjoin('d_fixed_asset_pd', 'd_fixed_asset_pd.produk_id', '=', 'm_produk.id')
                 ->join('t_fixed_asset_pd', 'd_fixed_asset_pd.sj_masuk_code', '=', 't_fixed_asset_pd.sj_masuk_code')
                 ->select('m_produk.id as barang_id','m_produk.name')
                 ->where('supplier',$supplier)
                 ->groupBy('m_produk.id')
                 ->get();
         }

         return Response::json($dataBarang);
     }

     public function getSJByPo($idSo)
     {
         $dataSJ = DB::table('t_fixed_asset_pd')
             ->where('po_code',$idSo)
             ->get();

         return Response::json($dataSJ);
     }

     public function getBarangBySj($sjId)
     {
         if ($sjId == '0') {
             $dataBarang = DB::table('m_produk')
                 ->select('id as barang_id','name')
                 ->groupBy('id')
                 ->get();
         }else{
             $dataBarang = DB::table('m_produk')
                 ->rightjoin('d_fixed_asset_pd', 'd_fixed_asset_pd.produk_id', '=', 'm_produk.id')
                 ->select('m_produk.id as barang_id','m_produk.name')
                 ->where('d_fixed_asset_pd.sj_masuk_code',$sjId)
                 ->groupBy('m_produk.id')
                 ->get();
         }
         //$dataBarang = 0;

         return Response::json($dataBarang);
     }


}
