<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiTempCustomerController extends Controller
{
	public function createNewCustomer(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 14-11-2017
	    * Fungsi       : create new Customer
	    * Tipe         : update
	    */

		$name   	= $request->input("name");
		$main_geo_lat = $request->input("geolat");
		$main_geo_lng = $request->input("geolng");
		$phone = $request->input("phone");
		$address = $request->input("address");
		$base64_string_img_depan = $request->input("rawphotodepan");
		$base64_string_img_dalam = $request->input("rawphotodalam");

		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_string_length = 10;
        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $random_string_length; $i++) {
            $string .= $characters[random_int(0, $max-1)];
        }

		$code = strtoupper($string);
		$name = strtoupper($name);

		DB::table('temp_m_customer')->insert([
				"code"  		=> $code,
		        "name"  		=> $name,
		        "main_geo_lat"  => $main_geo_lat,
		        "main_geo_lng"  => $main_geo_lng,
		        "main_phone"  	=> $phone,
		        "main_address"  => $address,
	        ]);

		$return['success'] = true;
		$return['msgServer'] = "Daftar success";
		$return['code customer sementara'] = $code;

		return response($return);
	}

	public function orderNewCustomer(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 14-11-2017
	    * Fungsi       : order new Customer
	    * Tipe         : create
	    */

		$sales = $request->input("sales");
		$customercode = $request->input("customercode");
		//$sending_address = $request->input("sendingaddress");
		$sending_date = $request->input("sendingdate");

		$detail = $request->input("detail");
		$countDetail = count($detail);

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

		//insert sales order
		$dataorder = [
			"so_code"          	=> $code,
	        "customer_code"     => $customercode,
	        "sales"         	=> $sales,
	        "sending_address"	=> $sending_address,
	        "sending_date"		=> $sending_date
        ];

        DB::table('temp_t_sales_order')->insert($dataorder);

        $grand_total = 0;
        //insert detail sales order
        for ($i=0; $i < $countDetail; $i++) {
        	$detailbooking = [];

        	$produkid = ($detail[$i]["produkid"] != "") ? $detail[$i]["produkid"] : null;
        	$produkqty = ($detail[$i]["produkqty"] != "") ? $detail[$i]["produkqty"] : null;
        	$customerprice = ($detail[$i]["customerprice"] != "") ? $detail[$i]["customerprice"] : null;
        	$totalprice = ($detail[$i]["totalprice"] != "") ? $detail[$i]["totalprice"] : null;
        	$produkdiscount = ($detail[$i]["produkdiscount"] != "") ? $detail[$i]["produkdiscount"] : null;

        	$detailbooking = [
        		'so_code' => $code,
			    'produk' => $produkid,
				'qty' => $produkqty,
				'customer_price' => $customerprice,
				'discount' => $produkdiscount,
				'total' => $totalprice
			];

			$grand_total = $grand_total + $totalprice;

			DB::table('temp_d_sales_order')->insert($detailbooking);
        }

		$return['success'] = true;
		$return['msgServer'] = "Order success.";

		return response($return);
	}
}
