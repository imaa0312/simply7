<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MProdukModel;
use App\Models\TSalesOrderModel;
use App\Models\DSalesOrder;
use App\Models\MStokProdukModel;
use App\Models\MKonfirmasiPembayaran;
use App\Models\TSuratJalanModel;
use App\Models\DSuratJalanModel;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Database\QueryException;
use DB;
use Auth;
use Response;
use App\Mail\PaymentConfirmMail;
use App\Mail\PaymentRejectMail;
use App\Mail\PaymentSuccessMail;
use Mail;

class PaymentConfirmCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($socode)
    {
        $cek_status_tf = MKonfirmasiPembayaran::select('status_pembayaran')
                            ->where('so_code',$socode)
                            ->orderBy('id_konfirmasi','desc')
                            ->first();
        $total_so = DB::table('t_sales_order')->select('grand_total')->where('so_code',$socode)->first();
        if(empty($cek_status_tf) || $cek_status_tf->status_pembayaran == 'cancel'){
            $bank= DB::table('m_rekening_tujuan')->select('m_rekening_tujuan.*','m_bank.name')->join('m_bank','m_bank.id','m_rekening_tujuan.bank')->get();
            return view('frontend.payment-confirmation', compact('socode','bank','total_so'));
        }else{
            return redirect()->back()->with('error_message','Anda sudah melakukan pembayaran');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $error = "";
        $kb = 1024;
        $mb = 1024*1024;
        $gb = 1024*1024*1024;
        $file   = $request->image;

        if ($file->getSize() == false) {
            return redirect()->back()->with('error_message','Ukuran File terlalu besar');
        }else{
            DB::beginTransaction();
            try{
                    //$file   = $request->image;
                    $filename_path_bukti = date("ymdHis").$request->socode.".jpg";
                    $destinationPath = 'upload/bukti-pembayaran';

                    //dd($file);

                    $store = new MKonfirmasiPembayaran;
                    $store->so_code             = $request->socode;
                    $store->email_pengirim      = $request->email_sender;
                    $store->bank_penerima       = $request->bank_tujuan;
                    $store->bank_pengirim       = $request->bank_sender;
                    $store->tanggal_transfer    = $request->tanggal_transfer;
                    $store->atas_nama           = $request->atas_nama;
                    $store->nominal_transfer    = $request->nominal_transfer;
                    $store->bukti_transfer      = $filename_path_bukti;
                    $store->created_at          = date('Y-m-d H:i:s');
                    $store->updated_at          = date('Y-m-d H:i:s');
                    $store->save();

                    $file->move($destinationPath, $filename_path_bukti);
                    $type_message= "success_message";
                    $message = "Terima Kasih Telah Melakukan Konfirmasi Pembayaran, Cek Pembayaran Anda Pada Menu Daftar Pembayaran";
                    $customer = DB::table('m_customer')
                                    ->select('m_customer.name','m_user.email')
                                    ->join('m_user','m_user.id','m_customer.id_user')
                                    ->where('m_customer.id_user',auth()->user()->id)
                                    ->first();

                    $bank = DB::table('m_rekening_tujuan')->select('m_rekening_tujuan.*','m_bank.name')->join('m_bank','m_bank.id','m_rekening_tujuan.bank')->where('m_rekening_tujuan.id',$request->bank_tujuan)->first();

                    Mail::to($customer->email)->send(new PaymentConfirmMail($customer,$bank,$request->socode,$request->tanggal_transfer,$request->nominal_transfer));
                    DB::commit();
                    return redirect('/profil-payment/'.auth()->user()->id)->with("$type_message", "$message");    
            }catch (\Exception $e) {
                    dd($e);
                    DB::rollback();
            }
        }
        
        // dd($request->socode, $request->tanggal_transfer);
        // return false;
    }

    public function post(Request $request)
    {   
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        
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
        
    }

    public function setujuiPembayaran($id_pembayaran, $id)
    {
        DB::beginTransaction();
            try{
                $dataPembayaran = DB::table('m_konfirmasi_pembayaran')
                    ->where('id_konfirmasi', '=', $id_pembayaran)
                    ->update([
                                'status_pembayaran' => 'success',
                                'checked_by'        => $id,
                                'updated_at'        => date("Y-m-d H:i:s"),
                            ]);

                $getSOCode = DB::table('m_konfirmasi_pembayaran')
                    ->select('m_konfirmasi_pembayaran.so_code')
                    ->where('id_konfirmasi', '=', $id_pembayaran)
                    ->first();

                $updateSO = DB::table('t_sales_order')
                            ->where('so_code', $getSOCode->so_code)
                            ->update(['status_aprove' => 'in process']);

                $so = DB::table('m_konfirmasi_pembayaran')
                        ->select('m_konfirmasi_pembayaran.so_code','m_bank.name as bank_name','m_konfirmasi_pembayaran.tanggal_transfer','m_konfirmasi_pembayaran.nominal_transfer','m_konfirmasi_pembayaran.reject_reason','m_user.email as customer_email','m_customer.name as customer_name')
                        ->join('m_rekening_tujuan','m_rekening_tujuan.id','m_konfirmasi_pembayaran.bank_penerima')
                        ->join('m_bank','m_bank.id','m_rekening_tujuan.bank')
                        ->join('t_sales_order','t_sales_order.so_code','m_konfirmasi_pembayaran.so_code')
                        ->join('m_customer','m_customer.id','t_sales_order.customer')
                        ->join('m_user','m_user.id','m_customer.id_user')
                        ->where('id_konfirmasi', $id_pembayaran)
                        ->first();
                
                Mail::to($so->customer_email)->send(new PaymentSuccessMail($so));
                DB::commit();

                return redirect('admin/transaksi-pembayaran-wait-transfer');
            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                return redirect()->back();
            }
    }

    public function gettolakPembayaran($id_pembayaran, $id)
    {
        $so = DB::table('m_konfirmasi_pembayaran')
                ->select('so_code')
                ->where('id_konfirmasi', $id_pembayaran)
                ->first();
        
        return view('admin.transaksi.pembayaran.reject-payment', compact('id_pembayaran','id','so'));
    }

    public function tolakPembayaran(Request $request)
    {
        DB::beginTransaction();
            try{
                $dataPembayaran = DB::table('m_konfirmasi_pembayaran')
                    ->where('id_konfirmasi', '=', $request->id_pembayaran)
                    ->update([
                                'status_pembayaran' => 'cancel',
                                'checked_by'        => $request->id,
                                'reject_reason'     => $request->reject_reason
                            ]);

                // $customer = DB::table('m_customer')
                //                 ->select('m_customer.name','m_user.email')
                //                 ->join('m_user','m_user.id','m_customer.id_user')
                //                 ->where('m_customer.id_user',auth()->user()->id)
                //                 ->first();
                
                $telp = DB::table('m_company_profile')->first();
                
                $so = DB::table('m_konfirmasi_pembayaran')
                        ->select('m_konfirmasi_pembayaran.so_code','m_bank.name as bank_name','m_konfirmasi_pembayaran.tanggal_transfer','m_konfirmasi_pembayaran.nominal_transfer','m_konfirmasi_pembayaran.reject_reason','m_user.email as customer_email','m_customer.name as customer_name')
                        ->join('m_rekening_tujuan','m_rekening_tujuan.id','m_konfirmasi_pembayaran.bank_penerima')
                        ->join('m_bank','m_bank.id','m_rekening_tujuan.bank')
                        ->join('t_sales_order','t_sales_order.so_code','m_konfirmasi_pembayaran.so_code')
                        ->join('m_customer','m_customer.id','t_sales_order.customer')
                        ->join('m_user','m_user.id','m_customer.id_user')
                        ->where('id_konfirmasi', $request->id_pembayaran)
                        ->first();
                
                Mail::to($so->customer_email)->send(new PaymentRejectMail($telp,$so));
                DB::commit();
                return redirect('admin/transaksi-pembayaran-wait-transfer');
            }catch (\Exception $e) {
                dd($e);
                DB::rollback();
                return redirect()->back();
            }
    }

    public function cekInvoice($so_code)
    {
        $dataSales = DB::table('t_sales_order')
        ->select('m_user.name as user_name','t_sales_order.id','t_sales_order.so_code','t_sales_order.biaya_kirim','t_sales_order.so_date','m_customer.name as cust_name','m_customer.main_email','m_customer.id_user','m_metode_bayar.nama_metode_bayar','m_metode_bayar.desc_metode_bayar','t_sales_order.grand_total','t_sales_order.status_approve','m_biaya_kirim.nama_biaya_kirim','t_sales_order.atas_nama','t_sales_order.type_atas_nama','t_sales_order.id_sending','t_sales_order.type_sending','t_sales_order.sending_address')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->join('m_user','m_user.id','m_customer.id_user')
        ->join('m_metode_bayar','m_metode_bayar.id','t_sales_order.metode_bayar')
        ->join('m_biaya_kirim','m_biaya_kirim.id','t_sales_order.metode_kirim')
        ->where('t_sales_order.so_code', $so_code)
        ->orderBy('t_sales_order.so_code','desc')
        ->first();

        if($dataSales->type_sending == "main"){
            $sending = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $dataSales->id_sending)->first();
            $dataSales->sending = $sending;
        }else{
            $sending = DB::table('m_alamat_customer')
                        ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_alamat_customer.id', $dataSales->id_sending)->first();
            $dataSales->sending = $sending;
        }

        if($dataSales->type_atas_nama == "main"){
            $billing = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $dataSales->atas_nama)->first();
            $dataSales->billing = $billing;
        }else{
            $billing = DB::table('m_alamat_customer')
                        ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.main_kelurahan')
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

        if($dataSales->status_approve == 'pending'){
            $cek_tf_status = MKonfirmasiPembayaran::select('status_pembayaran')->where('so_code',$dataSales->so_code)->where('status_pembayaran','!=','cancel')->orderBy('id_konfirmasi','desc')->first();
            if(!empty($cek_tf_status)){
                $dataSales->cek_tf = $cek_tf_status->status_pembayaran;
            }else{
                $dataSales->cek_tf = "0";
            }
        }else{
            $dataSales->cek_tf = "0";
        }
        

        
        $detailSales = DB::table('d_sales_order')
                        ->select('d_sales_order.*','m_produk.name_tampil')
                        ->join('m_produk','m_produk.id','d_sales_order.produk')
                        ->where('d_sales_order.so_code', $dataSales->so_code)
                        ->orderBy('d_sales_order.so_code','desc')
                        ->get();
        $dataSales->detail_barang = $detailSales;

        $dataBank = DB::table('m_rekening_tujuan')->join('m_bank','m_bank.id','m_rekening_tujuan.bank')->get();
        
        //dd($dataSales);
        return view('frontend.invoice', compact('dataSales','dataBank'));
    }
    
}
