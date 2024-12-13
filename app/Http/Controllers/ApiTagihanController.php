<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiTagihanController extends Controller
{
	public function listTagihan(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 20-11-2017
	    * Fungsi       : list tagihan
	    * Tipe         : update
	    */

		$return = [];
		$customer = $request->input("customer");

		$data = DB::table('t_faktur')
			->select('t_faktur.id','t_faktur.faktur_code', 't_faktur.sj_code','t_faktur.so_code','t_faktur.customer','t_faktur.sales','t_faktur.total','t_faktur.jumlah_yg_dibayarkan','t_faktur.status_payment','m_customer.name','m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_address','m_customer.main_office_phone_1','t_faktur.jatuh_tempo')
			->join("m_customer", "m_customer.id", "=" , "t_faktur.customer")
			->where('t_faktur.customer', $customer)
			//->where('t_faktur.status_payment', 'unpaid')
			->get();
		foreach ($data as $raw_data) {
			$raw_data->total_yg_ditagihkan = $raw_data->total - $raw_data->jumlah_yg_dibayarkan;

			$total_pembayaran_wait = DB::table('d_pembayaran')
                ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                ->where('d_pembayaran.faktur_code',$raw_data->faktur_code)
                ->where('t_pembayaran.status','in approval')
                ->sum('d_pembayaran.total');

            $raw_data->total_pembayaran_wait = $total_pembayaran_wait;
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List Penagihan not found';
		}

		return response($return);
	}

	public function listFaktur(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 22-01-2018
	    * Fungsi       : list faktur
	    * Tipe         : update
	    */

		$return = [];
		$customer 	= $request->input("customer");
		$date 		= $request->input("date");
		$page   	= $request->input("page");

		$query = DB::table('t_faktur');
		$query->select('t_faktur.id','t_faktur.faktur_code', 't_faktur.sj_code','t_faktur.so_code','t_faktur.customer','t_faktur.sales','t_faktur.total','t_faktur.jumlah_yg_dibayarkan','t_faktur.status_payment','m_customer.name','m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_address','m_customer.main_office_phone_1','t_faktur.created_at','t_faktur.jatuh_tempo');
		$query->join("m_customer", "m_customer.id", "=" , "t_faktur.customer");
		$query->where('t_faktur.customer', $customer);
		if ($date) {
            $query->whereDate('t_faktur.created_at', $date);
        }

		$take = 20;
  		$offset = $take*($page-1);
		$data = $query->skip($offset)->take($take)->get();

		foreach ($data as $raw_data) {
			$raw_data->total_yg_ditagihkan = $raw_data->total - $raw_data->jumlah_yg_dibayarkan;

            $total_pembayaran_wait = DB::table('d_pembayaran')
                ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                ->where('d_pembayaran.faktur_code',$raw_data->faktur_code)
                ->where('t_pembayaran.status','in approval')
                ->sum('d_pembayaran.total');

            $raw_data->total_pembayaran_wait = $total_pembayaran_wait;
		}

		$piutang = DB::table('t_faktur')
			->where('customer', $customer)
			->where('status_payment', 'unpaid')
			->sum('total');

		$piutang_dibayar = DB::table('t_pembayaran')
            ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
            ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
            ->where('t_pembayaran.customer', $customer)
            ->where('t_faktur.status_payment', 'unpaid')
            ->where('t_pembayaran.status', 'approved')
            ->sum('d_pembayaran.total');

        $piutang = $piutang - $piutang_dibayar;

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			$return['total_piutang'] = $piutang;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List Faktur not found';
			$return['total_piutang'] = $piutang;
		}

		return response($return);
	}

	public function pembayaran(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 03-01-2018
	    * Fungsi       : list tagihan
	    * Tipe         : update
	    */

		$return = [];
		$customer = $request->input("customer");
		$sales = $request->input("sales");
		$total = $request->input("total");
		$type = $request->input("type");
		$bank = $request->input("bank");
		$rekening_tujuan = $request->input("rekening_tujuan");
		$no_giro = $request->input("no_giro");
		$jatuh_tempo_giro = $request->input("jatuh_tempo_giro");
		$keterangan = $request->input("keterangan");
		$bukti_pembayaran = $request->input("bukti_pembayaran");

		$faktur_code = $request->input("faktur_code");
		$countFaktur = count($faktur_code);

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

        $pembayaran_code = 'NFWA'.$dataDate.$nol.$getLastCode;

        $data = [
				'pembayaran_code' => $pembayaran_code,
				//'faktur_code' => $faktur_code,
				'customer' => $customer,
				'sales' => $sales,
				//'total' => $total,
				'type' => $type,
				'user_receive' => $sales,
				'keterangan' => $keterangan,
	    	];

	    if ($bank > 0) {
	    	$data['bank'] = $bank;
	    	$data['rekening_tujuan'] = $rekening_tujuan;
	    }
	    if ($no_giro != null && $no_giro != '') {
	    	$data['no_giro'] = $no_giro;
	    }
	    if ($jatuh_tempo_giro != null && $jatuh_tempo_giro != '') {
	    	$data['jatuh_tempo_giro'] = $jatuh_tempo_giro;
	    }

	    if ($bukti_pembayaran != null && $bukti_pembayaran != "") {
        	$filename_path_bukti = date("ymdhis").$pembayaran_code.".jpg";
			$decoded_bukti=base64_decode($bukti_pembayaran);
			file_put_contents("upload/bukti-pembayaran/".$filename_path_bukti,$decoded_bukti);

			$data['bukti_pembayaran'] = $filename_path_bukti;
		}

	    $saldo = $total;

	    $insertheader = 1;
	    DB::beginTransaction();
	    try {
	    	for ($i=0; $i < $countFaktur; $i++) {
	        	if ($saldo > 0) {
	        		$detailpembayaran = [];
	        		$faktur = ($faktur_code[$i]["faktur"] != "") ? $faktur_code[$i]["faktur"] : null;

	        		$dataFaktur = DB::table('t_faktur')
			            ->where('faktur_code', $faktur)
			            ->first();

			        $start = strtotime(date('Y-m-d', strtotime($dataFaktur->created_at)));
			        $end = strtotime(date("Y-m-d"));
			        $usia_pembayaran = ceil(abs($end - $start) / 86400);

	                $waiting = DB::table('d_pembayaran')
	                    ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
	                    ->where('d_pembayaran.faktur_code',$faktur)
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

			        //insert header
	        		if ($insertheader == 1 && $belumdibayar > 0) {
	        			DB::table('t_pembayaran')->insert([$data]);
	        			$insertheader = 0;
	        		}

	        		//insert detail
			        if ($belumdibayar > 0) {
			        	$data2 =  DB::table('d_pembayaran')
				            ->insert([
				                'pembayaran_code' => $pembayaran_code,
				                'faktur_code' => $faktur,
				                'total' => $potong,
				                'usia_pembayaran' => $usia_pembayaran,
				            ]);
			        }
	        	}
	        }
	        DB::commit();

	        $return['success'] = true;
			$return['msgServer'] = 'Pembayaran berhasil dilakukan';
	    } catch (Exception $e) {
	    	$return['success'] = false;
			$return['msgServer'] = 'Pembayaran gagal dilakukan';
	    }

		return response($return);
	}

	public function listCustomerPembayaranReview(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 08-01-2018
	    * Fungsi       : list customer pembayaran
	    * Tipe         : update
	    */

		$return = [];
		$date = $request->input("tanggal");
		$sales   	= $request->input("sales");

		$query = DB::table('d_pembayaran');
		$query->join("t_pembayaran", "t_pembayaran.pembayaran_code", "=" , "d_pembayaran.pembayaran_code");
		$query->select('m_customer.id as customer_id','m_customer.code as customer_code','m_customer.name as customer_name');
		$query->join("m_customer", "m_customer.id", "=" , "t_pembayaran.customer");
		$query->leftjoin("m_bank", "m_bank.id", "=" , "t_pembayaran.bank");
		$query->leftjoin("m_user", "m_user.id", "=" , "t_pembayaran.sales");
		$query->join("m_metode_pembayaran", "m_metode_pembayaran.id", "=" , "t_pembayaran.type");

		if ($date) {
            $query->where('t_pembayaran.payment_date', $date);
        }
        if ($sales) {
            $query->where('t_pembayaran.sales', $sales);
        }
        $query->groupBy('customer_id','customer_code','customer_code');

		$data = $query->get();


		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List Pembayaran not found';
		}

		return response($return);
	}

	public function listPembayaran(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 22-01-2018
	    * Fungsi       : list pembayaran
	    * Tipe         : update
	    */

		$return = [];
		$customer 	= $request->input("customer");
		$date 		= $request->input("date");
		$faktur 	= $request->input("faktur");
		$sales   	= $request->input("sales");

		$query = DB::table('d_pembayaran');
		$query->join("t_pembayaran", "t_pembayaran.pembayaran_code", "=" , "d_pembayaran.pembayaran_code");
		$query->select('d_pembayaran.id','d_pembayaran.pembayaran_code','d_pembayaran.faktur_code','t_pembayaran.customer','t_pembayaran.sales','d_pembayaran.total','m_metode_pembayaran.metode as metode_pembayaran','m_bank.name as bank_name','t_pembayaran.payment_date','t_pembayaran.no_giro','t_pembayaran.jatuh_tempo_giro','t_pembayaran.keterangan','m_customer.name as customer_name','m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_address','m_customer.main_office_phone_1','m_user.name as sales_name','t_pembayaran.user_receive','t_pembayaran.user_confirm','t_pembayaran.confirm_date','t_pembayaran.status as status_pembayaran');
		$query->join("m_customer", "m_customer.id", "=" , "t_pembayaran.customer");
		$query->leftjoin("m_bank", "m_bank.id", "=" , "t_pembayaran.bank");
		$query->leftjoin("m_user", "m_user.id", "=" , "t_pembayaran.sales");
		$query->join("m_metode_pembayaran", "m_metode_pembayaran.id", "=" , "t_pembayaran.type");

		if ($customer != null ) {
			$query->where('t_pembayaran.customer', $customer);
		}
		if ($faktur != null ) {
			$query->where('d_pembayaran.faktur_code', $faktur);
		}
		if ($date) {
            $query->where('t_pembayaran.payment_date', $date);
        }
        if ($sales) {
            $query->where('t_pembayaran.sales', $sales);
        }
		$data = $query->get();

		foreach ($data as $raw_data) {
			$user_receive = DB::table('m_user')
				->where('id', $raw_data->user_receive)
				->pluck('name')
				->first();

			$user_confirm = DB::table('m_user')
				->where('id', $raw_data->user_confirm)
				->pluck('name')
				->first();

			$raw_data->user_receive = $user_receive;
			$raw_data->user_confirm = $user_confirm;
		}

		$piutang = 0;
		if ($customer != null ) {
			$piutang = DB::table('t_faktur')
				->where('customer', $customer)
				->where('status_payment', 'unpaid')
				->sum('total');

			$piutang_dibayar = DB::table('t_pembayaran')
                ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
                ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
                ->where('t_pembayaran.customer', $customer)
                ->where('t_faktur.status_payment', 'unpaid')
                ->where('t_pembayaran.status', 'approved')
                ->sum('d_pembayaran.total');

	        $piutang = $piutang - $piutang_dibayar;
		}


		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			$return['total_piutang'] = $piutang;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List Pembayaran not found';
			$return['total_piutang'] = $piutang;
		}

		return response($return);
	}

	public function detailPembayaran(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 03-01-2018
	    * Fungsi       : detail pembayaran
	    * Tipe         : create
	    */

		$return = [];
		$nopembayaran = $request->input("nopembayaran");

		$query = DB::table('t_pembayaran');
		$query->select('t_pembayaran.id','t_pembayaran.pembayaran_code', 't_pembayaran.customer','m_user.name as sales','t_pembayaran.status as status_pembayaran','t_pembayaran.confirm_date','m_metode_pembayaran.metode as metode_pembayaran','m_bank.name as bank_name','t_pembayaran.payment_date','t_pembayaran.no_giro','t_pembayaran.jatuh_tempo_giro','t_pembayaran.keterangan','m_customer.name as customer_name','m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_address','m_customer.main_office_phone_1','t_pembayaran.user_receive','t_pembayaran.user_confirm');
		$query->join("m_customer", "m_customer.id", "=" , "t_pembayaran.customer");
		$query->leftjoin("m_user", "m_user.id", "=" , "t_pembayaran.sales");
		$query->leftjoin("m_bank", "m_bank.id", "=" , "t_pembayaran.bank");
		$query->join("m_metode_pembayaran", "m_metode_pembayaran.id", "=" , "t_pembayaran.type");
		$query->where('t_pembayaran.pembayaran_code', $nopembayaran);
		$data = $query->first();

		if (count($data) > 0) {
			$user_receive = DB::table('m_user')
				->where('id', $data->user_receive)
				->pluck('name')
				->first();

			$user_confirm = DB::table('m_user')
				->where('id', $data->user_confirm)
				->pluck('name')
				->first();

			$total = DB::table('d_pembayaran')
				->where('pembayaran_code', $data->pembayaran_code)
				->sum('total');

			$data->total = $total;

			$data->user_receive = $user_receive;
			$data->user_confirm = $user_confirm;

			$detail = DB::table('d_pembayaran')
				->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
				->where('pembayaran_code', $data->pembayaran_code)
				->select('d_pembayaran.faktur_code','d_pembayaran.usia_pembayaran','d_pembayaran.total as pembayaran','t_faktur.so_code','t_faktur.sj_code','t_faktur.total as total_tagihan_faktur','t_faktur.jumlah_yg_dibayarkan as total_tagihan_faktur_sudah_dibayar')
				->get();

			$data->detail = $detail;
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List Pembayaran not found';
		}

		return response($return);
	}

	public function updateTagihan(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 07-11-2017
	    * Fungsi       : update tagihan
	    * Tipe         : tidak digunakan
	    */

		$return = [];
		$socode = $request->input("socode");
		$payment_total = $request->input("paymenttotal");

		//generate code
		$date_code = date('ymd');
		$getLastCode = DB::table('t_tagihan')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){
            $nol = "000000";
        }elseif(strlen($getLastCode) == 2){
            $nol = "00000";
        }elseif(strlen($getLastCode) == 3){
            $nol = "0000";
        }elseif(strlen($getLastCode) == 4){
            $nol = "000";
        }elseif(strlen($getLastCode) == 5){
            $nol = "00";
        }elseif(strlen($getLastCode) == 6){
            $nol = "0";
        }else{
            $nol = null;
        }
		$code = "TAGWA".$date_code.$nol.$getLastCode;

		DB::table('t_sales_order')
        	->where('so_code', $socode)
        	->update(['status_payment' => 'paid',
				    'payment_date' => date('ymd')]);

        DB::table('t_tagihan')->insert(
			    ['tagihan_code' => $code,
			    'so_code' => $socode,
			    'payment_date' => date('ymd'),
				'payment_total' => $payment_total]
			);

		$return['success'] = true;
		$return['msgServer'] = 'Penagihan done';

		return response($return);
	}

	public function listMetode(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 21-11-2017
	    * Fungsi       : list metode
	    * Tipe         : update
	    */

		$return = [];

		$data = DB::table('m_metode_pembayaran')->select('id','metode')->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List Metode Pembayaran not found';
		}

		return response($return);
	}

	public function listBank(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 21-11-2017
	    * Fungsi       : list metode
	    * Tipe         : update
	    */

		$return = [];

		$data = DB::table('m_bank')->select('id','name')->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List Bank not found';
		}

		return response($return);
	}

	public function listRekening(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 09-12-2017
	    * Fungsi       : list rekening tujuan
	    * Tipe         : update
	    */

		$return = [];

		$data = DB::table('m_rekening_tujuan')
            ->select('m_rekening_tujuan.id as id_rek','m_rekening_tujuan.no_rekening','m_rekening_tujuan.atas_nama','m_bank.name as bank_name')
            ->join('m_bank', 'm_bank.id', '=', 'm_rekening_tujuan.bank')
            ->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List Rekening not found';
		}

		return response($return);
	}
}