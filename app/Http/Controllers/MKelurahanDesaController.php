<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MKelurahanDesaModel;
use App\Models\MProvinsiModel;
use App\Models\MKecamatanModel;
use App\Models\MKotaKabModel;
use DB;
use Response;
use Yajra\Datatables\Datatables;




class MKelurahanDesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$getKelurahan = MKelurahanDesaModel::with('kecamatanRelation')->orderBy('code','DESC')->get();
        $getProvinsi = MProvinsiModel::get();

        //return view('admin.kelurahan.index', compact('getKelurahan','getProvinsi'));
        return view('admin.kelurahan.index-server-side', compact('getProvinsi'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $getLastCode = DB::table('m_kelurahan_desa')
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

        $setCodeKelurahan = 'KEL'.$nol.$getLastCode;

        $getKota = MKotaKabModel::get();
        return view('admin.kelurahan.create', compact('setCodeKelurahan', 'getKota'));
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
            'code' => 'required',
            'zipcode' => 'required|max:8',
            'name' => 'required|max:50',
            'kecamatan' => 'required'
        ]);
        $store = new MKelurahandesaModel;
        $store->code = $request->code;
        $store->name = $request->name;
        $store->zipcode = $request->zipcode;
        $store->kecamatan = $request->kecamatan;
        $store->save();

        return redirect('admin/kelurahan');
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
        $getKelurahan = MKelurahandesaModel::find($id);
        $getKecamatan = MKecamatanModel::where('id',$getKelurahan->kecamatan)->first();
        $getKotaKab   = MKotaKabModel::where('id',$getKecamatan->kota_kab)->first();
        $getKota = MKotaKabModel::get();

        return view('admin.kelurahan.update', compact('getKelurahan', 'getKota','getKecamatan','getKotaKab'));
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
            'code' => 'required',
            'zipcode' => 'required|max:8',
            'name' => 'required|max:50',
            'kecamatan' => 'required'
        ]);
        // $getLastCode = DB::table('m_kelurahan_desa')
        //         ->select('id')
        //         ->orderBy('id', 'desc')
        //         ->pluck('id')
        //         ->first();
        // $getLastCode = $getLastCode +1;

        // $nol = null;
        // if(strlen($getLastCode) == 1){
        //     $nol = "000000";
        // }elseif(strlen($getLastCode) == 2){
        //     $nol = "00000";
        // }elseif(strlen($getLastCode) == 3){
        //     $nol = "0000";
        // }elseif(strlen($getLastCode) == 4){
        //     $nol = "000";
        // }elseif(strlen($getLastCode) == 5){
        //     $nol = "00";
        // }elseif(strlen($getLastCode) == 6){
        //     $nol = "0";
        // }else{
        //     $nol = null;
        // }
        // $setCodeKelurahan = 'KEL'.$nol.$getLastCode;

        MKelurahanDesaModel::where('id', $id)->update([
            // 'code' => $setCodeKelurahan,
            'name' => $request->name,
            'zipcode' => $request->zipcode,
            'kecamatan' => $request->kecamatan,
        ]);

        return redirect('admin/kelurahan');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cekData = DB::table('m_customer')->where('gudang',$id)->count();
        if($cekData > 0 ){
            return redirect()->back()->with('message','Data tidak Bisa dihapus dipakai Master Customer');
        }

        $delete = MKelurahanDesaModel::find($id);
        $delete->delete();

        return redirect()->back()->with('message-success','Data Berhasil Dihapus');
    }

    public function getKelurahanByKecamatan($kecamatanId)
    {
        $data =  MKelurahanDesaModel::where('kecamatan',$kecamatanId)->orderBy('name')->get();

        return Response::json($data);
    }

    public function apiKelurahan()
    {
        $getKelurahan = MKelurahanDesaModel::join('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->select(['m_kelurahan_desa.*','m_kecamatan.name as kecamatan'])
                        ->orderBy('code','DESC')->get();

        return Datatables::of($getKelurahan)
            ->addColumn('action',function($getKelurahan){
                return '<a href="'.url("admin/kelurahan/".$getKelurahan->id."/edit").'" data-toggle="tooltip" data-placement="top" title="Ubah" class="btn btn-warning pull-left btn-sm"><i class="fa fa-edit"></i></a>'.

                '&nbsp;'.

                '<a href="'.url("/admin/kelurahan-delete/".$getKelurahan->id).'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" data-toggle="tooltip" data-placement="top" title="Hapus" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>';
            })
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }
}
