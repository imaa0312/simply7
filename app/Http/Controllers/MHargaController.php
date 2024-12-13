<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MProdukModel;
use App\Models\MHargaProdukModel;
use DB;
use Response;
use Yajra\Datatables\Datatables;


class MHargaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $prices = DB::table('m_golongan_harga_produk')->get();
        return view('admin.produk.harga.all-harga',compact('prices'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dataProduk = MProdukModel::orderBy('name')->get();
        return view('admin.produk.harga.create',compact('dataProduk'));
    }

    public function createHargaGh($id)
    {
        $dataProduk = MProdukModel::orderBy('name')->get();
        $dataGh = $id;
        return view('admin.produk.harga.create',compact('dataProduk','dataGh'));
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
            'produk' => 'required|numeric',
            'periode'  => 'required',
            'price' => 'required|numeric',
        ]);

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);
        $tglmulai = date('Y-m-d', strtotime($tglmulai));
        $tglsampai = date('Y-m-d', strtotime($tglsampai));

        $anchor = 0;
        $getHargaItem = MHargaProdukModel::where('produk', $request->produk)->where("gh_code", $request->gh_code)->get();
        if($getHargaItem != null){
            foreach($getHargaItem as $value){
                if($value->date_start > $tglmulai && $value->date_end <= $tglsampai){
                    $anchor += 1;
                }elseif($value->date_start <= $tglmulai && $value->date_end >= $tglsampai){
                    $anchor += 1;
                }elseif($value->date_start <= $tglmulai && $value->date_end > $tglsampai){
                    $anchor += 1;
                }
            }

            if($anchor > 0){
                return redirect()->back()->with("error_message","Harga Barang masih tersedia di periode yang lain");
            }
        }

        $store = new MHargaProdukModel;
        $store->produk = $request->produk;
        $store->gh_code = $request->gh_code;
        $store->date_start = $tglmulai;
        $store->date_end = $tglsampai;
        $store->price = $request->price;
        $store->price_coret = $request->price_coret;
        $store->save();

        //MHargaProdukModel::create($request->all());

        return redirect('admin/harga-produk/'.$request->gh_code);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $dataGh = $id;
        return view('admin.produk.harga.index-server-side',compact('dataGh'));

        // $dataHarga = MHargaProdukModel::with('produkRelation')->orderBy('produk', 'DESC')->where('gh_code',$id)->get();
        // return view('admin.produk.harga.index',compact('dataHarga','dataGh'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dataHarga = MHargaProdukModel::find($id);
        $dataProduk = MProdukModel::orderBy('name')->get();

        return view('admin.produk.harga.update',compact('dataHarga','dataProduk'));
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
            'produk' => 'required|numeric',
            'periode'  => 'required',
            'price' => 'required|numeric',
        ]);

        $price = DB::table('m_harga_produk')->where('id',$id)->first();

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);
        $tglmulai = date('Y-m-d', strtotime($tglmulai));
        $tglsampai = date('Y-m-d', strtotime($tglsampai));

        $anchor = 0;
        $getHargaItem = MHargaProdukModel::where("id","<>", $id)->where('produk', $price->produk)->where("gh_code", $request->gh_code)->get();

        if($getHargaItem != null){
            foreach($getHargaItem as $value){
                if($value->date_start > $tglmulai && $value->date_end <= $tglsampai){
                    $anchor += 1;
                }elseif($value->date_start <= $tglmulai && $value->date_end >= $tglsampai){
                    $anchor += 1;
                }elseif($value->date_start <= $tglmulai && $value->date_end > $tglsampai){
                    $anchor += 1;
                }
            }

            if($anchor > 0){
                return redirect()->back()->with("error_message","Harga Barang masih tersedia di periode yang lain");
            }
        }

        DB::table('m_harga_produk')
            ->where('id',$id)
            ->update([
                'produk' => $request->produk,
                'date_start' => date('Y-m-d', strtotime($tglmulai)),
                'date_end' => date('Y-m-d', strtotime($tglsampai)),
                'price' => $request->price,
                'price_coret' => $request->price_coret,
            ]);

        return redirect('admin/harga-produk/'.$request->gh_code);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(404);
    }

    public function apiHargaProduk($id)
    {
        $dataHarga = MHargaProdukModel::with('produkRelation')->orderBy('produk', 'DESC')->where('gh_code',$id)->get();

        return Datatables::of($dataHarga)
            ->addColumn('action', function ($dataHarga) {
                return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'.url('/admin/harga-produk/'.$dataHarga->id.'/edit').'" data-toggle="tooltip" data-placement="top" title="Ubah" class="btn btn-warning pull-left btn-sm"><i class="fa fa-edit"></i></a>'.

                            '&nbsp'.

                            '<a href="'.url('/admin/harga-produk-delete/'.$dataHarga->id).'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" data-toggle="tooltip" data-placement="top" title="Hapus" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>'.

                            '&nbsp'.

                        '</td>'.
                    '</tr>'.
                '</table>';

            })
            ->editColumn('price', function($dataHarga){
                return 'Rp. '.number_format($dataHarga->price,0,'.','.');
            }) 
            ->editColumn('price_coret', function($dataHarga){
                return 'Rp. '.number_format($dataHarga->price_coret,0,'.','.');
            })
            ->editColumn('price_coret', function($dataHarga){
                return 'Rp. '.number_format($dataHarga->price_coret,0,'.','.');
            })
            ->editColumn('date_start', function($dataHarga){
                return date("d-m-Y", strtotime($dataHarga->date_start));
            })
            ->editColumn('date_end', function($dataHarga){
                return date("d-m-Y", strtotime($dataHarga->date_end));
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function golongan()
    {
        return view('admin/produk/golongan/form');
    }

    public function addGolongan(Request $request)
    {
        $this->validate($request,[
            'gh_code' => 'required|max: 4',
            'name'    => 'required|string|alpha'
        ]);

        DB::table('m_golongan_harga_produk')->insert([
            'gh_code' =>  $request->gh_code,
            'name' =>  $request->name,
            'created_at' => date('now'),
        ]);

        return redirect()->route('harga-produk.index');
    }
}
