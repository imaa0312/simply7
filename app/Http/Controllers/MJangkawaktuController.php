<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MJangkaWaktu;

class MJangkawaktuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $top = MJangkaWaktu::orderBy('jangka_waktu')->get();

        return view('admin.jangkawaktu.index',compact('top'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.jangkawaktu.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $jangkawaktu = MJangkaWaktu::create($request->all());
        return redirect('admin/jangkawaktu');
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
        $data = MJangkaWaktu::find($id);
        return view('admin.jangkawaktu.edit',compact('data'));
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
        MJangkaWaktu::where('id',$id)->update($request->except('_token','_method'));
        return redirect('/admin/jangkawaktu');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        MJangkaWaktu::where('id', '=', $id)->delete();
        return redirect('admin/jangkawaktu');
    }

}
