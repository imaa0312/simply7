<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\MProvinsiModel;



class MProvinsiController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getData = MProvinsiModel::orderBy('code','DESC')->get();

        return view('admin.provinsi.index', compact('getData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $getLastCode = DB::table('m_provinsi')
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
        $getCode = 'PRV'.$nol.$getLastCode;
        // dd($getCode);
        return view('admin.provinsi.create', compact('getCode'));
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
            'name' => 'required|max:50',
        ]);

        $cekStirng = DB::table('m_provinsi')->where('name',strtoupper($request->name))->get();

        if( count($cekStirng) > 0 ){
            return redirect()->back()->with('message','Nama Provinsi Sudah ada');
        }

        $this->validate($request,[
            'name' => 'required|max:50',
        ]);

        $getLastCode = DB::table('m_provinsi')
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
        $getCode = 'PRV'.$nol.$getLastCode;

        $store = new MProvinsiModel;
        $store->code = $getCode;
        $store->name = strtoupper($request->name);
        $store->save();

        return redirect('admin/provinsi');
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
        $getData = MProvinsiModel::where('id', $id)->first();

        return view('admin.provinsi.update', compact('getData'));
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
            'name' => 'required|max:50',
        ]);

        $cekStirng = DB::table('m_provinsi')->where('name',strtoupper($request->name))->get();

        if( count($cekStirng) > 0 ){
            return redirect()->back()->with('message','Nama Provinsi Sudah ada');
        }

         $this->validate($request,[
            'name' => 'required|max:50',
        ]);

        MProvinsiModel::where('id', $id)->update([
            'code' => $request->code,
            'name' => strtoupper($request->name)
        ]);

        return redirect('admin/provinsi');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = DB::table('m_kota_kab')->where('provinsi',$id)->count();
        if($data > 0 ){
            return redirect()->back()->with('message','Data tidak Bisa dihapus karena dipakai master Kota / Kabupaten');
        }

        MProvinsiModel::where('id', $id)->delete();

        return redirect()->back()->with('message-success','Data Berhasil dihapus');
    }
}
