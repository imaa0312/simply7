<?php

namespace App\Http\Controllers;


use DB;
use Auth;
use Mail;
use App\Mail\OrderMail;
use App\Mail\PaymentConfirmMail;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Models\MStokProdukModel;

class ApiOrderController extends Controller
{
	public function listSalesOrder(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 20-12-2017
	    * Fungsi       : list sales order
	    * Tipe         : update
	    */

		$return = [];
		$page   	= $request->input("page");
		$customer   = $request->input("customer");
		$sales   	= $request->input("sales");
		$tanggal 	= $request->input("tanggal");
		$temp_code 	= $request->input("temp_code");

		if ($customer == ''){
			$query = DB::table('temp_t_sales_order')
				->select('temp_t_sales_order.id','temp_t_sales_order.so_code', 'temp_t_sales_order.sales', 'm_user.name as sales_name','temp_t_sales_order.customer_code','temp_m_customer.name as customer_name','temp_t_sales_order.so_date','temp_t_sales_order.sending_address','temp_t_sales_order.sending_date','temp_t_sales_order.diskon_header_potongan','temp_t_sales_order.diskon_header_persen','temp_t_sales_order.grand_total', DB::raw("'temp' as status_customer"))
				->join("temp_m_customer", "temp_m_customer.code", "=" , "temp_t_sales_order.customer_code")
				->join("m_user", "m_user.id", "=" , "temp_t_sales_order.sales");

	        $query->where('temp_m_customer.code', $temp_code);
	        $query->where('temp_t_sales_order.sales', $sales);

	        if ($tanggal) {
	            $query->whereYear('temp_t_sales_order.so_date', date('Y',strtotime($tanggal)));
	            $query->whereMonth('temp_t_sales_order.so_date', date('m',strtotime($tanggal)));
	            $query->whereDay('temp_t_sales_order.so_date', date('d',strtotime($tanggal)));
	        }

			$count = $query->count();

			$take = 20;
	  		$offset = $take*($page-1);
			$data = $query->skip($offset)->take($take)->get();
		}else{
			$query = DB::table('t_sales_order')
				->select('t_sales_order.id','t_sales_order.so_code', 't_sales_order.sales', 'm_user.name as sales_name','t_sales_order.customer','m_customer.name as customer_name','t_sales_order.atas_nama','t_sales_order.so_date','t_sales_order.sending_address','t_sales_order.sending_date','t_sales_order.status_aprove','t_sales_order.diskon_header_potongan','t_sales_order.diskon_header_persen','t_sales_order.grand_total', DB::raw("'fix' as status_customer"))
				->join("m_customer", "m_customer.id", "=" , "t_sales_order.customer")
				->join("m_user", "m_user.id", "=" , "t_sales_order.sales");

	        $query->where('m_customer.id', $customer);
	        $query->where('t_sales_order.sales', $sales);

	        if ($tanggal) {
	            $query->whereYear('t_sales_order.so_date', date('Y',strtotime($tanggal)));
	            $query->whereMonth('t_sales_order.so_date', date('m',strtotime($tanggal)));
	            $query->whereDay('t_sales_order.so_date', date('d',strtotime($tanggal)));
	        }

			$count = $query->count();

			$take = 20;
	  		$offset = $take*($page-1);
			$data = $query->skip($offset)->take($take)->get();

			foreach ($data as $raw_data) {
				$atas_nama_name = DB::table('m_customer')
					->where('id',$raw_data->atas_nama)
					->pluck('name')
					->first();
				$raw_data->atas_nama_name = $atas_nama_name;
			}
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			$return['count'] = $count;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Order not found';
		}

		return response($return);
	}

	public function listCustomerSOReview(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 08-01-2018
	    * Fungsi       : list customer by sales order
	    * Tipe         : update
	    */

		$return = [];
		$sales   	= $request->input("sales");
		$tanggal 	= $request->input("tanggal");

		$query = DB::table('t_sales_order')
			->select('m_customer.id as customer_id','m_customer.code as customer_code','m_customer.name as customer_name')
			->join("m_customer", "m_customer.id", "=" , "t_sales_order.customer")
			->join("m_user", "m_user.id", "=" , "t_sales_order.sales");

        $query->where('t_sales_order.sales', $sales);

        if ($tanggal) {
            $query->whereYear('t_sales_order.so_date', date('Y',strtotime($tanggal)));
            $query->whereMonth('t_sales_order.so_date', date('m',strtotime($tanggal)));
            $query->whereDay('t_sales_order.so_date', date('d',strtotime($tanggal)));
        }
        $query->groupBy('customer_id','customer_code','customer_name');

		$data = $query->get();

		foreach ($data as $raw_data) {
			$total_order = DB::table('t_sales_order')
				->select('m_customer.id as customer_id','m_customer.code as customer_code','m_customer.name as customer_name')
				->where('customer', $raw_data->customer_id)
				->whereYear('t_sales_order.so_date', date('Y',strtotime($tanggal)))
            	->whereMonth('t_sales_order.so_date', date('m',strtotime($tanggal)))
            	->whereDay('t_sales_order.so_date', date('d',strtotime($tanggal)))
            	->sum('grand_total');
            $raw_data->total_order = $total_order;
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Customer not found';
		}

		return response($return);
	}

	public function listSOReview(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 17-01-2018
	    * Fungsi       : list sales order
	    * Tipe         : update
	    */

		$return = [];
		$page   	= $request->input("page");
		$sales   	= $request->input("sales");
		$tanggal 	= $request->input("tanggal");
		$month 		= $request->input("month");
		$year 		= $request->input("year");
		$customer 	= $request->input("customer");

		$query = DB::table('t_sales_order')
			->select('t_sales_order.id','t_sales_order.so_code', 't_sales_order.sales', 'm_user.name as sales_name','t_sales_order.customer','m_customer.name as customer_name','t_sales_order.atas_nama','t_sales_order.so_date','t_sales_order.sending_address','t_sales_order.sending_date','t_sales_order.status_aprove','t_sales_order.diskon_header_potongan','t_sales_order.diskon_header_persen','t_sales_order.grand_total', DB::raw("'fix' as status_customer"))
			->join("m_customer", "m_customer.id", "=" , "t_sales_order.customer")
			->join("m_user", "m_user.id", "=" , "t_sales_order.sales");

        $query->where('t_sales_order.sales', $sales);

        if ($month && $year) {
        	$query->whereYear('t_sales_order.so_date', $year);
        	$query->whereMonth('t_sales_order.so_date', $month);
        }

        if ($tanggal) {
			$query->whereYear('t_sales_order.so_date', date('Y',strtotime($tanggal)));
            $query->whereMonth('t_sales_order.so_date', date('m',strtotime($tanggal)));
            $query->whereDay('t_sales_order.so_date', date('d',strtotime($tanggal)));
        }
        if ($customer) {
        	$query->where('m_customer.id', $customer);
        }

		$count = $query->count();

		$take = 20;
  		$offset = $take*($page-1);
		$data = $query->skip($offset)->take($take)->get();

		foreach ($data as $raw_data) {
			$atas_nama_name = DB::table('m_customer')
				->where('id',$raw_data->atas_nama)
				->pluck('name')
				->first();
			$raw_data->atas_nama_name = $atas_nama_name;
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			$return['count'] = $count;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Order not found';
		}

		return response($return);
	}

	public function listSOReviewMonth(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 10-01-2018
	    * Fungsi       : list sales order this month
	    * Tipe         : update
	    */

		$return 	= [];
		$page   	= $request->input("page");
		$sales   	= $request->input("sales");
		$type 		= $request->input("type");

		if ($type == 'this') {
			$tanggal = date('Y-m-d');
		}elseif($type == 'last'){
			$tanggal = date('Y-m-d',strtotime(' -1 month'));
		}

		$query = DB::table('t_sales_order')
			->select('t_sales_order.id','t_sales_order.so_code', 't_sales_order.sales', 'm_user.name as sales_name','t_sales_order.customer','m_customer.name as customer_name','t_sales_order.atas_nama','t_sales_order.so_date','t_sales_order.sending_address','t_sales_order.sending_date','t_sales_order.status_aprove','t_sales_order.diskon_header_potongan','t_sales_order.diskon_header_persen','t_sales_order.grand_total', DB::raw("'fix' as status_customer"))
			->join("m_customer", "m_customer.id", "=" , "t_sales_order.customer")
			->join("m_user", "m_user.id", "=" , "t_sales_order.sales");

        $query->where('t_sales_order.sales', $sales);

        if ($tanggal) {
            $query->whereYear('t_sales_order.so_date', date('Y',strtotime($tanggal)));
            $query->whereMonth('t_sales_order.so_date', date('m',strtotime($tanggal)));
        }

        $query->orderBy('t_sales_order.created_at', 'DESC');

		$count = $query->count();

		$take = 20;
  		$offset = $take*($page-1);
		$data = $query->skip($offset)->take($take)->get();

		$point_sales = DB::table('m_point_sales')
			->where('sales', $sales)
			->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->whereDay('created_at', date('d'))
			->sum('point');

		foreach ($data as $raw_data) {
			$atas_nama_name = DB::table('m_customer')
				->where('id',$raw_data->atas_nama)
				->pluck('name')
				->first();
			$raw_data->atas_nama_name = $atas_nama_name;
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
			$return['count'] = $count;
			$return['point_hari_ini'] = $point_sales;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Order not found';
			$return['point_hari_ini'] = $point_sales;
		}

		return response($return);
	}

	public function detailSalesOrder(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 29-12-2017
	    * Fungsi       : list sales order
	    * Tipe         : update
	    */

		$return = [];
		$socode   			= $request->input("socode");
		$status_customer   	= $request->input("status_customer");

		if ($status_customer == 'fix') {
			$data = DB::table('t_sales_order')
				->select('t_sales_order.id','t_sales_order.so_code', 't_sales_order.sales', 'm_user.name as sales_name','t_sales_order.customer','m_customer.name as customer_name','t_sales_order.atas_nama', DB::raw("'fix' as status_customer"),'t_sales_order.so_date','t_sales_order.sending_address','t_sales_order.sending_date','t_sales_order.status_aprove','m_customer.name','m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_address','m_customer.main_office_phone_1','m_customer.credit_limit','t_sales_order.diskon_header_potongan','t_sales_order.diskon_header_persen','t_sales_order.grand_total')
				->join("m_customer", "m_customer.id", "=" , "t_sales_order.customer")
				->join("m_user", "m_user.id", "=" , "t_sales_order.sales")
				->where('t_sales_order.so_code', $socode)
				->first();

			if (count($data) !== 0) {
				$atas_nama_name = DB::table('m_customer')
					->where('id',$data->atas_nama)
					->pluck('name')
					->first();
				$data->atas_nama_name = $atas_nama_name;

				$gt_sebelum_diskon = DB::table('d_sales_order')
					->where('so_code', $data->so_code)
					->sum('total');
				$data->gt_sebelum_diskon = $gt_sebelum_diskon;

				$detail_so = DB::table('d_sales_order')
					->select('d_sales_order.*','m_produk.name as produk_name')
					->join("m_produk", "m_produk.id", "=" , "d_sales_order.produk")
					->where('d_sales_order.so_code', $data->so_code)
					->get();
				$data->detail_so = $detail_so;

				//total piutang
				$piutang = DB::table('t_faktur')
					->where('customer', $data->customer)
					->where('status_payment', 'unpaid')
					->sum('total');

				$data_credit_customer = DB::table('t_sales_order')
				    ->where('t_sales_order.customer', $data->customer)
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
	                ->where('t_pembayaran.customer', $data->customer)
	                ->where('t_faktur.status_payment', 'unpaid')
	                ->where('t_pembayaran.status', 'approved')
	                ->sum('d_pembayaran.total');

	            $piutang = $piutang - $piutang_dibayar;

	            $order_this_month = DB::table('t_sales_order')
					//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
					->where('t_sales_order.customer', $data->customer)
					->whereYear('so_date', '=', date('Y'))
	              	->whereMonth('so_date', '=', date('m'))
					//->groupBy('d_sales_order.so_code')
					->sum('t_sales_order.grand_total');

				$order_last_month = DB::table('t_sales_order')
					//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
					->where('t_sales_order.customer', $data->customer)
					->whereYear('so_date', '=', date('Y'))
	              	->whereMonth('so_date', '=', ((int)date('m'))-1)
					//->groupBy('d_sales_order.so_code')
					->sum('t_sales_order.grand_total');

				$last_order = DB::table('t_sales_order')
					->where('customer', $data->customer)
					->orderBy('so_date', 'desc')
					->first();

				$oldest_piutang = DB::table('t_faktur')
					->where('customer', $data->customer)
					->where('status_payment', 'unpaid')
					->orderBy('created_at', 'asc')
					->first();

				if ($data->credit_limit == null) {
					$data->credit_limit = 0;
				}

				if ($last_order) {
					$data->last_order = date('Y-m-d',strtotime($last_order->so_date));
				}else{
					$data->last_order = "0";
				}

				if ($oldest_piutang) {
					$data->tgl_oldest_piutang = date('Y-m-d',strtotime($oldest_piutang->created_at));
					$data->jml_oldest_piutang = $oldest_piutang->total - $oldest_piutang->jumlah_yg_dibayarkan;
					$data->jatuh_tempo_oldest_piutang = date('Y-m-d',strtotime($oldest_piutang->jatuh_tempo));
				}else{
					$data->tgl_oldest_piutang = "0";
					$data->jml_oldest_piutang = 0;
					$data->jatuh_tempo_oldest_piutang = "0";
				}

				$data->piutang = $piutang;
				$data->credit_customer = $credit_customer;
				$data->sisa_credit_limit = $data->credit_limit - $credit_customer - $piutang;
				$data->order_this_month = $order_this_month;
				$data->order_last_month = $order_last_month;

				$other_address = DB::table("m_alamat_customer")
					->select('m_alamat_customer.id','m_customer.name as customer','m_alamat_customer.name','m_alamat_customer.type','m_alamat_customer.address','m_alamat_customer.kelurahan','m_alamat_customer.geo_lat','m_alamat_customer.geo_lng')
					->join("m_customer", "m_alamat_customer.customer", "=" , "m_customer.id")
					->where('customer', $data->customer)
					->orderBy("id")
					->get();

				if (count($other_address) !== 0) {
					$data->other_address = $other_address;
				}else{
					$data->other_address = "0";
				}

				$return['success'] = true;
				$return['msgServer'] = $data;
			}else{
				$return['success'] = false;
				$return['msgServer'] = 'Order not found';
			}
		}elseif($status_customer == 'temp'){
			$data = DB::table('temp_t_sales_order')
				->select('temp_t_sales_order.id','temp_t_sales_order.so_code', 'temp_t_sales_order.sales', 'm_user.name as sales_name','temp_t_sales_order.customer_code','temp_m_customer.code as customer_code','temp_m_customer.name as customer_name','temp_t_sales_order.so_date','temp_t_sales_order.sending_address','temp_t_sales_order.sending_date','temp_t_sales_order.diskon_header_potongan','temp_t_sales_order.diskon_header_persen','temp_t_sales_order.grand_total', DB::raw("'temp' as status_customer"))
				->join("temp_m_customer", "temp_m_customer.code", "=" , "temp_t_sales_order.customer_code")
				->join("m_user", "m_user.id", "=" , "temp_t_sales_order.sales")
				->where('temp_t_sales_order.so_code', $socode)
				->first();

			if (count($data) !== 0) {
				$gt_sebelum_diskon = DB::table('temp_d_sales_order')
					->where('so_code', $data->so_code)
					->sum('total');
				$data->gt_sebelum_diskon = $gt_sebelum_diskon;

				// $detail_so = DB::table('temp_d_sales_order')
				// 	->select('*')
				// 	->where('so_code', $data->so_code)
				// 	->get();
				// $data->detail_so = $detail_so;

				$detail_so = DB::table('temp_d_sales_order')
					->select('temp_d_sales_order.*','m_produk.name as produk_name')
					->join("m_produk", "m_produk.id", "=" , "temp_d_sales_order.produk")
					->where('temp_d_sales_order.so_code', $data->so_code)
					->get();
				$data->detail_so = $detail_so;

				$data->credit_limit = 0;
				$data->last_order = "0";
				$data->tgl_oldest_piutang = "0";
				$data->jml_oldest_piutang = "0";
				$data->piutang = 0;
				$data->credit_customer = 0;
				$data->sisa_credit_limit = 0;
				$data->order_this_month = 0;
				$data->order_last_month = 0;
				$data->other_address = "0";

				$return['success'] = true;
				$return['msgServer'] = $data;
			}else{
				$return['success'] = false;
				$return['msgServer'] = 'Order not found';
			}
		}


		return response($return);
	}

	public function orderSales(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 10-01-2018
	    * Fungsi       : order
	    * Tipe         : update
	    */
		$return = [];

		$sales = $request->input("sales");
		$customer = $request->input("customer");
		$atas_nama = $request->input("atas_nama");
		$sending_address = $request->input("sendingaddress");
		$sending_date = $request->input("sendingdate");
		$customercode = $request->input("temp_code");
		$diskon_header_potongan = $request->input("diskon_header_potongan");
		$diskon_header_persen = $request->input("diskon_header_persen");
		$grand_total = $request->input("grand_total");

		$detail = $request->input("detail");
		$countDetail = count($detail);

		$cekgagal = 0;

		if ($customer == '') {
			//new customer
			//generate code
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	        $random_string_length = 10;
	        $string = '';
	        $max = strlen($characters) - 1;
	        for ($i = 0; $i < $random_string_length; $i++) {
	            $string .= $characters[random_int(0, $max-1)];
	        }

			$code = strtoupper($string);
			$sending_address = 0;

			$dataCustomer = DB::table('temp_m_customer')
	        	->where('code', $customercode)
	        	->first();

	        if (count($dataCustomer) !== 0) {
	        	//insert sales order
				$dataorder = [
					"so_code"          		=> $code,
			        "customer_code"     	=> $customercode,
			        "sales"         		=> $sales,
			        "sending_address"		=> $dataCustomer->main_address,
			        "sending_date"			=> $sending_date,
			        "diskon_header_potongan"		=> $diskon_header_potongan,
			        "diskon_header_persen"	=> $diskon_header_persen,
			        "grand_total"			=> $grand_total,
			        "user_receive"			=> $sales,
			        "user_input"			=> $sales,
		        ];

		        DB::beginTransaction();
		    	try {
		    		DB::table('temp_t_sales_order')->insert($dataorder);

			        $grand_total = 0;
			        //insert detail sales order
			        for ($i=0; $i < $countDetail; $i++) {
			        	$detailbooking = [];

			        	$produkid = ($detail[$i]["produkid"] != "") ? $detail[$i]["produkid"] : null;
			        	$produkqty = ($detail[$i]["produkqty"] != "") ? $detail[$i]["produkqty"] : null;
			        	$customerprice = ($detail[$i]["customerprice"] != "") ? $detail[$i]["customerprice"] : null;
			        	$totalprice = ($detail[$i]["totalprice"] != "") ? $detail[$i]["totalprice"] : null;
			        	$diskonpotongan = ($detail[$i]["diskonpotongan"] != "") ? $detail[$i]["diskonpotongan"] : null;
			        	$diskonpersen = ($detail[$i]["diskonpersen"] != "") ? $detail[$i]["diskonpersen"] : null;
			        	$markup = ($detail[$i]["markup"] != "") ? $detail[$i]["markup"] : null;
			        	$markuppersen = ($detail[$i]["markuppersen"] != "") ? $detail[$i]["markuppersen"] : null;
			        	$freeqty = ($detail[$i]["freeqty"] != "") ? $detail[$i]["freeqty"] : null;

						$detailbooking = [
			        		'so_code' => $code,
						    'produk' => $produkid,
							'qty' => $produkqty,
							'customer_price' => $customerprice,
							'diskon_potongan' => $diskonpotongan,
							'diskon_persen' => $diskonpersen,
							'markup' => $markup,
							'markup_persen' => $markuppersen,
							'free_qty' => $freeqty,
							'total' => $totalprice
						];

						$grand_total = $grand_total + $totalprice;

						DB::table('temp_d_sales_order')->insert($detailbooking);
						DB::commit();
			        }
		    	} catch (Exception $e) {
		    		DB::rollback();
		            $cekgagal = 1;
		    	}
	        }else{
	        	$cekgagal = 1;
	        }

		}else{
			//generate code
			$date_code = date('ym');
			$getLastCode = DB::table('t_sales_order')
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
			$code = "SOWA".$date_code.$nol.$getLastCode;

			$dataCustomer = DB::table('m_customer')
	        	->where('id', $customer)
	        	->first();

	        if ($sending_address == 0) {
	        	$sending_address = $dataCustomer->main_address;
	        }elseif($sending_address > 0){
	        	$dataAlamat = DB::table('m_alamat_customer')
		        	->where('id', $sending_address)
		        	->where('customer', $customer)
		        	->first();

	        	$sending_address = $dataAlamat->address;
	        }

			//insert sales order
			$dataorder = [
				"so_code"          	=> $code,
		        "customer"      	=> $customer,
		        "atas_nama"      	=> $atas_nama,
		        "sales"         	=> $sales,
		        "sending_address"	=> $sending_address,
		        "sending_date"		=> $sending_date,
		        "diskon_header_potongan"		=> $diskon_header_potongan,
			    "diskon_header_persen"		=> $diskon_header_persen,
		        "grand_total"		=> $grand_total,
		        "user_receive"		=> $sales,
		        "user_input"		=> $sales,
		        "top_hari"			=> $dataCustomer->credit_limit_days,
			    "top_toleransi"		=> 14,
			    "gudang"			=> $dataCustomer->gudang,
	        ];

		    DB::beginTransaction();
		    try {
		    	DB::table('t_sales_order')->insert($dataorder);

		        $data = DB::table('t_sales_order')
		        	->select('id','so_code', 'sales')
		        	->where('sales', $sales)
		        	->orderBy('id', 'desc')
		        	->first();

		        $grand_total = 0;
		        //insert detail sales order
		        for ($i=0; $i < $countDetail; $i++) {
		        	$detailbooking = [];

		        	$produkid = ($detail[$i]["produkid"] != "") ? $detail[$i]["produkid"] : null;
		        	$produkqty = ($detail[$i]["produkqty"] != "") ? $detail[$i]["produkqty"] : null;
		        	$customerprice = ($detail[$i]["customerprice"] != "") ? $detail[$i]["customerprice"] : null;
		        	$totalprice = ($detail[$i]["totalprice"] != "") ? $detail[$i]["totalprice"] : null;
		        	$diskonpotongan = ($detail[$i]["diskonpotongan"] != "") ? $detail[$i]["diskonpotongan"] : null;
		        	$diskonpersen = ($detail[$i]["diskonpersen"] != "") ? $detail[$i]["diskonpersen"] : null;
		        	$markup = ($detail[$i]["markup"] != "") ? $detail[$i]["markup"] : null;
		        	$markuppersen = ($detail[$i]["markuppersen"] != "") ? $detail[$i]["markuppersen"] : null;
			        $freeqty = ($detail[$i]["freeqty"] != "") ? $detail[$i]["freeqty"] : null;

		        	$detailbooking = [
		        		'so_code' 			=> $data->so_code,
					    'produk' 			=> $produkid,
						'qty' 				=> $produkqty,
						'customer_price' 	=> $customerprice,
						'diskon_potongan' 	=> $diskonpotongan,
						'diskon_persen' 	=> $diskonpersen,
						'markup' 			=> $markup,
						'markup_persen' 	=> $markuppersen,
						'free_qty' 			=> $freeqty,
						'total' 			=> $totalprice
					];

					$grand_total = $grand_total + $totalprice;

					DB::table('d_sales_order')->insert($detailbooking);
					DB::commit();
	        	}
		    } catch (Exception $e) {
		    	DB::rollback();
		        $cekgagal = 1;
		    }

		}

		if ($cekgagal == 0) {
			$return['success'] = true;
			$return['msgServer'] = "Order success.";
		}else{
			$return['success'] = false;
			$return['msgServer'] = "Order gagal.";
		}

		return response($return);
	}

	public function statusUpdateSalesOrder(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 11-12-2017
	    * Fungsi       : ganti status
	    * Tipe         : update
	    */
		$socode = $request->input("socode");

		$cekSO = DB::table('t_sales_order')
	          ->where('so_code',$socode)
	          ->where('status_aprove','in process')
	          ->get();

	    if (count($cekSO) > 0) {
	    	DB::table('t_sales_order')
				->where('so_code',$socode)
				->update(['status_aprove' => 'in edit']);

			$return['success'] = true;
			$return['msgServer'] = "Status update success.";
	    }else{
	    	$return['success'] = false;
			$return['msgServer'] = "Status update gagal.";
	    }

		return response($return);
	}

	public function updateSalesOrder(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 20-12-2017
	    * Fungsi       : order
	    * Tipe         : update
	    */
		$return = [];

		$so_code = $request->input("so_code");
		$atas_nama = $request->input("atas_nama");
		$sending_address = $request->input("sendingaddress");
		$sending_date = $request->input("sendingdate");
		$customercode = $request->input("temp_code");
		$diskon_header_potongan = $request->input("diskon_header_potongan");
		$diskon_header_persen = $request->input("diskon_header_persen");
		$grand_total = $request->input("grand_total");

		$detail = $request->input("detail");
		$countDetail = count($detail);

		$status_customer   	= $request->input("status_customer");

		if ($status_customer == 'fix') {

			$dataSO = DB::table('t_sales_order')
	        	->where('so_code', $so_code)
	        	->first();

			$dataCustomer = DB::table('m_customer')
	        	->where('id', $dataSO->customer)
	        	->first();

	        if ($sending_address == 0) {
	        	$sending_address = $dataCustomer->main_address;
	        }elseif($sending_address > 0){
	        	$dataAlamat = DB::table('m_alamat_customer')
		        	->where('id', $sending_address)
		        	->where('customer', $customer)
		        	->first();

	        	$sending_address = $dataAlamat->address;
	        }

	        DB::table('t_sales_order')
		          ->where('so_code',$so_code)
		          ->update([
				        "atas_nama"      	=> $atas_nama,
				        "sending_address"	=> $sending_address,
				        "sending_date"		=> $sending_date,
				        "diskon_header_potongan"		=> $diskon_header_potongan,
			    		"diskon_header_persen"		=> $diskon_header_persen,
				        "grand_total"		=> $grand_total,
				        "status_aprove" 	=> "in process"
			        ]);

		    DB::table('d_sales_order')->where('so_code', $so_code)->delete();

		    for ($i=0; $i < $countDetail; $i++) {
		    	$detailbooking = [];

		    	$produkid = ($detail[$i]["produkid"] != "") ? $detail[$i]["produkid"] : null;
		    	$produkqty = ($detail[$i]["produkqty"] != "") ? $detail[$i]["produkqty"] : null;
		    	$customerprice = ($detail[$i]["customerprice"] != "") ? $detail[$i]["customerprice"] : null;
		    	$totalprice = ($detail[$i]["totalprice"] != "") ? $detail[$i]["totalprice"] : null;
		    	$diskonpotongan = ($detail[$i]["diskonpotongan"] != "") ? $detail[$i]["diskonpotongan"] : null;
		    	$diskonpersen = ($detail[$i]["diskonpersen"] != "") ? $detail[$i]["diskonpersen"] : null;
		    	$markup = ($detail[$i]["markup"] != "") ? $detail[$i]["markup"] : null;

		    	$detailbooking = [
		    		'so_code' => $so_code,
				    'produk' => $produkid,
					'qty' => $produkqty,
					'customer_price' => $customerprice,
					'diskon_potongan' => $diskonpotongan,
					'diskon_persen' => $diskonpersen,
					'markup' => $markup,
					'total' => $totalprice
				];

				$grand_total = $grand_total + $totalprice;

				DB::table('d_sales_order')->insert($detailbooking);
		    }
		}elseif ($status_customer == 'temp') {
			$dataSO = DB::table('temp_t_sales_order')
	        	->where('so_code', $so_code)
	        	->first();

			$dataCustomer = DB::table('temp_m_customer')
	        	->where('code', $dataSO->customer_code)
	        	->first();

	        DB::table('temp_t_sales_order')
		          ->where('so_code',$so_code)
		          ->update([
				        "sending_address"	=> $sending_address,
				        "sending_date"		=> $sending_date,
				        "diskon_header_potongan"		=> $diskon_header_potongan,
			    		"diskon_header_persen"		=> $diskon_header_persen,
				        "grand_total"		=> $grand_total,
			        ]);

		    DB::table('temp_d_sales_order')->where('so_code', $so_code)->delete();

		    for ($i=0; $i < $countDetail; $i++) {
	        	$detailbooking = [];

	        	$produkid = ($detail[$i]["produkid"] != "") ? $detail[$i]["produkid"] : null;
	        	$produkqty = ($detail[$i]["produkqty"] != "") ? $detail[$i]["produkqty"] : null;
	        	$customerprice = ($detail[$i]["customerprice"] != "") ? $detail[$i]["customerprice"] : null;
	        	$totalprice = ($detail[$i]["totalprice"] != "") ? $detail[$i]["totalprice"] : null;
	        	$diskonpotongan = ($detail[$i]["diskonpotongan"] != "") ? $detail[$i]["diskonpotongan"] : null;
	        	$diskonpersen = ($detail[$i]["diskonpersen"] != "") ? $detail[$i]["diskonpersen"] : null;
	        	$markup = ($detail[$i]["markup"] != "") ? $detail[$i]["markup"] : null;

				$detailbooking = [
	        		'so_code' => $so_code,
				    'produk' => $produkid,
					'qty' => $produkqty,
					'customer_price' => $customerprice,
					'diskon_potongan' => $diskonpotongan,
					'diskon_persen' => $diskonpersen,
					'markup' => $markup,
					'total' => $totalprice
				];

				DB::table('temp_d_sales_order')->insert($detailbooking);
	        }
		}

		$return['success'] = true;
		$return['msgServer'] = "Ubah order success.";

		return response($return);
	}

	public function addOngkir(Request $request)
	{
		$ongkir = intval(preg_replace('/\,.*|[^0-9]./', '', $request->biaya_ekspedisi));


		DB::beginTransaction();
		try {
			$so = DB::table('t_sales_order')->where('so_code', $request->so_code)->first();
			$detail_so = DB::table('d_sales_order')
							->select('m_produk.*','d_sales_order.qty','d_sales_order.customer_price')
							->where('so_code', $request->so_code)
							->join("m_produk",'m_produk.id','d_sales_order.produk')
							->get();
			$metode_bayar = DB::table('m_metode_bayar')->where('id', $so->metode_bayar)->first();
			$customer = DB::table('m_customer')->where('id', $so->customer)->first();
            $user = DB::table('m_user')->where('id', $customer->id_user)->first();
			$date_now_1 = date("d F Y", strtotime($so->so_date));

			DB::table('t_sales_order')->where('so_code', $request->so_code)
				->update([
					'biaya_kirim'       => $ongkir,
					'ekspedisi_payment' => $ongkir,
					'ekspedisi'         => $request->name_ekspedisi,
					'alamat_customer'   => $request->address,
					'grand_total'       => DB::raw("grand_total + $ongkir"),
					'status_aprove'	    => 'pending',
					'updated_at'        => date('now'),
					'transfer_deadline' => \Carbon\Carbon::now()->addDays(1)->format('Y-m-d H:i:s')
				]);

			if($metode_bayar->nama_metode_bayar == "Transfer"){
				$deadline = \Carbon\Carbon::now()->addDays(1)->format('l, d F Y H:i:s');
			}else if($metode_bayar->nama_metode_bayar == "COD"){
				$deadline = "Pembayaran dapat dilakukan pada saat pengiriman";
			}else{
				$deadline = $customer->credit_limit_days.' Hari';
			}
            $harga = intval($so->grand_total) + $ongkir;

			Mail::to($user->email)->send(new \App\Mail\OrderMail($detail_so,$metode_bayar->nama_metode_bayar,$request->so_code,$request->name_ekspedisi,$customer->name,$date_now_1,$harga,$deadline,$so->id));

			DB::commit();

		} catch (\Exception $e) {
			throw $e;
			return response()->json($e, 500);
		}

		return response()->json('OK', 200);

	}

	public function review(Request $request, $socode = null)
	{
		if($request->isMethod('GET')){
			return  DB::table('d_sales_order')
						->select('d_sales_order.*','m_produk.name as name','m_produk.image as image')
						->join('m_produk','m_produk.id','d_sales_order.produk')
						->where('d_sales_order.so_code', $socode)
						->orderBy('d_sales_order.so_code','desc')
						->whereNotIn('d_sales_order.produk', function($query) use ($socode){
							$query->select('id_barang')->from('m_review')
								->where('so_code', $socode);
						})->get();
		}elseif($request->isMethod('POST')){

			DB::beginTransaction();
			try{
				$request->merge([
					'created_at' => date('Y-m-d H:i:s'),
				]);

				DB::table('m_review')->insert($request->except("_token"));

				DB::commit();

				return response()->json([
					"status" => 200,
					"description" => "OK"
				]);

			}catch(\Exception $e){
				DB::rollback();
				dd($e);
			}
		}
	}

	public function buktiPembayaran(Request $request)
	{
		$this->validate($request,[
			"atas_nama" => "required|min:6:max:30",
			"email_pengirim" => "required",
			"bank_pengirim" => "required|min:2|max:15",
			"no_rekening" => "required|numeric|min:6",
			"file_transfer" => "file|required|mimes:jpg,jpeg"
		]);

		$path = null;
		DB::beginTransaction();
		try {
			$customer = DB::table('m_customer')
                                    ->select('m_customer.name','m_user.email')
                                    ->join('m_user','m_user.id','m_customer.id_user')
                                    ->where('m_customer.id_user', $request->awef)
									->first();

            $bank = DB::table('m_rekening_tujuan')->select('m_rekening_tujuan.*','m_bank.name')->join('m_bank','m_bank.id','m_rekening_tujuan.bank')->where('m_rekening_tujuan.id',$request->bank_penerima)->first();

			$insertData =  $request->only([
				"so_code","atas_nama","email_pengirim","bank_pengirim","bank_penerima","no_rekening",
				"nominal_transfer"
			]);

			$path = $request->file("file_transfer")->store("bukti-pembayaran");

			$insertData = array_merge($insertData, [
				"bukti_transfer" => $path,
				"tanggal_transfer" => date("Y-m-d", strtotime($request->tanggal_transfer)),
				"status_pembayaran" => "pending",
				"created_at" => date("now"),
			]);


			DB::table("m_konfirmasi_pembayaran")->insert($insertData);
			DB::commit();

			Mail::to($customer->email)->send(new PaymentConfirmMail($customer,$bank,$request->so_code,$request->tanggal_transfer,$request->nominal_transfer));

			return response()->json([
				"status" => 200,
				"message" => "OK",
			]);
		} catch (\Exception $e) {
			dd($e);
			DB::rollback();
			// return response()->json([
			// 	"status" => 500,
			// 	"message" => "ERROR INSERT",
			// 	"exception" =>  $e
			// ],403);
		}
	}

	public function cancelSo(Request $request)
	{
		$dataSo = DB::table("t_sales_order")
				->where('so_code', $request->so)
				->where("status_aprove", "pending")
				->first();

		if($dataSo != null){

			DB::beginTransaction();

			try {

				DB::table("t_sales_order")
				->where('so_code', $request->so)
				->where("status_aprove", "pending")
				->where("so_from","marketplace")
				->update([
					'status_aprove' => 'cancel'
				]);

				$detail_so = DB::table('d_sales_order')
						->join('t_sales_order','t_sales_order.so_code','d_sales_order.so_code')
						->where('d_sales_order.so_code',$request->so)->get();

                    foreach ($detail_so as $detail) {

                        $produkCode = DB::table('m_produk')->where('id',$detail->produk)->first();

                        //get-stok-awal-produk
                        $jumlahStok = DB::table('m_stok_produk')
                            ->where('produk_code',$produkCode->code)
                            ->where('gudang',$detail->gudang)
                            ->sum('stok');

                        MStokProdukModel::create([
							"produk_code" =>  $produkCode->code,
							"transaksi"   =>  $detail->so_code,
							"tipe_transaksi"   =>  'Retur SO',
							"stok_awal"   =>  $jumlahStok,
							"gudang"      =>  $detail->gudang,
							"stok"        =>  $detail->qty,
							"type"        =>  'in',
						]);
                    }

				DB::commit();
				return response()->json('OK', 200);
			} catch (\Exception $e) {
				DB::rollback();
				return response()->json($e, 500);
			}
		}else{
			return response()->json("DATA KOSONG", 500);
		}
	}

	// public function orderSales2(request $request)
	// {
	// 	/**
	//     * Programmer   : Kris
	//     * Tanggal      : 31-10-2017
	//     * Fungsi       : order
	//     * Tipe         : update
	//     */
	// 	$return = [];

	// 	$sales = $request->input("sales");
	// 	$customer = $request->input("customer");
	// 	$sending_address = $request->input("sendingaddress");
	// 	$sending_date = $request->input("sendingdate");

	// 	$produk_id = $request->input("produkid");
	// 	$produk_id_ex = explode(',', $produk_id);

	// 	$produk_qty = $request->input("produkqty");
	// 	$produk_qty_ex = explode(',', $produk_qty);

	// 	$customer_price = $request->input("customerprice");
	// 	$customer_price_ex = explode(',', $customer_price);

	// 	$total_price = $request->input("totalprice");
	// 	$total_price_ex = explode(',', $total_price);

	// 	$produk_discount = $request->input("produkdiscount");
	// 	$produk_discount_ex = explode(',', $produk_discount);

	// 	$count_detail = count($produk_id_ex);

	// 	//generate code
	// 	$date_code = date('ymd');
	// 	$getLastCode = DB::table('t_sales_order')
 //                ->select('id')
 //                ->orderBy('id', 'desc')
 //                ->pluck('id')
 //                ->first();
 //        $getLastCode = $getLastCode +1;

 //        $nol = null;
 //        if(strlen($getLastCode) == 1){
 //            $nol = "000000";
 //        }elseif(strlen($getLastCode) == 2){
 //            $nol = "00000";
 //        }elseif(strlen($getLastCode) == 3){
 //            $nol = "0000";
 //        }elseif(strlen($getLastCode) == 4){
 //            $nol = "000";
 //        }elseif(strlen($getLastCode) == 5){
 //            $nol = "00";
 //        }elseif(strlen($getLastCode) == 6){
 //            $nol = "0";
 //        }else{
 //            $nol = null;
 //        }
	// 	$code = "SOD".$date_code.$nol.$getLastCode;

	// 	//insert sales order
	// 	$dataorder = [
	// 		"so_code"          	=> $code,
	//         "customer"      	=> $customer,
	//         "sales"         	=> $sales,
	//         "sending_address"	=> $sending_address,
	//         "sending_date"		=> $sending_date
 //        ];

 //        DB::table('t_sales_order')->insert($dataorder);

 //        $data = DB::table('t_sales_order')
 //        	->select('id','so_code', 'sales')
 //        	->where('sales', $sales)
 //        	->orderBy('id', 'desc')
 //        	->first();

 //        //insert detail sales order
 //        for ($i=0; $i < $count_detail; $i++) {
 //        	DB::table('d_sales_order')->insert(
	// 		    ['so_code' => $data->so_code,
	// 		    'produk' => $produk_id_ex[$i],
	// 			'qty' => $produk_qty_ex[$i],
	// 			'customer_price' => $customer_price_ex[$i],
	// 			'discount' => $produk_discount_ex[$i],
	// 			'total' => $total_price_ex[$i]]
	// 		);
 //        }

	// 	$return['success'] = true;
	// 	$return['msgServer'] = "Order success.";

	// 	return response($return);
	// }
}