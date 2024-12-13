<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MRekeningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataRekening = DB::table('m_rekening_tujuan')
            ->select('m_rekening_tujuan.bank','m_rekening_tujuan.atas_nama','m_rekening_tujuan.no_rekening','m_rekening_tujuan.id as id_rekening','m_bank.name')
            ->join('m_bank', 'm_rekening_tujuan.bank', '=', 'm_bank.id')
            ->orderBy('m_bank.name')
            ->get();

        // dd($dataPenagihan);
        return view('admin.rekening.index', compact('dataRekening'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $getBank = DB::table('m_bank')->get();
        return view('admin.rekening.create', compact('getBank'));
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
            'no_rekening' => 'required|max:20',
            'atas_nama' => 'required|max:50',
            'bank' => 'required',
        ]);

        DB::table('m_rekening_tujuan')
            ->insert([
                'bank' => $request->bank,
                'no_rekening' => strtoupper($request->no_rekening),
                'atas_nama' => strtoupper($request->atas_nama),
            ]);

        return redirect('admin/rekening');
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
        $dataRekening = DB::table('m_rekening_tujuan')->where('id',$id)->first();
        $getBank = DB::table('m_bank')->get();

        return view('admin.rekening.update', compact('dataRekening', 'getBank'));
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
            'no_rekening' => 'required|max:20',
            'atas_nama' => 'required|max:50',
            'bank' => 'required',
        ]);

        DB::table('m_rekening_tujuan')
            ->where('id',$id)
            ->update([
                'bank' => $request->bank,
                'no_rekening' => strtoupper($request->no_rekening),
                'atas_nama' => strtoupper($request->atas_nama),
            ]);

        return redirect('admin/rekening');
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
