<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;
use App\Models\MBahanProdukModel;
use App\Models\MGudangModel;
use App\Models\MHargaProdukModel;
use App\Models\MJenisProdukModel;
use App\Models\MKategoriModel;
use App\Models\MMerekProdukModel;
use App\Models\MProdukImage;
use App\Models\MProdukModel;
use App\Models\MSatuanKemasanProdukModel;
use App\Models\MSatuanUnitModel;
use App\Models\MStokProdukModel;
use App\Models\MSubKategoriModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Yajra\Datatables\Datatables;




class MProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getData = DB::table('m_produk')
            ->leftjoin('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori')
            ->leftjoin('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori')
            ->leftjoin('m_merek_produk', 'm_merek_produk.id', '=', 'm_produk.merek')
            ->select('m_produk.id','m_produk.code','m_produk.name','m_produk.stok_minimal','m_kategori_produk.name as kategori','m_sub_kategori_produk.name as sub_kategori','m_merek_produk.name as merek')
            ->orderBy('m_produk.code', 'DESC')
            ->get();

        return view('admin.produk.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $setCodeProduct = $this->setProdukCode();

        $getMerek = MMerekProdukModel::get();
        $getKategori = MKategoriModel::get();
        // $getSubKategori = MSubKategoriModel::get();
        //$satuanKemasan = MSatuanKemasanProdukModel::get();
        $satuanUnit = MSatuanUnitModel::get();
        return view('admin.produk.create', compact('setCodeProduct', 'getMerek', 'getKategori','satuanUnit'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request,[
            'stok_minimal' => 'required|numeric',
            'satuan_terkecil_id' => 'required',
            'nilai_konversi_satuan_terkecil' => 'required|numeric',
            'satuan_terbesar_id' => 'required',
            'nilai_konversi_satuan_terbesar' => 'required|numeric',
            'harga' => 'required|numeric',
            'name' => 'max:75',
            'type_asset' => 'required',
            'type_barang' => 'required'
        ]);

        // dd($request->all(), explode(',', $request->image_detail));

        $setCodeProduct = $this->setProdukCode();
        $kategori = DB::table("m_kategori_produk")->where("id",$request->kategori_id)->first();
        $sub_kategori = DB::table("m_sub_kategori_produk")->where("id",$request->sub_kategori_id)->first();
        $satuan_kecil = DB::table("m_satuan_unit")->where("id",$request->satuan_terkecil_id)->first();
        $satuan_besar = DB::table("m_satuan_unit")->where("id",$request->satuan_terbesar_id)->first();
        $merek =MMerekProdukModel::where("id",$request->merek_id)->first();
        //set value request code
        $request->merge([
            'code' => $setCodeProduct,
            'kategori' => $kategori->name,
            'sub_kategori' => $sub_kategori->name,
            'satuan_terbesar' => $satuan_besar->code,
            'satuan_terkecil' => $satuan_kecil->code,
            'merek' => $merek->name,
        ]);

        $store = MProdukModel::create($request->except('harga','image_detail'));

        if($request->image_detail != null){
            $dataImage = explode(',', $request->image_detail);
            $imageDetail = [];
            foreach ($dataImage as $img) {
                $imageDetail[] = [
                    'image' => $img,
                ];
            }
        }

        if($request->image_detail != null){
            $store->imagedetail()->createMany($imageDetail);
        }

        $array = [];
        $index = 0;
        $jmlhGudang = MGudangModel::count();
        $getIdGudang = MGudangModel::get();
        //add-index-gudang-array
        foreach( $getIdGudang as $rowGudang )
        {
            $array[$index]['id_gudang'] = $rowGudang->id;

            $index++;
        }

        //insert stok di semua gudang
        for($i = 0;$i < count($array); $i++) {
            $stok = new MStokProdukModel;
            $stok->produk_code = $request->code;
            $stok->tipe_transaksi = "Initial Produk Baru";
            $stok->stok = 0;
            $stok->gudang = $array[$i]['id_gudang'] ;
            $stok->type = 'in';
            $stok->save();
        }

        // dd($stok);
        // $insertHarga = new MHargaProdukModel;
        // $insertHarga->produk = $store->id;
        // $insertHarga->date_start = date('Y-m-d');
        // $insertHarga->date_end = date('Y-m-d');
        // $insertHarga->price = $request->harga;
        // $insertHarga->save();

        return redirect('admin/produk');
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
        $dataProduk = DB::table('m_produk')
            ->leftjoin('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
            ->leftjoin('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
            ->leftjoin('m_merek_produk', 'm_merek_produk.id', '=', 'm_produk.merek_id')
            ->select('m_produk.*','m_kategori_produk.id as kategori_id','m_kategori_produk.name as kategori',
            'm_sub_kategori_produk.name as sub_kategori','m_sub_kategori_produk.id as sub_kategori_id','m_merek_produk.name as merek','m_merek_produk.id as m_merek_id')
            ->where('m_produk.id',$id)
            ->first();

        $getMerek = MMerekProdukModel::get();
        $getKategori = MKategoriModel::get();
        $getSubKategori = MSubKategoriModel::get();
        $satuanKemasan = MSatuanKemasanProdukModel::get();
        $satuanUnit = MSatuanUnitModel::get();
        $produkImage = MProdukImage::where('produk_id', $id)->get()->implode('image',',');

        return view('admin.produk.update', compact(
            'getMerek', 'getKategori', 'getSubKategori',
            'satuanKemasan','satuanUnit','dataProduk', 'produkImage'
        ));
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
        $produk = MProdukModel::where('id',$id)->first();
        $kategori = DB::table("m_kategori_produk")->where("id",$request->kategori_id)->first();
        $sub_kategori = DB::table("m_sub_kategori_produk")->where("id",$request->sub_kategori_id)->first();
        $satuan_kecil = DB::table("m_satuan_unit")->where("id",$request->satuan_terkecil_id)->first();
        $satuan_besar = DB::table("m_satuan_unit")->where("id",$request->satuan_terbesar_id)->first();
        $merek =MMerekProdukModel::where("id",$request->merek_id)->first();


        //set value request code
        $request->merge([
            'kategori' => $kategori->name,
            'sub_kategori' => $sub_kategori->name,
            'satuan_terbesar' => $satuan_besar->code,
            'satuan_terkecil' => $satuan_kecil->code,
            'merek' => $merek->name,
        ]);


        $produk->update($request->except('_token','_method', 'image_detail'));

        if($request->image_detail != null){
            $dataImage = explode(',', $request->image_detail);
            $imageDetail = [];
            foreach ($dataImage as $img) {
                $imageDetail[] = [
                    'produk_id' => $id,
                    'image' => $img,
                    'created_at' =>  Carbon::now(),
                ];
            }

            $oldData = MProdukImage::where('produk_id', $id)->get();

            foreach ($oldData as $img) {
                if(file_exists($img->image)){
                    File::delete($img->image);
                }
            }

            MProdukImage::where('produk_id', $id)->delete();
            MProdukImage::insert($imageDetail);
        }

        return redirect('admin/produk');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cek = DB::table('d_sales_order')->where('produk', '=', $id)->count();
        if ($cek > 0) {
            return redirect()->back()->with('message', 'Data Tidak Bisa Dihapus Karena Dipakai Untuk Transaksi');
        }

        DB::table('m_produk')->where('id', '=', $id)->delete();
        DB::table('m_produk_image')->where('produk_id', '=', $id)->delete();
        return redirect()->back()->with('message-success', 'Data Berhasil Dihapus');
    }

    protected function setProdukCode()
    {
        $getLastCode = DB::table('m_produk')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){
            $nol = "000000";
        }elseif(strlen($getLastCode) == 2){
            $nol = "00000";
        }elseif(strlen($getLastCode) == 3){
            $nol = "0000";
        }elseif(strlen($getLastCode) == 4){
            $nol = "000";
        }elseif(strlen($getLastCode) == 5){
            $nol = "00";
        }elseif(strlen($getLastCode) == 6){
            $nol = "0";
        }else{
            $nol = null;
        }

        return $setCodeProduct = 'PRDATK'.$nol.$getLastCode;

    }

    public function getSubKategorProdukByKategori($id)
    {
        $result = MSubKategoriModel::where('kategori_id',$id)->get();

        return Response::json($result);
    }


    public function apiProduk()
    {
        $produk = DB::table('m_produk')
            ->leftjoin('m_kategori_produk','m_kategori_produk.id','m_produk.kategori_id')
            ->leftjoin('m_sub_kategori_produk','m_sub_kategori_produk.id','m_produk.sub_kategori_id')
             ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek')
            ->select('m_produk.id as produk_id','m_produk.code','m_produk.name','m_kategori_produk.name as kategori','m_sub_kategori_produk.name as sub_kat','m_merek_produk.name as merek',
             'm_produk.stok_minimal','m_produk.type_asset','m_produk.type_barang', 'm_produk.image')
            ->orderBy('m_produk.id','desc')
            ->get();

        return Datatables::of($produk)
            ->addColumn('action', function ($produk) {
                return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'.url('/admin/produk/'.$produk->produk_id.'/edit').'" data-toggle="tooltip" data-placement="top" title="Ubah" class="btn btn-warning pull-left btn-sm"><i class="fa fa-edit"></i></a>'.

                            '&nbsp'.

                            '<a href="'.url('/admin/produk-delete/'.$produk->produk_id).'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" data-toggle="tooltip" data-placement="top" title="Hapus" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>'.

                            '&nbsp'.

                        '</td>'.
                    '</tr>'.
                '</table>';

            })
            ->editColumn('type_asset', function($produk){
              return ucfirst($produk->type_asset);
            })
            ->editColumn('image', function($produk){
                if($produk->image != null){
                    return "<img class=\"img-responsive\"style=\"height:120px; width:120px\" src=\"$produk->image\">";
                }

            })
            ->editColumn('type_barang', function($produk){
              return ucfirst($produk->type_barang);
            })
            ->addIndexColumn()
            ->rawColumns(['action','image'])
            ->make(true);
    }
}
