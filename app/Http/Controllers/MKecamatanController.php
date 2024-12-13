<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\MKecamatanModel;
use App\Models\MKotaKabModel;
use App\Models\MProvinsiModel;
use Response;
use Yajra\Datatables\Datatables;



class MKecamatanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd('aa');
        // $dataKecamatan = MKecamatanModel::with('kotaRelation')->orderBy('code','DESC')->get();
        $getProvinsi = MProvinsiModel::get();
        return view('admin.kecamatan.index',compact('getProvinsi'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $getLastCode = DB::table('m_kecamatan')
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

        $setCodeKecamatan = 'KEC'.$nol.$getLastCode;

        $dataKota = MKotaKabModel::orderBy('name')->get();
        return view('admin.kecamatan.create',compact('dataKota','setCodeKecamatan'));
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
            'name' => 'required|max:50',
            'kota' => 'required',
        ]);

        $getLastCode = DB::table('m_kecamatan')
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

        $setCodeKecamatan = 'KEC'.$nol.$getLastCode;

        MKecamatanModel::create([
            'code' => $setCodeKecamatan,
            'kota_kab' => $request->kota,
            'name' => $request->name,
        ]);

        return redirect('admin/kecamatan');
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
        $kecamatan = MKecamatanModel::find($id);
        $dataKota = MKotaKabModel::orderBy('name')->get();
        return view('admin.kecamatan.update',compact('dataKota','kecamatan'));
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
            'name' => 'required|max:50',
            'kota' => 'required',
        ]);

        MKecamatanModel::where('id',$id)->update([
            'code' => $request->code,
            'kota_kab' => $request->kota,
            'name' => $request->name,
        ]);

        return redirect('admin/kecamatan');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $cekData = DB::table('m_kelurahan_desa')->where('kecamatan',$id)->count();

         if($cekData > 0 ){
             return redirect()->back()->with('message','Data tidak Bisa dihapus Karena Sudah Dipakai Untuk Kelurahan');
         }

         $delete = MKecamatanModel::find($id);
         $delete->delete();

         return redirect()->back()->with('message-success','Data Berhasil dihapus');

     }

    public function getKecamatanByKota($kotaId)
    {
        $dataKecamatan = MKecamatanModel::where('kota_kab',$kotaId)->get();

        return Response::json($dataKecamatan);
    }

    public function apiKecamatan()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);
        $dataKecamatan = MKecamatanModel::with('kotaRelation')->orderBy('code','DESC')->get();

        return Datatables::of($dataKecamatan)
            ->addColumn('action', function ($dataKecamatan) {
                // return '<a href="#edit-'.$supplier->id.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'.url('/admin/kecamatan/'.$dataKecamatan->id.'/edit').'" data-toggle="tooltip" data-placement="top" title="Ubah" class="btn btn-warning pull-left btn-sm"><i class="fa fa-edit"></i></a>'.

                            '&nbsp'.

                            '<a href="'. url('/admin/kecamatan-delete/'.$dataKecamatan->id) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" data-toggle="tooltip" data-placement="top" title="Hapus" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>'.

                            '&nbsp'.

                        '</td>'.
                    '</tr>'.
                '</table>';

            })
			->addColumn('kota/kabupaten',function(MKecamatanModel $kecamatan){
				return $kecamatan->kotaRelation->type.' '.$kecamatan->kotaRelation->name;
			})
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->toJson();


    }
}
