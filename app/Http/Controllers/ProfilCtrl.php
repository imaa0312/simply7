<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use App\Models\MCustomerModel;
use App\Models\TSalesOrderModel;
use App\Models\TPurchaseOrderModel;
use App\Models\TSuratJalanModel;
use App\Models\DSuratJalanModel;
use App\Models\MKonfirmasiPembayaran;
use App\Models\MAlamatCustomerModel;
use DB;
use Auth;
use Response;



class ProfilCtrl extends Controller
{

    public function index()
    {
    	if(Auth::check()){


            $customer = DB::table('m_customer')
                    ->select('m_customer.*', 'm_kota_kab.id as kota_id','m_kota_kab.name as nama_kota',
                            'm_provinsi.id as provinsi_id','m_provinsi.name as nama_prov', 'm_kecamatan.id as kecamatan_id', 'm_kecamatan.name as nama_kecamatan')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->leftjoin('m_user','m_user.id','m_customer.id_user')
                    ->where('m_user.id', Auth::id())
                    ->first();

            $provinsi = DB::table('m_provinsi')->get();

            $kota = DB::table('m_kota_kab')->where('provinsi', $customer->provinsi_id)->get();

            $kecamatan = DB::table('m_kecamatan')->where('kota_kab', $customer->kota_id)->get();

            $kelurahan = DB::table('m_kelurahan_desa')->where('kecamatan', $customer->kecamatan_id)->get();

            return view('frontend.profile-account', compact('customer','provinsi', 'kota', 'kecamatan', 'kelurahan'));

    	}else{
    		$type_message= "error_message";
            $message = "Silahkan Login Terlebih Dahulu";
            return redirect('/login')->with("$type_message", "$message");
    	}

    }

    public function editProfile(Request $request)
    {
        DB::beginTransaction();
        try{
            DB::table('m_customer')->where('id_user', '=', $request->id_user)->update([
                'main_address' => $request->main_address,
                'main_phone_1' => $request->main_phone_1,
                'main_kelurahan' => $request->kelurahan,
                'main_pos' => $request->zip,
            ]);
        DB::commit();
        return redirect()->back()->with('success_message',"Profile berhasil ditambah");
        }catch(\Exception $e){
            DB::rollback();
            // dd($e);
            //              'id_detail'     => $detail->id,
            return redirect()->back()->with('error_message',"Profile gagal diubah");
        }
    }

    public function cekTransaksi()
    {
        if(Auth::check()){

            $id = Auth::id();
            $customer = DB::table('m_customer')->where('id_user', $id)->first();

            $dataSales = DB::table('t_sales_order')
            ->select('m_user.name as user_name','t_sales_order.id','t_sales_order.so_code','t_sales_order.biaya_kirim','t_sales_order.so_date','m_customer.name as cust_name','m_customer.id_user','m_metode_bayar.nama_metode_bayar','t_sales_order.grand_total','t_sales_order.status_aprove')
            ->join('m_customer','m_customer.id','t_sales_order.customer')
            ->join('m_user','m_user.id','m_customer.id_user')
            ->join('m_metode_bayar','m_metode_bayar.id','t_sales_order.metode_bayar')
            ->where('t_sales_order.customer', $customer->id)
            ->where('t_sales_order.so_from', "marketplace")
            ->orderBy('t_sales_order.so_code','desc')
            ->paginate(5);

            foreach ($dataSales as $key => $value) {
                $sj = true;
                $i = 0;
                $cekSj =  DB::table('t_surat_jalan')
                    ->select('t_surat_jalan.*')
                    ->where('t_surat_jalan.so_code',$value->so_code)
                    ->where('t_surat_jalan.status','!=',"cancel")
                    ->get();
                if (count($cekSj) > 0 ) {
                    $sj = false;
                    foreach ($cekSj as $key1 => $value1) {
                        if($value1->status == 'post'){
                            $cekConfirmPengiriman = DB::table('m_konfirmasi_pengiriman')
                                                    ->where('sj_code',$value1->sj_code)
                                                    ->get();
                            if(count($cekConfirmPengiriman) > 0){
                                if($cekConfirmPengiriman[0]->customer_confirmed_by != 0){
                                    $value1->status_confirm = 1;
                                }else{
                                    $value1->status_confirm = 2;
                                }
                            $i++;
                            }else{
                                $value1->status_confirm = 0;
                            }
                        }else{
                            $value1->status_confirm = 0;
                        }
                    }
                }
                if($value->status_aprove == 'pending'){
                    $cek_tf_status = DB::table("m_konfirmasi_pembayaran")->select('status_pembayaran')->where('so_code',$value->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
                    if(!empty($cek_tf_status)){
                       $dataSales[$key]->cek_tf = $cek_tf_status->status_pembayaran;
                    }else{
                       $dataSales[$key]->cek_tf = "0";
                    }
                }else{
                   $dataSales[$key]->cek_tf = "0";
                }

                $dataSales[$key]->sj = $sj;
                $dataSales[$key]->no_sj = $cekSj;
            }
            //dd($dataSales);

            return view('frontend.profile-transaction', compact('dataSales'));
        }else{
            $type_message= "error_message";
            $message = "Silahkan Login Terlebih Dahulu";
            return redirect('/log')->with("$type_message", "$message");
        }
    }

    public function detailTransaksiOrder($id)
    {

        $detailSales = DB::table('d_sales_order')
                        ->select('d_sales_order.*','t_sales_order.*','m_produk.name','m_merek_produk.name as nama_merek')
                        ->join('m_produk','m_produk.id','d_sales_order.produk')
                        ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek_id')
                        ->join('t_sales_order','t_sales_order.so_code','d_sales_order.so_code')
                        ->where('d_sales_order.id', $id)
                        ->first();



        $nama = $detailSales->name;
        //dd($detailSales);


        // if($detailSales->ukuran != '' || $detailSales->ukuran != null ){
        //     $nama = $nama.' UKURAN '.$detailSales->ukuran.' '.$detailSales->satuan_ukuran;

        // }
        // if($detailSales->nama_merek != '' || $detailSales->nama_merek != null ){
        //     $nama = $nama.' MERK '.$detailSales->nama_merek;
        // }
        // if($detailSales->class != '' || $detailSales->class != null){
        //     $nama = $nama.' CLASS '.$detailSales->class;
        // }
        // if($detailSales->warna != '' || $detailSales->warna != null ){
        //     $nama = $nama.' WARNA '.$detailSales->warna;
        // }

        $detailSales->nama_produk_tampil = $nama;

        $sj = true;
        $cekSj =  DB::table('t_surat_jalan')
                    ->select('t_surat_jalan.*','d_surat_jalan.*','m_produk.name','m_merek_produk.name as nama_merek')
                    ->join('d_surat_jalan','d_surat_jalan.sj_code','t_surat_jalan.sj_code')
                    ->join('m_produk','m_produk.id','d_surat_jalan.produk_id')
                    ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek_id')
                    ->where('t_surat_jalan.so_code',$detailSales->so_code)
                    ->where('d_surat_jalan.produk_id',$detailSales->produk)
                    ->where('d_surat_jalan.dso_id',$id)
                    ->where('t_surat_jalan.status','!=',"cancel")
                    ->get();
        if (count($cekSj) > 0 ) {
            $sj = false; // jika ada false
        }
        $i = 0;
        foreach ($cekSj as $key => $value) {
            $nama = $value->name_tampil;

            if($value->status == 'post'){
                $cekConfirmPengiriman = DB::table('m_konfirmasi_pengiriman')
                                        ->where('sj_code',$value->sj_code)
                                        ->get();
                if(count($cekConfirmPengiriman) > 0){
                    if($cekConfirmPengiriman[0]->customer_confirmed_by != 0){
                        $value->status_confirm = 1;
                    }else{
                        $value->status_confirm = 2;
                    }
                $i++;
                }else{
                    $value->status_confirm = 0;
                }
            }

            $value->nama_produk_tampil = $nama;
        }
        if(count($cekSj) == $i && count($cekSj)>0){
            $detailSales->status_confirm = 1;
        }else{
            $detailSales->status_confirm = 0;
        }
        $cekReview = DB::table('m_review')
                    ->where('so_code',$detailSales->so_code)
                    ->where('id_barang',$detailSales->produk)
                    ->get();
        if(count($cekReview) > 0){
            $add_review = 0;
        }else{
            $add_review = 1;
        }
        //dd($cekSj);
        $detailSales->t_sj = $cekSj;
        $detailSales->sj = $sj;
        $detailSales->review = $add_review;
        //dd($detailSales);
        return view('frontend.profile-transaction-invoice', compact('detailSales'));

    }


    public function profilePayment(Request $request)
    {
        return view('frontend/profile-payment-detail');

    }

    public function detailTransaksiInvoice($id)
    {
        //$customer = DB::table('m_customer')->where('id_user', $id)->first();

        $dataSales = DB::table('t_sales_order')
        ->select('m_user.name as user_name','t_sales_order.id','t_sales_order.so_code','t_sales_order.biaya_kirim','t_sales_order.so_date','m_customer.name as cust_name','m_customer.main_email','m_customer.id_user','m_metode_bayar.nama_metode_bayar','m_metode_bayar.desc_metode_bayar','t_sales_order.grand_total','t_sales_order.status_aprove','t_sales_order.atas_nama','t_sales_order.type_atas_nama','t_sales_order.id_sending','t_sales_order.type_sending','t_sales_order.sending_address','t_sales_order.id_rekening_tujuan','m_rekening_tujuan.atas_nama','m_bank.name as bank_name','m_rekening_tujuan.no_rekening','t_sales_order.ekspedisi','t_sales_order.ekspedisi_payment')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->join('m_user','m_user.id','m_customer.id_user')
        ->join('m_metode_bayar','m_metode_bayar.id','t_sales_order.metode_bayar')
        ->leftjoin('m_rekening_tujuan','m_rekening_tujuan.id','t_sales_order.id_rekening_tujuan')
        ->leftjoin('m_bank','m_bank.id','m_rekening_tujuan.bank')
        //->join('m_biaya_kirim','m_biaya_kirim.id','t_sales_order.metode_kirim')
        ->where('t_sales_order.id', $id)
        ->orderBy('t_sales_order.so_code','desc')
        ->first();

        if($dataSales->type_sending == "main"){
            $sending = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kelurahan_desa.zipcode','m_kecamatan.name as nama_kec')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $dataSales->id_sending)->first();
            $dataSales->sending = $sending;
        }else{
            $sending = DB::table('m_alamat_customer')
                        ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kelurahan_desa.zipcode','m_kecamatan.name as nama_kec')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_alamat_customer.id', $dataSales->id_sending)->first();
            $dataSales->sending = $sending;
        }

        if($dataSales->type_atas_nama == "main"){
            $billing = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kelurahan_desa.zipcode','m_kecamatan.name as nama_kec')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $dataSales->atas_nama)->first();
            $dataSales->billing = $billing;
        }else{
            $billing = DB::table('m_alamat_customer')
                        ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kelurahan_desa.zipcode','m_kecamatan.name as nama_kec')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_alamat_customer.id', $dataSales->atas_nama)->first();
            $dataSales->billing = $billing;
        }


        $sj = true;
        $cekSj = TSuratJalanModel::where('so_code',$dataSales->so_code)->get();
         //dd($cekSj);
        if (count($cekSj) > 0 ) {
            $sj = false; // jika ada false
        }
        $dataSales->sj = $sj;

        $dataSales->no_sj = $cekSj;

        if($dataSales->status_aprove == 'pending'){
            $cek_tf_status = DB::table("m_konfirmasi_pembayaran")->select('status_pembayaran')->where('so_code',$dataSales->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
            if(!empty($cek_tf_status)){
                $dataSales->cek_tf = $cek_tf_status->status_pembayaran;
            }else{
                $dataSales->cek_tf = "0";
            }
        }else{
            $dataSales->cek_tf = "0";
        }
        $i=0;

        foreach ($cekSj as $key => $value) {
            $nama = $value->name_tampil;

            if($value->status == 'post'){
                $cekConfirmPengiriman = DB::table('m_konfirmasi_pengiriman')
                                        ->where('sj_code',$value->sj_code)
                                        ->get();
                if(count($cekConfirmPengiriman) > 0){
                    if($cekConfirmPengiriman[0]->customer_confirmed_by != 0){
                        $value->status_confirm = 1;
                    }else{
                        $value->status_confirm = 2;
                    }
                $i++;
                }else{
                    $value->status_confirm = 0;
                }
            }else{
                $value->status_confirm = 0;
            }

            $value->nama_produk_tampil = $nama;
        }
        // if(count($cekSj) == $i && count($cekSj) > 0){
        //     $dataSales->status_confirm = 1;
        // }else{
        //     $dataSales->status_confirm = 0;
        // }
        $dataSales->no_sj = $cekSj;



        $detailSales = DB::table('d_sales_order')
                        ->select('d_sales_order.*','m_produk.name as name','m_produk.image as image')
                        ->join('m_produk','m_produk.id','d_sales_order.produk')
                        ->where('d_sales_order.so_code', $dataSales->so_code)
                        ->orderBy('d_sales_order.so_code','desc')
                        ->get();
        $dataSales->detail_barang = $detailSales;

        $dataBank = DB::table('m_rekening_tujuan')->join('m_bank','m_bank.id','m_rekening_tujuan.bank')->get();

        //dd($dataSales);
        return view('frontend.profile-transaction-detail', compact('dataSales','dataBank'));
    }public function detailPaymentInvoice($id)
    {
        //$customer = DB::table('m_customer')->where('id_user', $id)->first();

        $dataSales = DB::table('t_sales_order')
        ->select('m_user.name as user_name','t_sales_order.id','t_sales_order.so_code','t_sales_order.biaya_kirim','t_sales_order.so_date','m_customer.name as cust_name','m_customer.main_email','m_customer.id_user','m_metode_bayar.nama_metode_bayar','m_metode_bayar.desc_metode_bayar','t_sales_order.grand_total','t_sales_order.status_aprove','t_sales_order.atas_nama','t_sales_order.type_atas_nama','t_sales_order.id_sending','t_sales_order.type_sending','t_sales_order.sending_address','t_sales_order.id_rekening_tujuan','m_rekening_tujuan.atas_nama','m_bank.name as bank_name','m_rekening_tujuan.no_rekening','t_sales_order.ekspedisi','t_sales_order.ekspedisi_payment')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->join('m_user','m_user.id','m_customer.id_user')
        ->join('m_metode_bayar','m_metode_bayar.id','t_sales_order.metode_bayar')
        ->leftjoin('m_rekening_tujuan','m_rekening_tujuan.id','t_sales_order.id_rekening_tujuan')
        ->leftjoin('m_bank','m_bank.id','m_rekening_tujuan.bank')
        //->join('m_biaya_kirim','m_biaya_kirim.id','t_sales_order.metode_kirim')
        ->where('t_sales_order.id', $id)
        ->orderBy('t_sales_order.so_code','desc')
        ->first();

        if($dataSales->type_sending == "main"){
            $sending = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kelurahan_desa.zipcode','m_kecamatan.name as nama_kec')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $dataSales->id_sending)->first();
            $dataSales->sending = $sending;
        }else{
            $sending = DB::table('m_alamat_customer')
                        ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kelurahan_desa.zipcode','m_kecamatan.name as nama_kec')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_alamat_customer.id', $dataSales->id_sending)->first();
            $dataSales->sending = $sending;
        }

        if($dataSales->type_atas_nama == "main"){
            $billing = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kelurahan_desa.zipcode','m_kecamatan.name as nama_kec')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $dataSales->atas_nama)->first();
            $dataSales->billing = $billing;
        }else{
            $billing = DB::table('m_alamat_customer')
                        ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kelurahan_desa.zipcode','m_kecamatan.name as nama_kec')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_alamat_customer.id', $dataSales->atas_nama)->first();
            $dataSales->billing = $billing;
        }


        $sj = true;
        $cekSj = TSuratJalanModel::where('so_code',$dataSales->so_code)->get();
         //dd($cekSj);
        if (count($cekSj) > 0 ) {
            $sj = false; // jika ada false
        }
        $dataSales->sj = $sj;

        if($dataSales->status_aprove == 'pending'){
            $cek_tf_status = DB::table("m_konfirmasi_pembayaran")->select('status_pembayaran')->where('so_code',$dataSales->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
            if(!empty($cek_tf_status)){
                $dataSales->cek_tf = $cek_tf_status->status_pembayaran;
            }else{
                $dataSales->cek_tf = "0";
            }
        }else{
            $dataSales->cek_tf = "0";
        }
        $i=0;

        foreach ($cekSj as $key => $value) {
            $nama = $value->name_tampil;

            if($value->status == 'post'){
                $cekConfirmPengiriman = DB::table('m_konfirmasi_pengiriman')
                                        ->where('sj_code',$value->sj_code)
                                        ->get();
                if(count($cekConfirmPengiriman) > 0){
                    if($cekConfirmPengiriman[0]->customer_confirmed_by != 0){
                        $value->status_confirm = 1;
                    }else{
                        $value->status_confirm = 2;
                    }
                $i++;
                }else{
                    $value->status_confirm = 0;
                }
            }

            $value->nama_produk_tampil = $nama;
        }
        if(count($cekSj) == $i && count($cekSj) > 0){
            $dataSales->status_confirm = 1;
        }else{
            $dataSales->status_confirm = 0;
        }



        $detailSales = DB::table('d_sales_order')
                        ->select('d_sales_order.*','m_produk.name as name','m_produk.image as image')
                        ->join('m_produk','m_produk.id','d_sales_order.produk')
                        ->where('d_sales_order.so_code', $dataSales->so_code)
                        ->orderBy('d_sales_order.so_code','desc')
                        ->get();
        $dataSales->detail_barang = $detailSales;

        $dataBank = DB::table('m_rekening_tujuan')->join('m_bank','m_bank.id','m_rekening_tujuan.bank')->get();
        //dd($dataSales);
        return view('frontend.profile-payment-detail', compact('dataSales','dataBank'));
    }


    public function cekPayment()
    {
        if(Auth::check()){
            $id = Auth::id();
            $customer = DB::table('m_customer')->where('id_user', $id)->first();

            $dataSales = DB::table('t_sales_order')
            ->select('m_user.name as user_name','t_sales_order.id','t_sales_order.so_code','t_sales_order.biaya_kirim','t_sales_order.so_date','m_customer.name as cust_name','m_customer.id_user','m_metode_bayar.nama_metode_bayar','t_sales_order.grand_total','t_sales_order.status_aprove')
            ->join('m_customer','m_customer.id','t_sales_order.customer')
            ->join('m_user','m_user.id','m_customer.id_user')
            ->join('m_metode_bayar','m_metode_bayar.id','t_sales_order.metode_bayar')
            ->where('t_sales_order.customer', $customer->id)
            ->where('t_sales_order.so_from', "marketplace")
            ->orderBy('t_sales_order.so_code','desc')
            ->paginate(5);

            foreach ($dataSales as $dataSO) {
                $sj = true;
                $cekSj = TSuratJalanModel::where('so_code',$dataSO->so_code)->get();
                 //dd($cekSj);
                if (count($cekSj) > 0 ) {
                    $sj = false; // jika ada false
                }
                $dataSO->sj = $sj;

                if($dataSO->status_aprove == 'pending'){
                    $cek_tf_status = DB::table("m_konfirmasi_pembayaran")->select('status_pembayaran')->where('so_code',$dataSO->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
                    if(!empty($cek_tf_status)){
                        $dataSO->cek_tf = $cek_tf_status->status_pembayaran;
                    }else{
                        $dataSO->cek_tf = "0";
                    }
                }else{
                    $dataSO->cek_tf = "0";
                }
            }

            // foreach ($dataSales as $header) {
            //     $detailSales = DB::table('d_sales_order')
            //                     ->select('d_sales_order.*','m_produk.name')
            //                     ->join('m_produk','m_produk.id','d_sales_order.produk')
            //                     ->where('d_sales_order.so_code', $header->so_code)
            //                     ->orderBy('d_sales_order.so_code','desc')
            //                     ->get();

            //     $header->detail_barang = $detailSales;
            // }



            //dd($dataSales);
            return view('frontend.profile-payment', compact('dataSales'));
        }else{
            $type_message= "error_message";
            $message = "Silahkan Login Terlebih Dahulu";
            return redirect('/log')->with("$type_message", "$message");
        }
    }

    public function cekAddress($id)
    {
        $customer = DB::table('m_customer')
                    ->select('m_customer.*','m_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->where('id_user', $id)->first();

        $alamat_lain = DB::table('m_alamat_customer')
                        ->select('m_alamat_customer.*','m_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->join('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                        ->join('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->join('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->join('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('customer', $customer->id)
                        ->where('status', 'active')
                        ->get();
        if($customer->main_kelurahan == ""){
            $customer = "";
        }

        $provinsi = DB::table('m_provinsi')->get();

        //dd($dataSales);
        return view('frontend.profil-address', compact('customer','alamat_lain','provinsi'));
    }

    public function updateAddress(Request $request)
    {
        if($request->type_customer == 'main'){
            DB::beginTransaction();
            try{
                DB::table('m_customer')->where('id', $request->id_alamat)
                        ->update([
                            'name'                  => $request->name,
                            'main_office_phone_1'   => $request->main_office_phone_1,
                            'main_kelurahan'        => $request->id_kelurahan,
                            'main_address'          => $request->main_address
                        ]);
                DB::commit();
                return redirect()->back()->with('success_message',"Alamat berhasil diubah");
            }catch(\Exception $e){
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error_message',"Alamat gagal diubah");
            }
        }else{
            DB::beginTransaction();
            try{
                DB::table('m_alamat_customer')->where('id', $request->id_alamat)
                        ->update([
                            'name'                  => $request->name,
                            'main_office_phone_1'   => $request->main_office_phone_1,
                            'main_kelurahan'        => $request->id_kelurahan,
                            'main_address'          => $request->main_address,
                            'nama_alamat'           => $request->nama_alamat
                        ]);
                DB::commit();
                return redirect()->back()->with('success_message',"Alamat berhasil diubah");
            }catch(\Exception $e){
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error_message',"Alamat gagal diubah");
            }
        }
    }

    public function deleteAddress($id)
    {
        DB::beginTransaction();
        try{
            $alamat = DB::table('m_alamat_customer')->where('id', $id)->get();
            //dd($alamat);
            DB::table('m_alamat_customer')->where('id', $id)
                ->update([
                            'deleted_at'    => date("Y-m-d H:i:s"),
                            'status'        => 'inactive',
                        ]);

            DB::commit();
            return redirect()->back();
        }catch(\Exception $e){
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error_message',"Alamat gagal diubah");
        }
    }


    public function storeAddress(Request $request){
        DB::beginTransaction();
        try{
            $updateSales = DB::table('m_customer')->where('id_user', '=', $request->id_user_add)->update([
                'main_address' => $request->alamat_add,
                'main_kelurahan' => $request->id_kelurahan_add,
                'main_office_phone_1' => $request->phone_1_add,
                'main_pos'  => $request->codepos_add,
            ]);
        DB::commit();
        return redirect()->back()->with('success_message',"Alamat berhasil ditambah");
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error_message',"Alamat gagal diubah");
        }
    }


    public function storeOtherAddress(Request $request){
        DB::beginTransaction();
        try{
            $customer = DB::table('m_customer')->where('id_user', $request->id_user_lain)->first();

            $store = new MAlamatCustomerModel;
            $store->customer = $customer->id;
            $store->name = $request->nama_lain;
            $store->nama_alamat = $request->nama_alamat_lain;
            $store->main_address = $request->alamat_lain;
            $store->main_kelurahan = $request->id_kelurahan_lain;
            $store->main_pos = $request->codepos_lain;
            $store->main_office_phone_1 = $request->phone_1_lain;
            $store->save();

            DB::commit();
            return redirect()->back()->with('success_message',"Alamat berhasil ditambah");
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error_message',"Alamat gagal diubah");
        }
    }

    public function editAddress($id)
    {
        $customer = DB::table('m_customer')
                    ->select('m_customer.*','m_provinsi.id as id_prov','m_kota_kab.id as id_kota','m_kecamatan.id as id_kec','m_kelurahan_desa.id as id_kel')
                    ->join('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                    ->join('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    ->join('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    ->join('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->where('m_customer.id', $id)->first();

        return Response::json($customer);
    }

    public function editOtherAddress($id)
    {
        $customer = DB::table('m_alamat_customer')
                    ->select('m_alamat_customer.*','m_provinsi.id as id_prov','m_kota_kab.id as id_kota','m_kecamatan.id as id_kec','m_kelurahan_desa.id as id_kel')
                    ->join('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                    ->join('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    ->join('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    ->join('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->where('m_alamat_customer.id', $id)->first();

        return Response::json($customer);
    }

    public function getCity($id)
    {
        $city = DB::table('m_kota_kab')
                    ->select('m_kota_kab.*','m_kota_kab.id as id_kota_kab')
                    ->where('m_kota_kab.provinsi', $id)
                    ->get();
        //dd($city);

        return Response::json($city);
    }

    public function getKec($id)
    {
        $kec = DB::table('m_kecamatan')
                    ->select('m_kecamatan.*','m_kecamatan.id as id_kec')
                    ->where('m_kecamatan.kota_kab', $id)
                    ->get();
        //dd($city);

        return Response::json($kec);
    }

    public function getKel($id)
    {
        $kel = DB::table('m_kelurahan_desa')
                    ->select('m_kelurahan_desa.*','m_kelurahan_desa.id as id_kel')
                    ->where('m_kelurahan_desa.kecamatan', $id)
                    ->get();
        //dd($city);

        return Response::json($kel);
    }


    public function cekPassword($id)
    {
        return view('frontend.profil-password', compact('id'));
    }

    public function storeKonfirmasiPengiriman($sjcode){
        $cek =  DB::table('m_konfirmasi_pengiriman')->where('sj_code',$sjcode)->get();
        DB::beginTransaction();
        try{
            if(count($cek) == 0){
                DB::table('m_konfirmasi_pengiriman')
                    ->insert([
                        'sj_code' => $sjcode,
                        'customer_confirmed_by' => auth()->user()->id,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
            }else{
                DB::table('m_konfirmasi_pengiriman')
                    ->where('sj_code', $sjcode)
                    ->update([
                        'customer_confirmed_by' =>auth()->user()->id,
                    ]);
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }
        return redirect()->back();
    }

    public function storeReview(Request $request){
        DB::beginTransaction();
        try{
            DB::table('m_review')
                    ->insert([
                        'so_code' => $request->so_code,
                        'id_barang' => $request->id_produk,
                        'id_user' => $request->id_user,
                        'rating' => $request->rating,
                        'comment' => $request->comment,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }
        return redirect()->back();
    }

    public function storePassword(Request $request){
        $this->validate($request, [
                'current_password' => 'required',
                'new_password' => 'required|min:6',
            ]);

        $cek_pass = DB::table('m_user')->where('id',$request->id)->first();
        //dd($cek_pass);

        if(password_verify($request->current_password, $cek_pass->password)){
            DB::beginTransaction();
            try{
                DB::table('m_user')->where('id',$request->id)
                        ->update([
                            'password' => bcrypt(trim($request->new_password)),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
                dd($e);
            }
            return redirect()->back()->with('success_message','Password berhasil diubah');
        }else{
            return redirect()->back()->with('error_message','Password gagal diubah');
        }
    }

    public function filterPesanan(Request $request){

        $result = [];

        $date_now = date('Y-m-d 23:59:59');
        $date_start = date('Y-m-d 00:00:00');
        $week_now = date('Y-m-d 23:59:59', strtotime($date_now.' - 7 days'));
        $month_now = date('Y-m-d 23:59:59', strtotime($date_now.' - 30 days'));
        $year_now = date('Y-m-d 23:59:59', strtotime($date_now.' - 365 days'));

        $customer = DB::table('m_customer')->where('id_user', $request->customer)->first();

        $dataSales_query = DB::table('t_sales_order');
        $dataSales_query->select('m_user.name as user_name','t_sales_order.id','t_sales_order.so_code','t_sales_order.biaya_kirim','t_sales_order.so_date','m_customer.name as cust_name','m_customer.id_user','m_metode_bayar.nama_metode_bayar','t_sales_order.grand_total','t_sales_order.status_approve');
        $dataSales_query->join('m_customer','m_customer.id','t_sales_order.customer');
        $dataSales_query->join('m_user','m_user.id','m_customer.id_user');
        $dataSales_query->join('m_metode_bayar','m_metode_bayar.id','t_sales_order.metode_bayar');
        $dataSales_query->where('t_sales_order.customer', $customer->id);
        if($request->waktu != 0){
            if($request->waktu == 1){
                $dataSales_query->where('t_sales_order.so_date', '<' ,$date_now);
                $dataSales_query->where('t_sales_order.so_date', '>' ,$date_start);
            }elseif($request->waktu == 2){
                $dataSales_query->where('t_sales_order.so_date', '>' ,$week_now);
            }elseif($request->waktu == 3){
                //$dataSales_query->where('t_sales_order.so_date', '>' ,$month_now);
                $dataSales_query->whereMonth('t_sales_order.so_date',date('m', strtotime($date_now)));
                $dataSales_query->whereYear('t_sales_order.so_date',date('Y', strtotime($date_now)));
            }elseif($request->waktu = 4){
                //$dataSales_query->where('t_sales_order.so_date', '>' ,$year_now);
                $dataSales_query->whereYear('t_sales_order.so_date',date('Y', strtotime($date_now)));
            }
        }
        $dataSales_query->orderBy('t_sales_order.id','desc');
        $dataSales = $dataSales_query->get();

        foreach ($dataSales as $index_so => $dataSO) {
            $sj = true;
            $cekSj = TSuratJalanModel::where('so_code',$dataSO->so_code)->get();
             //dd($cekSj);
            if (count($cekSj) > 0 ) {
                $sj = false; // jika ada false
            }
            $dataSO->sj = $sj;
            if($dataSO->status_approve == 'pending'){
                $cek_tf_status = MKonfirmasiPembayaran::select('status_pembayaran')->where('so_code',$dataSO->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
                if(!empty($cek_tf_status)){
                    $dataSO->cek_tf = $cek_tf_status->status_pembayaran;
                }else{
                    $dataSO->cek_tf = "0";
                }
            }else{
                $dataSO->cek_tf = "0";
            }

            $detailSales = DB::table('d_sales_order')
                            ->select('d_sales_order.*','m_produk.name','m_produk.name_tampil','m_produk.ukuran','m_produk.satuan_ukuran','m_produk.class','m_produk.warna','m_merek_produk.name as nama_merek')
                            ->join('m_produk','m_produk.id','d_sales_order.produk')
                            ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek_id')
                            ->where('d_sales_order.so_code', $dataSO->so_code)
                            ->orderBy('d_sales_order.so_code','desc')
                            ->get();

            foreach ($detailSales as $key => $value) {
                $nama = $value->name_tampil;

                // if($value->ukuran != '' || $value->ukuran != null ){
                //     $nama = $nama.' UKURAN '.$value->ukuran.' '.$value->satuan_ukuran;

                // }
                // if($value->nama_merek != '' || $value->nama_merek != null ){
                //     $nama = $nama.' MERK '.$value->nama_merek;
                // }
                // if($value->class != '' || $value->class != null){
                //     $nama = $nama.' CLASS '.$value->class;
                // }
                // if($value->warna != '' || $value->warna != null ){
                //     $nama = $nama.' WARNA '.$value->warna;
                // }

                $value->nama_produk_tampil = $nama;
            }

            $dataSO->detail_barang = $detailSales;
            if($request->status != 0){
                if($request->status == 1){
                    if($dataSO->status_approve == 'pending'){
                       $cek_tf_status = MKonfirmasiPembayaran::select('status_pembayaran')->where('so_code',$dataSO->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
                       if($cek_tf_status){
                            unset($dataSales[$index_so]);
                        }
                    }else{
                        unset($dataSales[$index_so]);
                    }
                }elseif($request->status == 2){
                    if($dataSO->status_approve == 'pending'){
                        $cek_tf_status = MKonfirmasiPembayaran::select('status_pembayaran')->where('so_code',$dataSO->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
                        if($cek_tf_status){
                            if($cek_tf_status->status_pembayaran != "pending"){
                                unset($dataSales[$index_so]);
                            }
                        }else{
                            unset($dataSales[$index_so]);
                        }
                    }else{
                        unset($dataSales[$index_so]);
                    }
                }elseif($request->status == 8){
                    if($dataSO->status_approve != 'pending'){
                        unset($dataSales[$index_so]);
                    }
                }elseif($request->status == 3){
                    if($dataSO->status_approve == 'approved'){
                        $cek_sj =  DB::table('t_surat_jalan')
                        ->where('t_surat_jalan.so_code',$dataSO->so_code)
                        ->get();
                        if(sizeof($cek_sj) <= 0){
                            unset($dataSales[$index_so]);
                        }else{
                            $cek_sj_1 =  DB::table('t_surat_jalan')
                            ->where('t_surat_jalan.so_code',$dataSO->so_code)
                            ->where('status', 'post')
                            ->get();
                            if(sizeof($cek_sj_1) > 0){
                                unset($dataSales[$index_so]);
                            }
                        }
                    }else{
                        unset($dataSales[$index_so]);
                    }
                }elseif($request->status == 7){
                    if($dataSO->status_approve == 'approved'){
                        $cek_sj =  DB::table('t_surat_jalan')
                        ->where('t_surat_jalan.so_code',$dataSO->so_code)
                        ->get();
                        if(sizeof($cek_sj) > 0){
                            unset($dataSales[$index_so]);
                        }
                    }else{
                        unset($dataSales[$index_so]);
                    }
                }elseif($request->status == 4){
                    $cek_sj =  DB::table('t_surat_jalan')
                    ->where('t_surat_jalan.so_code',$dataSO->so_code)
                    ->where('t_surat_jalan.status',"post")
                    ->get();
                    if(sizeof($cek_sj) <= 0){
                        unset($dataSales[$index_so]);
                    }
                }elseif($request->status == 5){
                    $cek_sj =  DB::table('t_surat_jalan')
                    ->where('t_surat_jalan.so_code',$dataSO->so_code)
                    ->where('t_surat_jalan.status',"post")
                    ->get();

                    if(count($cek_sj) > 0){
                        foreach ($cek_sj as $key_sj => $value_sj) {
                            $cek_konfirm = DB::table('m_konfirmasi_pengiriman')->where('sj_code',$value_sj->sj_code)->get();
                            if(count($cek_konfirm) <= 0){
                                unset($dataSales[$index_so]);
                            }
                        }
                    }else{
                        unset($dataSales[$index_so]);
                    }
                }elseif($request->status == 6){
                    if($dataSO->status_approve == 'pending'){
                        $cek_tf_status = MKonfirmasiPembayaran::select('status_pembayaran')->where('so_code',$dataSO->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
                        if($cek_tf_status){
                            if($cek_tf_status->status_pembayaran == "pending"){
                                unset($dataSales[$index_so]);
                            }
                        }else{
                            unset($dataSales[$index_so]);
                        }
                    }
                }
            }
        }

        $dataSales = array_values($dataSales->toArray());
        $result['pesanan'] = $dataSales;
        return Response::json($result);
    }

}
