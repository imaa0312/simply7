<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;
use App\Models\MReasonModel;

class TPurchaseInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataSupplier = DB::table('m_supplier')
            ->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->select('m_supplier.id as supplier_id','name')
            ->where('t_purchase_invoice.status','unpaid')
            ->groupBy('m_supplier.id','m_supplier.name')
            ->get();

        $dataPI = DB::table('t_purchase_invoice')
            ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->where('t_purchase_invoice.type','pi')
            ->where('t_purchase_invoice.status', '!=', 'unpaid')
            ->select('t_purchase_invoice.po_code','t_purchase_invoice.pi_code','t_purchase_invoice.sj_masuk_code','t_purchase_invoice.jumlah_yg_dibayarkan','t_purchase_invoice.status','m_supplier.name as supplier','m_supplier.id as id_supplier','t_purchase_invoice.id as pi_id','t_purchase_invoice.total','t_purchase_invoice.print')
            ->orderBy('t_purchase_invoice.id','DESC')
            ->get();

        return view('admin.purchasing.purchase-invoice.index', compact('dataPI','dataSupplier'));
    }

    public function waiting()
    {
        $dataSupplier = DB::table('m_supplier')
            ->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->select('m_supplier.id as supplier_id','name')
            ->where('t_purchase_invoice.status','unpaid')
            ->groupBy('m_supplier.id','name')
            ->get();

        $dataPI = DB::table('t_purchase_invoice')
            ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->where('t_purchase_invoice.type','pi')
            ->where('t_purchase_invoice.status', '=', 'unpaid')
            ->select('t_purchase_invoice.po_code','t_purchase_invoice.pi_code','t_purchase_invoice.sj_masuk_code','t_purchase_invoice.jumlah_yg_dibayarkan','t_purchase_invoice.status','m_supplier.name as supplier','m_supplier.id as id_supplier','t_purchase_invoice.id as pi_id','t_purchase_invoice.total','t_purchase_invoice.print')
            ->orderBy('t_purchase_invoice.id','DESC')
            ->get();

        return view('admin.purchasing.purchase-invoice.waiting', compact('dataPI','dataSupplier'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function detail($pi_code)
    {
        $dataPI = DB::table('t_purchase_invoice')
            ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->select('*','t_purchase_invoice.status as status_payment')
            ->where('pi_code',$pi_code)
            ->first();

        $dataPembayaran = DB::table('d_pi_pembayaran')
            ->join('t_pi_pembayaran', 'd_pi_pembayaran.pembayaran_code', '=', 't_pi_pembayaran.pembayaran_code')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pi_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pi_pembayaran.bank')
            ->join('m_supplier', 'm_supplier.id', '=', 't_pi_pembayaran.supplier')
            ->select('*','t_pi_pembayaran.type as type_payment','m_bank.name as bank_name','t_pi_pembayaran.status as status_pembayaran')
            ->where('pi_code',$pi_code)
            ->orderBy('t_pi_pembayaran.pembayaran_code')
            ->get();

        return view('admin.purchasing.purchase-invoice.detail', compact('dataPI','dataPembayaran'));
    }

    public function listPembayaran()
    {
        $dataPembayaran = DB::table('t_pi_pembayaran')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pi_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pi_pembayaran.bank')
            ->join('m_supplier', 'm_supplier.id', '=', 't_pi_pembayaran.supplier')
            ->select('*','t_pi_pembayaran.type as type_payment','m_bank.name as bank_name','t_pi_pembayaran.status as status_pembayaran')
            ->where('t_pi_pembayaran.status','!=','in approval')
            ->orderBy('t_pi_pembayaran.id','desc')
            ->get();

        foreach ($dataPembayaran as $raw_data) {
            $total = DB::table('d_pi_pembayaran')
                ->where('pembayaran_code',$raw_data->pembayaran_code)
                ->sum('total');

            $raw_data->total = $total;
        }

        return view('admin.purchasing.pembayaran.index', compact('dataPembayaran'));
    }

    public function waitPembayaran()
    {
        $dataPembayaran = DB::table('t_pi_pembayaran')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pi_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pi_pembayaran.bank')
            ->join('m_supplier', 'm_supplier.id', '=', 't_pi_pembayaran.supplier')
            ->select('*','t_pi_pembayaran.type as type_payment','m_bank.name as bank_name','t_pi_pembayaran.status as status_pembayaran')
            ->where('t_pi_pembayaran.status','in approval')
            ->orderBy('t_pi_pembayaran.id','desc')
            ->get();

        foreach ($dataPembayaran as $raw_data) {
            $total = DB::table('d_pi_pembayaran')
                ->where('pembayaran_code',$raw_data->pembayaran_code)
                ->sum('total');

            $raw_data->total = $total;
        }

        return view('admin.purchasing.pembayaran.waiting', compact('dataPembayaran'));
    }

    public function detailPembayaran($pembayaran_code)
    {
        $detailPembayaran = DB::table('d_pi_pembayaran')
            ->where('pembayaran_code',$pembayaran_code)
            ->get();

        $dataPembayaran = DB::table('t_pi_pembayaran')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pi_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pi_pembayaran.bank')
            ->join('m_supplier', 'm_supplier.id', '=', 't_pi_pembayaran.supplier')
            ->select('*','t_pi_pembayaran.type as type_payment','m_bank.name as bank_name','t_pi_pembayaran.status as status_pembayaran')
            ->where('pembayaran_code',$pembayaran_code)
            ->first();

        $total = DB::table('d_pi_pembayaran')
            ->where('pembayaran_code',$pembayaran_code)
            ->sum('total');

        $dataPembayaran->totalpembayaran = $total;

        return view('admin.purchasing.pembayaran.detail', compact('dataPembayaran','detailPembayaran'));
    }

    public function pembayaran()
    {
        $dataSupplier = DB::table('t_purchase_invoice')
            ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->select('t_purchase_invoice.supplier','m_supplier.name as supplier','m_supplier.id as id_supplier')
            ->groupBy('t_purchase_invoice.supplier','m_supplier.name','m_supplier.id')
            ->where('t_purchase_invoice.status', '=', 'unpaid')
            ->get();

        foreach ($dataSupplier as $key => $raw_data) {
            $data =  DB::table('t_purchase_invoice')
                ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
                ->select('t_purchase_invoice.*','m_supplier.name as supplier','m_supplier.id as id_supplier')
                ->where('m_supplier.id',$raw_data->id_supplier)
                ->where('t_purchase_invoice.status', '=', 'unpaid')
                ->orderBy('t_purchase_invoice.pi_code', 'DESC')
                ->get();

            foreach ($data as $key2 => $raw_data2) {
                $waiting = DB::table('d_pi_pembayaran')
                    ->join('t_pi_pembayaran', 't_pi_pembayaran.pembayaran_code', '=', 'd_pi_pembayaran.pembayaran_code')
                    ->where('d_pi_pembayaran.pi_code',$raw_data2->pi_code)
                    ->where('t_pi_pembayaran.status','in approval')
                    ->sum('d_pi_pembayaran.total');

                $jumlah_dibayar = $raw_data2->jumlah_yg_dibayarkan + $waiting;

                if ($jumlah_dibayar >= $raw_data2->total) {
                    unset($data[$key2]);
                }
            }

            if (count($data) < 1) {
                unset($dataSupplier[$key]);
            }
        }

        $dataBank = DB::table('m_bank')->get();

        //get coa rek bank
        $interface = DB::table('m_interface')
            ->where('var','VAR_CASH')
            ->first();
        $codeCoa = explode(",", $interface->code_coa);

        if ($codeCoa[0]=='') {
            $codeCoa = [];
            $data = [];
        }else{
            $query = [];
            for ($i=0; $i < count($codeCoa); $i++) {
                $query[$i] = DB::table('m_coa');
                $query[$i]->select('id','code','desc');
                $query[$i]->where('code', 'like', $codeCoa[$i].'%');
                if ($i>0) {
                    $query[$i]->union($query[$i-1]);
                }
            }
            $query[count($codeCoa)-1]->orderBy('id');

            $data = $query[count($codeCoa)-1]->get();

            //cek code coa paling bawah
            $length = 0;
            foreach($data as $raw_data) {
                $lengthCode = strlen($raw_data->code);
                if ($lengthCode > $length) {
                    $length =$lengthCode;
                }
                $raw_data->test = $lengthCode;
            }

            //remove coa parent
            foreach ($data as $key => $raw_data) {
                $lengthCode = strlen($raw_data->code);
                if ($lengthCode < $length) {
                    unset($data[$key]);
                }
            }
        }

        $dataRekening = $data;

        $dataSelisih = DB::table('m_coa')
            ->where('code','90401')
            ->get();

        $dataMetode = DB::table('m_metode_pembayaran')->get();

        return view('admin.purchasing.pembayaran.create', compact('dataSupplier','dataBank','dataMetode','dataRekening','dataSelisih'));
    }

    public function getAllPI($id)
    {
        $data = DB::table('t_purchase_invoice')
            ->select('t_purchase_invoice.pi_code','t_purchase_invoice.po_code','t_purchase_invoice.sj_masuk_code','t_purchase_invoice.jatuh_tempo','jumlah_yg_dibayarkan','total')
            ->where('t_purchase_invoice.supplier',$id)
            ->where('t_purchase_invoice.status', '=', 'unpaid')
            ->groupBy('t_purchase_invoice.pi_code','jumlah_yg_dibayarkan','total','t_purchase_invoice.po_code','t_purchase_invoice.sj_masuk_code','t_purchase_invoice.jatuh_tempo')
            ->orderBy('t_purchase_invoice.pi_code', 'DESC')
            ->get();

        foreach ($data as $key => $raw_data) {
            $waiting = DB::table('d_pi_pembayaran')
                ->join('t_pi_pembayaran', 't_pi_pembayaran.pembayaran_code', '=', 'd_pi_pembayaran.pembayaran_code')
                ->where('d_pi_pembayaran.pi_code',$raw_data->pi_code)
                ->where('t_pi_pembayaran.status','in approval')
                ->sum('d_pi_pembayaran.total');

            $po_date = DB::table('t_purchase_order')
                ->select('po_date')
                ->where('po_code',$raw_data->po_code)
                ->orderBy('po_date', 'desc')
                ->pluck('po_date')
                ->first();

            $sudah_dibayar = $raw_data->jumlah_yg_dibayarkan + $waiting;
            $belum_dibayar = $raw_data->total - $raw_data->jumlah_yg_dibayarkan - $waiting;

            $raw_data->sudah_dibayar = $sudah_dibayar;
            $raw_data->belum_dibayar = $belum_dibayar;

            $raw_data->po_date = date('d-m-Y', strtotime($po_date));

            if ($sudah_dibayar >= $raw_data->total) {
                unset($data[$key]);
            }
        }

        return Response::json($data);
    }

    public function createPembayaran(request $request)
    {
        $this->validate($request, [
            'supplier' => 'required',
            'jumlah_yang_dibayar' => 'required',
            'type' => 'required',
        ]);

        //dd($request->all());

        $status = "";

        $pi = json_decode($request->use_pi);
        //dd($test[0]);

        if ($request->type == 2) {
            $this->validate($request, [
                'bank' => 'required',
                'rekening' => 'required',
            ]);
            $bank = $request->bank;
            $no_giro = null;
            $jatuh_tempo_giro = null;
            $tgl_ambil_giro = null;
            $rekening_tujuan = $request->rekening;
            $dp_code = null;
            //$status = "approved";
        }elseif ($request->type == 3){
             $this->validate($request, [
                'bank' => 'required',
                'no_giro' => 'required',
                'jatuh_tempo_giro' => 'required',
            ]);
            $bank = $request->bank;
            $no_giro = $request->no_giro;
            $tgl_ambil_giro = date('Y-m-d', strtotime($request->tgl_ambil_giro));
            $jatuh_tempo_giro = date('Y-m-d', strtotime($request->jatuh_tempo_giro));
            $rekening_tujuan = $request->rekening;
            $dp_code = null;
            //$status = "in approval";
        }
        elseif ($request->type == 4){
             $this->validate($request, [
                'dp' => 'required',
            ]);
            $bank = null;
            $no_giro = null;
            $jatuh_tempo_giro = null;
            $tgl_ambil_giro = null;
            $rekening_tujuan = null;
            $dp_code = $request->dp;
            //$status = "in approval";
        }
        else{
            $bank = null;
            $no_giro = null;
            $jatuh_tempo_giro = null;
            $tgl_ambil_giro = null;
            $rekening_tujuan = $request->rekening;
            $dp_code = null;
            //$status = "approved";
        }

        //dd($request->all());

        $dataDate =date("ym");

        $getLastCode = DB::table('t_pi_pembayaran')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){
            $nol = "000";
        }elseif(strlen($getLastCode) == 2){
            $nol = "00";
        }elseif(strlen($getLastCode) == 3){
            $nol = "0";
        }else{
            $nol = null;
        }

        $pembayaran_code = 'NPTK'.$dataDate.$nol.$getLastCode;

        // $sales = DB::table('m_customer')
        //     ->join('m_wilayah_pembagian_sales', 'm_wilayah_pembagian_sales.wilayah_sales', '=', 'm_customer.wilayah_sales')
        //     ->where('m_customer.id',$request->customer)
        //     ->pluck('m_wilayah_pembagian_sales.sales')
        //     ->first();

        //insert header
        $data =  DB::table('t_pi_pembayaran')
            ->insert([
                'pembayaran_code' => $pembayaran_code,
                'supplier' => $request->supplier,
                'type' => $request->type,
                'payment_date' => date('Y-m-d', strtotime($request->pembayaran_date)),
                'dp_code' => $dp_code,
                'keterangan' => $request->keterangan,
                'user_receive' => $request->user,
                'user_confirm' => $request->user,
                'bank' => $bank,
                'no_giro' => $no_giro,
                'rekening_tujuan' => $rekening_tujuan,
                'jatuh_tempo_giro' => $jatuh_tempo_giro,
                'tgl_ambil_giro' => $tgl_ambil_giro,
                'confirm_date' => date("Y-m-d"),
            ]);

        //insert detail
        asort($pi);
        //reindex
        $pi = array_values($pi);

        $saldo = str_replace(array('.', ','), '' , $request->jumlah_yang_dibayar);

        for ($i=0; $i < count($pi); $i++) {
            //dd($faktur[$i]);

            if ($saldo > 0) {
                $dataFaktur = DB::table('t_purchase_invoice')
                    ->where('pi_code', $pi[$i])
                    ->first();

                $start = strtotime(date('Y-m-d', strtotime($dataFaktur->pi_date)));
                $end = strtotime(date("Y-m-d"));
                $usia_pembayaran = ceil(abs($end - $start) / 86400);

                $waiting = DB::table('d_pi_pembayaran')
                    ->join('t_pi_pembayaran', 't_pi_pembayaran.pembayaran_code', '=', 'd_pi_pembayaran.pembayaran_code')
                    ->where('d_pi_pembayaran.pi_code',$pi[$i])
                    ->where('t_pi_pembayaran.status','in approval')
                    ->sum('d_pi_pembayaran.total');

                $belumdibayar = 0;
                $belumdibayar = $dataFaktur->total - $dataFaktur->jumlah_yg_dibayarkan - $waiting;

                $potong = 0;
                if ($saldo > $belumdibayar) {
                    $potong = $belumdibayar;
                }else{
                    $potong = $saldo;
                }

                $saldo = $saldo - $potong;

                $data =  DB::table('d_pi_pembayaran')
                    ->insert([
                        'pembayaran_code' => $pembayaran_code,
                        'pi_code' => $pi[$i],
                        'total' => $potong,
                        'usia_pembayaran' => $usia_pembayaran,
                    ]);
            }
        }

        return redirect('admin/purchasing-pembayaran-wait');
    }

    public function setujuiPembayaran($pembayaran_code,$id)
    {
        $dataPembayaran = DB::table('t_pi_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->first();

        $detailPembayaran = DB::table('d_pi_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->get();

        $jumlah = 0;
        foreach ($detailPembayaran as $raw_data) {
            $dataPI = DB::table('t_purchase_invoice')
                ->where('pi_code', $raw_data->pi_code)
                ->first();

            $totaldibayar = 0;
            $totaldibayar = $dataPI->jumlah_yg_dibayarkan + $raw_data->total;

            //update faktur
            DB::table('t_purchase_invoice')
                ->where('pi_code', '=', $raw_data->pi_code)
                ->update([
                    'jumlah_yg_dibayarkan' => $totaldibayar,
                ]);

            $dataPI2 = DB::table('t_purchase_invoice')
                ->where('pi_code', '=', $raw_data->pi_code)
                ->first();

            if ($dataPI2->jumlah_yg_dibayarkan >= $dataPI2->total) {
                DB::table('t_purchase_invoice')
                    ->where('pi_code', '=', $raw_data->pi_code)
                    ->update([
                        'status' => 'paid',
                    ]);
            }

            $jumlah = $jumlah + $raw_data->total;
        }

        if ($dataPembayaran->dp_code != null) {
            $dataDP = DB::table('t_pi_down_payment')
                ->where('dp_code', '=', $dataPembayaran->dp_code)
                ->first();

            if (count($dataDP)>0) {
                if ($dataDP->jumlah_yg_dipakai >= $dataDP->dp_total) {
                    DB::table('t_pi_down_payment')
                        ->where('dp_code', '=', $dataPembayaran->dp_code)
                        ->update([
                            'status' => 'close',
                        ]);
                }

                $jumlah_yg_dipakai = $dataDP->jumlah_yg_dipakai + $jumlah;

                DB::table('t_pi_down_payment')
                    ->where('dp_code', '=', $dataPembayaran->dp_code)
                    ->update([
                        'jumlah_yg_dipakai' => $jumlah_yg_dipakai,
                    ]);
                $last = DB::table('d_pi_down_payment')->where('dp_code',$dataPembayaran->dp_code)->orderBy('id','DESC')->first();

                $data =  DB::table('d_pi_down_payment')
                    ->insert([
                        'dp_code' => $dataPembayaran->dp_code,
                        'transaksi' => $pembayaran_code,
                        'out' => $jumlah,
                        'saldo_akhir' => $last->saldo_akhir - $jumlah,
                    ]);

                $dataDP = DB::table('t_pi_down_payment')
                    ->where('dp_code', '=', $dataPembayaran->dp_code)
                    ->first();

                if ($dataDP->jumlah_yg_dipakai >= $dataDP->dp_total) {
                    DB::table('t_pi_down_payment')
                        ->where('dp_code', '=', $dataPembayaran->dp_code)
                        ->update([
                            'status' => 'close',
                        ]);
                }
            }
        }

        DB::table('t_pi_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->update([
                'status' => 'approved',
                'user_confirm' => $id,
                'confirm_date' => date("Y-m-d"),
            ]);

        //KELUARKAN DARI KE CASH BANK
        $type_bukti = '';
        if ($dataPembayaran->type == 1) {
            $type_bukti = 'BKK';

            $data_code = $this->setCode($type_bukti);
            $cash_bank_code = $data_code['code'];

            try{
                DB::table('t_cash_bank')
                    ->insert([
                        'cash_bank_code' => $cash_bank_code,
                        'seq_code' => $data_code['sequence'],
                        'cash_bank_date' => date('Y-m-d'),
                        'cash_bank_type' => $type_bukti,
                        'cash_bank_group' => 'AP',
                        'cash_bank_status' => 'post',
                        'id_coa' => $dataPembayaran->rekening_tujuan,
                        'cash_bank_total' => $jumlah,
                        'cash_bank_keterangan' => $dataPembayaran->keterangan,
                        'user_confirm' => auth()->user()->id,
                        'confirm_date' => date("Y-m-d"),
                    ]);

                $id_coa_detail = DB::table('m_coa')
                    ->where('code','2010101')
                    ->first();

                DB::table('d_cb_expense_receipt')
                    ->insert([
                        'cash_bank_code' => $cash_bank_code,
                        'id_coa' => $id_coa_detail->id,
                        'total' => $jumlah,
                        'keterangan' => $dataPembayaran->keterangan,
                    ]);

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
                dd($e);
            }
        }elseif($dataPembayaran->type == 2){
            $type_bukti = 'BBK';

            $data_code = $this->setCode($type_bukti);
            $cash_bank_code = $data_code['code'];

            try{
                DB::table('t_cash_bank')
                    ->insert([
                        'cash_bank_code' => $cash_bank_code,
                        'seq_code' => $data_code['sequence'],
                        'cash_bank_date' => date('Y-m-d'),
                        'cash_bank_type' => $type_bukti,
                        'cash_bank_group' => 'AP',
                        'cash_bank_status' => 'post',
                        'id_coa' => $dataPembayaran->rekening_tujuan,
                        'cash_bank_total' => $jumlah,
                        'cash_bank_keterangan' => $dataPembayaran->keterangan,
                        'user_confirm' => auth()->user()->id,
                        'confirm_date' => date("Y-m-d"),
                    ]);

                $id_coa_detail = DB::table('m_coa')
                    ->where('code','2010101')
                    ->first();

                DB::table('d_cb_expense_receipt')
                    ->insert([
                        'cash_bank_code' => $cash_bank_code,
                        'id_coa' => $id_coa_detail->id,
                        'total' => $jumlah,
                        'keterangan' => $dataPembayaran->keterangan,
                    ]);

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
                dd($e);
            }
        }

        //AUTO JURNAL AP PAYMENT
        $id_gl = DB::table('t_general_ledger')
            ->insertGetId([
                'general_ledger_date' => date('Y-m-d'),
                'general_ledger_periode' => date('Ym'),
                'general_ledger_keterangan' => 'Payment A/P No.'.$pembayaran_code,
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
                'total' => $jumlah,
                'ref' => $pembayaran_code,
                'type_transaksi' => 'NP',
                'status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        $coa = '';
        if ($dataPembayaran->type == 1) {
            $coa = $dataPembayaran->rekening_tujuan;
        }
        elseif($dataPembayaran->type == 2){
            $coa = $dataPembayaran->rekening_tujuan;
        }
        elseif($dataPembayaran->type == 3){
            $id_coa = DB::table('m_coa')
                ->where('code','20103')
                ->first();
            $coa = $id_coa->id;
            //$coa = $dataPembayaran->rekening_tujuan;
        }
        elseif($dataPembayaran->type == 4){
            $id_coa = DB::table('m_coa')
                ->where('code','1010601')
                ->first();
            $coa = $id_coa->id;
        }

        DB::table('d_general_ledger')
            ->insert([
                't_gl_id' => $id_gl,
                'sequence' => 2,
                'id_coa' => $coa,
                'debet_credit' => 'credit',
                'total' => $jumlah,
                'ref' => $pembayaran_code,
                'type_transaksi' => 'NP',
                'status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        return redirect('admin/purchasing-pembayaran-list');
    }

    protected function setCode($type)
    {
        $getLastCode = DB::table('t_cash_bank')
            ->select('seq_code')
            ->where('cash_bank_type',$type)
            ->orderBy('id', 'desc')
            ->pluck('seq_code')
            ->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        $result = ['code' => $type.$dataDate.$nol.$getLastCode, 'sequence' => $getLastCode];

        return $result;
    }

    public function tolakPembayaran($pembayaran_code,$id)
    {
        $dataPembayaran = DB::table('t_pi_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->first();

        DB::table('t_pi_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->update([
                'status' => 'reject',
                'user_confirm' => $id,
                'confirm_date' => date("Y-m-d"),
            ]);

        return redirect('admin/purchasing-pembayaran-list');
    }

    public function laporanHutang()
    {
        $dataSupplier = DB::table('m_supplier')
            ->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->select('m_supplier.id as supplier_id','name')
            ->groupBy('m_supplier.id')
            ->get();

        return view('admin.purchasing.purchase-invoice.laporan', compact('dataSupplier'));
    }

    public function getSupplierHutang($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataSupplier = DB::table('m_supplier')
            ->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->select('m_supplier.id as supplier_id','name')
            ->where('t_purchase_invoice.created_at','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_purchase_invoice.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('m_supplier.id')
            ->get();

        return Response::json($dataSupplier);
    }

    public function laporanUmurHutang()
    {
        $dataSupplier = DB::table('m_supplier')
            ->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->select('m_supplier.id as supplier_id','name')
            ->groupBy('m_supplier.id')
            ->get();

        return view('admin.purchasing.purchase-invoice.laporan-umur-hutang', compact('dataSupplier'));
    }

    public function getSupplierUmurHutang($periode)
    {
        $dataSupplier = DB::table('m_supplier')
            ->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->select('m_supplier.id as supplier_id','name')
            ->where('t_purchase_invoice.created_at','<', date('Y-m-d', strtotime($periode. ' + 1 days')))
            ->groupBy('m_supplier.id')
            ->get();

        return Response::json($dataSupplier);
    }

    public function getDP($id)
    {
        $data =  DB::table('t_pi_down_payment')
            ->where('supplier',$id)
            ->where('status', '=', 'post')
            ->orderBy('dp_code', 'DESC')
            ->get();

        foreach ($data as $raw_data) {
            $dataPembayaran = DB::table('t_pi_pembayaran')
                ->join('d_pi_pembayaran', 'd_pi_pembayaran.pembayaran_code', '=', 't_pi_pembayaran.pembayaran_code')
                ->where('t_pi_pembayaran.dp_code', '=', $raw_data->dp_code)
                ->where('t_pi_pembayaran.status', '=', "in approval")
                ->sum('d_pi_pembayaran.total');

            $raw_data->sisa = $raw_data->dp_total - $raw_data->jumlah_yg_dipakai - $dataPembayaran;
        }

        return Response::json($data);
    }

    public function getValueDP($id)
    {
        $data =  DB::table('t_pi_down_payment')
            ->where('dp_code',$id)
            ->get();

        foreach ($data as $raw_data) {
            $dataPembayaran = DB::table('t_pi_pembayaran')
                ->join('d_pi_pembayaran', 'd_pi_pembayaran.pembayaran_code', '=', 't_pi_pembayaran.pembayaran_code')
                ->where('t_pi_pembayaran.dp_code', '=', $raw_data->dp_code)
                ->where('t_pi_pembayaran.status', '=', "in approval")
                ->sum('d_pi_pembayaran.total');
            $raw_data->jumlah_yg_dipakai = $raw_data->jumlah_yg_dipakai + $dataPembayaran;
        }

        return Response::json($data);
    }

    public function cancelPembayaran($id)
    {
        $kode_pembayaran = $id;
        $reason = MReasonModel::orderBy('id','DESC')->get();

        return view('admin.purchasing.pembayaran.cancel',compact('kode_pembayaran','reason'));
    }

    public function cancelPembayaranPost(Request $request)
    {
        //dd($request->all());
        $pembayaran_code = $request->kode_pembayaran;

        $pembayaran_header = DB::table('t_pi_pembayaran')
            ->select('*')
            ->where('pembayaran_code',$pembayaran_code)
            ->first();

        $pembayaran_detail = DB::table('d_pi_pembayaran')
            ->select('*')
            ->where('pembayaran_code',$pembayaran_code)
            ->get();
        //dd($request->all());

        try{

            DB::table('t_pi_pembayaran')
                ->where('pembayaran_code', '=', $pembayaran_code)
                ->update([
                    'status' => 'cancel',
                    'cancel_reason' => $request->cancel_reason,
                    'cancel_description' => $request->cancel_description,
                    'user_cancel' => auth()->user()->id,
                ]);

            foreach ($pembayaran_detail as $raw_data) {
                $faktur = DB::table('t_purchase_invoice')
                    ->select('*')
                    ->where('pi_code',$raw_data->pi_code)
                    ->first();

                $jumlah_yg_dibayarkan = $faktur->jumlah_yg_dibayarkan - $raw_data->total;

                DB::table('t_purchase_invoice')
                    ->where('pi_code', '=', $raw_data->pi_code)
                    ->update([
                        'status' => 'unpaid',
                        'jumlah_yg_dibayarkan' => $jumlah_yg_dibayarkan
                    ]);
            }

            if ($pembayaran_header->dp_code != null) {
                //update dp
                $last = DB::table('d_pi_down_payment')->where('dp_code',$pembayaran_header->dp_code)->orderBy('id','DESC')->first();

                $jumlah_dp = DB::table('d_pi_pembayaran')
                    ->where('pembayaran_code',$pembayaran_code)
                    ->sum('total');

                $data =  DB::table('d_pi_down_payment')
                    ->insert([
                        'dp_code' => $pembayaran_header->dp_code,
                        'transaksi' => $pembayaran_code,
                        'in' => $jumlah_dp,
                        'saldo_akhir' => $last->saldo_akhir + $jumlah_dp,
                    ]);

                $dataDP = DB::table('t_pi_down_payment')
                    ->where('dp_code', '=', $pembayaran_header->dp_code)
                    ->first();

                $jumlah = $dataDP->jumlah_yg_dipakai - $jumlah_dp;

                DB::table('t_pi_down_payment')
                    ->where('dp_code', '=', $pembayaran_header->dp_code)
                    ->update([
                        'status' => 'post',
                        'jumlah_yg_dipakai' => $jumlah,
                    ]);
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/purchasing-pembayaran-list');

    }
}
