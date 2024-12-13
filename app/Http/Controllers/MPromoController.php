<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MPromoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataPromo = DB::table('t_promo')
            ->select('t_promo.*','t_header_promo.name')
            ->join("t_header_promo", "t_header_promo.id", "=" , "t_promo.header")
            ->orderBy('code','desc')
            ->get();

        // dd($dataPenagihan);
        return view('admin.promo.index', compact('dataPromo'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dataHeader = DB::table('t_header_promo')->get();

        return view('admin.promo.create', compact('dataHeader'));
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
            'header' => 'required',
            'judul' => 'required|max:50',
            'deskripsi' => 'required',
        ]);

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        $getLastCode = DB::table('t_promo')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){
            $nol = "00";
        }elseif(strlen($getLastCode) == 2){
            $nol = "0";
        }else{
            $nol = null;
        }

        $setPromo = 'P'.$nol.$getLastCode;

        DB::table('t_promo')
            ->insert([
                'code' => $setPromo,
                'header' => $request->header,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'start_date' => date('Y-m-d', strtotime($tglmulai)),
                'end_date' => date('Y-m-d', strtotime($tglsampai)),
            ]);

        return redirect('admin/promo');
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
        $dataPromo = DB::table('t_promo')->where('id',$id)->first();
        $dataHeader = DB::table('t_header_promo')->get();

        return view('admin.promo.update',compact('dataPromo','dataHeader'));
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
            'header' => 'required',
            'judul' => 'required|max:50',
            'deskripsi' => 'required',
        ]);

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        DB::table('t_promo')
            ->where('id',$id)
            ->update([
                'header' => $request->header,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'start_date' => date('Y-m-d', strtotime($tglmulai)),
                'end_date' => date('Y-m-d', strtotime($tglsampai)),
            ]);

        return redirect('admin/promo');
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
