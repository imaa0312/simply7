<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MBankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataBank = DB::table('m_bank')->get();

        // dd($dataPenagihan);
        return view('admin.bank.index', compact('dataBank'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.bank.create');
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

        DB::table('m_bank')
            ->insert(['name' => strtoupper($request->name)]);

        return redirect('admin/bank');
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
        $dataBank = DB::table('m_bank')->where('id',$id)->first();
        return view('admin.bank.update',compact('dataBank'));
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

        DB::table('m_bank')
            ->where('id',$id)
            ->update(['name' => strtoupper($request->name)]);

        return redirect('admin/bank');
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
