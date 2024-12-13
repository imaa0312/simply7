<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use App\Models\MJangkaWaktu;
use App\Models\MGudangModel;
use App\Models\MKotaKabModel;
use App\Models\MProvinsiModel;
use App\Models\MSupplierModel;
use App\Models\MKecamatanModel;
use App\Models\MWilayahSalesModel;
use App\Models\MKelurahanDesaModel;
use App\Models\MWilayahPembagianSalesModel;
use Yajra\Datatables\Datatables;

class MSupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getProvinsi = MProvinsiModel::get();
        return view('admin.supplier.index-server-side',compact('getProvinsi'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $setCodeSupplier = $this->setCodeSupplier();

        $getProvinsi = MProvinsiModel::get();
        $dataWilayah = MWilayahSalesModel::get();

        $dataGudang = MGudangModel::orderBy('code')->get();
        $dataPusatKantor = MSupplierModel::orderBy('name')->get();
        $dataGolonganHarga = DB::table('m_golongan_harga_produk')->orderBy('id','asc')->get();
        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();

        return view('admin.supplier.create',compact('setCodeSupplier','Supplier','dataWilayah','getProvinsi','dataPusatKantor','dataGudang','dataGolonganHarga','jangkaWaktu'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request,[
            'code' => 'required',
            'name' => 'required|max:35',
            'type' => 'required',
            'main_address' => 'required',
            'main_kelurahan' => 'required',
            'main_geo_lat' => 'max:50',
            'main_geo_lng' => 'max:50',
            'main_phone_1' => 'max:20|required',
            'main_phone_2' => 'max:20',
            'main_office_phone_1' => 'max:20|required',
            'main_office_phone_2' => 'max:20',
            'main_fax_1' => 'max:20|required',
            'main_fax_2' => 'max:20',
            'main_email' => 'required',
            'main_cp_name_1' => 'max:50|required',
            'main_cp_name_2' => 'max:50',
            'main_cp_phone_1' => 'max:20|required',
            'main_cp_phone_2' => 'max:20',
            'main_cp_email_1' => 'required',
        ]);

        $request->merge(['code' => $this->setCodeSupplier()]);

        MSupplierModel::create($request->all());
        return redirect('admin/supplier');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $supplier = MSupplierModel::select('m_supplier.*','m_kota_kab.name as kota','m_kota_kab.type as type_kota',
                    'm_kecamatan.name as kecamatan','m_kelurahan_desa.name as kelurahan'
                    )
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_supplier.main_kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
                    ->where('m_supplier.id',$id)
                    ->first();
                    //dd($supplier->kecamatan);
        return view('admin.supplier.detail',compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $supplier = MSupplierModel::select('m_supplier.*','m_kota_kab.name as kota','m_kota_kab.type as type_kota',
                    'm_kecamatan.name as kecamatan','m_kecamatan.id as kecamatan_id',
                    'm_kelurahan_desa.name as kelurahan','m_kelurahan_desa.id as kelurahan_id',
                    'm_kota_kab.name as kota_kab', 'm_kota_kab.id as kota_id',
                    'm_provinsi.name as provinsi', 'm_provinsi.id as provinsi_id')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_supplier.main_kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
                    ->leftjoin('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
                    ->where('m_supplier.id',$id)
                    ->first();

        $getProvinsi = MProvinsiModel::get();
        $getKotaByProvinsiId = DB::table('m_kota_kab')->where('provinsi',$supplier->provinsi_id)->get();

        $getKecamatanByKotaId = DB::table('m_kecamatan')->where('kota_kab',$supplier->kota_id)->get();

        $getDesaByKotaId = DB::table('m_kelurahan_desa')->where('kecamatan',$supplier->kecamatan_id)->get();

        // dd($getKotaByProvinsiId,$getDesaByKotaId);

        $dataWilayah = MWilayahSalesModel::get();
        $dataGudang = MGudangModel::orderBy('code')->get();
        $dataPusatKantor = MSupplierModel::orderBy('name')->get();
        $dataGolonganHarga = DB::table('m_golongan_harga_produk')->orderBy('id','asc')->get();
        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();

        // dd($supplier);

        // $piutang = DB::table('t_faktur')
        //         ->where('supplier', $id)
        //         ->where('status_payment', 'unpaid')
        //         ->sum('total');
        //
        // $piutang_dibayar = DB::table('t_pembayaran')
        //             ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
        //             ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
        //             ->where('t_pembayaran.supplier', $id)
        //             ->where('t_faktur.status_payment', 'unpaid')
        //             ->where('t_pembayaran.status', 'approved')
        //             ->sum('d_pembayaran.total');
        //
        // $piutang = $piutang - $piutang_dibayar;

        return view('admin.supplier.update',compact('supplier','dataWilayah','getProvinsi','dataPusatKantor','dataGudang','getKotaByProvinsiId','getKecamatanByKotaId','getDesaByKotaId','dataGolonganHarga','jangkaWaktu'));
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
            'name' => 'required|max:100',
            'type' => 'required',
            'main_address' => 'required',
            'main_kelurahan' => 'required',
            'main_geo_lat' => 'max:50',
            'main_geo_lng' => 'max:50',
            'main_phone_1' => 'max:20|required',
            'main_phone_2' => 'max:20',
            'main_office_phone_1' => 'max:20|required',
            'main_office_phone_2' => 'max:20',
            'main_fax_1' => 'max:20|required',
            'main_fax_2' => 'max:20',
            'main_email' => 'required',
            'main_cp_name_1' => 'max:50|required',
            'main_cp_phone_1' => 'max:20|required',
            'main_cp_phone_2' => 'max:20',
            'main_cp_email_1' => 'required',

        ]);

        $input = $request->except(['_token','_method']);
        MSupplierModel::where('id',$id)->update($input);
        return redirect('admin/supplier');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cekDataCustomer = DB::table('t_purchase_order')->where('supplier',$id)->count();

        if($cekDataCustomer > 0 ){
            return redirect()->back()->with('message','Data tidak Bisa dihapus karena sudah dipakai untuk transaksi');
        }
        MSupplierModel::where('id','=',$id)->delete();
        return redirect()->back()->with('message-success','Data berhasil dihapus');
    }

    protected function setCodeSupplier()
    {
        $getLastCode = DB::table('m_supplier')
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

        return 'SPL'.$nol.$getLastCode;
    }

    public function apiSupplier()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);
        $supplier = MSupplierModel::select(['m_supplier.*','m_kota_kab.name as kota',
                        'm_kecamatan.name as kecamatan','m_kelurahan_desa.name as kelurahan'])
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_supplier.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
                        ->orderBy('m_supplier.code', 'DESC')
                        ->get();

        return Datatables::of($supplier)
            ->addColumn('action', function ($supplier) {
                // return '<a href="#edit-'.$supplier->id.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'.url("/admin/supplier/".$supplier->id."/edit").'" data-toggle="tooltip" data-placement="top" title="Ubah Supplier '.$supplier->name.' ?" class="btn btn-warning btn-sm"  >
                            <span class="fa fa-edit "></span></a>'.

                            '&nbsp'.

                            '<a href="'.url("/admin/supplier/".$supplier->id).'" data-toggle="tooltip" data-placement="top" title="Detail Supplier '.$supplier->name.' ?" class="btn btn-sm btn-primary"  >
                            <span class="fa fa-external-link "></span></a>'.

                            '&nbsp'.

                            '<a href="'.url("/admin/supplier/".$supplier->id."/delete-supplier").'" data-toggle="tooltip" data-placement="top" title="Hapus Supplier '.$supplier->name.' ?" class="btn btn-sm btn-danger" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')"  >
                            <span class="fa fa-trash "></span></a>'.

                        '</td>'.
                    '</tr>'.
                '</table>';

            })
            ->editColumn('code', function($supplier){
                return '<a href="'.url("/admin/supplier/".$supplier->id).'">'.$supplier->code.'</a>';
            })
            ->editColumn('status', function($supplier){
                if ($supplier->status == 1) return '<span class="label label-primary">Aktif</span></td>';
                if ($supplier->status == 0) return '<span class="label label-warning">Pasif</span>';
            })
            ->addIndexColumn()
            ->rawColumns(['code','action','status'])
            ->make(true);
    }
}
