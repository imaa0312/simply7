<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MGudangModel;
use App\Models\MCustomerModel;
use App\Models\MProvinsiModel;
use App\Models\MWilayahSalesModel;
use App\Models\MTemporaryCustomerModel;
use DB;



class MTemporaryCustomer extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataTempCustomer = MTemporaryCustomerModel::orderBy('created_at')->get();

        return view('admin.customer.temp-customer.index',compact('dataTempCustomer'));
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

        $dataCustomer = MCustomerModel::create($request->except('customer_temp_id','customer_temp_code'));

        $cekTempSalesOrder = DB::table('temp_t_sales_order')->where('customer_code',$request->customer_temp_code)->get();

        if( count($cekTempSalesOrder) > 0 ){

            //insert
            foreach($cekTempSalesOrder as $temp)
            {
                //generate code
                $dataDate =date("ym");

                $getLastCode = DB::table('t_sales_order')
                        ->select('id')
                        ->orderBy('id', 'desc')
                        ->pluck('id')
                        ->first();
                $getLastCode = $getLastCode +1;

                $nol = null;
                if(strlen($getLastCode) == 1){
                    $nol = "000";
                }elseif(strlen($getLastCode) == 2){
                    $nol = "00";
                }elseif(strlen($getLastCode) == 3){
                    $nol = "0";
                }else{
                    $nol = null;
                }

                $setInvoice = 'SOWA'.$dataDate.$nol.$getLastCode;

                DB::table('t_sales_order')->insert([
                    'so_code' => $setInvoice,
                    'customer' => $dataCustomer->id,
                    'atas_nama' => $dataCustomer->id,
                    'sales' => $temp->sales,
                    'so_date' => $temp->so_date,
                    //'payment_date' =>  $temp->payment_date,
                    'sending_address' =>  $request->main_address,
                ]);

                $dataDTempSO = DB::table('temp_d_sales_order')
                    ->where('so_code',$temp->so_code)
                    ->get();

                foreach ($dataDTempSO as $raw_data) {
                    DB::table('d_sales_order')->insert([
                        'so_code' => $setInvoice,
                        'produk' => $raw_data->produk,
                        'qty' => $raw_data->qty,
                        'customer_price' => $raw_data->customer_price,
                        'diskon_potongan' => $raw_data->diskon_potongan,
                        'diskon_persen' =>  $raw_data->diskon_persen,
                        'total' =>  $raw_data->total,
                    ]);

                    DB::table('temp_d_sales_order')->where('id',$raw_data->id)->delete();
                }

                DB::table('temp_t_sales_order')->where('id',$temp->id)->delete();
            }

        }

        //delete
        $destroy = MTemporaryCustomerModel::find($request->customer_temp_id);
        $destroy->delete();

        return redirect('admin/customer-temp');
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
        $getProvinsi = MProvinsiModel::get();
        $dataWilayah = MWilayahSalesModel::get();
        $dataCustomer = MTemporaryCustomerModel::find($id);
        // dd($dataCustomer);
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
        $dataGudang = MGudangModel::orderBy('code')->get();
        $dataPusatKantor = MCustomerModel::orderBy('name')->get();
        $dataGolonganHarga = DB::table('m_golongan_harga_produk')->orderBy('id','asc')->get();

        return view('admin.customer.temp-customer.update',compact('dataCustomer','dataWilayah','getProvinsi','setCodeCustomer','dataPusatKantor','dataGudang','dataGolonganHarga'));

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
        $dataCustomer = MTemporaryCustomerModel::find($id);
        $dataCustomer->delete();

        return redirect()->back();
    }

    //update temporary

    public function updateTemp($id)
    {
         $dataCustomer = MTemporaryCustomerModel::find($id);

        return view('admin.customer.temp-customer.update-temp',compact('dataCustomer'));
    }

    //save temp

    public function saveTemp(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:60',
            'main_phone' => 'required|max:30',
            'main_address' => 'required|max:40',
            'main_geo_lat' => 'required|numeric',
            'main_geo_lng' => 'required|numeric',
        ]);
        // dd($request->all());
        MTemporaryCustomerModel::where('id',$request->id)->update($request->except('id','_token'));

        return redirect('admin/customer-temp');
    }
}
