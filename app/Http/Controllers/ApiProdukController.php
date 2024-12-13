<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiProdukController extends Controller
{
	public function listProduk(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 30-10-2017
	    * Fungsi       : list product
	    * Tipe         : update
	    */
		$return = [];
		$data = DB::table('m_produk')->select('m_produk.id','m_produk.code','m_produk.name','m_kategori_produk.name as kategori','m_merek_produk.name as merek','m_sub_kategori_produk.name as sub_kategori','m_produk.panjang','m_produk.berat','m_produk.satuan_kemasan','m_produk.satuan_berat')
			->leftjoin("m_kategori_produk", "m_kategori_produk.id", "=" , "m_produk.kategori")
			->leftjoin("m_merek_produk", "m_merek_produk.id", "=" , "m_produk.merek")
			->leftjoin("m_sub_kategori_produk", "m_sub_kategori_produk.id", "=" , "m_produk.sub_kategori")
			->get();

		foreach ($data as $raw_data) {
			$raw_data->main_price = 54000;
			$raw_data->customer_price = 52000;
			$raw_data->stok = 1380;
		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Produk not found';
		}

		return response($return);
	}

	public function searchProduk(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 23-12-2017
	    * Fungsi       : search product
	    * Tipe         : update
	    */
		$return = [];
		$name   	= $request->input("name");
		$customer   = $request->input("customer");
		$code   	= $request->input("code");

		$query = DB::table('m_produk')
			->select('m_produk.id','m_produk.code','m_produk.name','m_kategori_produk.name as kategori','m_merek_produk.name as merek','m_sub_kategori_produk.name as sub_kategori','m_produk.panjang','m_produk.berat','m_produk.satuan_kemasan')
			->leftjoin("m_kategori_produk", "m_kategori_produk.id", "=" , "m_produk.kategori")
			->leftjoin("m_merek_produk", "m_merek_produk.id", "=" , "m_produk.merek")
			->leftjoin("m_sub_kategori_produk", "m_sub_kategori_produk.id", "=" , "m_produk.sub_kategori")
			->join('m_stok_produk','m_produk.code','=','m_stok_produk.produk_code')
			->where(function ($query) {
                $query->where('m_stok_produk.stok','!=', 0)
                ->orWhere('m_stok_produk.balance','!=',0);
            })
			->groupBy('m_produk.id','m_produk.code','m_produk.name','m_kategori_produk.name','m_merek_produk.name','m_sub_kategori_produk.name');

		if ($name) {
            $query->where('m_produk.name', 'ILIKE', '%' . $name . '%');
        }else if ($code) {
            $query->where('m_produk.code', 'ILIKE', '%' . $code . '%');
        }

        $data = $query->get();

        //dd($data);
        //$datenow = date('Y-m-d');
        foreach ($data as $key => $raw_data) {
        	$data_customer = DB::table('m_customer')
				->select('id','price_variant','gudang','gh_code')
				->where('id', $customer)
				->first();

        	$main_price_data = DB::table('m_harga_produk')
				->where('produk', $raw_data->id)
				->where('m_harga_produk.gh_code', $data_customer->gh_code)
				->where('date_start', '<=' , date('Y-m-d'))
				->where('date_end', '>=' , date('Y-m-d'))
				->orderBy('created_at', 'desc')
				->first();
			if ($main_price_data !== null) {
				$main_price = $main_price_data->price;
			}else{
				$main_price_data_last = DB::table('m_harga_produk')
				->where('produk', $raw_data->id)
				->where('m_harga_produk.gh_code', $data_customer->gh_code)
				->where('date_end', '<=' , date('Y-m-d'))
				->orderBy('created_at', 'desc')
				->orderBy('date_end', 'desc')
				->first();

				if($main_price_data_last !== null){
					$main_price = $main_price_data_last->price;
				}else{
					$main_price = 0;
				}
			}
			//ambil stok
			$gudang = $data_customer->gudang;

			// $stok = DB::table('m_stok_produk')
			// 	->where('m_stok_produk.produk_code', $raw_data->code)
			// 	->where('m_stok_produk.gudang', $gudang)
			// 	->groupBy('m_stok_produk.produk_code')
			// 	->sum('m_stok_produk.stok');

			$date_now = date('d-m-Y');
	        $date = '01-'.date('m-Y', strtotime($date_now));
	        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

	        $balance = DB::table('m_stok_produk')
	            ->where('m_stok_produk.produk_code', $raw_data->code)
	            ->where('m_stok_produk.gudang', $gudang)
	            ->where('type', 'closing')
	            ->whereMonth('periode',date('m', strtotime($date_last_month)))
	            ->whereYear('periode',date('Y', strtotime($date_last_month)))
	            ->sum('balance');

	        $stok = DB::table('m_stok_produk')
	            ->where('m_stok_produk.produk_code', $raw_data->code)
	            ->where('m_stok_produk.gudang', $gudang)
	            ->whereMonth('created_at',date('m', strtotime($date_now)))
	            ->whereYear('created_at',date('Y', strtotime($date_now)))
	            ->groupBy('m_stok_produk.produk_code')
	            ->sum('stok');

	        $stok = $stok + $balance;

			//ambil potongan
			if ($data_customer->price_variant == null) {
				$potongan = 0;
			}else{
				$potongan = $data_customer->price_variant;
			}

			$raw_data->main_price = $main_price;
			$raw_data->customer_price = $main_price + $potongan;
			$raw_data->stok = $stok;

			if($stok<1){
				unset($data[$key]);
			}
			if($main_price<1){
				unset($data[$key]);
			}
		}

		$data = $data->toArray();
		$data = array_values($data);

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Produk not found';
		}

		return response($return);
	}
}