<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\MGudangModel;
use App\Models\MProdukModel;



class MGudangController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataGudang = MGudangModel::all();

        return view('admin.gudang.index',compact('dataGudang'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $getLastCode = DB::table('m_gudang')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){
            $nol = "000";
        }elseif(strlen($getLastCode) == 2){
            $nol = "00";
        }elseif(strlen($getLastCode) == 3){
            $nol = "0";
        }else{
            $nol = null;
        }
        $setCodeGudang = 'GDG'.$nol.$getLastCode;

        return view('admin.gudang.create',compact('setCodeGudang'));
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
            'name' => 'required|min:2|max:40',
            'code' => 'required',
            'almat' => 'max:40',
        ]);

        $getLastCode = DB::table('m_gudang')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){
            $nol = "000";
        }elseif(strlen($getLastCode) == 2){
            $nol = "00";
        }elseif(strlen($getLastCode) == 3){
            $nol = "0";
        }else{
            $nol = null;
        }
        $setCodeGudang = 'GDG'.$nol.$getLastCode;
        //setvaluecode
        $request->merge(['code' => $setCodeGudang]);

        $gudang = MGudangModel::create($request->all());

        //insert all produk to new gudang
        $dataProduk = MProdukModel::all();
            foreach ($dataProduk as $produk) {
                DB::table('m_stok_produk')->insert([
                    'produk_code' => $produk->code,
                    'stok_awal'   => 0,
                    'stok'        => 0,
                    'gudang'      => $gudang->id,
                ]);
            }
        return redirect('admin/gudang');
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
        $dataGudang = MGudangModel::find($id);

        return view('admin.gudang.update',compact('dataGudang'));
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
            'name' => 'required|min:2|max:40',
            'code' => 'required',
            'almat' => 'max:40',
        ]);

        MGudangModel::where('id',$id)->update($request->except('_token','_method'));

        return redirect('admin/gudang');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
