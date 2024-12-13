<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiCustomerController extends Controller
{
	public function listCustomer(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 11-12-2017
	    * Fungsi       : list Customer
	    * Tipe         : update
	    */

		$return = [];
		$name       = $request->input("name");
		$customer   = $request->input("customer");
		$kotakab   	= $request->input("kotakab");
		$sales   	= $request->input("sales");
		$page   	= $request->input("page");

		$query = DB::table('m_customer');
		$query->select('m_customer.id','m_customer.code','m_customer.name','m_customer.type','m_customer.bentuk','m_customer.main_address as address','m_kelurahan_desa.name as kelurahan','m_kecamatan.name as kecamatan', DB::raw("CONCAT(m_kota_kab.type,' ',m_kota_kab.name) as kota_kab"),'m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.credit_limit','m_customer.credit_limit_days');
		$query->join("m_kelurahan_desa", "m_customer.main_kelurahan", "=" , "m_kelurahan_desa.id");
		$query->join("m_kecamatan", "m_kelurahan_desa.kecamatan", "=" , "m_kecamatan.id");
		$query->join("m_kota_kab", "m_kecamatan.kota_kab", "=" , "m_kota_kab.id");
		$query->join("m_wilayah_pembagian_sales", "m_wilayah_pembagian_sales.wilayah_sales", "=" , "m_customer.wilayah_sales");
		$query->where('m_customer.status', true);
		$query->where('m_customer.name', 'ILIKE', '%' . $name . '%');

		if ($customer) {
            $query->where('m_customer.code', $customer);
        }else if ($kotakab) {
            $query->where('m_kota_kab.code', $kotakab);
        }

        if ($sales) {
        	$query->where('m_wilayah_pembagian_sales.sales', $sales);
        }

        $query->orderBy("m_customer.code");
		$count = $query->count();

  		$take = 20;
  		$offset = $take*($page-1);
		$data = $query->skip($offset)->take($take)->get();

		$piutang = 0;
		$oldest_piutang = 0;
		foreach ($data as $raw_data) {
			$photo_depan = DB::table('m_photo_customer')
				->select('photo')
				->where('customer', $raw_data->id)
				->where('photo','ILIKE','%depan%')
				->orderBy('id', 'desc')
				->pluck('photo')
				->first();
			if (count($photo_depan) !== 0) {
				$raw_data->photo_depan = 'http://wais01.com/upload/customer-photo/'.$photo_depan;
			}else{
				$raw_data->photo_depan = 'http://wais01.com/img/imgnotfound.jpg';
			}

			$photo_dalam = DB::table('m_photo_customer')
				->select('photo')
				->where('customer', $raw_data->id)
				->where('photo','ILIKE','%dalam%')
				->orderBy('id', 'desc')
				->pluck('photo')
				->first();
			if (count($photo_dalam) !== 0) {
				$raw_data->photo_dalam = 'http://wais01.com/upload/customer-photo/'.$photo_dalam;
			}else{
				$raw_data->photo_dalam = 'http://wais01.com/img/imgnotfound.jpg';
			}

			//total piutang
			$piutang = DB::table('t_faktur')
				->where('customer', $raw_data->id)
				->where('status_payment', 'unpaid')
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

	public function searchCustomer(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 03-12-2017
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


		if ($page == $countpage) {
			$newcustomer = DB::table("temp_m_customer")
				->select('*')
				->where('name', 'ILIKE', '%' . $name . '%')
				->get();

			foreach ($newcustomer as $key => $raw_newcustomer) {
				$raw_newcustomer->id = '';
				$raw_newcustomer->tags = 'newcustomer';
				$raw_newcustomer->search = $raw_newcustomer->name;
				$raw_newcustomer->type = '';
				$raw_newcustomer->bentuk = '';
				$raw_newcustomer->address = $raw_newcustomer->main_address;
				$raw_newcustomer->kelurahan = '';
				$raw_newcustomer->kecamatan = '';
				$raw_newcustomer->kota_kab = '';
				$raw_newcustomer->credit_limit = 0;
				$raw_newcustomer->credit_limit_days = 0;
				$raw_newcustomer->last_order = "0";
				$raw_newcustomer->tgl_oldest_piutang = "0";
				$raw_newcustomer->jml_oldest_piutang = "0";
				$raw_newcustomer->piutang = 0;
				$raw_newcustomer->credit_customer = 0;
				$raw_newcustomer->sisa_credit_limit = 0;
				$raw_newcustomer->order_this_month = 0;
				$raw_newcustomer->order_last_month = 0;
				$raw_newcustomer->other_address = "0";
				$raw_newcustomer->toleransi = 14;

				unset($raw_newcustomer->created_at,$raw_newcustomer->updated_at,$raw_newcustomer->main_address);

				$data[$lastarray] = $newcustomer[$key];
				$lastarray++;
			}
		}elseif($count < 1){
			$newcustomer = DB::table("temp_m_customer")
				->select('*')
				->where('name', 'ILIKE', '%' . $name . '%')
				->get();

			foreach ($newcustomer as $key => $raw_newcustomer) {
				$raw_newcustomer->id = '';
				$raw_newcustomer->tags = 'newcustomer';
				$raw_newcustomer->search = $raw_newcustomer->name;
				$raw_newcustomer->type = '';
				$raw_newcustomer->bentuk = '';
				$raw_newcustomer->address = $raw_newcustomer->main_address;
				$raw_newcustomer->kelurahan = '';
				$raw_newcustomer->kecamatan = '';
				$raw_newcustomer->kota_kab = '';
				$raw_newcustomer->credit_limit = 0;
				$raw_newcustomer->credit_limit_days = 0;
				$raw_newcustomer->last_order = "0";
				$raw_newcustomer->tgl_oldest_piutang = "0";
				$raw_newcustomer->jml_oldest_piutang = "0";
				$raw_newcustomer->piutang = 0;
				$raw_newcustomer->credit_customer = 0;
				$raw_newcustomer->sisa_credit_limit = 0;
				$raw_newcustomer->order_this_month = 0;
				$raw_newcustomer->order_last_month = 0;
				$raw_newcustomer->other_address = "0";
				$raw_newcustomer->toleransi = 14;

				unset($raw_newcustomer->created_at,$raw_newcustomer->updated_at,$raw_newcustomer->main_address);
			}

			$data = $newcustomer;
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

	public function detailCustomer(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 27-11-2017
	    * Fungsi       : detail Customer
	    * Tipe         : update
	    */
		$return = [];
		$customer = $request->input("customer");

		$data = DB::table('m_customer')
			->select('m_customer.id','m_customer.code','m_customer.name','m_customer.type','m_customer.bentuk','m_customer.main_address as address','m_kelurahan_desa.name as kelurahan','m_kecamatan.name as kecamatan', DB::raw('CONCAT(m_kota_kab.type,m_kota_kab.name) as kota_kab'),'m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_email','m_customer.main_office_phone_1','m_customer.main_office_phone_2','m_customer.main_phone_1','m_customer.main_phone_2','m_customer.main_cp_name','m_customer.main_cp_title','m_customer.main_cp_jabatan','m_customer.credit_limit','m_customer.credit_limit_days')
			->join("m_kelurahan_desa", "m_customer.main_kelurahan", "=" , "m_kelurahan_desa.id")
			->join("m_kecamatan", "m_kelurahan_desa.kecamatan", "=" , "m_kecamatan.id")
			->join("m_kota_kab", "m_kecamatan.kota_kab", "=" , "m_kota_kab.id")
			->where('m_customer.code', $customer)
			->first();

		$photo_depan = DB::table('m_photo_customer')
			->select('photo')
			->where('customer', $data->id)
			->where('photo','ILIKE','%depan%')
			->orderBy('id', 'desc')
			->pluck('photo')
			->first();
		if (count($photo_depan) !== 0) {
			$data->photo_depan = 'http://wais01.com/upload/customer-photo/'.$photo_depan;
		}else{
			$data->photo_depan = 'http://wais01.com/img/imgnotfound.jpg';
		}

		$photo_dalam = DB::table('m_photo_customer')
			->select('photo')
			->where('customer', $data->id)
			->where('photo','ILIKE','%dalam%')
			->orderBy('id', 'desc')
			->pluck('photo')
			->first();
		if (count($photo_dalam) !== 0) {
			$data->photo_dalam = 'http://wais01.com/upload/customer-photo/'.$photo_dalam;
		}else{
			$data->photo_dalam = 'http://wais01.com/img/imgnotfound.jpg';
		}

		//total piutang
		$piutang = DB::table('t_faktur')
			->where('customer', $data->id)
			->where('status_payment', 'unpaid')
			//->groupBy('d_sales_order.so_code')
			->sum('total');

		$data_credit_customer = DB::table('t_sales_order')
		    ->where('t_sales_order.customer', $data->id)
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
                ->where('t_pembayaran.customer', $data->id)
                ->where('t_faktur.status_payment', 'unpaid')
                ->where('t_pembayaran.status', 'approved')
                ->sum('d_pembayaran.total');

        $piutang = $piutang - $piutang_dibayar;

        $order_this_month = DB::table('t_sales_order')
			//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
			->where('t_sales_order.customer', $data->id)
			->whereYear('so_date', '=', date('Y'))
          	->whereMonth('so_date', '=', date('m'))
			//->groupBy('d_sales_order.so_code')
			->sum('t_sales_order.grand_total');

		$order_last_month = DB::table('t_sales_order')
			//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
			->where('t_sales_order.customer', $data->id)
			->whereYear('so_date', '=', date('Y'))
          	->whereMonth('so_date', '=', ((int)date('m'))-1)
			//->groupBy('d_sales_order.so_code')
			->sum('t_sales_order.grand_total');

		$last_order = DB::table('t_sales_order')
			->where('customer', $data->id)
			->orderBy('so_date', 'desc')
			->first();

		$oldest_piutang = DB::table('t_faktur')
			->where('customer', $data->id)
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
		$data->toleransi = 14;

		$other_address = DB::table("m_alamat_customer")
			->select('m_alamat_customer.id','m_customer.name as customer','m_alamat_customer.name','m_alamat_customer.type','m_alamat_customer.address','m_alamat_customer.kelurahan','m_alamat_customer.geo_lat','m_alamat_customer.geo_lng')
			->join("m_customer", "m_alamat_customer.customer", "=" , "m_customer.id")
			->where('customer', $data->id)
			->orderBy("id")
			->get();

		if (count($other_address) !== 0) {
			$data->other_address = $other_address;
		}else{
			$data->other_address = "0";
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

	public function listAlamatCustomer(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 07-11-2017
	    * Fungsi       : detail Customer
	    * Tipe         : update
	    */
		$return = [];
		$customer = $request->input("customer");

		$data = DB::table("m_customer")
			->select(DB::raw("0 as id"),'m_customer.name as customer', DB::raw("'Main' as name"), DB::raw("'main' as type"),'m_customer.main_address as address','m_customer.main_kelurahan as kelurahan','m_customer.main_geo_lat as geo_lat','m_customer.main_geo_lng as geo_lng')
			->where('id', $customer)
			->orderBy("id")
			->get();

		$data2 = DB::table("m_alamat_customer")
			->select('m_alamat_customer.id','m_customer.name as customer','m_alamat_customer.name','m_alamat_customer.type','m_alamat_customer.address','m_alamat_customer.kelurahan','m_alamat_customer.geo_lat','m_alamat_customer.geo_lng')
			->join("m_customer", "m_alamat_customer.customer", "=" , "m_customer.id")
			->where('customer', $customer)
			->orderBy("id")
			->get();

		$data = array_merge($data->toArray(), $data2->toArray());

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Alamat customer not found';
		}

		return response($return);
	}

	public function listAtasnamaCustomer(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 14-11-2017
	    * Fungsi       : list atas nama
	    * Tipe         : update
	    */
		$return = [];
		$customer = $request->input("customer");

		$data_tail = [];
		$data_customer = DB::table("m_customer")->where('id', $customer)->first();

		$data_head = DB::table("m_customer")
			->select('id','name')
			->where('id', $customer)
			->orwhere('head_office', $customer)
			->get();

		if ($data_customer->head_office != null) {
			$data_tail = DB::table("m_customer")
				->select('id','name')
				->where('id', $data_customer->head_office)
				->orwhere('head_office', $data_customer->head_office)
				->get();

			foreach ($data_tail as $i=>$raw) {
				if ($raw->id == $customer) {
					unset($data_tail[$i]);
				}
			}

			$data_tail = $data_tail->toArray();
		}

		$data = array_merge($data_head->toArray(), $data_tail);

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Alamat customer not found';
		}

		return response($return);
	}

	public function updatePosition(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 08-11-2017
	    * Fungsi       : update Geotag Utama
	    * Tipe         : update
	    */
		$return = [];
		$customer = $request->input("customer");
		$geo_lat = $request->input("geolat");
		$geo_lng = $request->input("geolng");
		$detail = $request->input("detail");
		$countDetail = count($detail);

		$base64_string_img_depan = $request->input("rawphotodepan");
		$base64_string_img_dalam = $request->input("rawphotodalam");

		if ($geo_lat != null && $geo_lat != "" && $geo_lng != null && $geo_lng != "") {
			DB::table('m_customer')
        	->where('id', $customer)
        	->update(['main_geo_lat' => $geo_lat,
				    'main_geo_lng' => $geo_lng]);
		}

		if ($base64_string_img_depan != null && $base64_string_img_depan != "") {
        	$filename_path_depan = md5(time().uniqid())."depan.jpg";
			$decoded_depan=base64_decode($base64_string_img_depan);
			file_put_contents("upload/customer-photo/".$filename_path_depan,$decoded_depan);

			DB::table('m_photo_customer')->insert([
				"customer" 	=> $customer,
		        "photo" 	=> $filename_path_depan,
	        ]);
		}

		if ($base64_string_img_dalam != null && $base64_string_img_dalam != "") {
        	$filename_path_dalam = md5(time().uniqid())."dalam.jpg";
			$decoded_dalam=base64_decode($base64_string_img_dalam);
			file_put_contents("upload/customer-photo/".$filename_path_dalam,$decoded_dalam);

			DB::table('m_photo_customer')->insert([
				"customer"  => $customer,
		        "photo"  	=> $filename_path_dalam,
	        ]);
		}

		for ($i=0; $i < $countDetail; $i++) {
			$detailgeotag = [];
			$idotheraddress = ($detail[$i]["idotheraddress"] != "") ? $detail[$i]["idotheraddress"] : null;
			$geolatother = ($detail[$i]["geolatother"] != "") ? $detail[$i]["geolatother"] : null;
			$geolngother = ($detail[$i]["geolngother"] != "") ? $detail[$i]["geolngother"] : null;

			if ($geolatother != null && $geolatother != "" && $geolngother != null && $geolngother != "") {
				DB::table('m_alamat_customer')
	        	->where('id', $idotheraddress)
	        	->update(['geo_lat' => $geolatother,
					    'geo_lng' => $geolngother]);
			}
		}

		$return['success'] = true;
		$return['msgServer'] = "Update success.";

		return response($return);
	}

	public function updatePosition2(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : update Geotag Utama
	    * Tipe         : update
	    */
		$return = [];
		$customer = $request->input("customer");
		$geo_lat = $request->input("geolat");
		$geo_lng = $request->input("geolng");

		$base64_string_img_depan = $request->input("rawphotodepan");
		$base64_string_img_dalam = $request->input("rawphotodalam");

		if ($geo_lat != null && $geo_lat != "" && $geo_lng != null && $geo_lng != "") {
			DB::table('m_customer')
        	->where('id', $customer)
        	->update(['main_geo_lat' => $geo_lat,
				    'main_geo_lng' => $geo_lng]);
		}

		if ($base64_string_img_depan != null && $base64_string_img_depan != "") {
        	$filename_path_depan = md5(time().uniqid())."depan.jpg";
			$decoded_depan=base64_decode($base64_string_img_depan);
			file_put_contents("upload/customer-photo/".$filename_path_depan,$decoded_depan);

			DB::table('m_photo_customer')->insert([
				"customer" 	=> $customer,
		        "photo" 	=> $filename_path_depan,
	        ]);
		}

		if ($base64_string_img_dalam != null && $base64_string_img_dalam != "") {
        	$filename_path_dalam = md5(time().uniqid())."dalam.jpg";
			$decoded_dalam=base64_decode($base64_string_img_dalam);
			file_put_contents("upload/customer-photo/".$filename_path_dalam,$decoded_dalam);

			DB::table('m_photo_customer')->insert([
				"customer"  => $customer,
		        "photo"  	=> $filename_path_dalam,
	        ]);
		}

		$return['success'] = true;
		$return['msgServer'] = "Update success.";

		return response($return);
	}

	public function updateOtherPosition(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 31-10-2017
	    * Fungsi       : update Geotag other
	    * Tipe         : update
	    */
		$return = [];
		$alamatid = $request->input("alamatid");
		$geo_lat = $request->input("geolat");
		$geo_lng = $request->input("geolng");

		if ($geo_lat != null && $geo_lat != "" && $geo_lng != null && $geo_lng != "") {
			DB::table('m_alamat_customer')
        	->where('id', $alamatid)
        	->update(['geo_lat' => $geo_lat,
				    'geo_lng' => $geo_lng]);
		}

		$return['success'] = true;
		$return['msgServer'] = "Update success.";

		return response($return);
	}
}