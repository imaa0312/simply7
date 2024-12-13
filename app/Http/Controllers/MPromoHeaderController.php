<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MPromoHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataHeaderPromo = DB::table('t_header_promo')
            ->orderBy('id','asc')
            ->get();

        // dd($dataPenagihan);
        return view('admin.promo.promo-header.index', compact('dataHeaderPromo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.promo.promo-header.create');
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
            'header' => 'required|max:50',
        ]);

        //dd($request->all());

        DB::table('t_header_promo')
            ->insert([
                'name' => $request->header,
            ]);

        return redirect('admin/promo-header');
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
        $dataHeader = DB::table('t_header_promo')->where('id',$id)->first();

        return view('admin.promo.promo-header.update',compact('dataHeader'));
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
        $this->validate($request,[
            'header' => 'required|max:50',
        ]);

        DB::table('t_header_promo')
            ->where('id',$id)
            ->update([
                'name' => $request->header,
            ]);

        return redirect('admin/promo-header');
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
