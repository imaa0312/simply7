<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MWilayahGudangModel;
use App\Models\MGudangModel;
use App\Models\MKotaKabModel;



class MWilayahGudangController extends Controller
{
    public function index($id)
    {
        $dataWilayah = MWilayahGudangModel::with(['gudangRelation','kotaRelation'])
                ->where('gudang',$id)
                ->get();

        return view('admin.gudang.wilayah-gudang.index',compact('dataWilayah','id'));
    }

    public function create($id)
    {
        $dataWilayah = MGudangModel::where('id',$id)->get();
        $dataGudang = MGudangModel::where('id',$id)->first();

        if( count($dataWilayah) > 0  )
        {
            $dataKota = MKotaKabModel::orderBy('name')->get();
            return view('admin.gudang.wilayah-gudang.create',compact('dataKota','id','dataGudang'));
        }

        abort(404);
    }

    public function store(Request $request)
    {
        $validation = MWilayahGudangModel::where('gudang',$request->gudang)
                    ->where('kota_kab',$request->kota_kab)
                    ->get();

        if( ! count($validation) > 0 )
        {
            MWilayahGudangModel::create($request->all());

            return redirect()->back();
        }
        return redirect()->back()->with('message','Kota Sudah Ada');
    }

    public function destroy($id)
    {
        MWilayahGudangModel::where('id',$id)->delete();
        return redirect()->back();
    }
}
