<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MSatuanKemasanProdukModel;

class MSatuanKemasanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataKemasan = MSatuanKemasanProdukModel::orderBy('id','DESC')->get();

        return view('admin.produk.satuan.kemasan.index',compact('dataKemasan'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.produk.satuan.kemasan.create');
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
            'code' => 'required|max:5|unique:m_satuan_kemasan',
            'kemasan' => 'required|max:50',
        ]);

        MSatuanKemasanProdukModel::create($request->all());

        return redirect('admin/produk-satuan-kemasan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dataKemasan = MSatuanKemasanProdukModel::find($id);

        return view('admin.produk.satuan.kemasan.update',compact('dataKemasan'));
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
        $kemasan = MSatuanKemasanProdukModel::find($id);

        $this->validate($request,[
            'code' => 'required|max:5|unique:m_satuan_kemasan,code,'.$kemasan->id,
            'kemasan' => 'required|max:50',
        ]);

        MSatuanKemasanProdukModel::where('id',$id)->update($request->except('_token','_method'));

        return redirect('admin/produk-satuan-kemasan');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        MSatuanKemasanProdukModel::where('id',$id)->delete();

        return redirect()->back();
    }
}
