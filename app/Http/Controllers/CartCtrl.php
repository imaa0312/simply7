<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MProdukModel;
use App\Models\MAlamatCustomerModel;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Database\QueryException;
use DB;
use Auth;
use Response;

class CartCtrl extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function listCart()
     {
         return view('frontend.cart');
     }
    public function index()
    {
        if(Auth::check()){
            $customer = DB::table('m_customer')->where('id_user', Auth::user()->id)->first();
            $main_address = DB::table('m_customer')
                            ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                            ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                            ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                            ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                            ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                            ->where('id_user',Auth::user()->id)
                            ->where('m_customer.main_address','!=', "")->first();
            if(!empty($main_address)){
                $main_address->class = 'main';
            }
            //dd($main_address);
            $other_address = DB::table('m_alamat_customer')
                            ->select('m_alamat_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                            ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                            ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                            ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                            ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                            ->where('customer',$customer->id)->get();
            if(count($other_address) > 0){
                foreach ($other_address as $other) {
                    $other->class = 'other';
                }
            }
            //dd($other_address);
            $biaya_kirim = DB::table('m_biaya_kirim')->get();
            //$method_bayar = DB::table('m_metode_pembayaran')->where('')->get();
            $bank = DB::table('m_bank')->get();
            $prov = DB::table('m_provinsi')->get();
            return view('frontend.cart', compact('main_address','other_address','biaya_kirim','bank','prov','customer'));
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if(Auth::check()){

            $date_now = date('d-m-Y');
            $date = '01-'.date('m-Y', strtotime($date_now));
            $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

            $produk = MProdukModel::find($request->id);

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

            //dd($stok);

            $duplicates = Cart::search(function ($cartItem, $rowId) use ($request) {
                return $cartItem->id === $request->id;
            });

            if ($duplicates->isNotEmpty()) {
                foreach ($duplicates as $key => $value) {
                    $cek_qty = $value->qty;
                    $row = $key;
                }

                $update_qty = $cek_qty + $request->qty;

                if($update_qty <= $stok){
                    Cart::update($row, $update_qty)
                        ->associate('App\Models\MProdukModel');
                }else{
                    $type_message= "error_message";
                    $message = "Stok Item Tidak Mencukupi";
                    return back()->with("$type_message", "$message");
                }
            }else{
                if($stok >= 1){
                    if($request->qty <= $stok){
                        Cart::add($request->id, $request->name, $request->qty, $request->price,['image'=>$request->image,'max_qty' => $stok ])
                        ->associate('App\Models\MProdukModel');
                    }else{
                        $type_message= "error_message";
                        $message = "Stok Item Tidak Mencukupi";
                        return back()->with("$type_message", "$message");
                    }
                }else{
                    $type_message= "error_message";
                    $message = "Stok Item Tidak Mencukupi";
                    return back()->with("$type_message", "$message");
                }

            }

            $type_message= "success_message";
            $message = "Item sudah ditambahkan ke Cart!";
            return back()->with("$type_message", "$message");
        }else{
            $type_message= "error_message";
            $message = "Silahkan Login Terlebih Dahulu";
            return redirect('/login')->with("$type_message", "$message");
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
    public function update($id, $qty)
    {
        // $duplicates = Cart::search(function ($cartItem, $rowId) use ($id) {
        //     return $cartItem->id === $id;
        // });

        // $qty_all=0;
        // if($duplicates->isNotEmpty()){
        //     foreach ($duplicates as $key => $value) {
        //         $qty_all =+ $value->qty;
        //         Cart::remove($key);
        //     }
        // }

        // $produk     = MProdukModel::find($id);
        // $stok       = DB::table('m_stok_produk')
        //             ->where('m_stok_produk.produk_code', $produk->code)
        //             ->where('m_stok_produk.gudang', 5)
        //             ->groupBy('m_stok_produk.produk_code')
        //             ->sum('stok');
        // if($qty_all < $stok){
        //     foreach ($produk as $item) {
        //         // Cart::add($item->id, $item->name, $item->)
        //     }
        // }

        // foreach (Cart::content() as $key => $value) {
        //     if($value->options->type == "order"){
        //         foreach (Cart::content() as $key1 => $value1) {
        //             if($value1->options->type == "request" && $value1->id == $value->id){
        //                 $qty_all = $value1->qty;
        //             }
        //         }
        //     }else{

        //     }
        // }

        // $id_product;
        // $min_order;
        // $rowId_req;
        // foreach ($duplicates as $key => $value) {

        //     $id_product = $value->id;
        //     $min_order  = $value->options->min_order;
        //     $produk     = MProdukModel::find($value->id);
        //     $stok       = DB::table('m_stok_produk')
        //                 ->where('m_stok_produk.produk_code', $produk->code)
        //                 ->where('m_stok_produk.gudang', 5)
        //                 ->groupBy('m_stok_produk.produk_code')
        //                 ->sum('stok');
        // }

        // if($qty < $stok){
        //     Cart::update($id, $qty);
        // }else{
        //     $qty_now= $qty - $stok;
        //     Cart::update($id, $qty_now);
        //     $check_req = 0;
        //     $duplicates1 = Cart::search(function ($cartItem, $rowId) use ($id_product) {
        //             return $cartItem->id === $id_product;
        //         });

        //     foreach ($duplicates1 as $key1 => $value1) {
        //          if($value1->options->type=='request'){
        //             $rowId_req = $key1;
        //             Cart::update($key1, $);
        //             $check_req +=1;
        //          }
        //     }
        //     if($check_req == 0){

        //         foreach ($produk as $item) {
        //             Cart::add($produk->id, $$produk->name, $request->qty, $request->price,['type'=>'request','min_order'=>$request->min_order_req])
        //             ->associate('App\Models\MProdukModel');
        //         }
        //     }
        // }

        // return ;

        //Cart::update($id, $qty_req)->associate('App\Models\MProdukModel');
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
        // dd($duplicates);
        foreach ($duplicates as $key => $value) {
            if($value->options->type == "order"){
                $produk = MProdukModel::find($value->id);
                $date_now1 = date('d-m-Y');
                $date = '01-'.date('m-Y', strtotime($date_now1));
                $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));
                $balance = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $produk->code)
                    ->where('m_stok_produk.gudang', 5)
                    ->where('type', 'closing')
                    ->whereMonth('periode',date('m', strtotime($date_last_month)))
                    ->whereYear('periode',date('Y', strtotime($date_last_month)))
                    ->sum('balance');

                $stok = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $produk->code)
                    ->where('m_stok_produk.gudang', 5)
                    ->whereMonth('created_at',date('m', strtotime($date_now1)))
                    ->whereYear('created_at',date('Y', strtotime($date_now1)))
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('stok');

                $stok = $stok + $balance;

                if($stok <= 0){
                    $stok = 0;
                }else{
                    $stok = $stok;
                }
                $duplicates1 = Cart::search(function ($cartItem, $rowId) use ($value) {
                    return $cartItem->id === $value->id;
                });

                if($duplicates1->isNotEmpty()){
                    $check_remove_req=0;
                    $qty_req = 0;
                    $key_req;
                    foreach ($duplicates1 as $key1 => $value1) {
                        if($value1->options->type == "request"){
                            $qty_req = $value1->qty;
                            $check_remove_req +=1;
                            $key_req = $key1;
                        }
                    }
                }
                if($check_remove_req == 0){
                    Cart::remove($id);
                }else{
                    if($qty_req<=$stok){
                        Cart::remove($key_req);
                        Cart::update($id, $qty_req)->associate('App\Models\MProdukModel');
                    }else{
                        $update_qty_req = $qty_req - $stok;
                        Cart::update($key_req, $update_qty_req)->associate('App\Models\MProdukModel');
                    }
                }
            }else{
                Cart::remove($id);
            }
        }

        return back()->with('success_message', 'Item sudah dihapus dari Cart!');
    }

    public function checkout(Request $request)
    {
        $date_now1 = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now1));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));
        $date_now =date('Y-m-d H:i:s');

        foreach (Cart::content() as $key => $value) {

            $name_input = 'qty_update_'.$value->rowId;
            $qty_order = $value->qty;
            $qty_update = (int)$request->input($name_input);

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
                ->whereMonth('created_at',date('m', strtotime($date_now1)))
                ->whereYear('created_at',date('Y', strtotime($date_now1)))
                ->groupBy('m_stok_produk.produk_code')
                ->sum('stok');

            $stok = $stok + $balance;

            if($stok <= 0){
                $stok = 0;
            }else{
                $stok = $stok;
            }

            if($qty_order != $qty_update){
                if($qty_update <= $stok){
                    Cart::update($key, $qty_update)->associate('App\Models\MProdukModel');
                }else{
                    Cart::update($key, $stok)->associate('App\Models\MProdukModel');
                }
            }
        }
        return redirect("/checkout");
    }

    public function addAddressNew(Request $request)
    {
        DB::beginTransaction();
        try{

        $updateSales = DB::table('m_customer')->where('id_user', '=', $request->id_user)->update([
            'main_address' => $request->alamat,
            'main_kelurahan' => $request->id_kel,
            'main_office_phone_1' => $request->phone_1,
            'main_pos'  => $request->codepos,
        ]);
        DB::commit();
        $type_message= "success_message";
        $message = "Tambah Alamat berhasil";
        $data = DB::table('m_customer')
                ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                ->where('id_user','=', $request->id_user)
                ->first();
        if(!empty($data)){
            $data->class = 'main';
        }
        }catch(\Exception $e){
            dd($e);
            DB::rollback();
            $type_message= "error_message";
            $message = "Tambah Alamat gagal, silahkan coba lagi";
        }
        //$data = array_values($data->toArray());
        // return dd($data);
        // die();
        return Response::json($data);
    }

    public function addOtherAddressNew(Request $request)
    {
        DB::beginTransaction();
        try{
            $customer = DB::table('m_customer')->where('id_user', $request->id_user)->first();
            $store = new MAlamatCustomerModel;
            $store->customer = $customer->id;
            $store->name = $request->nama;
            $store->nama_alamat_lain = $request->nama_alamat_lain;
            $store->address = $request->alamat;
            $store->kelurahan = $request->id_kel;
            $store->pos = $request->codepos;
            $store->phone_1 = $request->phone_1;
            $store->type = "other";
            $store->save();

            DB::commit();
            $type_message= "success_message";
            $message = "Tambah Alamat berhasil";
            $data = DB::table('m_alamat_customer')
                    ->select('m_alamat_customer.*')
                    // ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                    // ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                    // ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                    // ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                    ->where('customer','=', $customer->id)
                    ->orderBy('id','desc')
                    ->first();
            if(!empty($data)){
                $data->class = 'other';
            }
        }catch(\Exception $e){
            dd($e);
            DB::rollback();
            $type_message= "error_message";
            $message = "Tambah Alamat gagal, silahkan coba lagi";
        }
        //$data = array_values($data->toArray());
        // return dd($data);
        // die();
        return Response::json($data);
    }

    public function deleteCart($id)
    {
        Cart::remove($id);
    }
}
