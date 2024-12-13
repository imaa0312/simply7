<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MDistribusiPromoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataDistribusi = DB::table('t_distribusi_promo')
            ->join("t_promo", "t_promo.id", "=" , "t_distribusi_promo.promo")
            ->join("m_user", "m_user.id", "=" , "t_distribusi_promo.sales")
            ->get();
        //$dataDistribusi = '';

        return view('admin.distribusi-promo.index', compact('dataDistribusi'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //$dataProduk = MProdukModel::orderBy('name')->get();
        $dataPromo = DB::table('t_promo')->orderBy('code')->get();

        $dataSales = DB::table('m_user')
            ->select('m_role.name as role_name','m_user.id as sales_id','m_user.name as sales_name')
            ->join("m_role", "m_role.id", "=" , "m_user.role")
            ->where('m_role.name','Sales')
            ->get();
        return view('admin.distribusi-promo.create',compact('dataSales','dataPromo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'promo' => 'required',
        ]);

        $count = count($request->sales);

        if ($count > 0) {
            for ($i=0; $i < $count; $i++) {
                $cek = DB::table('t_distribusi_promo')
                    ->where('sales',$request->sales[$i])
                    ->where('promo',$request->promo)
                    ->get();

                if (count($cek) == 0) {
                    DB::table('t_distribusi_promo')
                        ->insert([
                            'sales' => $request->sales[$i],
                            'promo' => $request->promo,
                        ]);
                }
            }
        }

        return redirect('admin/distribusi-promo');
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
}
