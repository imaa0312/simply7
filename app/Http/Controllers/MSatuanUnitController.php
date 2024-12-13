<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MSatuanUnitModel;

class MSatuanUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataUnit = MSatuanUnitModel::orderBy('id','DESC')->get();

        return view('admin.produk.satuan.unit.index',compact('dataUnit'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.produk.satuan.unit.create');

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
            'code' => 'required|max:5|unique:m_satuan_unit',
            'unit' => 'required|max:50',
        ]);

        MSatuanUnitModel::create($request->all());

        return redirect('admin/produk-satuan-unit');
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
        $dataUnit = MSatuanUnitModel::find($id);

        return view('admin.produk.satuan.unit.update',compact('dataUnit'));
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
        $unit = MSatuanUnitModel::find($id);

        $this->validate($request,[
            'code' => 'required|max:5|unique:m_satuan_unit,code,'.$unit->id,
            'unit' => 'required|max:50',
        ]);

        MSatuanUnitModel::where('id',$id)->update($request->except('_token','_method'));

        return redirect('admin/produk-satuan-unit');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        MSatuanUnitModel::where('id',$id)->delete();

        return redirect()->back();
    }
}
