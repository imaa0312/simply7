<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MJenisProdukModel;
use DB;



class MJenisProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getDataJenis = MJenisProdukModel::orderBy('id', 'DESC')->get();

        return view('admin.produk.jenis.index', compact('getDataJenis'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.produk.jenis.create');
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

        $store = new MJenisProdukModel;
        $store->name = $request->name;
        $store->save();

        return redirect('admin/produk/jenis');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        dd('aaaa');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $getData = MJenisProdukModel::where('id',$id)->first();

        return view('admin.produk.jenis.update', compact('getData'));
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

        $update = MJenisProdukModel::where('id', $id)->update([
            'name' => $request->name
        ]);

        return redirect('admin/produk/jenis');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cek = DB::table('m_produk')->where('jenis', $id)->count();
        if ($cek > 0) {
            return redirect()->back()->with('message', 'Data Tidak Bisa Dihapus Karena Sudah Dipakai Untuk Master Barang');
        }

        DB::table('m_jenis_produk')->where('id',$id)->delete();
        return redirect()->back()->with('message-success', 'Berhasil Dihapus');
    }
}
