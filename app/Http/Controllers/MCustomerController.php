<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\MRoleModel;
use App\Models\MUserModel;
use App\Models\MJangkaWaktu;
use App\Models\MGudangModel;
use App\Models\MKotaKabModel;
use App\Models\MProvinsiModel;
use App\Models\MCustomerModel;
use App\Models\MKecamatanModel;
use App\Models\MCPCustomerModel;
use App\Models\MCustomerNPWPModel;
use App\Models\MWilayahSalesModel;
use App\Models\MKelurahanDesaModel;
use App\Models\MWilayahPembagianSalesModel;
use App\Models\MCustomerOtherAddressModel;
use Yajra\Datatables\Datatables;




class MCustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $dataCustomer = MCustomerModel::select('m_customer.*','m_kota_kab.name as kota',
        //     'm_kecamatan.name as kecamatan','m_kelurahan_desa.name as kelurahan',
        //     'm_wilayah_sales.name as wilayah')
        //     ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
        //     ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
        //     ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        //     ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        //     ->orderBy('m_customer.code', 'DESC')
        //     ->get();
        $getProvinsi = MProvinsiModel::get();
        // dd($dataCustomer);
        // return view('admin.customer.index',compact('dataCustomer','getProvinsi'));
        return view('admin.customer.index-server-side',compact('getProvinsi'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //codecustomer
        $getLastCode = DB::table('m_customer')
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

        $setCodeCustomer = 'CST'.$nol.$getLastCode;
        $getProvinsi = MProvinsiModel::get();
        $dataWilayah = MWilayahSalesModel::get();

        $dataGudang = MGudangModel::orderBy('code')->get();
        $dataPusatKantor = MCustomerModel::orderBy('name')->get();
        $dataGolonganHarga = DB::table('m_golongan_harga_produk')->orderBy('id','asc')->get();
        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();

        return view('admin.customer.create',compact('setCodeCustomer','customer','dataWilayah','getProvinsi','dataPusatKantor','dataGudang','dataGolonganHarga','jangkaWaktu'));
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
            'name' => 'required|max:100',
            'bentuk' => 'required',
            'type' => 'required',
            'main_address' => 'required',
            'credit_limit' => 'max:11',
            'credit_limit_remain' => 'max:11',
            'main_geo_lat' => 'max:50',
            'main_geo_lng' => 'max:50',
            'main_phone_1' => 'max:20',
            'main_phone_2' => 'max:20',
            'main_office_phone_1' => 'max:20',
            'main_office_phone_2' => 'max:20',
            'main_fax_1' => 'max:20',
            'main_fax_2' => 'max:20',
            'main_cp_name' => 'max:50',
            'price_variant' => 'max:10',
        ]);
        $getLastCode = DB::table('m_customer')
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

        //set value request code
        $setCodeCustomer = 'CST'.$nol.$getLastCode;
        $request->merge(['code' => $setCodeCustomer]);

        if($request->main_cp_birthdate != "" || $request->main_cp_birthdate != null){
            $main_cp_birthdate = date('Y-m-d', strtotime($request->main_cp_birthdate));
            $request->merge(['main_cp_birthdate' => $main_cp_birthdate]);
        }

        MCustomerModel::create($request->all());

        return redirect('admin/customer');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = MCustomerModel::select('m_customer.*','m_kota_kab.name as kota','m_kota_kab.type as type_kota',
                    'm_kecamatan.name as kecamatan','m_kelurahan_desa.name as kelurahan',
                    'm_wilayah_sales.name as wilayah','m_wilayah_sales.id as wilayah_id', 'm_gudang.name as name_gudang')
                    ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
                    ->join('m_gudang', 'm_gudang.id', '=', 'm_customer.gudang')
                    ->where('m_customer.id',$id)
                    ->first();

        $dataSales = MWilayahPembagianSalesModel::join('m_user','m_user.id','m_wilayah_pembagian_sales.sales')
                    ->where('m_wilayah_pembagian_sales.wilayah_sales', $customer->wilayah_id)
                    ->get();

        $pusatKantor = MCustomerModel::select('name')->where('id',$customer->head_office)->get();

        $otherAddress = MCustomerOtherAddressModel::where('customer',$id)->get();


        $contactPerson = MCPCustomerModel::where('customer',$id)->get();

        $dataNpwp = MCustomerNPWPModel::where('customer',$id)->get();

        if ($customer->credit_limit == null) {
            $credit_limit = 0;
        }else{
            $credit_limit = $customer->credit_limit;
        }

        // $credit_customer = DB::table('t_sales_order')
        //     ->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
        //     ->where('t_sales_order.customer', $id)
        //     ->where(function ($query) {
        //         $query->where('t_sales_order.status_aprove','!=','closed')
        //               ->Where('t_sales_order.status_aprove','!=','reject');
        //         })
        //     ->sum('d_sales_order.total');

        $dataCustomer = DB::table('m_customer')
            ->join('m_gudang', 'm_gudang.id', '=', 'm_customer.gudang')
            ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
            ->select('m_customer.*','m_gudang.name as gudang','m_wilayah_sales.name as wilayah')
            ->where('m_customer.id',$id)
            ->first();
        //total credit limit
        if ($dataCustomer->credit_limit == null) {
            $credit_limit = 0;
        }else{
            $credit_limit = $dataCustomer->credit_limit;
        }
        $gudang = $dataCustomer->gudang;
        $wilayah = $dataCustomer->wilayah;

        $data_credit_customer = DB::table('t_sales_order')
            //->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
            ->where('t_sales_order.customer', $id)
            ->where(function ($query) {
                $query->where('t_sales_order.status_aprove','!=','closed')
                      ->Where('t_sales_order.status_aprove','!=','reject')
                      ->Where('t_sales_order.status_aprove','!=','cancel');
                })
            ->get();

        $credit_customer = 0;
        foreach ($data_credit_customer as $headerSo) {
            $dataDetailSo = DB::table('d_sales_order')->where('so_code',$headerSo->so_code)->get();
            $alltotaldetail = DB::table('d_sales_order')->where('so_code',$headerSo->so_code)->sum('total');
            $totalQtySo = DB::table('d_sales_order')->where('so_code',$headerSo->so_code)->sum('qty');

            $diskonHeader = $alltotaldetail - $headerSo->grand_total;
            $diskonHeaderPerItem = $diskonHeader / $totalQtySo;
            foreach ($dataDetailSo as $raw_data) {

                //get-diskon-header-per-produk

                //get-detail-total-so
                $qty = $raw_data->qty;
                $sj_qty = $raw_data->sj_qty;
                $sisa_qty = $qty - $sj_qty;
                $total = $raw_data->total;

                $total_detail = ( ($total / $qty) - $diskonHeaderPerItem ) * $sisa_qty;



                $credit_customer = $credit_customer + $total_detail;
            }
        }
        $credit_customer = (int)round($credit_customer);

        $piutang = DB::table('t_faktur')
                ->where('customer', $id)
                ->where('status_payment', 'unpaid')
                ->sum('total');

        $piutang_dibayar = DB::table('t_pembayaran')
                    ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
                    ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
                    ->where('t_pembayaran.customer', $id)
                    ->where('t_faktur.status_payment', 'unpaid')
                    ->where('t_pembayaran.status', 'approved')
                    ->sum('d_pembayaran.total');

        $piutang = $piutang - $piutang_dibayar;

        $sisaCredit = $credit_limit - $credit_customer - $piutang;
        //$sisaCredit = $credit_customer;

        return view('admin.customer.detail',compact('customer','dataSales','otherAddress','contactPerson','dataNpwp','sisaCredit','pusatKantor'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customer = MCustomerModel::select('m_customer.*','m_kota_kab.name as kota','m_kota_kab.type as type_kota',
                    'm_kecamatan.name as kecamatan','m_kecamatan.id as kecamatan_id',
                    'm_kelurahan_desa.name as kelurahan','m_kelurahan_desa.id as kelurahan_id',
                    'm_kota_kab.name as kota_kab', 'm_kota_kab.id as kota_id',
                    'm_provinsi.name as provinsi', 'm_provinsi.id as provinsi_id',
                    'm_wilayah_sales.name as wilayah','m_wilayah_sales.id as wilayah_id')
                    ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
                    ->leftjoin('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
                    ->where('m_customer.id',$id)
                    ->first();

        $getProvinsi = MProvinsiModel::get();
        $getKotaByProvinsiId = DB::table('m_kota_kab')->where('provinsi',$customer->provinsi_id)->get();

        $getKecamatanByKotaId = DB::table('m_kecamatan')->where('kota_kab',$customer->kota_id)->get();

        $getDesaByKotaId = DB::table('m_kelurahan_desa')->where('kecamatan',$customer->kecamatan_id)->get();

        // dd($getKotaByProvinsiId,$getDesaByKotaId);

        $dataWilayah = MWilayahSalesModel::get();
        $dataGudang = MGudangModel::orderBy('code')->get();
        $dataPusatKantor = MCustomerModel::orderBy('name')->get();
        $dataGolonganHarga = DB::table('m_golongan_harga_produk')->orderBy('id','asc')->get();

        if ($customer->credit_limit == null) {
            $credit_limit = 0;
        }else{
            $credit_limit = $customer->credit_limit;
        }

        $data_credit_customer = DB::table('t_sales_order')
            ->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
            ->where('t_sales_order.customer', $id)
            ->where(function ($query) {
                $query->where('t_sales_order.status_aprove','!=','closed')
                      ->Where('t_sales_order.status_aprove','!=','reject')
                      ->Where('t_sales_order.status_aprove','!=','cancel');
                })
            ->get();

        $credit_customer = 0;
        foreach ($data_credit_customer as $raw_data) {
            $qty = $raw_data->qty;
            $sj_qty = $raw_data->sj_qty;
            $sisa_qty = $qty - $sj_qty;
            $total = $raw_data->total;

            $total_credit = ($total / $qty) * $sisa_qty;

            $credit_customer = $credit_customer + $total_credit;
        }

        $piutang = DB::table('t_faktur')
                ->where('customer', $id)
                ->where('status_payment', 'unpaid')
                ->sum('total');

        $piutang_dibayar = DB::table('t_pembayaran')
                    ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
                    ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
                    ->where('t_pembayaran.customer', $id)
                    ->where('t_faktur.status_payment', 'unpaid')
                    ->where('t_pembayaran.status', 'approved')
                    ->sum('d_pembayaran.total');

        $piutang = $piutang - $piutang_dibayar;

        $sisaCredit = $credit_limit - $credit_customer - $piutang;
        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();


        return view('admin.customer.update',compact('customer','dataWilayah','getProvinsi','dataPusatKantor','dataGudang','sisaCredit','getKotaByProvinsiId','getKecamatanByKotaId','getDesaByKotaId','dataGolonganHarga','jangkaWaktu'));
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
            'bentuk' => 'required',
            'type' => 'required',
            'main_address' => 'required',
            'credit_limit' => 'max:11',
            'credit_limit_remain' => 'max:11',
            'main_geo_lat' => 'max:50',
            'main_geo_lng' => 'max:50',
            'main_phone_1' => 'max:20',
            'main_phone_2' => 'max:20',
            'main_office_phone_1' => 'max:20',
            'main_office_phone_2' => 'max:20',
            'main_fax_1' => 'max:20',
            'main_fax_2' => 'max:20',
            'main_cp_name' => 'max:50',
            'main_pos' => 'max:10',
            'price_variant' => 'max:10',
        ]);

        if($request->main_cp_birthdate != "" || $request->main_cp_birthdate != null){
            $main_cp_birthdate = date('Y-m-d', strtotime($request->main_cp_birthdate));
            $request->merge(['main_cp_birthdate' => $main_cp_birthdate]);
        }

        $input = $request->except(['_token','_method']);
        MCustomerModel::where('id',$id)->update($input);
        return redirect('admin/customer');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //dd($id);
        $cekDataCustomer = DB::table('t_sales_order')->where('customer',$id)->count();
        if($cekDataCustomer > 0 ){
            return redirect()->back()->with('message','Data tidak Bisa dihapus karena sudah dipakai untuk transaksi');
        }

        MCustomerModel::where('id','=',$id)->delete();
        return redirect()->back()->with('message-success','Data berhasil dihapus');
    }

    public function trash()
    {
        $trashCustomer = MCustomerModel::onlyTrashed()->get();
        return view('admin.customer.trash', compact('trashCustomer'));
    }

    public function restore($id)
    {
        MCustomerModel::withTrashed()->where('id', '=', $id)->restore();

        return redirect()->back();
    }

    public function deletePermanent($id)
    {
        $cekDataCustomer = DB::table('t_sales_order')->where('customer',$id)->count();
        if($cekDataCustomer > 0 ){
            return redirect()->back()->with('message','Data tidak Bisa dihapus Karena Sudah Diapakai Transaksi');
        }

        $customerDelete = MCustomerModel::withTrashed()->where('id', '=', $id)->first();
        $customerDelete->forceDelete();

        return redirect()->back();
    }

    public function komplain()
    {
        $dataKomplain = DB::table('d_komplain')
                ->select('t_komplain.*','d_komplain.*','m_customer.name as customer_name','m_user.name as sales_name')
                ->join('t_komplain','t_komplain.id','=','d_komplain.id_komplain')
                ->join('m_customer','m_customer.id','=','t_komplain.customer')
                ->join('m_user','m_user.id','=','t_komplain.sales')
                //->orderBy('id', 'desc')
                ->get();
        return view('admin.komplain.index',compact('dataKomplain'));
    }

    public function destroyOtherAddress($id)
    {
        DB::table('m_alamat_customer')->where('id', $id)->delete();
        return redirect()->back();
    }

    public function destroyKontakPerson($id)
    {
        DB::table('m_cp_customer')->where('id', $id)->delete();
        return redirect()->back();
    }

    public function destroyNPWP($id)
    {
        DB::table('m_customer_npwp')->where('id', $id)->delete();
        return redirect()->back();
    }

    public function apiCustomer()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);
        $customer = MCustomerModel::select(['m_customer.*','m_kota_kab.name as kota',
                        'm_kecamatan.name as kecamatan','m_kelurahan_desa.name as kelurahan',
                        'm_wilayah_sales.name as wilayah'])
                        ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
                        ->orderBy('m_customer.code', 'DESC')
                        ->get();

        return Datatables::of($customer)
            ->addColumn('action', function ($customer) {
                // return '<a href="#edit-'.$customer->id.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'.url("/admin/customer/".$customer->id."/edit").'" data-toggle="tooltip" data-placement="top" title="Ubah Customer '.$customer->name.' ?" class="btn btn-warning btn-sm"  >
                            <span class="fa fa-edit "></span></a>'.

                            '&nbsp'.

                            '<a href="'.url("/admin/customer/".$customer->id).'" data-toggle="tooltip" data-placement="top" title="Detail Customer '.$customer->name.' ?" class="btn btn-sm btn-primary"  >
                            <span class="fa fa-external-link "></span></a>'.

                            '&nbsp'.

                            '<a href="'.url("/admin/customer/".$customer->id."/delete-customer").'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" data-toggle="tooltip" data-placement="top" title="Hapus Customer '.$customer->name.' ?" class="btn btn-sm btn-danger"  >
                            <span class="fa fa-trash "></span></a>'.

                        '</td>'.
                    '</tr>'.

                    '<tr class="margin">'.
                        '<td>'.
                            '<a href="'.url("customer/add-alamat/".$customer->id).'"  class="btn btn-primary btn-sm " data-toggle="tooltip" data-placement="top" title="Tambah Alamat '.$customer->name.' ?" >
                            <span class="fa fa-address-book "></span></a>'.

                            '&nbsp'.

                            '<a href="'.url("customer/add-cp/".$customer->id).'" class="btn btn-primary btn-sm " data-toggle="tooltip" data-placement="top" title="Tambah Cp Customer '.$customer->name.' ?"  >
                            <span class="fa fa-phone "></span></a>'.

                            '&nbsp'.

                            '<a href="'.url("customer/add-npwp/".$customer->id).'" data-toggle="tooltip" data-placement="top" title="Tambah NPWP '.$customer->name.' ?"  class="btn btn-success btn-sm ">
                            <span class="fa fa-dollar "></span></a>'.

                        '</td>'.
                    '</tr>'.
                '</table>';

            })
            ->editColumn('id', 'ID: {{$id}}')
            ->editColumn('code', function($customer){
               return '<a href="'.url("/admin/customer/".$customer->id).'">'.$customer->code.'</a>';
            })
            ->editColumn('status', function($customer){
                if ($customer->status == 1) return '<span class="label label-primary">Aktif</span></td>';
                if ($customer->status == 0) return '<span class="label label-warning">Pasif</span>';
            })
            ->addIndexColumn()
            ->rawColumns(['code','action','status'])
            ->make(true);
    }
}
