<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiKomplainController extends Controller
{
	public function searchCustomerKomplain(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 11-12-2017
	    * Fungsi       : search Customer
	    * Tipe         : update
	    */

	    $return = [];
		$name   = $request->input("name");
		$page   = $request->input("page");
		$sales  = $request->input("sales");

		$query = DB::table("m_customer");
		$query->select('m_customer.id','m_customer.code', DB::raw("'Customer' as tags"), DB::raw("m_customer.name as search"),'m_customer.code','m_customer.name','m_customer.type','m_customer.bentuk','m_customer.main_address as address','m_kelurahan_desa.name as kelurahan','m_kecamatan.name as kecamatan', DB::raw("CONCAT(m_kota_kab.type,' ',m_kota_kab.name) as kota_kab"),'m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.credit_limit','m_customer.credit_limit_days');
		$query->join("m_kelurahan_desa", "m_customer.main_kelurahan", "=" , "m_kelurahan_desa.id");
		$query->join("m_kecamatan", "m_kelurahan_desa.kecamatan", "=" , "m_kecamatan.id");
		$query->join("m_kota_kab", "m_kecamatan.kota_kab", "=" , "m_kota_kab.id");
		$query->join("m_wilayah_pembagian_sales", "m_wilayah_pembagian_sales.wilayah_sales", "=" , "m_customer.wilayah_sales");
		$query->where('m_customer.status', true);
        $query->where('m_wilayah_pembagian_sales.sales', $sales);
		$query->where(function ($query) use ($name) {
            $query->where('m_customer.name', 'ILIKE', '%' . $name . '%')
                  ->orwhere('m_kota_kab.name', 'ILIKE', '%' . $name . '%');
            });
		// $query->where('m_customer.name', 'ILIKE', '%' . $name . '%');
		// $query->orwhere('m_kota_kab.name', 'ILIKE', '%' . $name . '%');

		$query->orderBy("m_customer.code");
		$count = $query->count();

		$take = 20;
  		$offset = $take*($page-1);
		$data = $query->skip($offset)->take($take)->get();

		//customer baru
		$countpage = (int)ceil($count/$take);
		$lastarray = $count % $take;

		//dd($countPage);

		$piutang = 0;
		$oldest_piutang = 0;
		foreach ($data as $raw_data) {
			//total piutang
			$piutang = DB::table('t_faktur')
				->where('customer', $raw_data->id)
				->where('status_payment', 'unpaid')
				//->groupBy('d_sales_order.so_code')
				->sum('total');

			$data_credit_customer = DB::table('t_sales_order')
			    ->where('t_sales_order.customer', $raw_data->id)
			    ->where(function ($query) {
			        $query->where('t_sales_order.status_aprove','!=','closed')
			              ->Where('t_sales_order.status_aprove','!=','reject')
			              ->Where('t_sales_order.status_aprove','!=','cancel');
			        })
			    ->get();

			$credit_customer = 0;

			foreach ($data_credit_customer as $raw_data2) {
			    $dataDetailSo = DB::table('d_sales_order')->where('so_code',$raw_data2->so_code)->get();

			    //get-diskon-header-per-produk
			    $alltotaldetail = DB::table('d_sales_order')->where('so_code',$raw_data2->so_code)->sum('total');
			    $totalQtySo = DB::table('d_sales_order')->where('so_code',$raw_data2->so_code)->sum('qty');
			    $diskonHeader = $alltotaldetail - $raw_data2->grand_total;
			    $diskonHeaderPerItem = $diskonHeader / $totalQtySo;

			    foreach ($dataDetailSo as $raw_data3) {
			        //get-detail-total-so
			        $qty = $raw_data3->qty;
			        $sj_qty = $raw_data3->sj_qty;
			        $sisa_qty = $qty - $sj_qty;
			        $total = $raw_data3->total;

			        $total_detail = ( ($total / $qty) - $diskonHeaderPerItem ) * $sisa_qty;
			        $credit_customer = $credit_customer + $total_detail;
			    }
			}

			$credit_customer = (int)round($credit_customer);

	        $piutang_dibayar = DB::table('t_pembayaran')
                ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
                ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
                ->where('t_pembayaran.customer', $raw_data->id)
                ->where('t_faktur.status_payment', 'unpaid')
                ->where('t_pembayaran.status', 'approved')
                ->sum('d_pembayaran.total');

            $piutang = $piutang - $piutang_dibayar;

            $order_this_month = DB::table('t_sales_order')
				//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
				->where('t_sales_order.customer', $raw_data->id)
				->whereYear('so_date', '=', date('Y'))
              	->whereMonth('so_date', '=', date('m'))
				//->groupBy('d_sales_order.so_code')
				->sum('t_sales_order.grand_total');

			$order_last_month = DB::table('t_sales_order')
				//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
				->where('t_sales_order.customer', $raw_data->id)
				->whereYear('so_date', '=', date('Y'))
              	->whereMonth('so_date', '=', ((int)date('m'))-1)
				//->groupBy('d_sales_order.so_code')
				->sum('t_sales_order.grand_total');

			$last_order = DB::table('t_sales_order')
				->where('customer', $raw_data->id)
				->orderBy('so_date', 'desc')
				->first();

			$oldest_piutang = DB::table('t_faktur')
				->where('customer', $raw_data->id)
				->where('status_payment', 'unpaid')
				->orderBy('created_at', 'asc')
				->first();

			if ($raw_data->credit_limit == null) {
				$raw_data->credit_limit = 0;
			}

			if ($last_order) {
				$raw_data->last_order = date('Y-m-d',strtotime($last_order->so_date));
			}else{
				$raw_data->last_order = "0";
			}

			if ($oldest_piutang) {
				$raw_data->tgl_oldest_piutang = date('Y-m-d',strtotime($oldest_piutang->created_at));
				$raw_data->jml_oldest_piutang = $oldest_piutang->total - $oldest_piutang->jumlah_yg_dibayarkan;
				$raw_data->jatuh_tempo_oldest_piutang = date('Y-m-d',strtotime($oldest_piutang->jatuh_tempo));
			}else{
				$raw_data->tgl_oldest_piutang = "0";
				$raw_data->jml_oldest_piutang = 0;
				$raw_data->jatuh_tempo_oldest_piutang = "0";
			}

			$raw_data->piutang = $piutang;
			$raw_data->credit_customer = $credit_customer;
			$raw_data->sisa_credit_limit = $raw_data->credit_limit - $credit_customer - $piutang;
			$raw_data->order_this_month = $order_this_month;
			$raw_data->order_last_month = $order_last_month;
			$raw_data->toleransi = 14;

			$other_address = DB::table("m_alamat_customer")
				->select('m_alamat_customer.id','m_customer.name as customer','m_alamat_customer.name','m_alamat_customer.type','m_alamat_customer.address','m_alamat_customer.kelurahan','m_alamat_customer.geo_lat','m_alamat_customer.geo_lng')
				->join("m_customer", "m_alamat_customer.customer", "=" , "m_customer.id")
				->where('customer', $raw_data->id)
				->orderBy("id")
				->get();

			if (count($other_address) !== 0) {
				$raw_data->other_address = $other_address;
			}else{
				$raw_data->other_address = "0";
			}
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			$return['count'] = $count;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Customer not found';
		}

		return response($return);
	}

	public function searchSOKomplain(request $request)
	{
		$return = [];
		$customer   = $request->input("customer");
		$date 		= $request->input("date");
		$page   	= $request->input("page");

		$query = DB::table("t_sales_order");
		$query->select('so_code');
		$query->where('customer', $customer);
		$query->whereYear('so_date', '=', date('Y',strtotime($date)));
        $query->whereMonth('so_date', '=', date('m',strtotime($date)));
        $query->whereDay('so_date', '=', date('d',strtotime($date)));
		$query->orderBy("so_code");
		$count = $query->count();

		$take = 20;
  		$offset = $take*($page-1);
		$data = $query->skip($offset)->take($take)->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			$return['count'] = $count;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'SO not found';
		}

		return response($return);
	}

	public function searchSJKomplain(request $request)
	{
		$return = [];
		//$socode = $request->input("socode");
		$page   	= $request->input("page");
		$customer   = $request->input("customer");
		$date 		= $request->input("date");

		$query = DB::table("t_surat_jalan");
		$query->select('sj_code');
		//$query->where('so_code', $socode);
		$query->where('customer', $customer);
		$query->whereYear('t_surat_jalan.sj_date', date('Y',strtotime($date)));
		$query->whereMonth('t_surat_jalan.sj_date', date('m',strtotime($date)));
		$query->whereDay('t_surat_jalan.sj_date', date('d',strtotime($date)));
		$query->orderBy("sj_code");
		$data = $query->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'SJ not found';
		}

		return response($return);
	}

	public function searchProdukKomplain(request $request)
	{
		$return = [];
		$sjcode = $request->input("sjcode");
		//$date 	= $request->input("date");

		$query = DB::table("d_surat_jalan");
		$query->select('produk_id','code','qty_delivery','m_produk.name as produk_name');
		$query->join("m_produk", "m_produk.id", "=" , "d_surat_jalan.produk_id");
		$query->where('sj_code', $sjcode);
		// $query->whereYear('d_surat_jalan.created_at', date('Y',strtotime($date)));
  //       $query->whereMonth('d_surat_jalan.created_at', date('m',strtotime($date)));
  //       $query->whereDay('d_surat_jalan.created_at', date('d',strtotime($date)));
		$data = $query->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Produk not found';
		}

		return response($return);
	}

	public function createKomplain(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 11-12-2017
	    * Fungsi       : update komplain
	    * Tipe         : update
	    */
		$return = [];
		$customer = $request->input("customer");
		$sales = $request->input("sales");
		$so_code = $request->input("so_code");
		$sj_code = $request->input("sj_code");
		$type_komplain = $request->input("type_komplain");

		$detail = $request->input("detail");
		$countDetail = count($detail);

		if ($type_komplain == 'Retur Barang') {
			DB::table('t_komplain')
    		->insert([
    			'customer' => $customer,
			    'sales' => $sales,
			    'so_code' => $so_code,
			    'sj_code' => $sj_code,
			]);
		}else{
			DB::table('t_komplain')
    		->insert([
    			'customer' => $customer,
			    'sales' => $sales,
			    'so_code' => $so_code,
			    'sj_code' => $sj_code,
			]);
		}

		$dataKomplain = DB::table("t_komplain")
			->where('sales', $sales)
			->orderBy('id','desc')
			->first();

		for ($i=0; $i < $countDetail; $i++) {
        	$detailkomplain = [];

        	$produk_code = ($detail[$i]["produk_code"] != "") ? $detail[$i]["produk_code"] : null;
        	$qty = ($detail[$i]["qty"] != "") ? $detail[$i]["qty"] : null;
        	$base64_string_img = ($detail[$i]["photo"] != "") ? $detail[$i]["photo"] : null;
        	$keterangan = ($detail[$i]["keterangan"] != "") ? $detail[$i]["keterangan"] : null;

        	$photo = null;
        	if ($base64_string_img != null && $base64_string_img != "") {
	        	$filename_path_depan = md5(time().uniqid())."depan.jpg";
				$decoded_depan=base64_decode($base64_string_img);
				file_put_contents("upload/komplain-photo/".$filename_path_depan,$decoded_depan);
			    $photo = $filename_path_depan;
			}

			$detailkomplain = [
        		'id_komplain' => $dataKomplain->id,
			    'produk_code' => $produk_code,
				'qty' => $qty,
				'photo' => $photo,
				'keterangan' => $keterangan,
			];

			DB::table('d_komplain')->insert($detailkomplain);
        }

		// $produk_code = $request->input("produk_code");
		// $qty = $request->input("qty");
		// $keterangan = $request->input("keterangan");

		$return['success'] = true;
		$return['msgServer'] = "Komplain telah diterima";

		return response($return);
	}

	public function reviewKomplainCustomer(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 30-01-2018
	    * Fungsi       : list review komplain
	    * Tipe         : update
	    */

		$return = [];
		$page   	= $request->input("page");
		$sales   	= $request->input("sales");
		$month 		= $request->input("month");
		$year 		= $request->input("year");

		$query = DB::table('t_komplain')
			->select('m_customer.id as customer_id','m_customer.name as customer_name','m_customer.code as customer_code')
			->join("m_customer", "m_customer.id", "=" , "t_komplain.customer")
			->join("m_user", "m_user.id", "=" , "t_komplain.sales");
        $query->where('t_komplain.sales', $sales);
    	$query->whereYear('t_komplain.tanggal', $year);
    	$query->whereMonth('t_komplain.tanggal', $month);
		$query->groupBy('m_customer.id','m_customer.code','m_customer.name');
		$count = $query->count();

		//$take = 20;
  		//$offset = $take*($page-1);
		//$data = $query->skip($offset)->take($take)->get();
		$data = $query->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			//$return['count'] = $count;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Data Customer Komplain not found';
		}

		return response($return);
	}

	public function reviewKomplain(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 29-01-2018
	    * Fungsi       : list komplain
	    * Tipe         : update
	    */

		$return = [];
		$page   	= $request->input("page");
		$customer   = $request->input("customer");
		$sales   	= $request->input("sales");
		$month 		= $request->input("month");
		$year 		= $request->input("year");

		$query = DB::table('t_komplain')
			->select('t_komplain.id','m_customer.name as customer_name','m_customer.id as customer_id','m_user.name as sales_name','t_komplain.type_komplain','t_komplain.sj_code','t_komplain.keterangan','t_komplain.status_komplain','t_komplain.tanggal')
			->join("m_customer", "m_customer.id", "=" , "t_komplain.customer")
			->join("m_user", "m_user.id", "=" , "t_komplain.sales");
		if ($sales) {
            $query->where('t_komplain.sales', $sales);
        }
        if ($customer) {
            $query->where('t_komplain.customer', $customer);
        }

    	$query->whereYear('t_komplain.tanggal', $year);
    	$query->whereMonth('t_komplain.tanggal', $month);

		$count = $query->count();

		$take = 20;
  		$offset = $take*($page-1);
		$data = $query->skip($offset)->take($take)->get();

		foreach ($data as $raw_data) {
			$detail = DB::table('d_komplain')
				->select('produk_code','qty','keterangan','name')
				->join("m_produk", "m_produk.code", "=" , "d_komplain.produk_code")
				->where('d_komplain.id_komplain', $raw_data->id)
				->get();

			$raw_data->detail = $detail;
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			$return['count'] = $count;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Komplain not found';
		}

		return response($return);
	}

	public function reviewKomplainDetail(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 01-02-2018
	    * Fungsi       : detail komplain
	    * Tipe         : update
	    */

		$return = [];
		$no_komplain  = $request->input("no_komplain");

		$query = DB::table('t_komplain')
			->select('t_komplain.id','m_customer.name as customer_name','m_customer.id as customer_id','m_user.name as sales_name','t_komplain.type_komplain','t_komplain.sj_code','t_komplain.keterangan','t_komplain.status_komplain','t_komplain.tanggal')
			->join("m_customer", "m_customer.id", "=" , "t_komplain.customer")
			->join("m_user", "m_user.id", "=" , "t_komplain.sales");
    	$query->where('t_komplain.id', $no_komplain);

		$data = $query->get();

		foreach ($data as $raw_data) {
			$detail = DB::table('d_komplain')
				->select('produk_code','qty','keterangan','name')
				->join("m_produk", "m_produk.code", "=" , "d_komplain.produk_code")
				->where('d_komplain.id_komplain', $raw_data->id)
				->get();

			$raw_data->detail = $detail;
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Komplain not found';
		}

		return response($return);
	}
}