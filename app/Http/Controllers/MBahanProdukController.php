<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MBahanProdukModel;
use DB;



class MBahanProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getDataBahan = MBahanProdukModel::orderBy('id', 'DESC')->get();

        return view('admin.produk.bahan.index', compact('getDataBahan'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.produk.bahan.create');
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
            'name' => 'required|max:50'
        ]);

        $store = new MBahanProdukModel;
        $store->name = $request->name;
        $store->save();

        return redirect('admin/produk/bahan');
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
        $getData = MBahanProdukModel::where('id',$id)->first();

        return view('admin.produk.bahan.update', compact('getData'));
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
            'name' => 'required|max:50'
        ]);

        $update = MBahanProdukModel::where('id', $id)->update([
            'name' => $request->name
        ]);

        return redirect('admin/produk/bahan');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cek = DB::table('m_produk')->where('bahan', '=', $id)->count();
        if ($cek > 0) {
            return redirect()->back()->with('message', 'Data Tidak Bisa Dihapus Karena Sudah Dipakai Untuk Master Barang');
        }
        DB::table('m_bahan_produk')->where('id', $id)->delete();
        return redirect()->back()->with('message-success', 'Berhasil Dihapus');
    }
}
