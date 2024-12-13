<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\MKotaKabModel;
use App\Models\MProvinsiModel;
use Response;
use Yajra\Datatables\Datatables;



class MKotaKabController extends Controller
{
    /**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		// $getCity = MKotaKabModel::with('provinsiRelation')->orderBy('code','DESC')->get();
		return view('admin.kota-kab.index');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$getLastCode = DB::table('m_kota_kab')
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

		$setCodeCity = 'KTB'.$nol.$getLastCode;
		$provinsiM = MProvinsiModel::get();
		// dd($area);
		return view('admin.kota-kab.create', compact('provinsiM', 'setCodeCity'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|max:50',
			'provinsi' => 'required',
		]);

		$cekStirng = DB::table('m_kota_kab')->where('name',strtoupper($request->name))->get();

        if( count($cekStirng) > 0 ){
            return redirect()->back()->with('message','Nama Kota/Kabupaten Sudah ada');
        }

		$cityCreate = new MKotaKabModel;
		$cityCreate->code = $request->code;
		$cityCreate->name = strtoupper($request->name);
		$cityCreate->type = $request->tipe;
		$cityCreate->provinsi = $request->provinsi;
		$cityCreate->save();

		return redirect('admin/kota-kab');
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
		$getCity = MKotaKabModel::where('id','=',$id)->first();
		$getAllProvinsi = MProvinsiModel::get();
		return view('admin.kota-kab.update', compact('getAllProvinsi','getCity'));
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
		$this->validate($request, [
			'name' => 'required|max:50',
			'provinsi' => 'required',
		]);

		$updateCity = MKotaKabModel::where('id', '=', $id)->update([
			'code' => $request->code,
			'name' => $request->name,
			'provinsi' => $request->provinsi,
			'type' => $request->tipe,
		]);

		return redirect('admin/kota-kab');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$cekData = DB::table('m_kecamatan')->where('kota_kab',$id)->count();

		if($cekData > 0 ){
			return redirect()->back()->with('message','Data tidak Bisa dihapus karena dipakai master Kecamatan');
		}

		$delete = MKotaKabModel::find($id);
		$delete->delete();

		return redirect()->back()->with('message-success','Data Berhasil dihapus');

	}

	public function getKotaByProvinsi($provinsiId)
	{
		$data = MKotaKabModel::where('provinsi',$provinsiId)->orderBy('name')->get();

		return Response::json($data);
	}

	public function apiKotakab()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);
        $getCity = MKotaKabModel::with('provinsiRelation')->select(['m_kota_kab.*'])->orderBy('code','DESC')->get();

        return Datatables::of($getCity)
            ->addColumn('action', function ($getCity) {
                // return '<a href="#edit-'.$supplier->id.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'.url('/admin/kota-kab/'.$getCity->id.'/edit').'" data-toggle="tooltip" data-placement="top" title="Ubah" class="btn btn-warning pull-left btn-sm"><i class="fa fa-edit"></i></a>'.

                            '&nbsp'.

                            '<a href="'. url('/admin/kota-kab-delete/'.$getCity->id).'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" data-toggle="tooltip" data-placement="top" title="Hapus" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>'.

                            '&nbsp'.

                        '</td>'.
                    '</tr>'.
                '</table>';

            })
			->addColumn('provinsi',function(MKotaKabModel $kota){
				return $kota->provinsiRelation->name;
			})
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->toJson();


    }
}
