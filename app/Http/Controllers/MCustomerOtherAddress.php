<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\MRoleModel;
use App\Models\MUserModel;
use App\Models\MKotaKabModel;
use App\Models\MProvinsiModel;
use App\Models\MCustomerModel;
use App\Models\MCustomerOtherAddressModel;



class MCustomerOtherAddress extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $dataCustomer = MCustomerModel::find($id);
        $getProvinsi = MProvinsiModel::get();

        return view('admin.customer.other-address.create',compact('dataCustomer','getProvinsi'));
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
            'name' => 'max:30',
            'kelurahan' => 'required',
        ]);
        // dd($request->all());
        MCustomerOtherAddressModel::create($request->all());

        return redirect('admin/customer/'.$request->customer);
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
        $dataCustomer = MCustomerOtherAddressModel::join('m_customer','m_customer.id','=','m_alamat_customer.customer')
                        ->join('m_kelurahan_desa','m_kelurahan_desa.id','=','m_alamat_customer.kelurahan')
                        ->join('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
                        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
                        ->join('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
                        ->select('m_customer.id as customer_id','m_customer.name as customer_name','m_customer.code as code',
                        'm_alamat_customer.*',
                        'm_kecamatan.name as kecamatan','m_kecamatan.id as kecamatan_id',
                        'm_kelurahan_desa.name as kelurahan','m_kelurahan_desa.id as kelurahan_id',
                        'm_kota_kab.name as kota_kab', 'm_kota_kab.id as kota_id',
                        'm_provinsi.name as provinsi', 'm_provinsi.id as provinsi_id')
                        ->where('m_alamat_customer.id',$id)
                        ->first();
        // dd($dataCustomer);
        $getProvinsi = MProvinsiModel::get();
        $getKotaByProvinsiId = DB::table('m_kota_kab')->where('provinsi',$dataCustomer->provinsi_id)->get();

        $getKecamatanByKotaId = DB::table('m_kecamatan')->where('kota_kab',$dataCustomer->kota_id)->get();

        $getDesaByKotaId = DB::table('m_kelurahan_desa')->where('kecamatan',$dataCustomer->kecamatan_id)->get();

        return view('admin.customer.other-address.update',compact('dataCustomer','getProvinsi','','getKotaByProvinsiId','getKecamatanByKotaId','getDesaByKotaId'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // dd($request->all());

        $this->validate($request,[
            'name' => 'max:30',
            'kelurahan' => 'required',
        ]);
        MCustomerOtherAddressModel::where('id',$request->id)->update($request->except('_token'));

        return redirect('/admin/customer/'.$request->customer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(404);
    }
}
