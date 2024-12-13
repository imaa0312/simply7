<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TSalesOrderModel;
use App\Models\TSuratJalanModel;
use App\Models\DSuratJalanModel;
use App\Models\MStokProdukModel;
use App\Models\MReasonModel;
use Yajra\Datatables\Datatables;



class TSuratJalanController extends Controller
{
    public function index()
    {
        $dataSuratJalan = TSuratJalanModel::join('m_customer','m_customer.id','=','t_surat_jalan.customer')
        ->leftjoin('m_user','m_user.id','=','t_surat_jalan.sales')
        ->select('t_surat_jalan.*','m_customer.name as customer','m_user.name as sales','m_customer.id as customer_id','m_user.id as sales_id')
        ->orderBy('t_surat_jalan.id', 'desc')
        ->get();

        foreach ($dataSuratJalan as $dataSJ) {
            $faktur = false;
            $cekFaktur = DB::table('t_faktur')->where('sj_code',$dataSJ->sj_code)
            ->where('jumlah_yg_dibayarkan','=',0)
            ->where('status_payment','unpaid')
            ->get();
            if (count($cekFaktur) > 0 ) {
                $faktur = true;
            }
            $dataSJ->faktur = $faktur;
        }
        // dd($dataSuratJalan);
        return view('admin.transaksi.surat-jalan.index', compact('dataSuratJalan'));
    }

    public function create()
    {
        $dataSo = TSalesOrderModel::join('d_sales_order','d_sales_order.so_code','=','t_sales_order.so_code')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->select('t_sales_order.so_code','t_sales_order.id','m_customer.name as customer','t_sales_order.sending_date')
        ->whereRaw('d_sales_order.qty != d_sales_order.save_qty')
        ->where('status_aprove','approved')
        ->orderBy('t_sales_order.so_code','DESC')
        ->groupBy('t_sales_order.so_code','t_sales_order.id','m_customer.name','t_sales_order.sending_date')
        ->get();


        // dd($dataSo);
        $setSj = $this->setCodeSJ();

        return view('admin.transaksi.surat-jalan.create', compact('dataSo','setSj'));
    }


    public function getProdukFromSOcode($idSo)
    {
        $result = DB::table('t_sales_order')
        ->join('d_sales_order','d_sales_order.so_code','=','t_sales_order.so_code')
        ->join('m_produk','m_produk.id','=','d_sales_order.produk')
        ->where('t_sales_order.id','=',$idSo)
        ->whereRaw('d_sales_order.qty != d_sales_order.save_qty')
        ->get();
        return Response::json($result);
    }

    public function dataSo($codeSo)
    {
        $result = DB::table('t_sales_order')
        ->join('m_customer','m_customer.id','=','t_sales_order.customer')
        ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
        ->leftjoin('m_user','m_user.id','=','t_sales_order.sales')
        ->select('t_sales_order.*','m_customer.name as customer','m_user.name as sales','m_customer.id as customer_id','m_user.id as sales_id','m_wilayah_sales.name as wilayah')
        ->where('t_sales_order.so_code','=',$codeSo)
        ->first();

        return Response::json($result);
    }

    public function getProdukSO(Request $request)
    {
        // $produk = $request->produk;
        // $id_product = $request->id;

        $result = DB::table('d_sales_order')
        // ->join('d_sales_order','d_sales_order.so_code','=','t_sales_order.so_code')
        ->join('m_produk','m_produk.id','=','d_sales_order.produk')
        ->join('t_sales_order','t_sales_order.so_code','=','d_sales_order.so_code')
        ->join('m_customer','m_customer.id','=','t_sales_order.customer')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.code','m_produk.id as produk_id','m_produk.name','m_produk.berat','d_sales_order.*',DB::raw('(qty - save_qty) as maxdeviverqty'  ),'t_sales_order.gudang','m_satuan_unit.code as code_unit')
        // ->select('m_produk.code','m_produk.id as produk_id','m_produk.name','m_produk.berat','d_sales_order.*',DB::raw('(qty - save_qty) as maxDeviverQty'  ),'m_customer.gudang','m_produk.satuan_kemasan')
        // ->where('m_produk.id','=',$request->id)
        ->where('d_sales_order.so_code','=',$request->so_code)
        ->get();
        // $cekSuratJalan = false;
        foreach ($result as $raw_so) {
            $cekSj = 0;
            $stok = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $raw_so->code)
            ->where('m_stok_produk.gudang', $raw_so->gudang)
            ->groupBy('m_stok_produk.produk_code')
            ->sum('stok');

            $cekSj = DB::table('d_surat_jalan')
            ->join('t_surat_jalan','t_surat_jalan.sj_code','d_surat_jalan.sj_code')
            ->where('t_surat_jalan.so_code','=',$raw_so->so_code)
            ->where('t_surat_jalan.status','!=','cancel')
            ->where('d_surat_jalan.produk_id',$raw_so->produk_id)
            ->get();
            if( count($cekSj) > 0 ){
                $raw_so->free_qty = 0;
            }
            $raw_so->stok = $stok;
            $raw_so->cek = $cekSj;
            // $raw_so->jmlah = $cekSj;

        }

        return Response::json($result);
    }

    public function store(Request $request)
    {
        $array = [];
        $i = 0;
        $success = null;
        $produk_code = $request->produk_code;
        $produk_id = $request->produk_id;
        $deliver = $request->deliver;
        $customer_price = $request->customer_price;

        $setSj = $this->setCodeSJ();
        $sending_date = date('Y-m-d', strtotime($request->alternative_sending_date));

        //arrayProdukID
        foreach($produk_id as $raw_produk_id){
            $array[$i]['id_produk'] = $raw_produk_id;
            $array[$i]['so_code'] = $request->so_code;
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
            $array[$i]['qty_delivery'] = $raw_deliver;

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
        $i=0;
        foreach($customer_price as $raw_customer_price){
            $array[$i]['customer_price'] = $raw_customer_price;

            // ($i <= count($raw_customer_price)) ? $i++ : $i = 0;
            $i++;
        }
        $i=0;
        //
        // echo "<pre>";
        //     print_r($array);
        // echo "</pre>";
        // dd($request->all());
        // die();
        $company_code = DB::table("t_sales_order")->select("company_code")->where("so_code",$request->so_code)->first();
        DB::beginTransaction();
        try{
            //insert to t surat-jalan
            $store = new TSuratJalanModel;
            // $store->sj_code = $request->sj_code;
            $store->sj_code = $setSj;
            $store->company_code = $company_code->company_code;
            $store->so_code = $request->so_code;
            $store->customer = $request->customer;
            $store->sales = $request->sales;
            $store->driver_name = $request->driver_name;
            $store->license_plate = $request->license_plate;
            $store->alternative_sending_date = $sending_date;
            $store->name_car = $request->name_car;
            $store->description = $request->description;
            $store->gudang = $request->gudang;
            $store->cod = $request->cod;
            $store->user_input = auth()->user()->id;
            $store->save();

            //insert detail surat-jalan
            for($x=0; $x<count($array); $x++){
                DSuratJalanModel::insert([
                    'sj_code' => $store->sj_code,
                    'produk_id' => $array[$x]['id_produk'],
                    'qty_delivery' => $array[$x]['qty_delivery'],
                    'free_qty' => $array[$x]['free_qty'],
                    'customer_price' => $array[$x]['customer_price'],
                ]);
            }

            // update d sales order
            for($n=0; $n<count($array); $n++){
                //select
                $getsaveQty = DB::table('d_sales_order')->where('so_code',$array[$n]['so_code'])
                ->where('produk',$array[$n]['id_produk'])->first();
                if( $getsaveQty->save_qty != 0 ){
                    //update
                    DB::table('d_sales_order')->where('so_code',$array[$n]['so_code'])->where('produk',$array[$n]['id_produk'])
                    ->update([
                        'save_qty' => $getsaveQty->save_qty + $array[$n]['qty_delivery'],
                    ]);
                }else{
                    DB::table('d_sales_order')->where('so_code',$array[$n]['so_code'])->where('produk',$array[$n]['id_produk'])
                    ->update([
                        'save_qty' => $array[$n]['qty_delivery'],
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
        return redirect('admin/transaksi-surat-jalan');
        // }else{
        // 	return redirect()->back()->with('message', 'Code Order Sudah Ada Atau Produk Belum Terisi, Coba Lagi');
        // }
    }

    public function show($sjcode,$status)
    {
        $dataSuratJalan = DB::table('d_surat_jalan')
            ->join('t_surat_jalan','t_surat_jalan.sj_code','=','d_surat_jalan.sj_code')
            ->join('m_produk','m_produk.id','=','d_surat_jalan.produk_id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
            ->select('*','m_produk.code as produk_code','m_satuan_unit.code as code_unit')
            ->where('d_surat_jalan.sj_code',$sjcode)
            ->where('t_surat_jalan.status',$status)
            ->get();

        $detailSuratJalan = DB::table('d_surat_jalan')
            ->join('t_surat_jalan','t_surat_jalan.sj_code','=','d_surat_jalan.sj_code')
            ->join('m_customer','m_customer.id','=','t_surat_jalan.customer')
            ->leftjoin('m_user as user_cancel','user_cancel.id','t_surat_jalan.user_cancel')
            ->leftjoin('m_user','m_user.id','=','t_surat_jalan.sales')
            ->select('d_surat_jalan.*','t_surat_jalan.*','m_customer.name as customer','m_user.name as sales','user_cancel.name as user_cancel')
            ->where('d_surat_jalan.sj_code',$sjcode)
            ->where('t_surat_jalan.status',$status)
            ->first();

        // dd($detailSuratJalan);
        return view('admin.transaksi.surat-jalan.detail', compact('dataSuratJalan','detailSuratJalan'));
    }

    public function posting($id)
    {
        //definition variable

        $dataSuratJalan = DB::table('t_surat_jalan')
        	->select("t_surat_jalan.*",'d_surat_jalan.*','t_surat_jalan.company_code as header_faktur')
            ->join('d_surat_jalan','d_surat_jalan.sj_code','=','t_surat_jalan.sj_code')
            ->where('t_surat_jalan.id',$id)
            ->get();

        $totalqtysj = DB::table('t_surat_jalan')
            ->join('d_surat_jalan','d_surat_jalan.sj_code','=','t_surat_jalan.sj_code')
            ->where('t_surat_jalan.id',$id)
            ->sum('qty_delivery');

        //get-t-so
        $dataSOfromSJ = DB::table('t_sales_order')
            ->join('m_customer','m_customer.id','=','t_sales_order.customer')
            ->select('t_sales_order.*','m_customer.credit_limit_days')
            ->where('so_code',$dataSuratJalan[0]->so_code)
            ->first();
        //get top_hari and top_toleransi from so

        ($dataSOfromSJ->top_hari != null ) ? $top_hari = $dataSOfromSJ->top_hari : $top_hari = $dataSOfromSJ->credit_limit_days;
        ($dataSOfromSJ->top_toleransi != null ) ? $top_toleransi = $dataSOfromSJ->top_toleransi : $top_toleransi = 0;

        //$jatuh_tempo = $top_hari + $top_toleransi;
        $jatuh_tempo = $top_hari;

        // dd($dataSuratJalan,$dataSOfromSJ,$jatuh_tempo);

        //get diskon header so
        $totalheader = $dataSOfromSJ->grand_total;

        $totaldetail = DB::table('d_sales_order')
            ->where('so_code',$dataSuratJalan[0]->so_code)
            ->sum('total');

        $qtybarang = DB::table('d_sales_order')
            ->where('so_code',$dataSuratJalan[0]->so_code)
            ->sum('qty');

        $diskonheader = $totaldetail - $totalheader;
        $diskonheaderperbarang = (int)round($diskonheader / $qtybarang);

        $ppn = $dataSOfromSJ->amount_ppn;
        $ppn_per_barang = (int)round($ppn / $qtybarang);

        //dd($diskonheaderperbarang);
        //dd($dataSuratJalan);
        //dd($dataSOfromSJ);
        $total = 0;
        $amount_ppn = 0;
        DB::beginTransaction();
        try{
            // update sales order sj_qty
            foreach ($dataSuratJalan as $key => $value) {
                //get sj_qty
                $oldDSuratjalan = DB::table('d_sales_order')
                    ->where('so_code',$value->so_code)
                    ->where('produk',$value->produk_id)->first();

                //update d sales order sj_qty
                DB::table('d_sales_order')->where('so_code',$value->so_code)
                ->where('produk',$value->produk_id)->update([
                    'sj_qty' => $oldDSuratjalan->sj_qty + $value->qty_delivery
                ]);

                //getdata d so
                $dataDSO = DB::table('d_sales_order')
                    ->where('so_code',$value->so_code)
                    ->where('produk',$value->produk_id)
                    ->first();

                //insert stok out
                $getCodeProduk = DB::table('m_produk')->where('id',$value->produk_id)->first();
                // $getGudangCustomer = DB::table('m_customer')->where('m_customer.id','=',$value->customer)
                // ->first();

                $jumlahStok = DB::table('m_stok_produk')->where('produk_code',$getCodeProduk->code)
                    ->where('gudang',$dataSOfromSJ->gudang)
                    ->sum('stok');

                //total qty and free qty
                $outBarang = $value->qty_delivery + $value->free_qty;
                // dd($outBarang);

                $insertStokModel = new MStokProdukModel;
                $insertStokModel->produk_code =  $getCodeProduk->code;
                $insertStokModel->produk_id =  $getCodeProduk->id;
                $insertStokModel->transaksi   =  $value->sj_code;
                $insertStokModel->tipe_transaksi   =  'Surat Jalan';
                $insertStokModel->person   =  $dataSuratJalan[0]->customer;
                $insertStokModel->stok_awal   =  $jumlahStok;
                $insertStokModel->gudang      =  $dataSOfromSJ->gudang;
                $insertStokModel->stok        =  -$outBarang;
                $insertStokModel->type        =  'out';
                $insertStokModel->save();

                //get harga per barang
                $hargabarang = $dataDSO->total / $dataDSO->qty;
                $hargabarang = (int) round($hargabarang);

                //dd($hargabarang);

                //hitung total price per faktur
                // $total = $total + ($value->qty_delivery * ($hargabarang - $diskonheaderperbarang));
                $total = $total + ($value->qty_delivery * ($hargabarang - $diskonheaderperbarang));
                $amount_ppn = $amount_ppn + ($value->qty_delivery * $ppn_per_barang);
            }
            //dd($total);
            //cek so close
            $detailSuratJalan = DB::table('d_sales_order')->where('so_code',$dataSuratJalan[0]->so_code)->get();
            $cekClose = 1 ;
            foreach ($detailSuratJalan as $key => $value) {
                if( $value->sj_qty < $value->qty ){
                    $cekClose = 0;
                }
            }

            if($cekClose == 1){
                DB::table('t_sales_order')
                    ->where('so_code',$dataSuratJalan[0]->so_code)
                    ->update([
                        'status_aprove' => 'closed'
                    ]);
            }

            if ($totalqtysj == $qtybarang) {
                $total = $totalheader;
                $amount_ppn = $dataSOfromSJ->amount_ppn;
            }

            if ($dataSOfromSJ->top_hari == 0) {
                $jatuh_tempo = 0;
            }

            $getCode = substr($dataSuratJalan['0']->sj_code,2,10);
            //dd($getCode);
            $faktur_code = 'SI'.$getCode;

            //create faktur
            DB::table('t_faktur')
                ->insert([
                    'faktur_code' => $faktur_code,
                    'company_code' => $dataSuratJalan[0]->header_faktur,
                    'sj_code' => $dataSuratJalan[0]->sj_code,
                    'so_code' => $dataSuratJalan[0]->so_code,
                    'customer' => $dataSuratJalan[0]->customer,
                    'sales' => $dataSuratJalan[0]->sales,
                    'jatuh_tempo' => date('Y-m-d', strtotime(date('d-m-Y'). ' + '.$jatuh_tempo.' days')),
                    'amount_ppn' => $amount_ppn,
                    'total_sesuai_sj' => $total,
                    'total' => $total,
            ]);

            //update status
            DB::table('t_surat_jalan')->where('t_surat_jalan.id',$id)->update([
                'status' => 'post',
            ]);

            //AUTO JURNAL
            $id_gl = DB::table('t_general_ledger')
                ->insertGetId([
                    'general_ledger_date' => date('Y-m-d'),
                    'general_ledger_periode' => date('Ym'),
                    'general_ledger_keterangan' => 'SJ|SI No.'.$dataSuratJalan[0]->sj_code,
                    'general_ledger_status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','601')
                ->first();

            //dd($total);

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 1,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'debet',
                    'total' => $total,
                    'ref' => $faktur_code,
                    'type_transaksi' => 'SJ',
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
                    'total' => $total,
                    'ref' => $faktur_code,
                    'type_transaksi' => 'SJ',
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
                    'sequence' => 3,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'debet',
                    'total' => $total,
                    'ref' => $faktur_code,
                    'type_transaksi' => 'SI',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','40101')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 4,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'credit',
                    'total' => ($total-$amount_ppn),
                    'ref' => $faktur_code,
                    'type_transaksi' => 'SI',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','7150301')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 5,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'credit',
                    'total' => $amount_ppn,
                    'ref' => $faktur_code,
                    'type_transaksi' => 'SI',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi-surat-jalan');
    }


    public function posted()
    {
        $dataSuratJalan = TSuratJalanModel::where('status','post')->get();
        return view('admin.transaksi.surat-jalan.posting-index', compact('dataSuratJalan'));
    }

    //old
    public function edit($sjCode)
    {
        $detailSJ = DB::table('t_surat_jalan')
        ->join('m_customer','m_customer.id','=','t_surat_jalan.customer')
        ->leftjoin('m_user','m_user.id','=','t_surat_jalan.sales')
        ->select('t_surat_jalan.*','m_user.name as sales','m_user.id as sales_id','m_customer.name as customer')
        ->where('t_surat_jalan.sj_code',$sjCode)
        ->first();
        // dd($detailSJ);
        $dataSuratJalan = DB::table('d_surat_jalan')
        ->join('t_surat_jalan','t_surat_jalan.sj_code','=','d_surat_jalan.sj_code')
        ->join('m_produk','m_produk.id','=','d_surat_jalan.produk_id')
        ->select('d_surat_jalan.*','m_produk.id as id_produk','m_produk.code','m_produk.name','t_surat_jalan.so_code')
        ->where('d_surat_jalan.sj_code',$sjCode)
        ->get();
        foreach ($dataSuratJalan as $sj) {
            $data = DB::table('d_sales_order')->where('so_code',$sj->so_code)
            ->where('produk',$sj->produk_id)->first();
            $sj->qty_so = $data->qty;
            $sj->save_qty = $data->save_qty;
            $sj->sj_qty = $data->sj_qty;
            $sj->maxdeviverqty = ($data->save_qty == 0 ) ? $data->qty : $data->qty - ($data->save_qty - $sj->qty_delivery) ;
        }
        // dd($dataSuratJalan);
        return view('admin.transaksi.surat-jalan.update-new',compact('detailSJ','dataSuratJalan'));
    }

    //old
    public function update(Request $request)
    {
        $array = [];
        $i = 0;
        //ambil data baru dimasukkan array
        foreach ($request->id_produk as $raw_produk_id) {
            $array[$i]['id_produk'] = $raw_produk_id;
            $array[$i]['so_code'] = $request->so_code;
            $array[$i]['sj_code'] = $request->sj_code;
            //    ($i <= count($raw_produk_id)) ? $i++ : $i = 0;
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
            //    ($i <= count($rawsaveqty)) ? $i++ : $i = 0;
            $i++;
        }

        //ambil data lama
        $detailSjLama = DB::table('t_surat_jalan')
        ->join('d_surat_jalan','d_surat_jalan.sj_code','=','t_surat_jalan.sj_code')
        ->where('t_surat_jalan.sj_code', $request->sj_code)
        ->select('*')
        ->get();
        // echo "<pre>";
        //    print_r($array);
        // dd($detailSjLama);
        // die();
        DB::beginTransaction();
        try {

            //update t_sj
            DB::table('t_surat_jalan')->where('t_surat_jalan.sj_code',$request->sj_code)
            ->where('t_surat_jalan.so_code',$request->so_code)
            ->update([
                'driver_name' => $request->driver_name,
                'name_car' => $request->name_car,
                'license_plate' => $request->license_plate,
                'description' => $request->description,
            ]);

            foreach( $detailSjLama as $key => $sj ){

                $cekIdSj = 0; //flag id

                for ($x=0; $x <count($array) ; $x++) {

                    //cekid detailSjLama dan detailSjBaru (yang dilempar dari view)
                    if ( $sj->id == $array[$x]['id_sj'] ){
                        $cekIdSj = 1;
                        $soCodeForUpdate = $array[$x]['so_code'];
                        $sjCodeForUpdate = $array[$x]['sj_code'];
                        $idProdukForUpdate = $array[$x]['id_produk'];
                        $saveQtyForUpdate = $array[$x]['save_qty'];

                        // echo $saveQtyForUpdate."<br>";
                        // echo $x."<br>";
                        // echo count($array);
                        // die();

                        $oldSo = DB::table('d_sales_order')
                        ->where('so_code',$soCodeForUpdate)
                        ->where('produk','=',$idProdukForUpdate)->first();

                        $allSaveQtyWithoutMe = $oldSo->save_qty -  $sj->qty_delivery;

                        //  $data->qty - ($data->save_qty - $sj->qty_delivery)
                        DB::table('d_sales_order')->where('so_code',$soCodeForUpdate)
                        ->where('produk',$idProdukForUpdate)->update([
                            'save_qty' => $oldSo->qty - ( ($oldSo->qty - $allSaveQtyWithoutMe) -  $saveQtyForUpdate ),
                            //   'save_qty' => $saveQtyForUpdate,
                        ]);

                        //update d surat-jalan
                        DB::table('d_surat_jalan')->where('sj_code',$sjCodeForUpdate)
                        ->where('produk_id',$idProdukForUpdate)->update([
                            'qty_delivery' => $saveQtyForUpdate,
                        ]);
                    }
                    //$saveQtyForDelete = $array[$x]['save_qty'];
                } //endfor

                //kondisi hapus
                if( $cekIdSj == 0 ){

                    $oldSalesOrder = DB::table('d_sales_order')->where('so_code',$sj->so_code)
                    ->where('produk',$sj->produk_id)->first();

                    $oldDSuratjalan = DB::table('d_surat_jalan')->where('id', '=', $sj->id)->first();

                    // echo "<pre>";
                    //    print_r($array);
                    // die();
                    //update d sales order sj_qty
                    DB::table('d_sales_order')->where('so_code',$sj->so_code)
                    ->where('produk',$sj->produk_id)->update([
                        'save_qty' => $oldSalesOrder->save_qty - $oldDSuratjalan->qty_delivery
                    ]);

                    //delete
                    DB::table('d_surat_jalan')->where('id', '=', $sj->id)->delete();

                }
            } //endforeach
            DB::commit();
        } catch (\Exception $e) {

            DB::rollback();
            dd($e);
        }
        return redirect('admin/transaksi-surat-jalan');
    }

    // public function edit($sjCode)
    // {
    //     //data-sj-yang-di-copy
    //     $dataSJ = TSuratJalanModel::join('m_customer','m_customer.id','=','t_surat_jalan.customer')
    //             ->join('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
    //             ->join('m_user as sales','sales.id','=','t_surat_jalan.sales')
    //             ->join('m_user as user_input','user_input.id','=','t_surat_jalan.user_input')
    //             ->select('t_surat_jalan.*','m_customer.name as customer','m_customer.id as customer_id','m_wilayah_sales.name as wilayah',
    //             'sales.name as sales','sales.id as sales_id','user_input.name as user_input')
    //             ->where('sj_code',$sjCode)
    //             ->first();
    //
    //     //semua-data-so
    //     $dataSo = TSalesOrderModel::where('status_aprove','approved')->orderBy('so_code','DESC')->get();
    //
    //     //barang-so
    //     $barangSoFromSJCopy = DB::table('d_surat_jalan')
    //                     ->join('t_surat_jalan','t_surat_jalan.sj_code','=','d_surat_jalan.sj_code')
    //                     ->join('m_produk','m_produk.id','=','d_surat_jalan.produk_id')
    //                     ->select('m_produk.id as produk','m_produk.code','m_produk.name','m_produk.berat',
    //                     'm_produk.satuan_kemasan','m_produk.berat',
    //                     'd_surat_jalan.*','t_surat_jalan.gudang','t_surat_jalan.so_code')
    //                     ->where('d_surat_jalan.sj_code',$sjCode)
    //                     ->get();
    //                  //    t_surat_jalan.qty_so as maxDeviverQty'
    //
    //     foreach ($barangSoFromSJCopy as $raw_so) {
    //         $stok = DB::table('m_stok_produk')
    //                 ->where('m_stok_produk.produk_code', $raw_so->code)
    //                 ->where('m_stok_produk.gudang', $raw_so->gudang)
    //                 ->groupBy('m_stok_produk.produk_code')
    //                 ->sum('stok');
    //         $data = DB::table('d_sales_order')->where('so_code',$raw_so->so_code)
    //                         ->where('produk',$raw_so->produk)->first();
    //         $raw_so->stok = $stok;
    //         $raw_so->qty =  $data->qty;
    //         $raw_so->maxdeviverqty = ( $data->qty == $data->save_qty) ? $data->qty : $data->qty - $data->save_qty;
    //      //    DB::raw('(qty - save_qty) as maxDeviverQty'
    //     }
    //  //    dd($barangSoFromSJCopy);
    //
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
    //         $customer_price = $request->customer_price;
    //
    //         $setSj = $this->setCodeSJ();
    //          $sending_date = date('Y-m-d', strtotime($request->alternative_sending_date));
    //
    //          //arrayProdukID
    //          foreach($produk_id as $raw_produk_id){
    //              $array[$i]['id_produk'] = $raw_produk_id;
    //              $array[$i]['so_code'] = $request->so_code;
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
    //              $array[$i]['qty_delivery'] = $raw_deliver;
    //
    //              // ($i <= count($raw_deliver)) ? $i++ : $i = 0;
    //              $i++;
    //          }
    //          $i=0;
    //          foreach($customer_price as $raw_customer_price){
    //              $array[$i]['customer_price'] = $raw_customer_price;
    //
    //              // ($i <= count($raw_customer_price)) ? $i++ : $i = 0;
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
    //             $update = TSuratJalanModel::find($request->id);
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
    //             DSuratJalanModel::where('sj_code',$request->sj_code)->delete();
    //
    //
    //              //insert detail surat-jalan baru
    //             for($x=0; $x<count($array); $x++){
    //                 DSuratJalanModel::insert([
    //                     'sj_code' => $request->sj_code,
    //                     'produk_id' => $array[$x]['id_produk'],
    //                     'qty_delivery' => $array[$x]['qty_delivery'],
    //                     'customer_price' => $array[$x]['customer_price'],
    //                 ]);
    //             }
    //
    //             for($d=0; $d<count($array); $d++){
    //                 //select
    //                 $getsaveQty = DB::table('d_sales_order')->where('so_code',$array[$d]['so_code'])
    //                     ->where('produk',$array[$d]['id_produk'])->delete();
    //              }
    //
    //              // update d sales order
    //              for($n=0; $n<count($array); $n++){
    //                  //select
    //                  $getsaveQty = DB::table('d_sales_order')->where('so_code',$array[$n]['so_code'])
    //                      ->where('produk',$array[$n]['id_produk'])->first();
    //                  if( $getsaveQty->save_qty != 0 ){
    //                      //update
    //                      DB::table('d_sales_order')->where('so_code',$array[$n]['so_code'])->where('produk',$array[$n]['id_produk'])
    //                      ->update([
    //                          'save_qty' => $getsaveQty->save_qty + $array[$n]['qty_delivery'],
    //                      ]);
    //                  }else{
    //                      DB::table('d_sales_order')->where('so_code',$array[$n]['so_code'])->where('produk',$array[$n]['id_produk'])
    //                      ->update([
    //                          'save_qty' => $array[$n]['qty_delivery'],
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

    public function delete($sjcode)
    {
        //    dd($sjcode);
        $dataSJ = DB::table('t_surat_jalan')
        ->join('d_surat_jalan','d_surat_jalan.sj_code','=','t_surat_jalan.sj_code')
        ->where('t_surat_jalan.sj_code',$sjcode)
        ->get();
        // dd($dataSJ);
        foreach ($dataSJ as $key => $value) {
            //get sj_qty
            $oldDSuratjalan = DB::table('d_sales_order')->where('so_code',$value->so_code)
            ->where('produk',$value->produk_id)->first();

            //update d sales order sv_qty
            //   if( $oldDSuratjalan->save_qty != $oldDSuratjalan->qty ){
            DB::table('d_sales_order')->where('so_code',$value->so_code)
            ->where('produk',$value->produk_id)->update([
                'save_qty' => $oldDSuratjalan->save_qty - $value->qty_delivery
            ]);
            //   }
        }
        DB::table('t_surat_jalan')->where('sj_code', '=',$sjcode)->where('status','in process')->delete();
        DB::table('d_surat_jalan')->where('sj_code', '=', $sjcode)->delete();
        return redirect()->back();

    }

    public function laporanSJ()
    {
        $dataCustomer = DB::table('m_customer')
        ->join('t_surat_jalan', 'm_customer.id', '=', 't_surat_jalan.customer')
        ->select('m_customer.id as customer_id','name')
        ->groupBy('m_customer.id','m_customer.name')
        ->get();

        $dataBarang = DB::table('m_produk')
        ->rightjoin('d_surat_jalan', 'd_surat_jalan.produk_id', '=', 'm_produk.id')
        ->select('m_produk.id as barang_id','m_produk.name')
        ->groupBy('m_produk.id','m_produk.name')
        ->get();

        return view('admin.transaksi.surat-jalan.laporan',compact('dataCustomer','dataBarang'));
    }

    public function getCustomerByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataCustomer = DB::table('m_customer')
            ->join('t_surat_jalan', 'm_customer.id', '=', 't_surat_jalan.customer')
            ->select('m_customer.id as customer_id','name','main_address')
            ->where('t_surat_jalan.sj_date','>=',date('Y-m-d', strtotime($tglmulai)))
            ->where('t_surat_jalan.sj_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('m_customer.id')
            ->get();

        return Response::json($dataCustomer);
    }

    public function getSJByCustomer($customerID)
    {
        $dataSJ = DB::table('t_surat_jalan')
        ->where('customer',$customerID)
        ->get();

        return Response::json($dataSJ);
    }

    public function getSJBySo($idSo)
    {
        $dataSJ = DB::table('t_surat_jalan')
        ->where('so_code',$idSo)
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
            ->rightjoin('d_surat_jalan', 'd_surat_jalan.produk_id', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('d_surat_jalan.sj_code',$sjId)
            ->groupBy('m_produk.id')
            ->get();
        }
        //$dataBarang = 0;

        return Response::json($dataBarang);
    }

    public function getBarangByCustomer($customer)
    {
        if ($customer == '0') {
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_surat_jalan', 'd_surat_jalan.produk_id', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->groupBy('m_produk.id')
            ->get();
        }else{
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_surat_jalan', 'd_surat_jalan.produk_id', '=', 'm_produk.id')
            ->join('t_surat_jalan', 't_surat_jalan.sj_code', '=', 'd_surat_jalan.sj_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('t_surat_jalan.customer',$customer)
            ->groupBy('m_produk.id')
            ->get();
        }

        return Response::json($dataBarang);
    }

    public function cancelSJ($id)
    {
        $dataSj = TSuratJalanModel::findOrFail($id);
        $reason = MReasonModel::orderBy('id','DESC')->get();

        return view('admin.transaksi.surat-jalan.cancel',compact('dataSj','reason'));
    }

    public function cancelSJPost(Request $request)
    {
        $detailSJ = DSuratJalanModel::select('t_surat_jalan.so_code','t_surat_jalan.gudang','d_surat_jalan.*')
        ->join('t_surat_jalan','t_surat_jalan.sj_code','d_surat_jalan.sj_code')
        ->where('d_surat_jalan.sj_code',$request->sj_code)
        ->get();
        // dd($detailSJ);
        DB::beginTransaction();
        try{

            foreach ($detailSJ as $key => $value) {

                //get-produk-code
                $produkCode = DB::table('m_produk')->where('id',$value->produk_id)->first();

                //get-stok-awal-produk
                $jumlahStok = DB::table('m_stok_produk')->where('produk_code',$value->gudang)
                ->where('gudang',$value->gudang)
                ->sum('stok');

                //qty dan free_qty
                $inStok = $value->qty_delivery + $value->free_qty;
                //update-stok
                $insertStokModel = new MStokProdukModel;
                $insertStokModel->produk_code =  $produkCode->code;
                $insertStokModel->transaksi   =  $value->sj_code;
                $insertStokModel->tipe_transaksi   =  'Surat Jalan';
                $insertStokModel->stok_awal   =  $jumlahStok;
                $insertStokModel->gudang      =  $value->gudang;
                $insertStokModel->stok        =  $inStok;
                $insertStokModel->type        =  'in';
                $insertStokModel->save();

                //get-old-s0
                $oldSo = DB::table('d_sales_order')->where('so_code',$value->so_code)->first();

                //update-detail-so-mengurangi-sj_qty dan save_qty
                DB::table('d_sales_order')
                ->where('so_code',$value->so_code)
                ->where('produk',$value->produk_id)
                ->update([
                    'sj_qty' => $oldSo->sj_qty - $value->qty_delivery,
                    'save_qty' => $oldSo->save_qty - $value->qty_delivery,
                ]);
            }

            //hapus-t-faktur
            DB::table('t_faktur')->where('sj_code',$request->sj_code)->delete();

            //update-t-surat-jalan
            DB::table('t_surat_jalan')->where('sj_code',$request->sj_code)->update([
                'cancel_reason' => $request->cancel_reason,
                'cancel_description' => $request->cancel_description,
                'user_cancel' => auth()->user()->id,
                'status' => 'cancel',
            ]);

            //update-t-so
            DB::table('t_sales_order')->where('so_code',$detailSJ[0]->so_code)->update([
                'status_aprove' => 'approved'
            ]);


            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi-surat-jalan');
    }

    public function copySj($sjCode)
    {
        //data-sj-yang-di-copy
        $dataSJ = TSuratJalanModel::join('m_customer','m_customer.id','=','t_surat_jalan.customer')
        ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
        ->leftjoin('m_user as sales','sales.id','=','t_surat_jalan.sales')
        ->leftjoin('m_user as user_input','user_input.id','=','t_surat_jalan.user_input')
        ->select('t_surat_jalan.*','m_customer.name as customer','m_customer.id as customer_id','m_wilayah_sales.name as wilayah',
        'sales.name as sales','sales.id as sales_id','user_input.name as user_input')
        ->where('sj_code',$sjCode)
        ->first();

        //semua-data-so
        $dataSo = TSalesOrderModel::where('status_aprove','approved')->orderBy('so_code','DESC')->get();

        //barang-so
        $barangSoFromSJCopy = DB::table('d_sales_order')
        ->join('m_produk','m_produk.id','=','d_sales_order.produk')
        ->join('t_sales_order','t_sales_order.so_code','=','d_sales_order.so_code')
        ->join('m_customer','m_customer.id','=','t_sales_order.customer')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.code','m_produk.name','m_produk.berat','d_sales_order.*',DB::raw('(qty - save_qty) as maxDeviverQty'  ),'m_customer.gudang','m_satuan_unit.code as code_unit')
        ->where('d_sales_order.so_code','=',$dataSJ->so_code)
        ->get();

        foreach ($barangSoFromSJCopy as $raw_so) {
            $stok = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $raw_so->code)
            ->where('m_stok_produk.gudang', $raw_so->gudang)
            ->groupBy('m_stok_produk.produk_code')
            ->sum('stok');
            $raw_so->stok = $stok;
        }

        $setSj = $this->setCodeSJ();

        // dd($dataSJ,$barangSoFromSJCopy,$setSj);
        return view('admin.transaksi.surat-jalan.copy-sj',compact('dataSJ','barangSoFromSJCopy','dataSo','setSj'));
    }

    public function konfirmasiPengiriman()
    {
        $dataSJ = DB::table('t_surat_jalan')
                ->select('t_surat_jalan.*','m_customer.name as customer')
                ->join('m_customer','m_customer.id','=','t_surat_jalan.customer')
                ->join('t_sales_order','t_sales_order.so_code','=','t_surat_jalan.so_code')
                ->where('t_surat_jalan.status','=','post')
                ->where('t_sales_order.so_from','=','marketplace')
                ->orderBy('t_surat_jalan.id','desc')
                ->get();

        foreach ($dataSJ as $sj) {
            $konfirmasi = DB::table('m_konfirmasi_pengiriman')->where('sj_code',$sj->sj_code)->get();

            //dd($konfirmasi);

            if(count($konfirmasi) > 0){
                if($konfirmasi[0]->admin_confirmed_by != 0){
                    $sj->status_confirm = 1;
                }else{
                    $sj->status_confirm = 0;
                }
            }else{
                $sj->status_confirm = 0;
            }
        }

        return view('admin.transaksi.surat-jalan.konfirmasi.index', compact('dataSJ'));
    }

    public function createKonfirmasiPengiriman($sjcode)
    {

        return view('admin.transaksi.surat-jalan.konfirmasi.create', compact('sjcode'));
    }

     public function storeKonfirmasiPengiriman(Request $request)
    {
        $cek =  DB::table('m_konfirmasi_pengiriman')->where('sj_code',$request->sj_code)->get();
        DB::beginTransaction();
        try{
            if(count($cek) == 0){
                DB::table('m_konfirmasi_pengiriman')
                    ->insert([
                        'sj_code' => $request->sj_code,
                        'admin_confirmed_by' => auth()->user()->id,
                        'received_by' =>$request->penerima_pengiriman,
                        'received_date' => date('Y-m-d H:i:s',strtotime($request->penerimaan_date)),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
            }else{
                DB::table('m_konfirmasi_pengiriman')
                    ->where('sj_code', $request->sj_code)
                    ->update([
                        'admin_confirmed_by' =>auth()->user()->id,
                        'received_by' =>$request->penerima_pengiriman,
                        'received_date' =>date('Y-m-d H:i:s',strtotime($request->penerimaan_date)),
                    ]);
            }
            DB::commit();
            $detail_sj = DB::table('d_surat_jalan')
                        ->select('d_surat_jalan.*','m_produk.name')
                        ->join('m_produk','m_produk.id','d_surat_jalan.produk_id')
                        ->where('d_surat_jalan.sj_code',$request->sj_code)
                        ->get();

            $get_so = DB::table('t_surat_jalan')
                        ->select('t_surat_jalan.so_code')
                        ->where('t_surat_jalan.sj_code',$request->sj_code)
                        ->first();

            $cek_so = DB::table('t_sales_order')
                        ->select('t_sales_order.*','m_user.email','m_customer.name','m_biaya_kirim.nama_biaya_kirim')
                        ->join('m_customer','m_customer.id','=','t_sales_order.customer')
                        ->join('m_user','m_user.id','=','m_customer.id_user')
                        ->join('m_biaya_kirim','m_biaya_kirim.id','t_sales_order.metode_kirim')
                        ->where('t_sales_order.so_code', $get_so->so_code)
                        ->first();

            // Mail::to($cek_so->email)->send(new ConfirmReceivedMail($detail_sj,$cek_so,$request->penerima_pengiriman,$request->penerimaan_date));

        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi-konfirmasi-pengiriman');
    }

    protected function setCodeSJ()
    {
        $dataDate = date("ym");

        $getLastCode = DB::table('t_surat_jalan')
        ->select('id')
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";
        }else{$nol = null;}

        return $setSj = 'SJTK'.$dataDate.$nol.$getLastCode;
    }

    public function apiSj()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);

        $dataSuratJalan = TSuratJalanModel::join('m_customer','m_customer.id','=','t_surat_jalan.customer')
        ->leftjoin('m_user','m_user.id','=','t_surat_jalan.sales')
        ->select('t_surat_jalan.*','m_customer.name as customer','m_user.name as sales','m_customer.id as customer_id','m_user.id as sales_id')
        ->orderBy('t_surat_jalan.id', 'desc')
        ->get();

        foreach ($dataSuratJalan as $dataSJ) {
            $faktur = false;
            $cekFaktur = DB::table('t_faktur')->where('sj_code',$dataSJ->sj_code)
            ->where('jumlah_yg_dibayarkan','=',0)
            ->where('status_payment','unpaid')
            ->get();
            if (count($cekFaktur) > 0 ) {
                $faktur = true;
            }
            $dataSJ->faktur = $faktur;
        }

        $roleSuperAdmin = DB::table('m_role')
        ->where('name','Super Admin')
        ->first();
        $i=0;
        //dd(auth()->user()->role);
        return Datatables::of($dataSuratJalan)
        ->addColumn('action', function ($dataSuratJalan) use ($i){
            if(  $dataSuratJalan->status == 'in process'){
                if(auth()->user()->role == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    '<a href="'. url('admin/report-sj/'.$dataSuratJalan->sj_code.'/'.$dataSuratJalan->status) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                    '<a href="'.url('admin/surat-jalan/'.$dataSuratJalan->sj_code.'/update') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $dataSuratJalan->sj_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '<a href="'. url('/admin/transaksi-sj-delete/'.$dataSuratJalan->sj_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $dataSuratJalan->sj_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/surat-jalan/posting/'.$dataSuratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $dataSuratJalan->sj_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                }
                else {
                    if($dataSuratJalan->print == false){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        '<a href="  '. url('admin/report-sj/'.$dataSuratJalan->sj_code.'/'.$dataSuratJalan->status) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak" onclick="hide('.$i++.')" id="print_'.$i++.'" data-value="'.$dataSuratJalan->status.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                        '<a href="'.url('admin/surat-jalan/'.$dataSuratJalan->sj_code.'/update') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $dataSuratJalan->sj_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                        '<a href="'. url('/admin/transaksi-sj-delete/'.$dataSuratJalan->sj_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $dataSuratJalan->sj_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/surat-jalan/posting/'.$dataSuratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $dataSuratJalan->sj_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                    else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        '<a href="'.url('admin/surat-jalan/'.$dataSuratJalan->sj_code.'/update') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $dataSuratJalan->sj_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                        '<a href="'. url('/admin/transaksi-sj-delete/'.$dataSuratJalan->sj_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $dataSuratJalan->sj_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/surat-jalan/posting/'.$dataSuratJalan->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $dataSuratJalan->sj_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                }
            }elseif(  $dataSuratJalan->status == 'post' && $dataSuratJalan->status==true){
                if(auth()->user()->role == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    '<a href="'. url('admin/report-sj/'.$dataSuratJalan->sj_code.'/'.$dataSuratJalan->status) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp'.
                    '<a href="'. url('admin/surat-jalan/cancel/'.$dataSuratJalan->id) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $dataSuratJalan->sj_code .'"><span class="fa fa-times"></span></a>'.'&nbsp'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                }
                else{
                    if($dataSuratJalan->print == false){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        '<a href="'. url('admin/report-sj/'.$dataSuratJalan->sj_code.'/'.$dataSuratJalan->status) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp'.
                        '<a href="'. url('admin/surat-jalan/cancel/'.$dataSuratJalan->id) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $dataSuratJalan->sj_code .'"><span class="fa fa-times"></span></a>'.'&nbsp'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                    else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        '<a href="'. url('admin/surat-jalan/cancel/'.$dataSuratJalan->id) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $dataSuratJalan->sj_code .'"><span class="fa fa-times"></span></a>'.'&nbsp'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                }

            }elseif($dataSuratJalan->status == 'cancel'){
                if(auth()->user()->role == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    '<a href="'. url('admin/report-sj/'.$dataSuratJalan->sj_code.'/'.$dataSuratJalan->status) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp'.
                    '<a href="'. url('admin/surat-jalan/copy/'.$dataSuratJalan->sj_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                }
                else{
                    if($dataSuratJalan->print == false){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        '<a href="'. url('admin/report-sj/'.$dataSuratJalan->sj_code.'/'.$dataSuratJalan->status) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp'.
                        '<a href="'. url('admin/surat-jalan/copy/'.$dataSuratJalan->sj_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                    else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        '<a href="'. url('admin/surat-jalan/copy/'.$dataSuratJalan->sj_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                }

            }
        })
        ->editColumn('code', function($dataSuratJalan){
            $statussj=($dataSuratJalan->status == 'save') ? '-' : $dataSuratJalan->sj_code;
            return '<a href="'. url('admin/transaksi-surat-jalan/'.$dataSuratJalan->sj_code.'/'.$dataSuratJalan->status) .'">'. $statussj .'</a></td> ';
        })
        ->editColumn('sj_date', function($dataSuratJalan){
            return date('d-m-Y',strtotime($dataSuratJalan->sj_date));
        })
        ->editColumn('status', function($dataSuratJalan){
            if( $dataSuratJalan->status == 'in process' ){
                return '<span class="label label-default">in process</span>';}
                elseif ($dataSuratJalan->status == 'post'){
                    return '<span class="label label-success">post</span>';}

                    elseif ($dataSuratJalan->status == 'cancel'){
                        return '<span class="label label-danger">cancel</span>';}

                    })
                    ->addIndexColumn()
                    ->rawColumns(['code','action','status','sj_date'])
                    ->make(true);
                }
            }
