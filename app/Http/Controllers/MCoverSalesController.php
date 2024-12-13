<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MRoleModel;
use App\Models\MUserModel;
use DB;

class MCoverSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roleSales = MRoleModel::where('name', 'Sales')->first();

        $getMsales = DB::table('m_user')
            ->select('m_user.id as user_id','m_user.name as name', 'm_user.username','m_user.email','m_user.address', 'm_wilayah_sales.name as wilayah_name','cover.name as cover_name')
            ->join('m_wilayah_pembagian_sales','m_user.id','m_wilayah_pembagian_sales.sales')
            ->join('m_wilayah_sales','m_wilayah_sales.id','m_wilayah_pembagian_sales.wilayah_sales')
            ->leftjoin('m_cover_sales','m_cover_sales.sales','m_user.id')
            ->leftjoin('m_user as cover','m_cover_sales.cover_sales','cover.id')
            ->where('m_user.role', '=', $roleSales->id)
            ->get();
        return view('admin.sales.cover-sales.index', compact('getMsales'));
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
        $roleSales = MRoleModel::where('name', 'Sales')->first();
        $getMsales = MUserModel::where('id', '=', $id)->first();

        $getCover = DB::table('m_user')
            ->select('m_user.id as user_id','m_user.name as name', 'm_user.username','m_user.email','m_user.address')
            ->where('m_user.role', '=', $roleSales->id)
            ->where('m_user.id', '!=', $id)
            ->get();

        return view('admin.sales.cover-sales.update', compact('getMsales','getCover'));
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
        $dataCover = DB::table('m_cover_sales')
            ->where('sales',$id)
            ->get();

        if (count($dataCover) > 0) {
            DB::table('m_cover_sales')
                ->where('sales',$id)
                ->update(['cover_sales' => $request->cover]);
        }else{
            DB::table('m_cover_sales')
                ->insert(['sales' => $id, 'cover_sales' => $request->cover]);
        }

        //dd($request->all());

        return redirect('admin/cover-sales');
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
