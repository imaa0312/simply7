<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use App\Models\TargetSalesModel;
use App\Models\MKategoriModel;
use App\Models\MProdukModel;
use App\Models\MProdukImage;
use App\Models\MCartModel;
use App\Models\DCartModel;
use App\Models\MCustomerModel;
use DB;
use carbon;



class MSalesController extends Controller
{
    public function load_cart($cart_id){
        $cart = '';
        if($cart_id == 0){
            $mcart = MCartModel::where('session_code', '=', 'bdsjakfghjdsk')
                ->where('status', '=', 1)
                ->first();
            
            if($mcart) $cart_id = $mcart->id;
            else $cart_id = 0;

            $cartid = 0;
        } else
            $cartid = 1;
        
        $dcart = DCartModel::where('cart_id', '=', $cart_id)->orderBy('id', 'ASC')->get();
        foreach($dcart as $dc){
            $data = MProdukModel::find($dc->product_id);
            $data_img = MProdukImage::where('produk_id', '=', $dc->product_id)->first();
            if($data_img === null){
                $image = "no_image.png";
            } else {
                $image = $data_img->image;
            }
            $cart .= '<div class="product-list d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center product-info" data-bs-toggle="modal"
                    data-bs-target="#products">
                    <a href="javascript:void(0);" class="img-bg">
                        <img src="'.'product_images/'.$image.'"
                            alt="Products">
                    </a>
                    <div class="info">
                        <span>'.$data->sku.'</span>
                        <h6><a href="javascript:void(0);">'.$data->name.'</a></h6>
                        <p>Rp '.$data->price_sale.'</p>
                    </div>
                </div>
                <div class="qty-item text-center">
                    <a href="javascript:void(0);" class="dec d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="minus" data-id="'.$dc->id.'"><i class="fa-solid fa-circle-minus" style="color: #ff0000;"></i></a>
                        <input type="text" min="1" class="form-control text-center qty" name="qty"
                        value="'.$dc->qty.'" data-id="'.$dc->id.'">
                    <a href="javascript:void(0);" class="inc d-flex justify-content-center align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="plus" data-id="'.$dc->id.'"><i class="fa-solid fa-circle-plus" style="color: #ff0000;"></i></a>                
                </div>
                <div class="d-flex align-items-center action">
                    <a class="btn-icon delete-icon confirm-text del" href="javascript:void(0);" data-id="'.$dc->id.'">
                        <i class="fa-solid fa-trash-can" style="color: #ff0000;"></i>
                    </a>
                </div>
            </div>';
        }

        if($cartid == 0)
            echo json_encode($cart);
        else
            return $cart;
    }
    public function getProduct($id){
        if($id  > 0)
                $data = MProdukModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size')
                ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
                ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
                ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_produk.ssub_kategori_id')
                ->join('m_sssub_kategori_produk', 'm_sssub_kategori_produk.id', '=', 'm_produk.sssub_kategori_id')
                ->join('m_brand', 'm_brand.id', '=', 'm_produk.brand_id')
                ->join('m_size', 'm_size.id', '=', 'm_produk.size_id')
                ->where('m_produk.kategori_id', '=', $id)->get();
        else
            $data = MProdukModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size')
            ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
            ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
            ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_produk.ssub_kategori_id')
            ->join('m_sssub_kategori_produk', 'm_sssub_kategori_produk.id', '=', 'm_produk.sssub_kategori_id')
            ->join('m_brand', 'm_brand.id', '=', 'm_produk.brand_id')
            ->join('m_size', 'm_size.id', '=', 'm_produk.size_id')->get();

        $product = '';
        $list = "<option value='0'>Search Products</option>";

        foreach($data as $dt){
            $data_img = MProdukImage::where('produk_id', '=', $dt->id)->first();
            if($data_img === null){
                $image = "no_image.png";
            } else {
                $image = $data_img->image;
            }
            $product .= '<div class="col-sm-2 col-md-6 col-lg-3 col-xl-3 pe-2 prod" data-id="'.$dt->id.'">
                <div class="product-info default-cover card">
                    <a href="javascript:void(0);" class="img-bg">
                        <img src="'.'product_images/'.$image.'" alt="Products">
                        <span><i data-feather="check" class="feather-16"></i></span>
                    </a>
                    <h6 class="cat-name"><a href="javascript:void(0);">'.$dt->sub_kategori."-".$dt->sssub_kategori.'</a></h6>
                    <h6 class="product-name"><a href="javascript:void(0);">'.$dt->brand."-".$dt->name.'</a></h6>
                    <div class="d-flex align-items-center justify-content-between price">
                        <span>'.$dt->size.'</span>
                        <p>Rp '.$dt->price_sale.'</p>
                    </div>
                </div>
            </div>'; 

            $list .= "<option value='".$dt->id."'>".$dt->brand." / ".$dt->name." / ".$dt->sssub_kategori." / ".$dt->size."</option>";
        }

        $return = array(
            "grid" => $product,
            "list" => $list
        );

        echo json_encode($return);
    }

    public function addtocart($id){
        $check = MCartModel::select('m_cart.*', 'd_cart.product_id', 'd_cart.id as dcart_id', 'd_cart.qty')
            ->join('d_cart', 'm_cart.id', '=', 'd_cart.cart_id')
            ->where('session_code', '=', 'bdsjakfghjdsk')
            ->where('product_id', '=', $id)
            ->first();
        $data = MProdukModel::find($id);

        $check_mcart = MCartModel::where('session_code', '=', 'bdsjakfghjdsk')
            ->where('status', '=', 1)
            ->first();

        if($check_mcart){
            $cart_id = $check_mcart->id;

            if($check){
                $d_cart = DCartModel::find($check->dcart_id);
                $d_cart->qty = (int)$check->qty+1;
                $d_cart->sale_price = $data->price_sale;
            } else {
                $d_cart = new  DCartModel;
                $d_cart->cart_id = $cart_id;
                $d_cart->product_id = $id;
                $d_cart->qty = 1;
                $d_cart->sale_price = $data->price_sale;
                $d_cart->save();
            }
            $d_cart->save();
        } else {
            $var = new  MCartModel;
            $var->user_id = 4;
            $var->trx_date = date("Y-m-d H:i:s");
            $var->session_code = "bdsjakfghjdsk";
            $var->status = 1;
            $var->save();

            $d_cart = new  DCartModel;
            $d_cart->cart_id = $var->id;
            $d_cart->product_id = $id;
            $d_cart->qty = 1;
            $d_cart->sale_price = $data->price_sale;
            $d_cart->save();

            $cart_id = $var->id;
            $d_cart->save();
        }

        $return = array(
            "cart" => $this->load_cart($cart_id),
            "id" => $cart_id,
            "count" => DCartModel::where('cart_id', '=', $cart_id)->sum('qty')
        );

        echo json_encode($return);
    }

    public function cartQty($id, $desc){
        $d_cart = DCartModel::find($id);
        $qty = $d_cart->qty;
        $product = $d_cart->product_id;
        $cart_id = $d_cart->cart_id;
        if($desc==0)
            $d_cart->qty = (int)$qty-1;
        else
            $d_cart->qty = (int)$qty+1;

        $data = MProdukModel::find($product);
        $d_cart->sale_price = $data->price_sale;
        $d_cart->save();

        $return = array(
            "cart" => $this->load_cart($cart_id),
            "id" => $cart_id,
            "count" => DCartModel::where('cart_id', '=', $cart_id)->sum('qty')
        );

        echo json_encode($return);
    }

    public function fillQty($id, $amount){
        $d_cart = DCartModel::find($id);
        $qty = $d_cart->qty;
        $product = $d_cart->product_id;

        $cart_id = $d_cart->cart_id;
        $d_cart->qty = $amount;

        $data = MProdukModel::find($product);
        $d_cart->sale_price = $data->price_sale;
        $d_cart->save();

        $return = array(
            "cart" => $this->load_cart($cart_id),
            "id" => $cart_id,
            "count" => DCartModel::where('cart_id', '=', $cart_id)->sum('qty')
        );

        echo json_encode($return);
    }

    public function posDel($id){
        $d_cart = DCartModel::find($id);
        $cart_id = $d_cart->cart_id;
        $d_cart->delete();

        $return = array(
            "cart" => $this->load_cart($cart_id),
            "id" => $cart_id,
            "count" => DCartModel::where('cart_id', '=', $cart_id)->sum('qty')
        );

        echo json_encode($return);
    }

    public function posVoid($id){
        $d_cart = DCartModel::where("cart_id", "=", $id);
        $d_cart->delete();

        $m_cart = MCartModel::find($id);
        $m_cart->delete();

        if($d_cart && $m_cart){
            $return = array(
                "status" => true,
                "count" => DCartModel::where('cart_id', '=', $id)->sum('qty'),
                "msg" => "Transaction has been void"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data not found"
            );
        }
        echo json_encode($return);
    }

    public function index()
    {
        $data['category'] =  MKategoriModel::where('status', '=', 1)->get();
        $all_product =  MProdukModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size')
        ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
        ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
        ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_produk.ssub_kategori_id')
        ->join('m_sssub_kategori_produk', 'm_sssub_kategori_produk.id', '=', 'm_produk.sssub_kategori_id')
        ->join('m_brand', 'm_brand.id', '=', 'm_produk.brand_id')
        ->join('m_size', 'm_size.id', '=', 'm_produk.size_id')->get();
        $data['all_product'] = array();
        $i=0;
        foreach($all_product as $all){
            $data['all_product'][$i] = $all;
            $data_img = MProdukImage::where('produk_id', '=', $all->id)->first();
            if($data_img === null){
                $data['all_product'][$i]['image'] = "no_image.png";
            } else {
                $data['all_product'][$i]['image'] = $data_img->image;
            }
            $i++;
        }

        // echo "<pre>";
        // print_r($product); die;

        return view('pos', compact('data'));
    }
    public function posCust(Request $request)
    {
        $customers =  MCustomerModel::where('status', '=', 1)->where('name', 'like', '%'.$request->input('q').'%')->get();
        echo json_encode($customers);
    }
    public function posProd(Request $request)
    {
        $data = MProdukModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size')
            ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
            ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
            ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_produk.ssub_kategori_id')
            ->join('m_sssub_kategori_produk', 'm_sssub_kategori_produk.id', '=', 'm_produk.sssub_kategori_id')
            ->join('m_brand', 'm_brand.id', '=', 'm_produk.brand_id')
            ->join('m_size', 'm_size.id', '=', 'm_produk.size_id')
            ->where('m_produk.name', 'like', '%'.$request->input('q').'%')
            ->orWhere('m_brand.name', 'like', '%'.$request->input('q').'%')
            ->orWhere('m_sssub_kategori_produk.name', 'like', '%'.$request->input('q').'%')
            ->orWhere('m_size.name', 'like', '%'.$request->input('q').'%')
            ->orWhere('m_produk.sku', 'like', '%'.$request->input('q').'%')
            ->get();
        echo json_encode($data);
    }
    









    public function store(Request $request)
    {
        $nama = $request->old('nama');
        $email = $request->old('email');
        $alamat = $request->old('alamat');
        $birthdate = $request->old('birthdate');

        $this->validate($request, [
            'nama' => 'required|max:50',
            'email' => 'required|email|unique:m_user',
        ]);

        $roleSales  = MRoleModel::where('name', 'Sales')->first();
        $newSales = new MUserModel;
        $newSales->name = $request->nama;
        $newSales->email = $request->email;
        $newSales->address = $request->alamat;
        $newSales->birthdate = $request->birthdate;
        $newSales->password =  bcrypt(str_replace(' ', '', $request->nama));
        $newSales->role = $roleSales->id;
        $newSales->save();

        return redirect('admin/sales');
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
        $getMsales = MUserModel::where('id', '=', $id)->first();

        return view('admin.sales.update', compact('getMsales'));
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
            'nama' => 'required|max:50'
        ]);

        $updateSales = MUserModel::where('id', '=', $id)->update([
            'name' => $request->nama,
            'address' => $request->alamat,
            'birthdate' => $request->birthdate
        ]);

        return redirect('admin/sales');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteSales = MUserModel::where('id', '=', $id)->delete();

        return redirect()->back();
    }

    public function trash()
    {
        $userTrash = MUserModel::onlyTrashed()->get();
        // dd($userTrash);
        return view('admin.sales.trash', compact('userTrash'));
    }

    public function restore($id)
    {
        $userRestore = MUserModel::withTrashed()->where('id', '=', $id)->restore();

        return redirect()->back();
    }

    public function permanentDelete($id)
    {
        $cekDataSales = DB::table('t_sales_order')->where('sales',$id)->count();
        $cekDataWilyahSales = DB::table('m_wilayah_pembagian_sales')->where('sales',$id)->count();

        if($cekDataSales > 0 ){
            return redirect()->back()->with('message','Data tidak Bisa dihapus');
        }elseif($cekDataWilyahSales > 0){
            return redirect()->back()->with('message','Data tidak Bisa dihapus');
        }

        $permanentDelete = MUserModel::withTrashed()->where('id', '=', $id)->first();
        $permanentDelete->forceDelete();

        return redirect()->back();
    }

    public function salesTarget()
    {
        $roleAdmin = MRoleModel::where('name', 'admin')->first();
        $getMsales = MUserModel::where('role','!=',$roleAdmin->id)->get();
        $dataTargetSales = DB::table('t_target_sales')->get();

        return view('admin.sales.target_create', compact('getMsales'));
    }

    public function indexTarget()
    {
        $getData = TargetSalesModel::get();
        // dd($getData);
        // $ceka = DB::table('t_target_sales')->select('bulan_target')->get();
        // dd($ceka);S

        return view('admin.sales.target_index', compact('getData'));
    }

    public function storeTarget(Request $request)
    {
        $cekBulanSales = TargetSalesModel::get();

        $month = date('m', strtotime($request->bulan_target));
        $year = date('Y', strtotime($request->bulan_target));

        $validationBulan = DB::table('t_target_sales')
        ->where('sales_id','=',$request->sales)
        ->whereMonth('bulan_target', '=', $month)
        ->whereYear('bulan_target', '=', $year)
        ->get();

        // dd($validationBulan);
        foreach($validationBulan as $bulan)
        {
            echo $bulan->bulan_target."<br>";
            echo $month.$year;
        }
        var_dump(count($validationBulan));
        if(count($validationBulan) == 0)
        {
            $storeTarget = new TargetSalesModel;
            $storeTarget->sales_id = $request->sales;
            $storeTarget->bulan_target = $request->bulan_target;
            $storeTarget->target = $request->target;
            $storeTarget->save();
            return redirect('admin/target/sales/index');
        }else{
            return redirect()->back()->with('message','Target Bulan sudah Ada');
        }
    }

    public function salesTargetDelete($id)
    {
        TargetSalesModel::where('id', '=', $id)->delete();

        return redirect()->back();
    }


}
