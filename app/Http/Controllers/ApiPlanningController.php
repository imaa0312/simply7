<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiPlanningController extends Controller
{
	public function createPlan(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : create plan
	    * Tipe         : update
	    */

		$return = [];
		$sales = $request->input("sales");
		$customer = $request->input("customer");
		$plan = $request->input("plan");
		$date = $request->input("date");
		$hourstart = urldecode($request->input("hourstart"));
		$hourfinish = urldecode($request->input("hourfinish"));

		DB::table('t_planning')->insert(
			    ['sales' => $sales,
			    'customer' => $customer,
			    'plan' => $plan,
				'date' => $date,
				'start_hour' => $hourstart,
				'finish_hour' => $hourfinish]
			);

		$return['success'] = true;
		$return['msgServer'] = "Insert success.";

		return response($return);
	}

	public function createPlanLain(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 22-01-2018
	    * Fungsi       : create plan Lain2
	    * Tipe         : update
	    */

		$return = [];
		$sales = $request->input("sales");
		$customer = $request->input("customer");
		$plan = $request->input("plan");
		$date = $request->input("date");
		$jam = urldecode($request->input("jam"));
		$keterangan = $request->input("keterangan");

		DB::table('t_planning')->insert(
			    ['sales' => $sales,
			    'customer' => $customer,
			    'plan' => $plan,
				'date' => $date,
				'status' => false,
				'start_hour' => $jam,
				'finish_hour' => $jam,
				'description' => $keterangan]
			);

		$return['success'] = true;
		$return['msgServer'] = "Insert success.";

		return response($return);
	}

	public function reviewPlanLain(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 25-01-2018
	    * Fungsi       : review plan Lain2
	    * Tipe         : update
	    */

		$return = [];
		$page   	= $request->input("page");
		$sales   	= $request->input("sales");
		$date 		= $request->input("date");

		$query = DB::table('t_planning');
		$query->select('t_planning.id','t_planning.customer as customer_id','t_planning.sales','t_planning.plan','t_planning.date','t_planning.start_hour','t_planning.finish_hour','t_planning.status','t_planning.description');
		$query->where('sales', $sales);
		$query->where('date', $date);
		$query->where('plan', 'Lain - lain');
		$query->orderBy('start_hour');

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
			$return['msgServer'] = 'Tugas not found';
		}

		return response($return);
	}

	public function listPlan(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 24-11-2017
	    * Fungsi       : list plan
	    * Tipe         : update
	    */

		$return = [];
		$sales = $request->input("sales");
		$date = $request->input("date");
		$plan = $request->input("plan");

		$query = DB::table('t_planning');
		$query->select('t_planning.id','t_planning.customer as customer_id','m_customer.name','m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_address','t_planning.sales','t_planning.plan','t_planning.date','t_planning.start_hour','t_planning.finish_hour','t_planning.status','m_customer.credit_limit');
		$query->join("m_customer", "m_customer.id", "=" , "t_planning.customer");
		$query->where('sales', $sales);
		$query->where('date', $date);
		$query->orderBy('start_hour');
		if ($plan) {
            $query->where('plan', $plan);
        }

		$data = $query->get();

		$piutang = 0;
		$oldest_piutang = 0;
		foreach ($data as $raw_data) {
			$photo_depan = DB::table('m_photo_customer')
				->select('photo')
				->where('customer', $raw_data->customer_id)
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
				->where('customer', $raw_data->customer_id)
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
				->where('customer', $raw_data->customer_id)
				->where('status_payment', 'unpaid')
				->sum('total');

			$data_credit_customer = DB::table('t_sales_order')
			    ->where('t_sales_order.customer', $raw_data->customer_id)
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
	                ->where('t_pembayaran.customer', $raw_data->customer_id)
	                ->where('t_faktur.status_payment', 'unpaid')
	                ->where('t_pembayaran.status', 'approved')
	                ->sum('d_pembayaran.total');

            $piutang = $piutang - $piutang_dibayar;

            $order_this_month = DB::table('t_sales_order')
				//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
				->where('t_sales_order.customer', $raw_data->customer_id)
				->whereYear('so_date', '=', date('Y'))
              	->whereMonth('so_date', '=', date('m'))
				//->groupBy('d_sales_order.so_code')
				->sum('t_sales_order.grand_total');

			$order_last_month = DB::table('t_sales_order')
				//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
				->where('t_sales_order.customer', $raw_data->customer_id)
				->whereYear('so_date', '=', date('Y'))
              	->whereMonth('so_date', '=', ((int)date('m'))-1)
				//->groupBy('d_sales_order.so_code')
				->sum('t_sales_order.grand_total');

			$last_order = DB::table('t_sales_order')
				->where('customer', $raw_data->customer_id)
				->orderBy('so_date', 'desc')
				->first();

			$oldest_piutang = DB::table('t_faktur')
				->where('customer', $raw_data->customer_id)
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
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Plan not found';
		}

		return response($return);
	}

	public function listPlanReview(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 11-12-2017
	    * Fungsi       : list plan
	    * Tipe         : update
	    */

		$return = [];
		$sales = $request->input("sales");
		$date = $request->input("date");
		$plan = $request->input("plan");

		$query = DB::table('t_planning');
		$query->select('t_planning.id','t_planning.customer as customer_id','m_customer.name','m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_address','t_planning.sales','t_planning.plan','t_planning.date','t_planning.start_hour','t_planning.finish_hour','t_planning.status','m_customer.credit_limit');
		$query->join("m_customer", "m_customer.id", "=" , "t_planning.customer");
		$query->where('m_customer.status', true);
		$query->where('sales', $sales);
		$query->where('date', $date);
		$query->orderBy('start_hour');
		if ($plan) {
            $query->where('plan', $plan);
        }

		$data = $query->get();

		$dataMerge = [];
		foreach ($data as $key => $raw_data) {
			if ($key == 0) {
				$ambil_order = false;
				$tagihan = false;
				if ($raw_data->plan == 'Ambil Order') {
					$ambil_order = true;
				}
				if ($raw_data->plan == 'Tagihan') {
					$tagihan = true;
				}
				$dataMerge[] = (object) array(
					'customer_id' => $raw_data->customer_id,
					'customer_name' => $raw_data->name,
					'ambil_order' => $ambil_order,
					'tagihan' => $tagihan,
				);

			}else{
				$cek = 0;
				foreach ($dataMerge as $key => $raw_2) {
					if ($raw_2->customer_id == $raw_data->customer_id){
						if ($raw_data->plan == 'Ambil Order') {
							$raw_2->ambil_order = true;
						}
						if ($raw_data->plan == 'Tagihan') {
							$raw_2->tagihan = true;
						}
						$cek = 1;
					}
				}
				if ($cek == 0) {
					$ambil_order = false;
					$tagihan = false;
					if ($raw_data->plan == 'Ambil Order') {
						$ambil_order = true;
					}
					if ($raw_data->plan == 'Tagihan') {
						$tagihan = true;
					}
					$dataMerge[] = (object) array(
						'customer_id' => $raw_data->customer_id,
						'customer_name' => $raw_data->name,
						'ambil_order' => $ambil_order,
						'tagihan' => $tagihan,
					);
				}
			}
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $dataMerge;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Plan not found';
		}

		return response($return);
	}

	public function listPlanReviewDetail(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 11-12-2017
	    * Fungsi       : list plan
	    * Tipe         : update
	    */

		$return = [];
		$sales 		= $request->input("sales");
		$date 		= $request->input("date");
		$plan 		= $request->input("plan");
		$customer 	= $request->input("customer");

		$query = DB::table('t_planning');
		$query->select('t_planning.id','t_planning.customer as customer_id','m_customer.name','m_customer.main_geo_lat','m_customer.main_geo_lng','m_customer.main_address','t_planning.sales','t_planning.plan','t_planning.date','t_planning.start_hour','t_planning.finish_hour','t_planning.status','m_customer.credit_limit');
		$query->join("m_customer", "m_customer.id", "=" , "t_planning.customer");
		$query->where('m_customer.status', true);
		$query->where('sales', $sales);
		$query->where('date', $date);
		$query->where('t_planning.customer', $customer);
		$query->orderBy('start_hour');
		if ($plan) {
            $query->where('plan', $plan);
        }

		$data = $query->get();

		$piutang = 0;
		$oldest_piutang = 0;
		foreach ($data as $raw_data) {
			$photo_depan = DB::table('m_photo_customer')
				->select('photo')
				->where('customer', $raw_data->customer_id)
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
				->where('customer', $raw_data->customer_id)
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
				->where('customer', $raw_data->customer_id)
				->where('status_payment', 'unpaid')
				//->groupBy('d_sales_order.so_code')
				->sum('total');

			$data_credit_customer = DB::table('t_sales_order')
	            ->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
	            ->where('t_sales_order.customer', $raw_data->customer_id)
	            ->where(function ($query) {
	                $query->where('t_sales_order.status_aprove','!=','closed')
	                      ->Where('t_sales_order.status_aprove','!=','reject')
	                      ->Where('t_sales_order.status_aprove','!=','cancel');
	                })
	            ->get();

	        $credit_customer = 0;
	        foreach ($data_credit_customer as $raw_datas) {
	            $qty = $raw_datas->qty;
	            $sj_qty = $raw_datas->sj_qty;
	            $sisa_qty = $qty - $sj_qty;
	            $total = $raw_datas->total;

	            $total_credit = ($total / $qty) * $sisa_qty;

	            $credit_customer = $credit_customer + $total_credit;
	        }

            $piutang_dibayar = DB::table('t_pembayaran')
	                ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
	                ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
	                ->where('t_pembayaran.customer', $raw_data->customer_id)
	                ->where('t_faktur.status_payment', 'unpaid')
	                ->where('t_pembayaran.status', 'approved')
	                ->sum('d_pembayaran.total');

            $piutang = $piutang - $piutang_dibayar;

            $order_this_month = DB::table('t_sales_order')
				//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
				->where('t_sales_order.customer', $raw_data->customer_id)
				->whereYear('so_date', '=', date('Y'))
              	->whereMonth('so_date', '=', date('m'))
				//->groupBy('d_sales_order.so_code')
				->sum('t_sales_order.grand_total');

			$order_last_month = DB::table('t_sales_order')
				//->join("d_sales_order", "t_sales_order.so_code", "=" , "d_sales_order.so_code")
				->where('t_sales_order.customer', $raw_data->customer_id)
				->whereYear('so_date', '=', date('Y'))
              	->whereMonth('so_date', '=', ((int)date('m'))-1)
				//->groupBy('d_sales_order.so_code')
				->sum('t_sales_order.grand_total');

			$last_order = DB::table('t_sales_order')
				->where('customer', $raw_data->customer_id)
				->orderBy('so_date', 'desc')
				->first();

			$oldest_piutang = DB::table('t_faktur')
				->where('customer', $raw_data->customer_id)
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
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Plan not found';
		}

		return response($return);
	}

	public function updatePlan(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : update plan
	    * Tipe         : update
	    */

		$return = [];
		$id = $request->input("id");
		$plan = $request->input("plan");
		$date = $request->input("date");
		$hourstart = urldecode($request->input("hourstart"));
		$hourfinish = urldecode($request->input("hourfinish"));

		DB::table('t_planning')
        	->where('id', $id)
        	->update(['plan' => $plan,
				'date' => $date,
				'start_hour' => $hourstart,
				'finish_hour' => $hourfinish]);

		$return['success'] = True;
		$return['msgServer'] = 'Update success';

		return response($return);
	}

	public function updatePlanStatus(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : update plan status
	    * Tipe         : update
	    */

		$return = [];
		$id = $request->input("id");

		DB::table('t_planning')
        	->where('id', $id)
        	->update(['status' => false]);

		$return['success'] = True;
		$return['msgServer'] = 'Update success';

		return response($return);
	}


}
