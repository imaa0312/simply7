<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Yajra\Datatables\Datatables;

class TKunjunganController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        // dd($dataPenagihan);
        return view('admin.sales.kunjungan-sales.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

    public function apiKunjungan()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);
        $dataCustomer = DB::table('m_customer')
            ->select('m_customer.id as customer_id','m_customer.name as customer_name','m_customer.main_address','m_kota_kab.name as kota_kab_name','m_customer.wilayah_sales')
            ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
            ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
            ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
            ->where('status',true)
            ->orderBy('m_customer.id')
            ->get();

        foreach ($dataCustomer as $raw_data) {
            $sales = DB::table('m_wilayah_pembagian_sales')
                ->join('m_user','m_user.id','=','m_wilayah_pembagian_sales.sales')
                ->where('m_wilayah_pembagian_sales.wilayah_sales',$raw_data->wilayah_sales)
                ->pluck('name')
                ->first();
            $raw_data->sales_name = $sales;

            $data_kunjungan_hari_ini = DB::table('t_checkin')
                ->where('customer',$raw_data->customer_id)
                ->where('date',date('Y-m-d'))
                ->get();
            $raw_data->kunjungan_hari_ini = count($data_kunjungan_hari_ini);

            $data_kunjungan_bulan_ini = DB::table('t_checkin')
                ->where('customer',$raw_data->customer_id)
                ->whereYear('date',date('Y'))
                ->whereMonth('date',date('m'))
                ->get();
            $raw_data->kunjungan_bulan_ini = count($data_kunjungan_bulan_ini);
        }

        return Datatables::of($dataCustomer)

            ->addIndexColumn()
            ->make(true);
    }
}
