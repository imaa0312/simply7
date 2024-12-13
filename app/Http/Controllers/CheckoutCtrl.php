<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\OrderMail;
use App\Mail\PreOrderMail;
use App\Models\MProdukModel;
use App\Models\TSalesOrderModel;
use App\Models\DSalesOrder;
use App\Models\MStokProdukModel;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Database\QueryException;
use DB;
use Auth;
use Response;
use Mail;

class CheckoutCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Cart::count()>0){
            $customer = DB::table('m_customer')->where('id_user', Auth::user()->id)->first();
            // if ($customer->credit_limit == null) {
            //     $credit_limit = 0;
            // }else{
            //     $credit_limit = $customer->credit_limit;
            // }

            // $data_credit_customer = DB::table('t_sales_order')
            //     //->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
            //     ->where('t_sales_order.customer', $customer->id)
            //     ->where('t_sales_order.metode_bayar', 3)
            //     ->where(function ($query) {
            //         $query->where('t_sales_order.status_approve','!=','closed')
            //               ->Where('t_sales_order.status_approve','!=','reject')
            //               ->Where('t_sales_order.status_approve','!=','cancel');
            //         })
            //     ->get();

            // $credit_customer = 0;
            // $array_so = [];
            // foreach ($data_credit_customer as $index_so => $headerSo) {
            //     $dataDetailSo = DB::table('d_sales_order')->where('so_code',$headerSo->so_code)->get();
            //     $alltotaldetail = DB::table('d_sales_order')->where('so_code',$headerSo->so_code)->sum('total');
            //     $totalQtySo = DB::table('d_sales_order')->where('so_code',$headerSo->so_code)->sum('qty');

            //     $diskonHeader = $alltotaldetail - $headerSo->grand_total;
            //     $diskonHeaderPerItem = $diskonHeader / $totalQtySo;
            //     foreach ($dataDetailSo as $raw_data) {

            //         //get-diskon-header-per-produk

            //         //get-detail-total-so
            //         $qty = $raw_data->qty;
            //         $sj_qty = $raw_data->sj_qty;
            //         $sisa_qty = $qty - $sj_qty;
            //         $total = $raw_data->total;

            //         $total_detail = ( ($total / $qty) - $diskonHeaderPerItem ) * $sisa_qty;



            //         $credit_customer = $credit_customer + $total_detail;
            //     }
            //     // $qty = $raw_data->qty;
            //     // $sj_qty = $raw_data->sj_qty;
            //     // $sisa_qty = $qty - $sj_qty;
            //     // $total = $raw_data->total;

            //     // $total_credit = ($total / $qty) * $sisa_qty;

            //     // $credit_customer = $credit_customer + $total_credit;
            //     $array_so[$index_so] = $headerSo->so_code;
            // }

            // $piutang = DB::table('t_faktur')
            //         ->where('customer', $customer->id)
            //         ->whereIn('so_code', $array_so)
            //         ->where('status_payment', 'unpaid')
            //         ->sum('total');

            // $piutang_dibayar = DB::table('t_pembayaran')
            //             ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
            //             ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
            //             ->where('t_pembayaran.customer', $customer->id)
            //             ->where('t_faktur.status_payment', 'unpaid')
            //             ->where('t_pembayaran.status', 'approved')
            //             ->sum('d_pembayaran.total');

            // $piutang = $piutang - $piutang_dibayar;

            // $sisaCredit = $credit_limit - $credit_customer - $piutang;
            $main_address = DB::table('m_customer')
                            ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov','m_kelurahan_desa.name as nama_kel','m_kecamatan.name as nama_kec')
                            ->join('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                            ->join('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                            ->join('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                            ->join('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                            ->where('id_user',Auth::user()->id)->first();
            if(!empty($main_address)){
                $main_address->class = 'main';
            }else{
                $main_address= "";
            }
            $other_address = DB::table('m_alamat_customer')
                            ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                            ->join('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                            ->join('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                            ->join('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                            ->join('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                            ->where('customer',$customer->id)
                            ->where('status','active')
                            ->get();
            if(count($other_address) > 0){
                foreach ($other_address as $other) {
                    $other->class = 'other';
                }
            }else{
                $other_address =[];
            }
            //dd($other_address);
            //$biaya_kirim = DB::table('m_biaya_kirim')->get();
            $method_bayar = DB::table('m_metode_bayar')->where("status",'active')->where("nama_metode_bayar",'Transfer')->get();
            $bank = DB::table('m_rekening_tujuan')->select("m_rekening_tujuan.*","m_bank.name")->join('m_bank','m_bank.id','m_rekening_tujuan.bank')->get();
            $provinsi = DB::table('m_provinsi')->get();
            // $kota = DB::table('m_kota_kab')->get();
            // $kec = DB::table('m_kecamatan')->get();
            // $kel = DB::table('m_kelurahan_desa')->get();
            //dd($main_address,$other_address);
            return view('frontend.checkout', compact('main_address','other_address','method_bayar','bank','provinsi','customer'));
        }else{
            return redirect('/');
        }
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

    public function post(Request $request)
    {
        if(Cart::count()>0){
        // $biaya_kirim = $request->input('biaya_kirim');
        // $nama_biaya_kirim = $request->input('nama_biaya_kirim');
        $biaya_kirim = $request->input('biaya_kirim_1');
        $nama_biaya_kirim = $request->input('nama_biaya_kirim_1');
        $id_billing_address = $request->input('id_billing_address');
        $type_billing_address = $request->input('type_billing_address');
        // $id_shipping_address = $request->input('id_shipping_address');
        // $type_shipping_address = $request->input('type_shipping_address');
        $id_shipping_address = $request->input('id_shipping_address_1');
        $type_shipping_address = $request->input('type_shipping_address_1');
        $method_bayar = $request->input('method_bayar');
        $nama_method_bayar = $request->input('nama_method_bayar');

        if($type_shipping_address == "main"){
            $shipping = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $id_shipping_address)->first();
        }else{
            $shipping = DB::table('m_alamat_customer')
                            ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                            ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                            ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                            ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                            ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                            ->where('m_alamat_customer.id', $id_shipping_address)->first();
        }

        if($type_billing_address == "main"){
            $billing = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $id_billing_address)->first();
        }else{
            $billing = DB::table('m_alamat_customer')
                            ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                            ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                            ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                            ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                            ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                            ->where('m_alamat_customer.id', $id_billing_address)->first();
        }

        $all_biaya_kirim = DB::table('m_biaya_kirim')->where('id',$nama_biaya_kirim)->first();
        $all_method_bayar = DB::table('m_metode_bayar')->where('id',$method_bayar)->first();
        $bank = DB::table('m_rekening_tujuan')->join('m_bank','m_bank.id','m_rekening_tujuan.bank')->get();
        //dd($biaya_kirim);


        $hasil_checkout = array(
            "biaya_kirim" => $biaya_kirim,
            "nama_biaya_kirim" => $nama_biaya_kirim,
            "id_billing_address" => $id_billing_address,
            "id_shipping_address" => $id_shipping_address,
            "method_bayar" => $method_bayar,
            "nama_method_bayar" => $nama_method_bayar,
            "type_billing_address" => $type_billing_address,
            "type_shipping_address" => $type_shipping_address,
        );

        return view('frontend.order-confirm', compact('hasil_checkout', 'billing', 'shipping','all_biaya_kirim','all_method_bayar','bank'));
        }else{
            return redirect('/');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(count(Cart::content()) > 0)
        {
            // $this->validate($request, [
            //     'customer' => 'required',
            //     'id_shipping_address' => 'required',
            //     'id_billing_address' => 'required',
            // ]);

            //{Cek Cart Final}
            $date_now = date('d-m-Y');
            $date_now_1 = date('d F Y');
            $date = '01-'.date('m-Y', strtotime($date_now));
            $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

            $cek_order_all = 0;
            foreach (Cart::content() as $key => $value) {
                $produk = MProdukModel::find($value->id);

                $balance = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $produk->code)
                    ->where('m_stok_produk.gudang', 2)
                    ->where('type', 'closing')
                    ->whereMonth('periode',date('m', strtotime($date_last_month)))
                    ->whereYear('periode',date('Y', strtotime($date_last_month)))
                    ->sum('balance');

                $stok = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $produk->code)
                    ->where('m_stok_produk.gudang', 2)
                    ->whereMonth('created_at',date('m', strtotime($date_now)))
                    ->whereYear('created_at',date('Y', strtotime($date_now)))
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('stok');

                $stok = $stok + $balance;
                if($stok <= 0){
                    $stok = 0;
                }else{
                    $stok = $stok;
                }

                $qty_order = $value->qty;
                $id_order = $value->id;
                $name_order = $value->name;
                $price_order = $value->price;
                $duplicates = Cart::search(function ($cartItem, $rowId) use ($value) {
                    return $cartItem->id === $value->id;
                });

                if($stok > 0){
                    if($value->qty > $stok){
                        $update_cek_qty = $value->qty - $stok;
                        Cart::update($key, $stok);
                        $cek_order_all = $cek_order_all+1;
                    }
                }else{
                    Cart::remove($key);
                    $cek_order_all = $cek_order_all+1;

                }

                // if($value->options->type=="order"){
                //     $qty_order = $value->qty;
                //     $id_order = $value->id;
                //     $name_order = $value->name;
                //     $price_order = $value->price;
                //     $duplicates = Cart::search(function ($cartItem, $rowId) use ($value) {
                //         return $cartItem->id === $value->id;
                //     });
                //     $check_req =0;
                //     foreach ($duplicates as $key1 => $value1) {
                //         if($value1->options->type == "request"){
                //             $check_req +=1;
                //         }
                //     }
                //     if($check_req == 0){
                //         if($stok > 0){
                //             if($value->qty > $stok){
                //                 $update_cek_qty = $value->qty - $stok;
                //                 Cart::update($key, $stok);
                //             }
                //         }else{
                //             Cart::remove($key);
                //         }
                //     }
                // }else{
                //     $qty_order = $value->qty;
                //     $id_order = $value->id;
                //     $name_order = $value->name;
                //     $price_order = $value->price;
                //     $duplicates = Cart::search(function ($cartItem, $rowId) use ($value) {
                //         return $cartItem->id === $value->id;
                //     });
                //     $check_order = 0;
                //     foreach ($duplicates as $key1 => $value1) {
                //         if($value1->options->type == "order"){
                //             $check_order +=1;
                //             $qty_all = $value1->qty + $value->qty;
                //             $key_order = $key1;
                //         }
                //     }

                //     if($check_order == 0){
                //         if($stok > 0){
                //             if($value->qty > $stok){
                //                 $update_cek_qty = $value->qty - $stok;
                //                 Cart::update($key,$update_cek_qty);
                //                 Cart::add($id_order, $name_order, $stok, $price_order,['type'=>'order','min_order'=>1]);
                //             }
                //         }
                //     }else{
                //         if($stok > 0){
                //             if($qty_all > $stok){
                //                 $update_cek_qty = $qty_all - $stok;
                //                 Cart::update($key_order, $stok);
                //                 Cart::update($key, $update_cek_qty);
                //             }else{
                //                 Cart::update($key_order, $qty_all);
                //                 Cart::remove($key);
                //             }
                //         }else{
                //             Cart::remove($key_order);
                //             Cart::update($key, $qty_all);
                //         }
                //     }
                // }
            }
            //{cek cart final}

            //dd(Cart::content());
            if($cek_order_all == 0){

                $dataDate = date("ym");

                $getLastCode = DB::table('t_sales_order')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
                $getLastCode = $getLastCode +1;
                $date_Now = date('Y-m-d H:i:s');
                $sending_date = date('Y-m-d H:i:s', strtotime($date_Now.' + 1 days'));

                $customer = DB::table('m_customer')->where('id_user', $request->customer)->first();
                $user = DB::table('m_user')->where('id', $request->customer)->first();
                //dd($user);
                if($request->type_shipping_address == "main"){
                    $shipping = DB::table('m_customer')
                                ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                                ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                                ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                                ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                                ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                                ->where('m_customer.id', $request->id_shipping_address)->first();
                    $type_shipping_address = "main";
                }else{
                    $shipping = DB::table('m_alamat_customer')
                                    ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                                    ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                                    ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                                    ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                                    ->where('m_alamat_customer.id', $request->shipping_address)->first();
                    $type_shipping_address = "other";
                }

                $billing = DB::table('m_customer')
                                ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                                ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                                ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                                ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                                ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                                ->where('m_customer.id', $customer->id)->first();
                $type_billing_address = "main";

                //$all_biaya_kirim = DB::table('m_biaya_kirim')->where('id',$request->nama_biaya_kirim)->first();
                $metode_bayar = DB::table('m_metode_bayar')->where('id',$request->method_bayar)->first();
                //dd($billing);

                $nol = null;
                if(strlen($getLastCode) == 1){
                    $nol = "000";
                }elseif(strlen($getLastCode) == 2){
                    $nol = "00";
                }elseif(strlen($getLastCode)== 3){
                    $nol = "0";
                }else{
                    $nol = null;
                }

                $setInvoice = 'SOTK'.$dataDate.$nol.$getLastCode;
                // dd($setInvoice);

                //$sending_date = date('Y-m-d', strtotime($request->sending_date));
                //$so_date = date('Y-m-d', strtotime($request->so_date));
                // ( $request->cod == 'on' ) ? $request->cod = true : $request->cod = false ;

                ////insert d_transaksi
                $array = [];
                $array_req = [];
                $i = 0;
                $cek_request = 0;
                $cek_order = 0;
                $grand_total = 0;
                $grand_total_req = 0;
                // $getIdProduk = $request->id_produk;
                // $getQty = $request->jumlah;
                // $getTotal = str_replace(array('.', ','), '' , $request->subTotal);
                // // $getHarga = $request->hargaProduk;
                // $getHarga = str_replace(array('.', ','), '' , $request->hargaDasar);
                //getValueProduk insert To array

                //dd($getIdProduk);

                foreach (Cart::content() as $key => $value) {
                    $array[$i]['invoice_t'] = $setInvoice;
                    $array[$i]['produk_id'] = $value->id;
                    $array[$i]['qty'] = strval($value->qty);
                    $array[$i]['total'] = str_replace(array('.', ','), '' , ($value->qty*$value->price));
                    $array[$i]['harga'] = str_replace(array('.', ','), '' , $value->price);
                    $grand_total += $value->qty*$value->price;


                    $produk = MProdukModel::select('m_produk.name','m_produk.satuan_terkecil','m_merek_produk.name as nama_merek')
                        ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek_id')
                        ->find($value->id);

                    $nama = $produk->name;

                    // if($produk->ukuran != '' || $produk->ukuran != null ){
                    //     $nama = $nama.' UKURAN '.$produk->ukuran.' '.$produk->satuan_ukuran;

                    // }
                    // if($produk->nama_merek != '' || $produk->nama_merek != null ){
                    //     $nama = $nama.' MERK '.$produk->nama_merek;
                    // }
                    // if($produk->class != '' || $produk->class != null){
                    //     $nama = $nama.' CLASS '.$produk->class;
                    // }
                    // if($produk->warna != '' || $produk->warna != null ){
                    //     $nama = $nama.' WARNA '.$produk->warna;
                    // }

                    $produk->nama_produk_tampil = $nama;

                    $array_all[$i]['invoice_t'] = $setInvoice;
                    $array_all[$i]['produk_id'] = $value->id;
                    $array_all[$i]['qty'] = strval($value->qty);
                    $array_all[$i]['total'] = str_replace(array('.', ','), '' , ($value->qty*$value->price));
                    $array_all[$i]['harga'] = str_replace(array('.', ','), '' , $value->price);
                    $array_all[$i]['type'] = $value->options->type;
                    $array_all[$i]['nama_produk'] = $produk->nama_produk_tampil;

                    $i++;
                }

                $array = array_values($array);
                //$array_req = array_values($array_req);
                $array_all = array_values($array_all);
                //$harga_tanpa_ongkir = $grand_total + $grand_total_req;
                $harga_tanpa_ongkir = $grand_total;
                //$harga_all = $grand_total + $grand_total_req + $all_biaya_kirim->harga_biaya_kirim;
                $harga_all = $grand_total;



                    DB::beginTransaction();
                        try{
                                $store = new TSalesOrderModel;
                                $store->so_code = $setInvoice;
                                $store->customer = $customer->id;
                                $store->atas_nama = $billing->id;
                                $store->type_atas_nama = $type_billing_address;
                                $store->sending_address = $shipping->main_address.','.$shipping->nama_kota.','.$shipping->nama_prov;
                                $store->type_sending = $type_shipping_address;
                                $store->id_sending = $shipping->id;
                                $store->gudang = 2;
                                $store->so_from = 'marketplace';
                                if($metode_bayar->nama_metode_bayar == "Transfer"){
                                    $store->status_aprove = "hold";
                                }
                                $store->metode_bayar = $request->method_bayar;
                                $store->metode_kirim = $request->nama_biaya_kirim;
                                $store->id_rekening_tujuan = $request->id_rekening_tujuan;
                                //$store->biaya_kirim = $all_biaya_kirim->harga_biaya_kirim;
                                $store->grand_total = (int)$harga_tanpa_ongkir;
                                $store->ppn = 0;
                                $store->amount_ppn = 0;
                                $store->sending_date = $sending_date;
                                $store->so_date = $date_Now;
                                if($metode_bayar->nama_metode_bayar == "Kredit"){
                                    $store->top_hari = $customer->credit_limit_days;
                                    $store->top_toleransi = 14;
                                }else{
                                    $store->top_hari = 0;
                                    $store->top_toleransi = 1;
                                }
                                $store->save();

                                $cek_id = DB::table('t_sales_order')->select('id')->where('so_code',$setInvoice)->first();
                                $id_so = $cek_id->id;

                            if(count($array) > 0){
                                for($n=0; $n<count($array); $n++){
                                    $insertDetailTransaksi = new DSalesOrder;
                                    $insertDetailTransaksi->so_code = $array[$n]['invoice_t'];
                                    $insertDetailTransaksi->produk = $array[$n]['produk_id'];
                                    $insertDetailTransaksi->qty = $array[$n]['qty'];
                                    $insertDetailTransaksi->total = $array[$n]['total'];
                                    $insertDetailTransaksi->customer_price = $array[$n]['harga'];
                                    //$insertDetailTransaksi->type = 'order';
                                    $insertDetailTransaksi->save();

                                    $getCodeProduk = DB::table('m_produk')->where('id',$array[$n]['produk_id'])->first();

                                    $jumlahStok = DB::table('m_stok_produk')->where('produk_code',$getCodeProduk->code)
                                    ->where('gudang', 2)
                                    ->sum('stok');

                                    $insertStokModel = new MStokProdukModel;
                                    $insertStokModel->produk_code =  $getCodeProduk->code;
                                    $insertStokModel->produk_id =  $getCodeProduk->id;
                                    $insertStokModel->transaksi   =  $array[$n]['invoice_t'];
                                    $insertStokModel->tipe_transaksi   =  'Sales Order';
                                    $insertStokModel->person   =  $customer->id;
                                    $insertStokModel->stok_awal   =  $jumlahStok;
                                    $insertStokModel->gudang      =  2;
                                    $insertStokModel->stok        =  -$array[$n]['qty'];
                                    $insertStokModel->type        =  'out';
                                    $insertStokModel->save();

                                    // $insertStokModel = new MStokProdukModel;
                                    // $insertStokModel->produk_code =  $getCodeProduk->code;
                                    // $insertStokModel->transaksi   =  $array[$n]['invoice_t'];
                                    // $insertStokModel->tipe_transaksi   =  'Sales Order';
                                    // $insertStokModel->tipe_order   =  'order';
                                    // $insertStokModel->stok_awal   =  $jumlahStok;
                                    // $insertStokModel->gudang      =  5;
                                    // $insertStokModel->stok        =  -$array[$n]['qty'];
                                    // $insertStokModel->type        =  'out';
                                    // $insertStokModel->save();
                                }
                            }


                            $success = true;
                            $metode_bayar_status = $metode_bayar->nama_metode_bayar;
                            if($metode_bayar->nama_metode_bayar == "Transfer"){
                                $deadline = \Carbon\Carbon::now()->addDays(1)->format('l, d F Y H:i:s');
                            }else if($metode_bayar->nama_metode_bayar == "COD"){
                                $deadline = "Pembayaran dapat dilakukan pada saat pengiriman";
                            }else{
                                $deadline = $customer->credit_limit_days.' Hari';
                            }
                            //dd($array_all);

                            // Mail::to($user->email)->send(new OrderEmail($array_all,$metode_bayar->nama_metode_bayar,$setInvoice,$all_biaya_kirim->nama_biaya_kirim,$customer->name,$date_now_1,$harga_all,$deadline));
                            Mail::to($user->email)->send(new PreOrderMail($user));
                            DB::commit();
                            Cart::destroy();
                        } catch (\Exception $e) {
                            $success = false;
                            $deadline = "";
                            DB::rollback();
                            dd($e);
                        }

                    //dd($success);

                return view('frontend.order_status', compact('success','harga_all','deadline','setInvoice','metode_bayar_status','id_so'));
            }else{
                return redirect('/cart')->with("error_message","Qty Barang Pesanan ada yang melebihi Stok kami");
            }
        }else{
            return redirect('/');
        }
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
        $duplicates = Cart::search(function ($cartItem, $rowId) use ($id) {
            return $cartItem->rowId === $id;
        });
        foreach ($duplicates as $key => $value) {
            if($value->options->type == "order"){
                $duplicates1 = Cart::search(function ($cartItem, $rowId) use ($value) {
                    return $cartItem->id === $value->id;
                });

                if($duplicates1->isNotEmpty()){
                    $check_remove_req=0;
                    $qty_req = 0;
                    foreach ($duplicates1 as $key1 => $value1) {
                        if($value1->options->type == "request"){
                            $qty_req = $value1->qty;
                            Cart::remove($key1);
                            $check_remove_req +=1;
                        }
                    }
                }
                if($check_remove_req == 0){
                    Cart::remove($id);
                }else{
                    Cart::update($id, $qty_req)->associate('App\Models\MProdukModel');
                }
            }else{
                Cart::remove($id);
            }
        }

        return back()->with('success_message', 'Item has been removed!');
    }

    public function addressShipping($id, $type)
    {
        if($type == 'main'){
            $data = DB::table('m_customer')
                    ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->where('m_customer.id',$id)->first();
            $data->class = 'main';
        }else{
            $data = DB::table('m_alamat_customer')
                    ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->where('m_alamat_customer.id',$id)->first();
            $data->class = 'other';
        }

        return Response::json($data);
    }

    public function addressBilling($id, $type)
    {
        if($type == 'main'){
            $data = DB::table('m_customer')
                    ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->where('m_customer.id',$id)->first();
            $data->class = 'main';
        }else{
            $data = DB::table('m_alamat_customer')
                    ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                    ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                    ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->where('m_alamat_customer.id',$id)->first();
            $data->class = 'other';
        }

        return Response::json($data);
    }
}
