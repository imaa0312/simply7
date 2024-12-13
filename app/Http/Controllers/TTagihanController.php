<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;
use App\Models\MReasonModel;
use Yajra\Datatables\Datatables;



class TTagihanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataCustomer = DB::table('m_customer')
            ->join('t_faktur', 'm_customer.id', '=', 't_faktur.customer')
            ->select('m_customer.id as customer_id','name')
            ->where('t_faktur.status_payment','unpaid')
            ->groupBy('m_customer.id','name')
            ->get();

        $dataPenagihan = DB::table('t_faktur')
            ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
            ->where('status_payment', '=', 'unpaid')
            ->select('t_faktur.so_code','t_faktur.faktur_code','t_faktur.sj_code','t_faktur.jumlah_yg_dibayarkan','t_faktur.status_payment','m_customer.name as customer','m_customer.id as id_customer','t_faktur.id as faktur_id','t_faktur.total','t_faktur.print')
            ->orderBy('t_faktur.id','DESC')
            ->get();

        // dd($dataPenagihan);
        return view('admin.transaksi.tagihan.index', compact('dataPenagihan','dataCustomer'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $getData = DB::table('t_sales_order')->where('id', '=', $id)->first();

        return view('admin.transaksi.tagihan.tagihan', compact('getData'));
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
        $date = date('Y-m-d', strtotime($request->date));

        $update = DB::table('t_sales_order')->where('id', '=', $id)->update([
            'status_payment' => $request->payment,
            'payment_date' => $date
        ]);

        return redirect('admin/transaksi/tagihan');
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

    public function history()
    {
        $dataPenagihan = DB::table('t_faktur')
                        ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
                        ->where('status_payment', '=', 'paid')
                        ->select('t_faktur.so_code','t_faktur.faktur_code','t_faktur.sj_code','t_faktur.status_payment','m_customer.name as customer','m_customer.id as id_customer','t_faktur.id as faktur_id','t_faktur.total','t_faktur.print')
                        ->orderBy('t_faktur.faktur_code', 'DESC')
                        ->get();
        // dd($dataPenagihan);
        return view('admin.transaksi.tagihan.history', compact('dataPenagihan'));
    }

    public function payment()
    {   
        $dataCompany = DB::table('m_company_profile')->get();

        $dataCustomer = DB::table('t_faktur')
            ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
            ->select('t_faktur.customer','m_customer.name as customer','m_customer.id as id_customer')
            ->groupBy('t_faktur.customer','m_customer.name','m_customer.id')
            ->where('status_payment', '=', 'unpaid')
            ->get();

        foreach ($dataCustomer as $key => $raw_data) {
            $data =  DB::table('t_faktur')
                ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
                ->select('t_faktur.*','m_customer.name as customer','m_customer.id as id_customer')
                ->where('m_customer.id',$raw_data->id_customer)
                ->where('status_payment', '=', 'unpaid')
                ->orderBy('t_faktur.faktur_code', 'DESC')
                ->get();

            foreach ($data as $key2 => $raw_data2) {
                $waiting = DB::table('d_pembayaran')
                    ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                    ->where('d_pembayaran.faktur_code',$raw_data2->faktur_code)
                    ->where('t_pembayaran.status','in approval')
                    ->sum('d_pembayaran.total');

                $jumlah_dibayar = $raw_data2->jumlah_yg_dibayarkan + $waiting;

                if ($jumlah_dibayar >= $raw_data2->total) {
                    unset($data[$key2]);
                }
            }

            if (count($data) < 1) {
                unset($dataCustomer[$key]);
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

        // $dataRekening = DB::table('m_rekening_tujuan')
        //     ->select('m_rekening_tujuan.id as id_rek','m_rekening_tujuan.no_rekening','m_rekening_tujuan.atas_nama','m_bank.name as bank_name')
        //     ->join('m_bank', 'm_bank.id', '=', 'm_rekening_tujuan.bank')
        //     ->get();

        $dataSelisih = DB::table('m_coa')
            ->where('code','90401')
            ->get();

        $dataMetode = DB::table('m_metode_pembayaran')->get();

        // return view('admin.transaksi.pembayaran.create',compact('dataCustomer','dataBank','dataMetode','dataRekening'));
        return view('admin.transaksi.pembayaran.create-multiple',compact('dataCustomer','dataBank','dataMetode','dataRekening','dataSelisih','dataCompany'));
    }

    public function getAkunCoa($id)
    {
        $var = '';
        if ($id == 1) {
            $var = 'VAR_CASH';
        }
        elseif($id == 2){
            $var = 'VAR_BANK';
        }else{
            $var = 'VAR_BANK';
        }

        $interface = DB::table('m_interface')
            ->where('var',$var)
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

        return Response::json($data);
    }

    public function getfaktur($id)
    {
        $data =  DB::table('t_faktur')
            ->select('t_faktur.faktur_code','jumlah_yg_dibayarkan','total')
            ->where('t_faktur.customer',$id)
            ->where('status_payment', '=', 'unpaid')
            ->groupBy('t_faktur.faktur_code','jumlah_yg_dibayarkan','total')
            ->orderBy('t_faktur.faktur_code', 'DESC')
            ->get();

        foreach ($data as $key => $raw_data) {
            $waiting = DB::table('d_pembayaran')
                ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                ->where('d_pembayaran.faktur_code',$raw_data->faktur_code)
                ->where('t_pembayaran.status','in approval')
                ->sum('d_pembayaran.total');

            $jumlah_dibayar = $raw_data->jumlah_yg_dibayarkan + $waiting;

            if ($jumlah_dibayar >= $raw_data->total) {
                unset($data[$key]);
            }
        }

        return Response::json($data);
    }

    public function getAllfaktur($id,$company)
    {
        $data =  DB::table('t_faktur')
            ->select('t_faktur.faktur_code','t_faktur.so_code','t_faktur.sj_code','t_faktur.jatuh_tempo','jumlah_yg_dibayarkan','total')
            ->where('t_faktur.customer',$id)
            ->where('status_payment', '=', 'unpaid')
            ->where('company_code', '=', $company)
            ->groupBy('t_faktur.faktur_code','jumlah_yg_dibayarkan','total','t_faktur.so_code','t_faktur.sj_code','t_faktur.jatuh_tempo')
            ->orderBy('t_faktur.faktur_code', 'DESC')
            ->get();

        foreach ($data as $key => $raw_data) {
            $waiting = DB::table('d_pembayaran')
                ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                ->where('d_pembayaran.faktur_code',$raw_data->faktur_code)
                ->where('t_pembayaran.status','in approval')
                ->sum('d_pembayaran.total');

            $sudah_dibayar = $raw_data->jumlah_yg_dibayarkan + $waiting;
            $belum_dibayar = $raw_data->total - $raw_data->jumlah_yg_dibayarkan - $waiting;

            $raw_data->sudah_dibayar = $sudah_dibayar;
            $raw_data->belum_dibayar = $belum_dibayar;

            if ($sudah_dibayar >= $raw_data->total) {
                unset($data[$key]);
            }
        }

        return Response::json($data);
    }

    public function detailFaktur(Request $request)
    {
        $faktur = $request->faktur;

        $cekfaktur = 0;
        if ($faktur != null || $faktur != '') {
            foreach ($faktur as $i => $raw_faktur) {
                if ($request->faktur_code == $faktur[$i]) {
                    $cekfaktur = 1;
                }
            }
        }

        if ($cekfaktur == 0) {
	        $result = DB::table('t_faktur')->where('t_faktur.faktur_code',$request->faktur_code)->first();

            $waiting = DB::table('d_pembayaran')
                ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                ->where('d_pembayaran.faktur_code',$request->faktur_code)
                ->where('t_pembayaran.status','in approval')
                ->sum('d_pembayaran.total');

            $bayar = $result->total - $result->jumlah_yg_dibayarkan - $waiting;
	        $sudah_bayar = $result->jumlah_yg_dibayarkan + $waiting;

            $kode_faktur = '"'.$result->faktur_code.'"';

	        $row = "<tr id='tr_".$result->faktur_code."'>";
	        	$row .= "<td>";
	                $row .= "<input type='text' class='form-control input-sm' value='".$request->faktur_code."' name='faktur[".$result->faktur_code."]' readonly id='faktur_code_".$result->faktur_code."'>";
	            $row .= "</td>";

	            $row .= "<td>";
	                $row .= "<input type='text' class='form-control input-sm' value='".$result->so_code."'  readonly >";
	            $row .= "</td>";

	            $row .= "<td>";
	                $row .= "<input type='text' class='form-control input-sm' value='".$result->sj_code."'  readonly >";
	            $row .= "</td>";

	            $row .= "<td>";
                    $row .= "<input type='text' class='form-control input-sm' value='".date('d-m-Y',strtotime($result->jatuh_tempo))."' readonly >";
                $row .= "</td>";

                $row .= "<td>";
                    $row .= "<input type='text' class='form-control input-sm bayar' value='".$bayar."' readonly id='bayar_".$result->faktur_code."'>";
                    $row .= "<input type='hidden' value='".$sudah_bayar."' id='sudah_bayar_".$result->faktur_code."'>";
                $row .= "</td>";
	            $row .= "<td>";
	                $row .= "<button type='button' value='".$result->faktur_code."' class='btn btn-danger btn-sm btn-delete' onclick='hapusBaris(".$kode_faktur.");'><span class='fa fa-trash'></span></button";
	            $row .= "</td>";

	        $row .= "<tr>";
	    }

        return $row;
    }

    public function pembayaran(request $request)
    {
        $this->validate($request, [
            'customer' => 'required',
            'company' => 'required',
            'jumlah_yang_dibayar' => 'required',
            'type' => 'required',
        ]);

        //dd($request->all());

        $status = "";

        $faktur = json_decode($request->use_faktur);
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
        }elseif ($request->type == 4){
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
        }else{
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

        $getLastCode = DB::table('t_pembayaran')
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

        $pembayaran_code = 'NFTK'.$dataDate.$nol.$getLastCode;

        $sales = DB::table('m_customer')
            ->join('m_wilayah_pembagian_sales', 'm_wilayah_pembagian_sales.wilayah_sales', '=', 'm_customer.wilayah_sales')
            ->where('m_customer.id',$request->customer)
            ->pluck('m_wilayah_pembagian_sales.sales')
            ->first();

        //insert header
        $data =  DB::table('t_pembayaran')
            ->insert([
                'pembayaran_code' => $pembayaran_code,
                //'faktur_code' => $request->faktur_code,
                'customer' => $request->customer,
                'company_code' => $request->company,
                'sales' => $sales,
                //'total' => $request->jumlah_yang_dibayar,
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
                //'usia_pembayaran' => $usia_pembayaran,
                'confirm_date' => date("Y-m-d"),
            ]);

        //insert detail
        //$faktur = $request->faktur;
        asort($faktur);
        //reindex
        $faktur = array_values($faktur);

        $saldo = str_replace(array('.', ','), '' , $request->jumlah_yang_dibayar);

        for ($i=0; $i < count($faktur); $i++) {
        	//dd($faktur[$i]);

        	if ($saldo > 0) {
        		$dataFaktur = DB::table('t_faktur')
		            ->where('faktur_code', $faktur[$i])
		            ->first();

		        $start = strtotime(date('Y-m-d', strtotime($dataFaktur->created_at)));
		        $end = strtotime(date("Y-m-d"));
		        $usia_pembayaran = ceil(abs($end - $start) / 86400);

                $waiting = DB::table('d_pembayaran')
                    ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                    ->where('d_pembayaran.faktur_code',$faktur[$i])
                    ->where('t_pembayaran.status','in approval')
                    ->sum('d_pembayaran.total');

		        $belumdibayar = 0;
		        $belumdibayar = $dataFaktur->total - $dataFaktur->jumlah_yg_dibayarkan - $waiting;

		        $potong = 0;
		        if ($saldo > $belumdibayar) {
		        	$potong = $belumdibayar;
		        }else{
		        	$potong = $saldo;
		        }

		        $saldo = $saldo - $potong;

		        $data =  DB::table('d_pembayaran')
		            ->insert([
		                'pembayaran_code' => $pembayaran_code,
                        'company_code' => $request->company,
		                'faktur_code' => $faktur[$i],
		                'total' => $potong,
		                'usia_pembayaran' => $usia_pembayaran,
		            ]);
        	}
        }

        return redirect('admin/transaksi-pembayaran-wait');
    }

    public function listPembayaran(request $request)
    {

        $dataPembayaran = DB::table('t_pembayaran')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
            ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
            ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
            ->where('t_pembayaran.status','!=','in approval')
            ->orderBy('t_pembayaran.id','desc')
            ->get();

        foreach ($dataPembayaran as $raw_data) {
        	$total = DB::table('d_pembayaran')
        		->where('pembayaran_code',$raw_data->pembayaran_code)
        		->sum('total');

        	$raw_data->total = $total;
        }

        return view('admin.transaksi.pembayaran.index', compact('dataPembayaran'));
    }

    public function waitPembayaran(request $request)
    {

        // $dataPembayaran = DB::table('t_pembayaran')
        //     ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
        //     ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
        //     ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
        //     ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
        //     ->where('t_pembayaran.status','in approval')
        //     ->orderBy('t_pembayaran.id','desc')
        //     ->get();
        //
        // foreach ($dataPembayaran as $raw_data) {
        //     $total = DB::table('d_pembayaran')
        //         ->where('pembayaran_code',$raw_data->pembayaran_code)
        //         ->sum('total');
        //
        //     $raw_data->total = $total;
        // }

        return view('admin.transaksi.pembayaran.waiting-approval');
    }

    public function waitPembayaranTransfer(request $request)
    {

        $dataPembayaran = DB::table('m_konfirmasi_pembayaran')
            ->leftjoin('m_rekening_tujuan', 'm_rekening_tujuan.id', '=', 'm_konfirmasi_pembayaran.bank_penerima')
            ->leftjoin('m_bank', 'm_bank.id', '=', 'm_rekening_tujuan.bank')
            ->leftjoin('m_user', 'm_user.id', '=', 'm_konfirmasi_pembayaran.checked_by')
            ->leftjoin('t_sales_order', 't_sales_order.so_code', '=', 'm_konfirmasi_pembayaran.so_code')
            ->leftjoin('m_customer', 'm_customer.id', '=', 't_sales_order.customer')
            ->select('*','m_customer.name as atas_nama_customer','m_konfirmasi_pembayaran.atas_nama as nama_rekening','m_bank.name as bank_name','m_user.username','m_konfirmasi_pembayaran.no_rekening as no_rek')
            ->orderBy('m_konfirmasi_pembayaran.id_konfirmasi','desc')
            ->orderBy('m_konfirmasi_pembayaran.status_pembayaran','desc')
            ->get();
        
        // foreach ($dataPembayaran as $raw_data) {
        //     $total = DB::table('d_pembayaran')
        //         ->where('pembayaran_code',$raw_data->pembayaran_code)
        //         ->sum('total');
        
        //     $raw_data->total = $total;
        // }

        //dd($dataPembayaran);

        return view('admin.transaksi.pembayaran.waiting-approval-transfer', compact('dataPembayaran'));
    }

    public function detailWaitPembayaranTransfer($id_konfirmasi)
    {

        // $detailPembayaran = DB::table('t_pembayaran')
        //     ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
        //     ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
        //     ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
        //     ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
        //     ->get();
        
        $dataPembayaran = DB::table('m_konfirmasi_pembayaran')
            ->leftjoin('m_rekening_tujuan', 'm_rekening_tujuan.id', '=', 'm_konfirmasi_pembayaran.bank_penerima')
            ->leftjoin('m_bank', 'm_bank.id', '=', 'm_rekening_tujuan.bank')
            ->leftjoin('m_user', 'm_user.id', '=', 'm_konfirmasi_pembayaran.checked_by')
            ->leftjoin('t_sales_order', 't_sales_order.so_code', '=', 'm_konfirmasi_pembayaran.so_code')
            ->leftjoin('m_customer', 'm_customer.id', '=', 't_sales_order.customer')
            ->select('m_konfirmasi_pembayaran.*','m_bank.name as bank_name','m_user.username','t_sales_order.type_atas_nama','m_customer.name as atas_nama_customer','m_rekening_tujuan.*','m_konfirmasi_pembayaran.atas_nama as nama_rekening','m_konfirmasi_pembayaran.no_rekening as no_rek')
            ->orderBy('m_konfirmasi_pembayaran.id_konfirmasi','desc')
            ->orderBy('m_konfirmasi_pembayaran.status_pembayaran','desc')
            ->where('m_konfirmasi_pembayaran.id_konfirmasi',$id_konfirmasi)
            ->first();


        // if($dataPembayaran->type_atas_nama == "main"){
        //     $data_customer = DB::table('m_customer')
        //                     ->leftjoin('t_sales_order', 't_sales_order.customer', '=', 'm_customer.id')
        //                     ->select('m_customer.*', 't_sales_order.so_code')
        //                     ->where('t_sales_order.so_code',$dataPembayaran->so_code)
        //                     ->first();
        //     $dataPembayaran->atas_nama_customer = $data_customer->name;
        // }elseif($dataPembayaran->type_atas_nama == "other"){
        //     $data_customer = DB::table('m_alamat_customer')
        //                     ->leftjoin('t_sales_order', 't_sales_order.atas_nama', '=', 'm_alamat_customer.id')
        //                     ->select('m_alamat_customer.*', 't_sales_order.so_code')
        //                     ->where('t_sales_order.so_code',$dataPembayaran->so_code)
        //                     ->first();
        //     //dd($data_customer);
        //     $dataPembayaran->atas_nama_customer = $data_customer->name;
        // }
        //dd($dataPembayaran);

        // $detailPembayaran = DB::table('d_pembayaran')
        //     ->where('pembayaran_code',$pembayaran_code)
        //     ->get();

        // $dataPembayaran = DB::table('t_pembayaran')
        //     ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
        //     ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
        //     ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
        //     ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
        //     ->where('pembayaran_code',$pembayaran_code)
        //     ->first();

        // $total = DB::table('d_pembayaran')
        //     ->where('pembayaran_code',$pembayaran_code)
        //     ->sum('total');

        // $dataPembayaran->totalpembayaran = $total;

        return view('admin.transaksi.pembayaran.detailwaitpembayarantransfer', compact('dataPembayaran','detailPembayaran'));
    }


    public function detailTagihan($faktur_code)
    {
        $dataFaktur = DB::table('t_faktur')
            ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
            ->select('*')
            ->where('faktur_code',$faktur_code)
            ->first();

        $dataPembayaran = DB::table('d_pembayaran')
            ->join('t_pembayaran', 'd_pembayaran.pembayaran_code', '=', 't_pembayaran.pembayaran_code')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
            ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
            ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
            ->where('faktur_code',$faktur_code)
            ->orderBy('t_pembayaran.pembayaran_code')
            ->get();

        return view('admin.transaksi.tagihan.detailtagihan', compact('dataPembayaran','dataFaktur'));
    }

    public function detailPembayaran($pembayaran_code)
    {
        $detailPembayaran = DB::table('d_pembayaran')
            ->where('pembayaran_code',$pembayaran_code)
            ->get();

        $dataPembayaran = DB::table('t_pembayaran')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
            ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
            ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
            ->where('pembayaran_code',$pembayaran_code)
            ->first();

        $total = DB::table('d_pembayaran')
            ->where('pembayaran_code',$pembayaran_code)
            ->sum('total');

        $dataPembayaran->totalpembayaran = $total;

        return view('admin.transaksi.pembayaran.detailpembayaran', compact('dataPembayaran','detailPembayaran'));
    }

    public function setujuiPembayaran($pembayaran_code,$id)
    {
        $dataPembayaran = DB::table('t_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->first();

        $detailPembayaran = DB::table('d_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->get();

        $jumlah = 0;
        foreach ($detailPembayaran as $raw_data) {
            $dataFaktur = DB::table('t_faktur')
                ->where('faktur_code', $raw_data->faktur_code)
                ->first();

            $totaldibayar = 0;
            $totaldibayar = $dataFaktur->jumlah_yg_dibayarkan + $raw_data->total;

            //update faktur
            DB::table('t_faktur')
                ->where('faktur_code', '=', $raw_data->faktur_code)
                ->update([
                    'jumlah_yg_dibayarkan' => $totaldibayar,
                ]);

            $datafaktur = DB::table('t_faktur')
                ->where('faktur_code', '=', $raw_data->faktur_code)
                ->first();

            if ($datafaktur->jumlah_yg_dibayarkan >= $datafaktur->total) {
                DB::table('t_faktur')
                    ->where('faktur_code', '=', $raw_data->faktur_code)
                    ->update([
                        'status_payment' => 'paid',
                    ]);
            }

            $jumlah = $jumlah + $raw_data->total;
        }

        if ($dataPembayaran->dp_code != null) {
            $dataDP = DB::table('t_down_payment')
                ->where('dp_code', '=', $dataPembayaran->dp_code)
                ->first();

            if (count($dataDP)>0) {
                if ($dataDP->jumlah_yg_dipakai >= $dataDP->dp_total) {
                    DB::table('t_down_payment')
                        ->where('dp_code', '=', $dataPembayaran->dp_code)
                        ->update([
                            'status' => 'close',
                        ]);
                }

                $jumlah_yg_dipakai = $dataDP->jumlah_yg_dipakai + $jumlah;

                DB::table('t_down_payment')
                    ->where('dp_code', '=', $dataPembayaran->dp_code)
                    ->update([
                        'jumlah_yg_dipakai' => $jumlah_yg_dipakai,
                    ]);
                $last = DB::table('d_down_payment')->where('dp_code',$dataPembayaran->dp_code)->orderBy('id','DESC')->first();

                $data =  DB::table('d_down_payment')
                    ->insert([
                        'dp_code' => $dataPembayaran->dp_code,
                        'transaksi' => $pembayaran_code,
                        'out' => $jumlah,
                        'saldo_akhir' => $last->saldo_akhir - $jumlah,
                    ]);

                $dataDP = DB::table('t_down_payment')
                    ->where('dp_code', '=', $dataPembayaran->dp_code)
                    ->first();

                if ($dataDP->jumlah_yg_dipakai >= $dataDP->dp_total) {
                    DB::table('t_down_payment')
                        ->where('dp_code', '=', $dataPembayaran->dp_code)
                        ->update([
                            'status' => 'close',
                        ]);
                }
            }
        }

        DB::table('t_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->update([
                'status' => 'approved',
                'user_confirm' => $id,
                'confirm_date' => date("Y-m-d"),
            ]);

        //ISI KE CASH BANK
        $type_bukti = '';
        if ($dataPembayaran->type == 1) {
            $type_bukti = 'BKM';

            $data_code = $this->setCode($type_bukti);
            $cash_bank_code = $data_code['code'];

            try{
                DB::table('t_cash_bank')
                    ->insert([
                        'cash_bank_code' => $cash_bank_code,
                        'seq_code' => $data_code['sequence'],
                        'cash_bank_date' => date('Y-m-d'),
                        'cash_bank_type' => $type_bukti,
                        'cash_bank_group' => 'AR',
                        'cash_bank_status' => 'post',
                        'id_coa' => $dataPembayaran->rekening_tujuan,
                        'cash_bank_total' => $jumlah,
                        'cash_bank_keterangan' => $dataPembayaran->keterangan,
                        'user_confirm' => auth()->user()->id,
                        'confirm_date' => date("Y-m-d"),
                    ]);

                $id_coa_detail = DB::table('m_coa')
                    ->where('code','101040101')
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
            $type_bukti = 'BBM';

            $data_code = $this->setCode($type_bukti);
            $cash_bank_code = $data_code['code'];

            try{
                DB::table('t_cash_bank')
                    ->insert([
                        'cash_bank_code' => $cash_bank_code,
                        'seq_code' => $data_code['sequence'],
                        'cash_bank_date' => date('Y-m-d'),
                        'cash_bank_type' => $type_bukti,
                        'cash_bank_group' => 'AR',
                        'cash_bank_status' => 'post',
                        'id_coa' => $dataPembayaran->rekening_tujuan,
                        'cash_bank_total' => $jumlah,
                        'cash_bank_keterangan' => $dataPembayaran->keterangan,
                        'user_confirm' => auth()->user()->id,
                        'confirm_date' => date("Y-m-d"),
                    ]);

                $id_coa_detail = DB::table('m_coa')
                    ->where('code','101040101')
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

        //AUTO JURNAL AR PAYMENT
        $id_gl = DB::table('t_general_ledger')
            ->insertGetId([
                'general_ledger_date' => date('Y-m-d'),
                'general_ledger_periode' => date('Ym'),
                'general_ledger_keterangan' => 'Payment A/R No.'.$pembayaran_code,
                'general_ledger_status' => 'post',
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
                ->where('code','2010201')
                ->first();
            $coa = $id_coa->id;
        }

        DB::table('d_general_ledger')
            ->insert([
                't_gl_id' => $id_gl,
                'sequence' => 1,
                'id_coa' => $coa,
                'debet_credit' => 'debet',
                'total' => $jumlah,
                'ref' => $pembayaran_code,
                'type_transaksi' => 'NF',
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
                'sequence' => 2,
                'id_coa' => $id_coa->id,
                'debet_credit' => 'credit',
                'total' => $jumlah,
                'ref' => $pembayaran_code,
                'type_transaksi' => 'NF',
                'status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        return redirect('admin/transaksi-pembayaran-list');
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
        $dataPembayaran = DB::table('t_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->first();

        //$faktur_code = $dataPembayaran->faktur_code;

        DB::table('t_pembayaran')
            ->where('pembayaran_code', '=', $pembayaran_code)
            ->update([
                'status' => 'reject',
                'user_confirm' => $id,
                'confirm_date' => date("Y-m-d"),
            ]);

        //return redirect('admin/tagihan-detail/'.$faktur_code);
        return redirect('admin/transaksi-pembayaran-list');
    }

    public function cancelPembayaran($id)
    {
        $kode_pembayaran = $id;
        $reason = MReasonModel::orderBy('id','DESC')->get();

        return view('admin.transaksi.pembayaran.cancel',compact('kode_pembayaran','reason'));
    }

    public function cancelPembayaranPost(Request $request)
    {
        //dd($request->all());
        $pembayaran_code = $request->kode_pembayaran;

        $pembayaran_header = DB::table('t_pembayaran')
            ->select('*')
            ->where('pembayaran_code',$pembayaran_code)
            ->first();

        $pembayaran_detail = DB::table('d_pembayaran')
            ->select('*')
            ->where('pembayaran_code',$pembayaran_code)
            ->get();
        //dd($request->all());

        try{

            DB::table('t_pembayaran')
                ->where('pembayaran_code', '=', $pembayaran_code)
                ->update([
                    'status' => 'cancel',
                    'cancel_reason' => $request->cancel_reason,
                    'cancel_description' => $request->cancel_description,
                    'user_cancel' => auth()->user()->id,
                ]);

            foreach ($pembayaran_detail as $raw_data) {
                $faktur = DB::table('t_faktur')
                    ->select('*')
                    ->where('faktur_code',$raw_data->faktur_code)
                    ->first();

                $jumlah_yg_dibayarkan = $faktur->jumlah_yg_dibayarkan - $raw_data->total;

                DB::table('t_faktur')
                    ->where('faktur_code', '=', $raw_data->faktur_code)
                    ->update([
                        'status_payment' => 'unpaid',
                        'jumlah_yg_dibayarkan' => $jumlah_yg_dibayarkan
                    ]);
            }

            if ($pembayaran_header->dp_code != null) {
                //update dp
                $last = DB::table('d_down_payment')->where('dp_code',$pembayaran_header->dp_code)->orderBy('id','DESC')->first();

                $jumlah_dp = DB::table('d_pembayaran')
                    ->where('pembayaran_code',$pembayaran_code)
                    ->sum('total');

                $data =  DB::table('d_down_payment')
                    ->insert([
                        'dp_code' => $pembayaran_header->dp_code,
                        'transaksi' => $pembayaran_code,
                        'in' => $jumlah_dp,
                        'saldo_akhir' => $last->saldo_akhir + $jumlah_dp,
                    ]);

                $dataDP = DB::table('t_down_payment')
                    ->where('dp_code', '=', $pembayaran_header->dp_code)
                    ->first();

                $jumlah = $dataDP->jumlah_yg_dipakai - $jumlah_dp;

                DB::table('t_down_payment')
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

        return redirect('admin/transaksi-pembayaran-list');
    }

    public function laporanPiutang()
    {
        $dataCustomer = DB::table('m_customer')
            ->join('t_faktur', 'm_customer.id', '=', 't_faktur.customer')
            ->select('m_customer.id as customer_id','name')
            ->groupBy('m_customer.id')
            ->get();

        return view('admin.transaksi.tagihan.laporan', compact('dataCustomer'));
    }

    public function getCustomerPiutang($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataCustomer = DB::table('m_customer')
            ->join('t_faktur', 'm_customer.id', '=', 't_faktur.customer')
            ->select('m_customer.id as customer_id','name')
            ->where('t_faktur.created_at','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_faktur.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('m_customer.id')
            ->get();

        return Response::json($dataCustomer);
    }

    public function laporanUmurPiutang()
    {
        $dataCustomer = DB::table('m_customer')
            ->join('t_faktur', 'm_customer.id', '=', 't_faktur.customer')
            ->select('m_customer.id as customer_id','name')
            ->groupBy('m_customer.id')
            ->get();

        return view('admin.transaksi.tagihan.laporan-umur-piutang', compact('dataCustomer'));
    }

    public function getCustomerUmurPiutang($periode)
    {
        $dataCustomer = DB::table('m_customer')
            ->join('t_faktur', 'm_customer.id', '=', 't_faktur.customer')
            ->select('m_customer.id as customer_id','name')
            ->where('t_faktur.created_at','<', date('Y-m-d', strtotime($periode. ' + 1 days')))
            ->groupBy('m_customer.id')
            ->get();

        return Response::json($dataCustomer);
    }

    public function getDP($id)
    {
        $data =  DB::table('t_down_payment')
            ->where('customer',$id)
            ->where('status', '=', 'post')
            ->orderBy('dp_code', 'DESC')
            ->get();

        foreach ($data as $raw_data) {
            $dataPembayaran = DB::table('t_pembayaran')
                ->join('d_pembayaran', 'd_pembayaran.pembayaran_code', '=', 't_pembayaran.pembayaran_code')
                ->where('t_pembayaran.dp_code', '=', $raw_data->dp_code)
                ->where('t_pembayaran.status', '=', "in approval")
                ->sum('d_pembayaran.total');

            $raw_data->sisa = $raw_data->dp_total - $raw_data->jumlah_yg_dipakai - $dataPembayaran;
        }

        return Response::json($data);
    }

    public function getValueDP($id)
    {
        $data =  DB::table('t_down_payment')
            ->where('dp_code',$id)
            ->get();

        foreach ($data as $raw_data) {
            $dataPembayaran = DB::table('t_pembayaran')
                ->join('d_pembayaran', 'd_pembayaran.pembayaran_code', '=', 't_pembayaran.pembayaran_code')
                ->where('t_pembayaran.dp_code', '=', $raw_data->dp_code)
                ->where('t_pembayaran.status', '=', "in approval")
                ->sum('d_pembayaran.total');
            $raw_data->jumlah_yg_dipakai = $raw_data->jumlah_yg_dipakai + $dataPembayaran;
        }

        return Response::json($data);
    }

    public function apiTagihan()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);

        $dataPenagihan = DB::table('t_faktur')
            ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
            ->where('status_payment', '=', 'unpaid')
            ->select('t_faktur.so_code','t_faktur.faktur_code','t_faktur.sj_code','t_faktur.jumlah_yg_dibayarkan','t_faktur.status_payment','m_customer.name as customer','m_customer.id as id_customer','t_faktur.id as faktur_id','t_faktur.total','t_faktur.print')
            ->orderBy('t_faktur.id','DESC')
            ->get();
            $i=0;
$roleSuperAdmin = \App\Models\MRoleModel::whereIn('name', ['Super Admin','Admin'])->first();
        return Datatables::of($dataPenagihan)
        ->addColumn('action', function ($dataPenagihan) use ($roleSuperAdmin,$i) {
            if(auth()->user()->role == $roleSuperAdmin->id){
                return '
                <button type="button" class="btn btn-info btn-sm" onclick="showModal('."'$dataPenagihan->faktur_code'".')" data-toggle="tooltip" data-placement="top" title="show coa"><span class="fa fa-money"></span></button> &nbsp;'.
                '<a href="'.url('admin/tagihan-detail/'.$dataPenagihan->faktur_code).'" data-toggle="tooltip" data-placement="top" title="Detail" class="btn btn-primary  btn-sm"><i class="fa fa-edit"></i></a>'.
                '<a style="margin-left: 5px" href="'.url('admin/report-faktur/'.$dataPenagihan->faktur_code).'" class="btn btn-warning  btn-sm" data-toggle="tooltip" data-placement="top" title="Cetak" id="print_'.$i++.'"><i class="fa fa-file-pdf-o"></i></a>';
            }
            else{
                if($dataPenagihan->print == false){
                    return '<button type="button" class="btn btn-info btn-sm" onclick="showModal('."'$dataPenagihan->faktur_code'".')" data-toggle="tooltip" data-placement="top" title="show coa"><span class="fa fa-money"></span></button> &nbsp;'.
                    '<a href="'.url('admin/tagihan-detail/'.$dataPenagihan->faktur_code).'" data-toggle="tooltip" data-placement="top" title="Detail" class="btn btn-primary  btn-sm"><i class="fa fa-edit"></i></a>'.
                    '<a style="margin-left: 5px" href="'.url('admin/report-faktur/'.$dataPenagihan->faktur_code).'" class="btn btn-warning  btn-sm" data-toggle="tooltip" data-placement="top" title="Cetak" id="print_'.$i.'" onclick="hide('.$i++.')"><i class="fa fa-file-pdf-o"></i></a>';

                }
                else{
                    return
                    '<button type="button" class="btn btn-info btn-sm" onclick="showModal('."'$dataPenagihan->faktur_code'".')" data-toggle="tooltip" data-placement="top" title="show coa"><span class="fa fa-money"></span></button> &nbsp;'.
                    '<a href="'.url('admin/tagihan-detail/'.$dataPenagihan->faktur_code).'" data-toggle="tooltip" data-placement="top" title="Detail" class="btn btn-primary pull-left btn-sm"><i class="fa fa-edit"></i></a>';
                }
            }
            })
            ->editColumn('faktur_code', function($dataPenagihan){
                return '<a href="'. url('admin/tagihan-detail/'.$dataPenagihan->faktur_code) .'">'.$dataPenagihan->faktur_code.'</a>';
            })
            ->editColumn('total', function($dataPenagihan){
                return 'Rp. '.number_format($dataPenagihan->total,0,'.','.');
            })

            ->addIndexColumn()
            ->rawColumns(['action','faktur_code','total'])
            ->make(true);
    }

    public function apiTunggupembayaran()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);

        $dataPembayaran = DB::table('t_pembayaran')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
            ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
            ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
            ->where('t_pembayaran.status','in approval')
            ->orderBy('t_pembayaran.id','desc')
            ->get();

        foreach ($dataPembayaran as $raw_data) {
            $total = DB::table('d_pembayaran')
                ->where('pembayaran_code',$raw_data->pembayaran_code)
                ->sum('total');

            $raw_data->total = $total;
        }
//             $i=0;
// $roleSuperAdmin = \App\Models\MRoleModel::where('name', 'Super Admin')->first();
        return Datatables::of($dataPembayaran)
        ->addColumn('action', function ($dataPembayaran)  {
            return '<a href="'. url('admin/transaksi-pembayaran-setujui/'.$dataPembayaran->pembayaran_code.'/'.auth()->user()->id) .'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Setujui"><i class="fa fa-check"></i></a> &nbsp;'.
            '<a href="'. url('admin/transaksi-pembayaran-tolak/'.$dataPembayaran->pembayaran_code.'/'.auth()->user()->id) .'" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Tolak"><i class="fa fa-close"></i></td>';
            })
            ->editColumn('metode', function($dataPembayaran){
                return ucfirst($dataPembayaran->metode);
            })
            ->editColumn('pembayaran_code', function($dataPembayaran){
                return '<a href="'. url('admin/pembayaran-detail/'.$dataPembayaran->pembayaran_code).'">'.$dataPembayaran->pembayaran_code.'</a>';
            })
            ->editColumn('payment_date', function($dataPembayaran){
                return date('d-m-Y',strtotime($dataPembayaran->payment_date));
            })
            ->editColumn('total', function($dataPembayaran){
                return 'Rp. '.number_format($dataPembayaran->total,0,'.','.');
            })

            ->addIndexColumn()
            ->rawColumns(['action','metode','total','payment_date','pembayaran_code'])
            ->make(true);
    }

    public function apiListpembayaran()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);

        $dataPembayaran = DB::table('t_pembayaran')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
            ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
            ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
            ->where('t_pembayaran.status','!=','in approval')
            ->orderBy('t_pembayaran.id','desc')
            ->get();

        foreach ($dataPembayaran as $raw_data) {
        	$total = DB::table('d_pembayaran')
        		->where('pembayaran_code',$raw_data->pembayaran_code)
        		->sum('total');

        	$raw_data->total = $total;
        }
//             $i=0;
// $roleSuperAdmin = \App\Models\MRoleModel::where('name', 'Super Admin')->first();
        return Datatables::of($dataPembayaran)
        ->addColumn('action', function ($dataPembayaran) {
            if($dataPembayaran->status_pembayaran == 'approved'){
            return '<table id="tabel-in-opsi">'.
                '<tr>'.
                    '<td>'.
                    '<button type="button" class="btn btn-info btn-sm" onclick="showModal('."'$dataPembayaran->pembayaran_code'".')" data-toggle="tooltip" data-placement="top" title="show coa"><span class="fa fa-money"></span></button>&nbsp;'.
                    '<a href="'.url('admin/pembayaran-detail/'.$dataPembayaran->pembayaran_code).'" data-toggle="tooltip" data-placement="top" title="Detail" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>&nbsp;'.
                    '<a href="'. url('admin/pembayaran/cancel/'.$dataPembayaran->pembayaran_code) .'" data-toggle="tooltip" data-placement="top" title="Cancel '. $dataPembayaran->pembayaran_code .'"  class="btn btn-sm btn-danger"><span class="fa fa-times"></span></a>&nbsp;'.
                    '<a href="'.url('admin/report-kuitansi/'.$dataPembayaran->pembayaran_code).'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Kuitansi"><i class="fa fa-money"></i></a>';
                     '</td>'.
                '</tr>'.
            '</table>';
        }
        else{
            return
                '<button type="button" class="btn btn-info btn-sm" onclick="showModal('."'$dataPembayaran->pembayaran_code'".')" data-toggle="tooltip" data-placement="top" title="show coa"><span class="fa fa-money"></span></button>&nbsp;'
                .'<a href="'.url('admin/pembayaran-detail/'.$dataPembayaran->pembayaran_code).'" data-toggle="tooltip" data-placement="top" title="Detail" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>';
        }
    })
            ->editColumn('metode', function($dataPembayaran){
                return ucfirst($dataPembayaran->metode);
            })
            ->editColumn('pembayaran_code', function($dataPembayaran){
                return '<a href="'. url('admin/pembayaran-detail/'.$dataPembayaran->pembayaran_code).'">'.$dataPembayaran->pembayaran_code.'</a>';
            })
            ->editColumn('payment_date', function($dataPembayaran){
                return date('d-m-Y',strtotime($dataPembayaran->payment_date));
            })
            ->editColumn('total', function($dataPembayaran){
                return 'Rp. '.number_format($dataPembayaran->total,0,'.','.');
            })

            ->addIndexColumn()
            ->rawColumns(['action','metode','total','payment_date','pembayaran_code'])
            ->make(true);
    }
}
