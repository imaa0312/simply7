<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;



class TRedeemController extends Controller
{
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data_redeem = DB::table("t_redeem_point")
        	->select('*', 't_redeem_point.id as redeem_id')
            ->join("m_user", "m_user.id", "=" , "t_redeem_point.sales")
			->get();

		//dd($data_redeem);

        return view('admin.redeem.index', compact('data_redeem'));
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

    public function setuju($id)
    {
    	DB::table('t_redeem_point')
    		->where('id', '=', $id)
    		->update([
	            'status' => 'redeem',
	        ]);
        return redirect()->back();
    }

    public function reject($id)
    {
    	DB::table('t_redeem_point')
    		->where('id', '=', $id)
    		->update([
	            'status' => 'reject',
	        ]);

	    $dataRedeem = DB::table('t_redeem_point')
    		->where('id', '=', $id)
    		->first();

	    DB::table('m_point_sales')
			->insert([
                "sales"    => $dataRedeem->sales,
                "type"     => 'get-point',
                "point"    => $dataRedeem->point,
	        ]);
        return redirect()->back();
    }
}
