<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MRoleModel;



class MRoleController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataRole = MRoleModel::all();
        return view("admin.role.index", compact('dataRole'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.role.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->Validate($request, [
            'name' => 'required|max:50'
        ]);

        $role = 0;
        $approval = 0;
        $master = 0;
        $rencana = 0;
        $komplain = 0;
        $stok = 0;
        $so = 0;
        $sj = 0;
        $tagihan = 0;
        $gudang = 0;
        $po = 0;
        $pd = 0;
        $pi = 0;
        $wo = 0;
        $mat_req = 0;
        $mat_us = 0;
        $prod_res = 0;

        if ($request->role == 'on') {
            $role = 1;
        }
        if ($request->approval == 'on') {
            $approval = 1;
        }
        if ($request->master == 'on') {
            $master = 1;
        }
        if ($request->rencana == 'on') {
            $rencana = 1;
        }
        if ($request->komplain == 'on') {
            $komplain = 1;
        }
        if ($request->stok == 'on') {
            $stok = 1;
        }
        if ($request->so == 'on') {
            $so = 1;
        }
        if ($request->sj == 'on') {
            $sj = 1;
        }
        if ($request->tagihan == 'on') {
            $tagihan = 1;
        }
        if ($request->gudang == 'on') {
            $gudang = 1;
        }
        if ($request->po == 'on') {
            $po = 1;
        }
        if ($request->pd == 'on') {
            $pd = 1;
        }
        if ($request->pi == 'on') {
            $pi = 1;
        }
        if ($request->wo == 'on') {
            $wo = 1;
        }
        if ($request->mat_us == 'on') {
            $mat_us = 1;
        }
        if ($request->mat_req == 'on') {
            $mat_req = 1;
        }
        if ($request->prod_res == 'on') {
            $prod_res = 1;
        }

        MRoleModel::create([
            'name' => $request->name,
            'status_role' => $role,
            'status_approval' => $approval,
            'status_master' => $master,
            'status_plan' => $rencana,
            'status_komplain' => $komplain,
            'status_stok' => $stok,
            'status_so' => $so,
            'status_sj' => $sj,
            'status_tagihan' => $tagihan,
            'status_gudang' => $gudang,
            'status_po' => $po,
            'status_pd' => $pd,
            'status_pi' => $pi,
            'status_wo' => $wo,
            'status_mat_us' => $mat_us,
            'status_mat_req' => $mat_req,
            'status_prod_res' => $prod_res,
        ]);

        return redirect('admin/userrole');
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
        $dataRole = MRoleModel::find($id);
        return view('admin.role.update',compact('dataRole'));
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
        $this->Validate($request, [
            'name' => 'required|max:50'
        ]);

        $role = 0;
        $approval = 0;
        $master = 0;
        $rencana = 0;
        $komplain = 0;
        $stok = 0;
        $so = 0;
        $sj = 0;
        $tagihan = 0;
        $gudang = 0;
        $po = 0;
        $pd = 0;
        $pi = 0;
        $wo = 0;
        $mat_req = 0;
        $mat_us = 0;
        $prod_res = 0;

        if ($request->role == 'on') {
            $role = 1;
        }
        if ($request->approval == 'on') {
            $approval = 1;
        }
        if ($request->master == 'on') {
            $master = 1;
        }
        if ($request->rencana == 'on') {
            $rencana = 1;
        }
        if ($request->komplain == 'on') {
            $komplain = 1;
        }
        if ($request->stok == 'on') {
            $stok = 1;
        }
        if ($request->so == 'on') {
            $so = 1;
        }
        if ($request->sj == 'on') {
            $sj = 1;
        }
        if ($request->tagihan == 'on') {
            $tagihan = 1;
        }
        if ($request->gudang == 'on') {
            $gudang = 1;
        }
        if ($request->po == 'on') {
            $po = 1;
        }
        if ($request->pd == 'on') {
            $pd = 1;
        }
        if ($request->pi == 'on') {
            $pi = 1;
        }
        if ($request->wo == 'on') {
            $wo = 1;
        }
        if ($request->mat_us == 'on') {
            $mat_us = 1;
        }
        if ($request->mat_req == 'on') {
            $mat_req = 1;
        }
        if ($request->prod_res == 'on') {
            $prod_res = 1;
        }

        MRoleModel::where('id', '=', $id)->update([
            'name' => $request->name,
            'status_role' => $role,
            'status_approval' => $approval,
            'status_master' => $master,
            'status_plan' => $rencana,
            'status_komplain' => $komplain,
            'status_stok' => $stok,
            'status_so' => $so,
            'status_sj' => $sj,
            'status_tagihan' => $tagihan,
            'status_gudang' => $gudang,
            'status_po' => $po,
            'status_pd' => $pd,
            'status_pi' => $pi,
            'status_wo' => $wo,
            'status_mat_us' => $mat_us,
            'status_mat_req' => $mat_req,
            'status_prod_res' => $prod_res,
        ]);

        return redirect('admin/userrole');
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
