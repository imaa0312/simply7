<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MSatuanBeratProdukModel;

class MSatuanBeratProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataSatuanBerat = MSatuanBeratProdukModel::get();
        return view('admin/produk/satuan/berat/index', compact('dataSatuanBerat'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/produk/satuan/berat/create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        MSatuanBeratProdukModel::create($request->all());
        return redirect('admin/satuan-berat/produk');
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
        $dataSatuanBerat = MSatuanBeratProdukModel::find($id)->first();
        return view('admin/produk/satuan/berat/update', compact('dataSatuanBerat'));
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
        $dataSatuanBerat = MSatuanBeratProdukModel::find($id)->update($request->all());
        return redirect('admin/satuan-berat/produk');
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
