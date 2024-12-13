<?php

namespace App\Http\Controllers;

use App\Models\MKategoriModel;
use App\Models\MSubKategoriModel;
use DB;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class MSubKategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$getSubDataKategori = MSubKategoriModel::orderBy('id','DESC')->get();

        return view('admin.produk.sub-kategori.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $kategori = MKategoriModel::orderBy('name')->get();
        return view('admin.produk.sub-kategori.create',compact('kategori'));
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
            'name' => 'required|max:50|unique:m_sub_kategori_produk',
        ]);

        $kategori = DB::table("m_kategori_produk")->where("id", $request->kategori_id)->first();

        $request->merge([
            'name' => strtoupper($request->name),
            'kategori' => $kategori->name,
        ]);

        MSubKategoriModel::create($request->all());

        return redirect('admin/produk/sub-kategori');
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
        $dataSub = MSubKategoriModel::find($id);
        $kategori = MKategoriModel::orderBy('name')->get();

        return view('admin.produk.sub-kategori.update',compact('dataSub','kategori'));
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
            'name' => 'required|max:50|unique:m_sub_kategori_produk,name,'.$id,
        ]);

        $kategori = DB::table("m_kategori_produk")->where("id", $request->kategori_id)->first();

        $request->merge([
            'name' => strtoupper($request->name),
            'kategori' => $kategori->name,
        ]);

        MSubKategoriModel::where('id',$id)->update($request->except('_token','_method'));

        return redirect('admin/produk/sub-kategori');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = MSubKategoriModel::where('id',$id)->delete();

        return redirect()->back()->with('message-success', 'Berhasil Dihapus');
    }

    public function apiSubkat()
    {
        $SubDataKategori = MSubKategoriModel::with('kategoriRelation')->orderBy('id','DESC')->get();
        // dd($SubDataKategori);

        return Datatables::of($SubDataKategori)
        ->addColumn('action', function ($SubDataKategori) {
            return '<a href="'.url('/admin/produk/sub-kategori/'.$SubDataKategori->id.'/edit').'" data-toggle="tooltip" data-placement="top" title="Ubah" class="btn btn-warning pull-left btn-sm"><i class="fa fa-edit"></i></a>'.'&nbsp;'.
            '<a href="'.url('/admin/produk/sub-kategori-delete/'.$SubDataKategori->id).'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus"><i class="fa fa-trash"></i></a>';
            })
        ->editColumn('kategori',function($data){
            return  $data['kategoriRelation']['name'];
        })
        ->addIndexColumn()
        ->rawColumns(['action','kategori'])
        ->make(true);
    }
}
