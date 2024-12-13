<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MMerekProdukModel;
use DB;
use Yajra\Datatables\Datatables;



class MMerekProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $getDataMerek = MMerekProdukModel::orderBy('id', 'DESC')->get();

        return view('admin.produk.merek.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.produk.merek.create');
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

        $store = new MMerekProdukModel;
        $store->name = $request->name;
        $store->save();

        return redirect('admin/produk/merek');
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
        $getData = MMerekProdukModel::where('id',$id)->first();

        return view('admin.produk.merek.update', compact('getData'));
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

        $update = MMerekProdukModel::where('id', $id)->update([
            'name' => $request->name
        ]);

        return redirect('admin/produk/merek');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cek = DB::table('m_produk')->where('merek','=',$id)->count();
        if ($cek > 0) {
            return redirect()->back()->with('message', 'Data Tidak Bisa Dihapus Karena Sudah Dipakai Untuk Master Barang');
        }
        DB::table('m_merek_produk')->where('id', '=', $id)->delete();
        return redirect()->back()->with('message-success', 'Data Berhasil Dihapus');
    }

    public function apiMerek()
    {
        $getDataMerek = MMerekProdukModel::orderBy('id', 'DESC')->get();

        return Datatables::of($getDataMerek)
            ->addColumn('action',function($getDataMerek){
                return '<a href="'.url('admin/produk/merek/'.$getDataMerek->id.'/edit').'" data-toggle="tooltip" data-placement="top" title="Ubah" class="btn btn-warning pull-left btn-sm"><i class="fa fa-edit"></i></a>'.

                '&nbsp;'.

                '<a href="'.url('admin/produk/merek-delete/'.$getDataMerek->id).'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" data-toggle="tooltip" data-placement="top" title="Hapus" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>';
            })
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }
}
