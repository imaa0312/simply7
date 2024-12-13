<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MSaldoAwalCoaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('m_saldo_awal_coa')
            ->select('m_saldo_awal_coa.*','m_coa.code','m_coa.desc')
            ->join('m_coa','m_coa.id','m_saldo_awal_coa.id_coa')
            ->orderBy('m_saldo_awal_coa.id','desc')
            ->get();

        return view('admin.accounting.saldo-awal-coa.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = DB::table('m_coa')->get();

        $data_pembanding = DB::table('m_coa')->get();

        foreach ($data as $key => $raw_data) {
            $count = $raw_data->code.'=';
            $pos = '';
            $jumlah = 0;
            foreach ($data_pembanding as $raw_data2) {
                if (stripos($raw_data2->code, $raw_data->code) !== false) {
                    if (stripos($raw_data2->code, $raw_data->code) == 0) {
                        $jumlah++;
                    }
                }
            }
            if ($jumlah > 1) {
                unset($data[$key]);
            }
        }

        $dataSaldoAwalCoa = DB::table('m_saldo_awal_coa')->get();

        foreach ($dataSaldoAwalCoa as $raw_data) {
            foreach ($data as $key => $raw_data2) {
                if ($raw_data2->id == $raw_data->id_coa) {
                    unset($data[$key]);
                }
            }
        }

        //dd($data);

        return view('admin.accounting.saldo-awal-coa.create',compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());

        DB::table('m_saldo_awal_coa')
            ->insert([
                'id_coa' => $request->akun,
                'total' => $request->total,
                'keterangan' => $request->keterangan,
                'status' => 'post',
                'date' => date("Y-m-d"),
        ]);

        return redirect('admin/accounting/saldo-awal-coa');
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
        //
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
        //
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
