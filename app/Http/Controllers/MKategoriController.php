<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MKategoriModel;
use Yajra\Datatables\Datatables;

use Illuminate\Foundation\Validation\ValidatesRequests;

class MKategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getDataKategori = MKategoriModel::orderBy('id','DESC')->get();

        echo "<pre>";
        print_r($getDataKategori);

        // return view('kategori_index',compact('getDataKategori'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('kategori_create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50|unique:m_kategori_produk'
        ]);

        $request->merge(['name' => strtoupper($request->name)]);

        MKategoriModel::create($request->all());

        return redirect('produk/kategori');

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
        $dataKategori = MKategoriModel::find($id);
        return view('kategori_update',compact('dataKategori'));
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
        $request->validate([
            'name' => 'required|max:50|unique:m_kategori_produk'
        ]);

        $request->merge(['name' => strtoupper($request->name)]);

        MKategoriModel::where('id',$id)->update($request->except('_token','_method'));

        return redirect('produk/kategori');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $cek = DB::table('m_produk')->where('jenis', $id)->count();
        // if ($cek > 0) {
        //     return redirect()->back()->with('message', 'Data Tidak Bisa Dihapus Karena Sudah Dipakai Untuk Master Barang');
        // }

        $delete = MKategoriModel::where('id',$id)->delete();

        return redirect()->back()->with('message-success', 'Berhasil Dihapus');
    }

    public function apiKategori()
    {
        $Kategori = MKategoriModel::orderBy('id','DESC')->get();

        return Datatables::of($Kategori)
        ->addColumn('action', function ($Kategori) {
            return '<a href="'.url('produk/kategori/'.$Kategori->id.'/edit').'" data-toggle="tooltip" data-placement="top" title="Ubah" class="btn btn-warning pull-left btn-sm"><i class="fa fa-edit"></i></a>'.'&nbsp;'.
            '<a href="'.url('produk/kategori-delete/'.$Kategori->id).'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus"><i class="fa fa-trash"></i></a>';
            })

        ->addIndexColumn()
        ->rawColumns(['action'])
        ->make(true);
    }
}
