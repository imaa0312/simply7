<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiCheckinController extends Controller
{
	public function checkinList(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 31-10-2017
	    * Fungsi       : checkin list
	    * Tipe         : update
	    */
		$sales = $request->input("sales");
		$date = $request->input("date");

		$query = DB::table('t_checkin');
		$query->select("id","sales","customer","plan","date","hour","geo_lat","geo_lng","type");
		$query->where('sales', $sales);
		$query->where('date', $date);
		$query->orderBy('hour');

		$data = $query->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'List checkin not found';
		}

		return response($return);
	}

	public function checkinPlan(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 04-11-2017
	    * Fungsi       : checkin
	    * Tipe         : create
	    */
		$sales = $request->input("sales");
		$customer = $request->input("customer");
		$date = $request->input("date");
		$hour = urldecode($request->input("hour"));
		$plan = $request->input("plan");
		$geo_lat = $request->input("geolat");
		$geo_lng = $request->input("geolng");

		$datacheckin = [
			"sales"        => $sales,
	        "customer"     => $customer,
	        "date"     	   => $date,
	        "hour"         => $hour,
	        "plan"         => $plan,
	        "geo_lat"      => $geo_lat,
	        "geo_lng"      => $geo_lng,
	        "type"		   => "Plan"
        ];

        DB::table('t_checkin')->insert($datacheckin);

        if ($plan != "" || $plan != null) {
        	$check = DB::table('t_planning')
				->select("*")
				->where('id', $plan)
				->get();

			if (count($check) !== 0) {
				DB::table('t_planning')
					->where('id', $plan)
					->update(['status' => false]);
			}
        }

		$return['success'] = true;
		$return['msgServer'] = "Checkin planed success.";

		return response($return);
	}

	public function checkinUnPlan(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 15-11-2017
	    * Fungsi       : checkin
	    * Tipe         : create
	    */
		$sales = $request->input("sales");
		$customer = $request->input("customer");
		$date = $request->input("date");
		$hour = urldecode($request->input("hour"));
		$plan = $request->input("plan");
		$geo_lat = $request->input("geolat");
		$geo_lng = $request->input("geolng");

		$datacheckin = [
			"sales"        => $sales,
	        "customer"     => $customer,
	        "date"     	   => $date,
	        "hour"         => $hour,
	        "plan"         => $plan,
	        "geo_lat"      => $geo_lat,
	        "geo_lng"      => $geo_lng,
	        "type"		   => "Unplan"
        ];

        DB::table('t_checkin')->insert($datacheckin);

		$return['success'] = true;
		$return['msgServer'] = "Checkin planed success.";

		return response($return);
	}
}