<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Carbon\Carbon;
use Response;
use App\Models\MUserModel;
use App\Models\MProdukModel;
use App\Models\MJangkaWaktu;
use Illuminate\Http\Request;
use App\Models\TSalesOrderModel;
use App\Models\MWilayahPembagianSalesModel;
use App\Models\MCustomerModel;
use App\Models\MAlamatCustomerModel;
use App\Models\DSalesOrder;
use App\Models\MStokProdukModel;
use App\Models\TSuratJalanModel;
use App\Models\MReasonModel;
use Illuminate\Support\Facades\Log;
use Yajra\Datatables\Datatables;



class TSalesOrderController extends Controller
{
    public function index($type = null)
    {
        $dataSales = DB::table('t_sales_order')
        ->select('m_user.name as user_name','m_customer.name as customer_name','t_sales_order.*')
        ->join('m_user','m_user.id','t_sales_order.sales')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->orderBy('t_sales_order.so_code','desc')
        ->get();

        foreach ($dataSales as $dataSO) {
            $sj = true;
            $cekSj = TSuratJalanModel::where('so_code',$dataSO->so_code)->get();
            // dd($cekSj);
            if (count($cekSj) > 0 ) {
                $sj = false; // jika ada false
            }
            $dataSO->sj = $sj;
        }
        // dd($dataSales);
        return view('admin.transaksi.sales-order.index',compact('dataSales'));
    }

    public function inApproval()
    {
        $datainApproval = DB::table('t_sales_order')
        ->select('m_user.name as user_name','m_customer.name as customer_name','t_sales_order.so_date','t_sales_order.status_aprove','t_sales_order.so_code')
        ->join('m_user','m_user.id','t_sales_order.sales')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->where('status_aprove', 'in-approval')
        ->get();

        return view('admin.transaksi.sales-order.inapproval',compact('datainApproval'));
    }

    public function approved()
    {
        $dataApproved = DB::table('t_sales_order')
        ->select('m_user.name as user_name','m_customer.name as customer_name','t_sales_order.so_date','t_sales_order.status_aprove','t_sales_order.so_code')
        ->join('m_user','m_user.id','t_sales_order.sales')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        //->where('status_aprove', 'approved')
        ->get();

        return view('admin.transaksi.sales-order.approved',compact('dataApproved'));
    }

    public function waitinglist()
    {
        $dataSales = DB::table('t_sales_order')
        ->select('m_user.name as user_name','m_customer.name as customer_name','t_sales_order.so_date','t_sales_order.status_aprove','t_sales_order.so_code','t_sales_order.so_from')
        ->join('m_user','m_user.id','t_sales_order.sales')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->where('status_aprove', 'in approval')
        ->orderBy('t_sales_order.so_code','desc')
        ->get();

        return view('admin.transaksi.sales-order.waiting-approval',compact('dataSales'));
    }

    public function approve($id)
    {
        //approve
        DB::table('t_sales_order')
        ->where('so_code',$id)
        ->update(['status_aprove' => 'approved']);

        $dataSO = DB::table('t_sales_order')
        ->where('so_code',$id)
        ->first();

        $grand_total = 0;

        $grand_total = DB::table('d_sales_order')
        ->where('so_code',$id)
        ->sum('total');

        //masukkan point sales
        // $target_data = DB::table('m_target_sales')
        // ->select('*')
        // ->where('sales', $dataSO->sales)
        // ->first();

        // $target = 0;
        // if (count($target_data) !== 0) {
        //     $target = $target_data->monthly_target;
        // }
        // $daily_target = $target / 25;

        // $treshold_point = $daily_target + ($daily_target * 10 / 100);

        // if ($grand_total > $treshold_point) {
        //     //dapat point
        //     //$point = (int)(($grand_total - $treshold_point) / 100000);
        //     $point = (int)(($grand_total / 100000));
        //     DB::table('m_point_sales')
        //     ->insert(['sales' => $dataSO->sales, 'type' => 'get-point', 'point'=> $point]);
        // }

        return redirect()->back();
    }

    public function reject($id)
    {
        DB::table('t_sales_order')
        ->where('so_code',$id)
        ->update(['status_aprove' => 'reject']);

        return redirect()->back();
    }

    public function inApprove($id)
    {
        // dd($id);
        DB::table('t_sales_order')
        ->where('so_code',$id)
        ->update(['status_aprove' => 'in approval']);

        return redirect()->back();
    }

    public function create()
    {
        $company = DB::table('m_company_profile')->get();

        $getCustomer = MCustomerModel::select(DB::raw("CONCAT(m_customer.main_address,' - ',m_kelurahan_desa.name,' - ',m_kecamatan.name,' - ',m_kota_kab.type,' ',m_kota_kab.name) as address"),'m_customer.id','m_customer.name')
        ->join('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
        ->join('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->orderBy('m_customer.name')
        ->where('status',true)->get();

        $getProduk = MProdukModel::orderBy('name')->get();

        $biaya_kirim = DB::table('m_biaya_kirim')->get();
        $method_bayar = DB::table('m_metode_bayar')->get();
        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();

        $dataDate = date("ym");

        $getLastCode = DB::table('t_sales_order')
        ->select('id')
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode)== 3){$nol = "0";}else{$nol = null;}

        $setInvoice = 'SOTK'.$dataDate.$nol.$getLastCode;

        //dd($getCustomer);

        $getLastCode = DB::table('t_sales_order')
        ->select('id')
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode)== 3){$nol = "0";}else{$nol = null;}

        $setInvoice = 'SOTK'.$dataDate.$nol.$getLastCode;


        //dd($getCustomer);


        return view('admin.transaksi.sales-order.create',compact('getCustomer','getProduk','setInvoice','jangkaWaktu','company','biaya_kirim','method_bayar'));

    }

    public function showSalesByCustomerWilayah(Request $request,$customerID)
    {
        $customer = MCustomerModel::where('id',$customerID)->first();
        $dataSalesByCustomerWilayah = MWilayahPembagianSalesModel::join('m_user','m_user.id','m_wilayah_pembagian_sales.sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_wilayah_pembagian_sales.wilayah_sales',$customer->wilayah_sales)
        ->orderBy('m_wilayah_pembagian_sales.id','ASC')
        // ->whereNull('m_user.deleted_at')
        ->first();
        return Response::json($dataSalesByCustomerWilayah);
    }

    public function showSalesCounter($customerID)
    {
        $customer = MCustomerModel::where('id',$customerID)->first();
        $query = MWilayahPembagianSalesModel::join('m_user','m_user.id','m_wilayah_pembagian_sales.sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_wilayah_pembagian_sales.wilayah_sales',$customer->wilayah_sales)
        ->orderBy('m_wilayah_pembagian_sales.id','ASC')
        ->take(1);

        //tambah sales Counter
        ///
        $salesCounter = $query->get();

        $dataCover = DB::table('m_cover_sales')
        ->join('m_user','m_user.id','=','m_cover_sales.cover_sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_cover_sales.sales',$salesCounter[0]->sales_id)
        ->get();

        $salesCounter = array_merge($salesCounter->toArray(),$dataCover->toArray());
        return Response::json($salesCounter);

    }

    public function showAddressCustomer(Request $request,$customerID)
    {
        $data = [];
        //ambil alamat asli customer
        $customer = DB::table('m_customer')->select('m_customer.id',DB::raw("CONCAT(m_customer.main_address,' - ',m_kelurahan_desa.name,' - ',m_kecamatan.name,' - ',m_kota_kab.type,' ',m_kota_kab.name) as address"), DB::raw("'main' as type"))
        ->join('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
        ->join('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->where('m_customer.id',$customerID)
        ->get();

        //mencari alamat lainnya customer di m_alamat_customer
        $otherAddress = DB::table('m_alamat_customer')->select('m_alamat_customer.id',DB::raw("CONCAT(m_alamat_customer.address,' - ',m_kelurahan_desa.name,' - ',m_kecamatan.name,' - ',m_kota_kab.type,' ',m_kota_kab.name) as address"), DB::raw("'other' as type"))
        ->join('m_kelurahan_desa','m_kelurahan_desa.id','=','m_alamat_customer.kelurahan')
        ->join('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->where('m_alamat_customer.customer',$customerID)
        ->get();
        //merge array
        $data = array_merge($customer->toArray(),$otherAddress->toArray());
        return Response::json($data);
    }

    public function showProdukByCustomerId($id)
    {
        // $customer = DB::table('m_customer')->where('id',$id)->first();

        $data = DB::table('m_stok_produk')
        ->join('m_produk','m_produk.code','=','m_stok_produk.produk_code')
        ->select('m_produk.*',DB::raw('SUM(m_stok_produk.stok) as stok'))
        ->groupBy('m_produk.id','m_produk.code')
        ->where('m_stok_produk.gudang',$id)
        ->where('m_stok_produk.stok','!=', 0)
        ->get();

        return Response::json($data);

    }

    public function showProdukSO($idGudang,$idCustomer)
    {

        $customer = DB::table('m_customer')->where('id',$idCustomer)->first();

        $data = DB::table('m_stok_produk')
            ->join('m_produk','m_produk.code','=','m_stok_produk.produk_code')
            ->select('m_produk.id','m_produk.code','m_produk.name',DB::raw('SUM(m_stok_produk.stok) as stok'))
            ->groupBy('m_produk.id','m_produk.code','m_produk.name')
            ->orderBy('m_produk.name','ASC')
            ->where('m_stok_produk.gudang',$idGudang)
            ->where(function ($query) {
                $query->where('m_stok_produk.stok','!=', 0)
                ->orWhere('m_stok_produk.balance','!=',0);
            })
            ->get();

        foreach ($data as $key => $value) {
            $harga = DB::table('m_harga_produk')
            ->where('gh_code',$customer->gh_code)
            ->where('produk',$value->id)
            ->orderBy('created_at','DESC')
            ->pluck('price')
            ->first();
            $value->harga = $harga;
            if( $harga == 0 ){
                //delete-array
                unset($data[$key]);
            }

        }

        //re-index-array
        $data = array_values($data->toArray());

        return Response::json($data);

    }

    public function getGudang($idCustomer)
    {
        $data = [];

        $customer = DB::table('m_customer')->where('id',$idCustomer)->first();

        $dataGudang = DB::table('m_gudang')->where('id',"!=", 2)->orderBy('name','DESC')->get();

        // array_push($data,$dataGudang,$customer->gudang);
        $data = [
            'dataGudang' => $dataGudang,
            'gudang' => $customer->gudang,
        ];

        return Response::json($data);
    }

    public function validateTopFaktur($idCustomer)
    {
        setlocale (LC_TIME, 'id_ID');
        setlocale (LC_TIME, 'INDONESIA');

        $success = false;

        $dataFakturCustomer = DB::table('t_faktur')
        ->join('m_customer','m_customer.id','t_faktur.customer')
        ->join('t_sales_order','t_sales_order.so_code','t_faktur.so_code')
        ->select('t_faktur.*','m_customer.name as name_customer','m_customer.id as id_customer','t_sales_order.so_date','t_sales_order.top_toleransi')
        ->where('m_customer.id',$idCustomer)
        ->where('t_faktur.status_payment','unpaid')
        ->orderBy('id','DESC')
        ->get();

        foreach ($dataFakturCustomer as $faktur) {

            //ambil toptanggal jatuh_tempo (faktur) + top_toleransi (so)
            $faktur->top_date = date('Y-m-d', strtotime($faktur->jatuh_tempo. ' + '.$faktur->top_toleransi.' days'));

            //perbandingan top-date dan hari ini
            if( date('Y-m-d') > $faktur->top_date ){
                $success = true;
            }
        }
        return Response::json($success);
    }

    public function getProduk(Request $request)
    {
        ini_set('memory_limit', '512MB');
        ini_set('max_execution_time', 3000);
        $result = ($request->id)
                ? MProdukModel::find($request->id)
                : MProdukModel::where('barcode', $request->barcode)->first();

        Log::info($result->id);

        $id_product = $result->id;
        $id_customer = $request->id_customer;
        $produk = $request->produk;

        //ambil DATA & price_variant customer
        $dataCustomer= DB::table('m_customer')->where('id',$id_customer)->first();

        //ambil harga price_list
        $produkName = $result->name;
        $produkCode = $result->code;
        $main_price_data = DB::table('m_harga_produk')
            ->where('produk', $result->id)
            ->where('m_harga_produk.gh_code', $dataCustomer->gh_code)
            ->where('date_start', '<=' , date('Y-m-d'))
            ->where('date_end', '>=' , date('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();
        if ($main_price_data !== null) {
            $main_price = $main_price_data->price;
        }else{
            $main_price_data_last = DB::table('m_harga_produk')
            ->where('m_harga_produk.gh_code', $dataCustomer->gh_code)
            ->where('produk', $result->id)
            ->where('date_end', '<=' , date('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->orderBy('date_end', 'desc')
            ->first();

            if($main_price_data_last !== null){
                $main_price = $main_price_data_last->price;
            }else{
                $main_price = 0;
            }
        }

        //cek array
        if(!empty($dataCustomer)){
            //cek positive
            if( $dataCustomer->price_variant > 0){
                $main_price = $main_price + $dataCustomer->price_variant;
            }else{
                $main_price = $main_price - $dataCustomer->price_variant;
            }
        }
        //set produk-price
        $produkPrice = $main_price;

        $code_product = DB::table('m_produk')
            ->select('m_produk.*','m_satuan_unit.code as code_unit')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
            ->where('m_produk.id', $id_product)
            ->first();

        $data_customer = DB::table('m_customer')
            ->select('id','price_variant','gudang')
            ->where('id', $id_customer)
            ->first();

        //ambil stok
        $gudang = $request->gudang;

        $date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

        $balance = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $code_product->code)
            ->where('m_stok_produk.gudang', $gudang)
            ->where('type', 'closing')
            ->whereMonth('periode',date('m', strtotime($date_last_month)))
            ->whereYear('periode',date('Y', strtotime($date_last_month)))
            ->sum('balance');

        $stok = DB::table('m_stok_produk')
            ->select('m_stok_produk.produk_code')
            ->where('m_stok_produk.produk_code', $code_product->code)
            ->where('m_stok_produk.gudang', $gudang)
            ->whereMonth('created_at',date('m', strtotime($date_now)))
            ->whereYear('created_at',date('Y', strtotime($date_now)))
            ->groupBy('m_stok_produk.produk_code')
            ->sum('stok');

        $stok = $stok + $balance;
        // $id_product
        $hpp = DB::table('t_closing_hpp')->where('status','open')->where('id_barang',$id_product)->whereMonth('closing_date',date('m'))->first();

        (!empty($hpp)) ? $oldHpp = $hpp->old_hpp : $oldHpp = 0;

        $cekproduk = 0;
        if ($produk != null || $produk != '') {
            foreach ($produk as $i => $raw_produk) {
                if ($id_product == $produk[$i]) {
                    $cekproduk = 1;
                }
            }
        }

        $stokfree = $stok -1;

        //$lengthProduk = count($produk)+1;

        $isi = null;
        $row = [];
        if ($cekproduk == 0) {
            $isi = "<tr id='tr_".$result->id ."'>";

            $isi .= "<input type='hidden' value='". $result->id ."'name='id_produk[".$request->length."]' id='produk_id_". $result->id."'>";
            $isi .= "<input type='hidden' value='". $oldHpp ."' name='hpp[".$result->id."]' id='hpp_id_". $result->id."'>";

            // $isi .= "<td> <input type='text' disabled class='form-control input-sm' value='".$produkCode."'></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$produkName."' name='produk[".$request->length."]' data-toggle='tooltip' data-placement='top' title='".$produkName."' style='cursor:pointer;'></td>";

            // $isi .= "<td style='font-size:8px;'> <input type='text' class='form-control input-sm' readonly value='".$dataCustomer->gh_code."'></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm text-only-number' id='". $result->id."_persen'
            lass='form-control input-sm' onkeyup='hitungSubTotal(". $result->id.",1)' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' autocomplete='off' onchange='hitungSubTotal(". $result->id.",1)' value='0' name='persen[".$request->length."]'></td>";

            $isi .= "<td> <input type='text' id='". $result->id."_potongan' class='form-control input-sm text-only-number' onkeyup='hitungSubTotal(". $result->id.",1)' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' onchange='hitungSubTotal(". $result->id.",1)' value='0' name='potongan[".$request->length."]'></td>";
            // id='".$lengthProduk."_diskon'
            // onchange='diskonItem(".$lengthProduk.")' onkeyup='diskonItem(".$lengthProduk.")'

            $isi .= "<td> <input type='text' class='form-control input-sm text-only-number' id='". $result->id."_markup_persen' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' name='markup_persen[".$request->length."]' onchange='hitungSubTotal(". $result->id.",2)' value='0' onkeyup='hitungSubTotal(". $result->id.",2)'></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm text-only-number' id='". $result->id."_markup' onkeypress='hitungSubTotal(". $result->id.",2)' autocomplete='off' name='markup[".$request->length."]' onchange='hitungSubTotal(". $result->id.",2)' value='0' onkeyup='hitungSubTotal(". $result->id.",2)'></td>";

            $isi .= "<td>
            <input type='hidden' class='form-control input-sm ". $result->id."_produkPrice' readonly value='".$produkPrice."' name='hargaProduk[".$request->length."]' id='". $result->id."_harga'>

            <input type='text' style='text-align:right;' readonly value='".number_format($produkPrice,0,'.','.')."' name='hargaDasar[".$request->length."]' id='". $result->id."_hargaDasar' class='form-control input-sm'>
            </td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$stok."' name='stok[".$request->length."]' id='". $result->id."_stok'></td>";

            $isi .= "<td> <input type='number' min='1' max='".$stok."' id='".$result->id."_jumlah' class='form-control input-sm' onkeyup='hitungSubTotal(".$result->id.",0); setMaxStok(". $result->id.");' onkeypress='hitungSubTotal(". $result->id.",0); setMaxStok(". $result->id.");' autocomplete='off' onchange='hitungSubTotal(". $result->id.",0); setMaxStok(". $result->id.");' name='jumlah[".$request->length."]' value='1'></td>";

            // $isi .= "<td> <input type='number' min='0' max='".$stokfree."' class='form-control input-sm' name='qty_free[".$request->length."]' value='0'  onkeyup='setMaxStok(".$result->id.");' onkeypress='setMaxStok(". $result->id.");' onchange='setMaxStok(". $result->id.");'  autocomplete='off' id='". $result->id."_free_qty'></td>";

            $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$code_product->code_unit."' ></td>";

            $isi .= "<td> <input type='text' style='text-align:right;' readonly class='form-control input-sm ". $result->id."_subTotal' value='".number_format($produkPrice,0,'.','.')."' name='subTotal[". $result->id."]' id='". $result->id."_subTotal'></td>";

            $isi .= "<td> <button type='button' value='".$request->length."' class='btn btn-danger btn-sm btn-delete' onclick='hapusBaris(". $result->id.")'><span class='fa fa-trash'></span></button></td>";

            // $isi .= "<input type='hidden' class='form-control input-sm ".$lengthProduk."_subTotal' value='' name='subTotal[".$lengthProduk."]' id='".$lengthProduk."_subTotal'>";


            $isi .= "</tr>";
        }
        array_push($row, $isi, $produkPrice, $id_product);

        return $row;
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'customer' => 'required',
            'sending_address' => 'required',
            'sending_date' => 'required',
        ]);

        $dataDate = date("ym");

        $getLastCode = DB::table('t_sales_order')
        ->select('id')
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode)== 3){$nol = "0";}else{$nol = null;}

        $setInvoice = 'SOTK'.$dataDate.$nol.$getLastCode;


        $sending_date = date('Y-m-d', strtotime($request->sending_date));
        $so_date = date('Y-m-d', strtotime($request->so_date));
        $date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));
        // ( $request->cod == 'on' ) ? $request->cod = true : $request->cod = false ;

        //     //insert d_transaksi
        $array = [];
        $i = 0;
        $getIdProduk = $request->id_produk;
        $getQty = $request->jumlah;
        $getTotal = str_replace(array('.', ','), '' , $request->subTotal);
        // $getHarga = $request->hargaProduk;
        $getHarga = str_replace(array('.', ','), '' , $request->hargaDasar);
        //$getPPN = str_replace(array('.', ','), '' , $request->amount_ppn);
        //getValueProduk insert To array

        //dd($getIdProduk);

        foreach($getIdProduk as $rowProduk)
        {
            $array[$i]['invoice_t'] = $setInvoice;
            $array[$i]['produk_id'] = $rowProduk;

            $i++;
            //($i <= count($rowProduk)) ? $i++ : $i = 0;
        }

        $i = 0;

        foreach($getQty as $rowQty){
            $array[$i]['qty'] = $rowQty;
            $i++;
        }

        $i = 0;

        // foreach($request->qty_free as $rowQtyFree){
        //     $array[$i]['free_qty'] = $rowQtyFree;
        //     $i++;
        // }

        $i = 0;

        $grand_total = 0;
        foreach($getTotal as $rowTotal){
            $array[$i]['total'] = $rowTotal;

            $i++;
            //($i <= count($rowTotal)) ? $i++ : $i = 0;
            //grand total
            $grand_total = $grand_total + $rowTotal;
        }

        $i = 0;

        foreach($request->potongan as $rowpotongan){
            $array[$i]['potongan'] = str_replace(array('.', ','), '' , $rowpotongan);
            $i++;
        }

        $i = 0;

        foreach($request->persen as $rowpersen){
            $array[$i]['persen'] = $rowpersen;
            $i++;
        }

        $i = 0;

        foreach($request->markup as $rowmarkup){
            $array[$i]['markup'] = str_replace(array('.', ','), '' , $rowmarkup);
            $i++;
        }

        $i = 0;

        foreach($request->markup_persen as $rowmarkuppersen){
            $array[$i]['markup_persen'] = $rowmarkuppersen;
            $i++;
        }


        $i = 0;

        foreach($getHarga as $rowHarga){
            $array[$i]['harga'] = str_replace(array('.', ','), '' , $rowHarga);
            $i++;
            //($i <= count($rowHarga)) ? $i++ : $i = 0;
        }

        // echo "<pre>";
        //     print_r($array);
        // die();

        // dd($request->all(),$request->cod,$request->total);
        if($request->type_shipping_address == "main"){
            $shipping = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $request->sending_address)->first();
            $type_shipping_address = "main";

        }else{
            $shipping = DB::table('m_alamat_customer')
                            ->select('m_alamat_customer.*','m_alamat_customer.address as main_address', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                            ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                            ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                            ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                            ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                            ->where('m_alamat_customer.id', $request->sending_address)->first();
            $type_shipping_address = "other";
        }
        // dd($shipping, $request->sending_address,$request->type_shipping_address);
        if($request->method_kirim == null || $request->method_kirim == ""){
            $all_biaya_kirim_id = 0;
            $all_biaya_kirim_biaya = 0;
        }else{
            $all_biaya_kirim = DB::table('m_biaya_kirim')->where('id',$request->method_kirim)->first();
            $all_biaya_kirim_id = $all_biaya_kirim->id;
            $all_biaya_kirim_biaya = $all_biaya_kirim->harga_biaya_kirim;
        }
        if($request->method_bayar == null || $request->method_bayar == ""){
            $metode_bayar_id = 0;
        }else{
            $metode_bayar = DB::table('m_metode_bayar')->where('id',$request->method_bayar)->first();
            $metode_bayar_id = $metode_bayar->id;
        }
        if($request->sales == null || $request->sales == ""){
            $sales = 3;
        }else{
            $sales = $request->sales;
        }
        DB::beginTransaction();
        try{
            if($request->diskon_potongan == ''){
                $diskon_potongan = 0;
            }else{
                $diskon_potongan = $request->diskon_potongan;
            }

            if($request->diskon_persen == ''){
                $diskon_persen = 0;
            }else{
                $diskon_persen = $request->diskon_persen;
            }

            // insert t_transaksi
            $store = new TSalesOrderModel;
            $store->so_code = $setInvoice;
            $store->company_code = $request->company_code;
            $store->customer = $request->customer;
            $store->atas_nama = $request->atas_nama;
            $store->type_atas_nama = 'main';
            $store->sales = $sales;
            $store->sending_date = $sending_date;
            $store->so_date = $so_date;
            $store->sending_address = $shipping->main_address.','.$shipping->nama_kota.','.$shipping->nama_prov;
            $store->type_sending = $type_shipping_address;
            $store->id_sending = $request->sending_address;
            $store->description = $request->description;
            $store->user_receive = $request->user_receive;
            $store->user_input = auth()->user()->id;
            $store->top_hari = $request->top_hari;
            $store->top_toleransi = $request->top_toleransi;
            $store->gudang = $request->gudang;
            //$store->so_from = 'admin';
            $store->ppn = $request->ppn;
            $store->metode_kirim = $all_biaya_kirim_id;
            $store->biaya_kirim = $all_biaya_kirim_biaya;
            $store->so_from = "web";
            // $store->biaya_kirim = 0;
            $store->metode_bayar = $metode_bayar_id;
            $store->ppn = $request->ppn;
            $store->amount_ppn = $request->amount_ppn;
            $store->grand_total = str_replace(array('.', ','), '' , $request->grand_total);
            // $store->grand_total = str_replace(array('.', ','), '' , $request->total);
            //$store->grand_total = str_replace(array('.', ','), '' , $request->grandTotal);
            $store->cod = 0;
            $store->diskon_header_potongan = $request->diskon_total_rp;
            $store->diskon_header_persen = $request->diskon_total_persen;
            $store->description = $request->description;
            $store->save();

            //detail-so
            for($n=0; $n<count($array); $n++){
                $insertDetailTransaksi = new DSalesOrder;
                $insertDetailTransaksi->so_code = $array[$n]['invoice_t'];
                $insertDetailTransaksi->produk = $array[$n]['produk_id'];
                $insertDetailTransaksi->qty = $array[$n]['qty'];
                // $insertDetailTransaksi->free_qty = $array[$n]['free_qty'];
                $insertDetailTransaksi->total = $array[$n]['total'];
                $insertDetailTransaksi->diskon_potongan = $array[$n]['potongan'];
                $insertDetailTransaksi->diskon_persen = $array[$n]['persen'];
                $insertDetailTransaksi->markup = $array[$n]['markup'];
                $insertDetailTransaksi->markup_persen = $array[$n]['markup_persen'];
                $insertDetailTransaksi->customer_price = $array[$n]['harga'];
                $insertDetailTransaksi->save();
            }

            DB::commit();

            $success = true;

        } catch (\Exception $e) {
            dd($e);
            $success = false;
            DB::rollback();
        }

        if( $setInvoice != $request->invoice ){
            return redirect('admin/transaksi-sales-order')->with('message', 'Kode Sales Order Sudah Dipakai Kode Sales order anda '.$setInvoice.'');
        }
        return redirect('admin/transaksi-sales-order');
        // if($success == true){
        //     return redirect('admin/transaksi-sales-order');
        // }else{
        //     return redirect()->back()->with('message', 'Code Order Sudah Ada Atau Produk Belum Terisi, Coba Lagi');
        // }
    }

    public function detailShow($soCode)
    {

        $detailSalesOrder = DB::table('d_sales_order')
            ->join('t_sales_order','t_sales_order.so_code','=','d_sales_order.so_code')
            ->leftjoin('m_user','m_user.id','=','t_sales_order.sales')
            ->join('m_customer','m_customer.id','=','t_sales_order.customer')
            ->join('m_produk','m_produk.id','=','d_sales_order.produk')
            // ->join('m_harga_produk','m_harga_produk.produk','=','m_produk.id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
            ->select('d_sales_order.*','m_produk.name as produk','m_produk.code as produk_code','m_produk.id as produkID','m_satuan_unit.code as code_unit',
            't_sales_order.so_date','t_sales_order.sending_date','t_sales_order.id as id_transaksi','t_sales_order.so_code','t_sales_order.user_input','t_sales_order.user_receive','t_sales_order.top_hari','t_sales_order.top_toleransi',
            't_sales_order.diskon_header_potongan','t_sales_order.diskon_header_persen','t_sales_order.grand_total','t_sales_order.status_aprove','t_sales_order.cancel_reason','t_sales_order.user_cancel','t_sales_order.cancel_description',
            'm_user.name as sales','m_customer.name as customer','t_sales_order.ppn','t_sales_order.amount_ppn')
            ->where('d_sales_order.so_code','=',$soCode)
            ->get();

        foreach ($detailSalesOrder as $detailSO) {
            $userInput = DB::table('m_user')->where('id',$detailSO->user_input)->first();

            $userReceive = DB::table('m_user')->where('id',$detailSO->user_receive)->first();

            $user_cancel = DB::table('m_user')->where('id',$detailSO->user_cancel)->first();

            // $detailSO->userinput = $userInput->name;
            // $detailSO->userReceive = $userReceive->name;
            (!empty($userInput)) ? $detailSO->userinput = $userInput->name : $detailSO->userinput = '';
            (!empty($userReceive)) ? $detailSO->userReceive = $userReceive->name : $detailSO->userReceive = '';
            (!empty($user_cancel)) ? $detailSO->user_cancel = $user_cancel->name : $detailSO->user_cancel = '';
        }

        $subTotal1 = DB::table('d_sales_order')->where('so_code', '=', $soCode)->sum('total');
        //dd($detailSalesOrder,$subTotal1);
        return view('admin.transaksi.sales-order.detail', compact('detailSalesOrder', 'subTotal1','soCode'));
    }

    public function getFaktur($customer)
    {
        $data_tail = [];
        $data_customer = DB::table("m_customer")->where('id', $customer)->first();

        $data_head = DB::table("m_customer")
        ->select('id','name')
        ->where('id', $customer)
        ->orwhere('head_office', $customer)
        ->get();

        if ($data_customer->head_office != null) {
            $data_tail = DB::table("m_customer")
            ->select('id','name')
            ->where('id', $data_customer->head_office)
            ->orwhere('head_office', $data_customer->head_office)
            ->get();

            foreach ($data_tail as $i=>$raw) {
                if ($raw->id == $customer) {
                    unset($data_tail[$i]);
                }
            }

            $data_tail = $data_tail->toArray();
        }

        $data = array_merge($data_head->toArray(), $data_tail);

        return Response::json($data);
    }
    //edit-lama
    // public function edit($socode)
    // {
    //     $detailSO = DB::table('t_sales_order')
    //                 ->join('m_customer','m_customer.id','=','t_sales_order.customer')
    //                 ->join('m_user','m_user.id','=','t_sales_order.sales')
    //                 ->select('t_sales_order.*','m_user.name as sales','m_customer.name as customer','m_customer.id as id_customer','m_customer.gudang')
    //                 ->where('t_sales_order.so_code','=',$socode)
    //                 ->first();
    //     $dataProdukUpdate = DB::table('d_sales_order')
    //                     ->join('t_sales_order','t_sales_order.so_code','=','d_sales_order.so_code')
    //                     ->join('m_user','m_user.id','=','t_sales_order.sales')
    //                     ->join('m_customer','m_customer.id','=','t_sales_order.customer')
    //                     ->join('m_produk','m_produk.id','=','d_sales_order.produk')
    //                     ->select('d_sales_order.*','t_sales_order.so_date','m_produk.name as produk','m_produk.code as produk_code',
    //                     'm_produk.id as produk_id','m_produk.satuan_kemasan',
    //                     't_sales_order.id as id_transaksi','t_sales_order.so_code',
    //                     'm_user.name as sales','m_customer.name as customer','m_customer.gudang')
    //                     ->where('d_sales_order.so_code','=',$socode)
    //                     ->get();
    //     // dd($dataProdukUpdate);
    //     //add array index from stok produk
    //     foreach ($dataProdukUpdate as $produk) {
    //         $stok = DB::table('m_stok_produk')
    //                 ->where('m_stok_produk.produk_code', $produk->produk_code)
    //                 ->where('m_stok_produk.gudang', $produk->gudang)
    //                 ->groupBy('m_stok_produk.produk_code')
    //                 ->sum('stok');
    //         $produk->stokGudang = $stok;
    //     }
    //
    //     $query = DB::table('m_stok_produk')->join('m_produk','m_produk.code','=','m_stok_produk.produk_code')
    //             ->select('m_produk.*',DB::raw('SUM(m_stok_produk.stok) as stok'))
    //             ->groupBy('m_produk.id','m_produk.code')
    //             ->where('gudang',$detailSO->gudang)
    //             ->where('stok','!=', 0);
    //     //filter brang yang sudah dipilih
    //
    //     // foreach ($dataProdukUpdate as $produk) {
    //     //     $query->where('m_produk.id','!=',$produk->produk_id);
    //     // }
    //     $getProduk = $query->get();
    //
    //     //get credit_limit
    //     $dataCustomer = DB::table('m_customer')
    //         ->where('id',$detailSO->id_customer)
    //         ->first();
    //
    //     if ($dataCustomer->credit_limit == null) {
    //         $credit_limit = 0;
    //     }else{
    //         $credit_limit = $dataCustomer->credit_limit;
    //     }
    //
    //     $data_credit_customer = DB::table('t_sales_order')
    //         ->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
    //         ->where('t_sales_order.customer', $dataCustomer->id)
    //         ->where(function ($query) {
    //             $query->where('t_sales_order.status_aprove','!=','closed')
    //                   ->Where('t_sales_order.status_aprove','!=','reject')
    //                   ->Where('t_sales_order.status_aprove','!=','cancel');
    //             })
    //         ->get();
    //
    //     $credit_customer = 0;
    //     foreach ($data_credit_customer as $raw_data) {
    //         $qty = $raw_data->qty;
    //         $sj_qty = $raw_data->sj_qty;
    //         $sisa_qty = $qty - $sj_qty;
    //         $total = $raw_data->total;
    //
    //         $total_credit = ($total / $qty) * $sisa_qty;
    //
    //         $credit_customer = $credit_customer + $total_credit;
    //     }
    //
    //     $piutang = DB::table('t_faktur')
    //             ->where('customer', $dataCustomer->id)
    //             ->where('status_payment', 'unpaid')
    //             ->sum('total');
    //
    //     $piutang_dibayar = DB::table('t_pembayaran')
    //             ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
    //             ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
    //             ->where('t_pembayaran.customer', $dataCustomer->id)
    //             ->where('t_faktur.status_payment', 'unpaid')
    //             ->where('t_pembayaran.status', 'approved')
    //             ->sum('d_pembayaran.total');
    //
    //     $piutang = $piutang - $piutang_dibayar;
    //
    //     $sisaCredit = $credit_limit - $credit_customer - $piutang;
    //
    //     return view('admin.transaksi.sales-order.update',compact('dataProdukUpdate','getProduk','detailSO','sisaCredit'));
    // }

    // public function update(Request $request)
    // {
    //     $oldDetailSO = DB::table('d_sales_order')->where('so_code',$request->so_code)->get();
    //
    //     // arrayUpdate
    //     $arrayUpdate = [];
    //     $i = 0;
    //     if( count($request->id_detail_so) > 0 && count($request->subTotal) > 0 && count($request->qty) > 0 && count($request->customer_price) > 0 ){
    //
    //         foreach ($request->id_detail_so as $rawid) {
    //             $arrayUpdate[$i]['id'] = $rawid;
    //             $arrayUpdate[$i]['so_code'] = $request->so_code;
    //             // ($i <= count($rawid)) ? $i++ : $i = 0;
    //             $i++;
    //         }
    //         $i = 0;
    //         foreach ($request->subTotal as $subtotal) {
    //             $arrayUpdate[$i]['subTotal'] = $subtotal;
    //             // ($i <= count($subtotal)) ? $i++ : $i = 0;
    //             $i++;
    //         }
    //         $i = 0;
    //         foreach ($request->qty as $rawqty) {
    //             $arrayUpdate[$i]['qty'] = $rawqty;
    //             // ($i <= count($rawqty)) ? $i++ : $i = 0;
    //             $i++;
    //         }
    //         $i = 0;
    //         foreach ($request->customer_price as $rawcustomer) {
    //             $arrayUpdate[$i]['customer_price'] = $rawcustomer;
    //             // ($i <= count($rawcustomer)) ? $i++ : $i = 0;
    //             $i++;
    //         }
    //     }
    //
    //     //
    //     // echo "<pre>";
    //     //     print_r($arrayUpdate);
    //     // echo "</pre>";
    //
    //     $arrayNew = [];
    //     $n=0;
    //     if( count($request->id_produkNew) > 0 &&  $request->jumlahNew > 0 && count($request->hargaProdukNew) > 0 && count($request->subTotalNew) )
    //     {
    //         foreach ($request->id_produkNew as $newproduk) {
    //             $arrayNew[$n]['produk'] = $newproduk;
    //             // ($n <= count($rawcustomer)) ? $n++ : $n = 0;
    //             $n++;
    //         }
    //         $n=0;
    //         foreach ($request->jumlahNew as $newqty) {
    //             $arrayNew[$n]['qty'] = $newqty;
    //             // ($n <= count($newqty)) ? $n++ : $n = 0;
    //             $n++;
    //         }
    //         $n=0;
    //         foreach ($request->hargaProdukNew as $customerprice) {
    //             $arrayNew[$n]['customer_price'] = $customerprice;
    //             // ($n <= count($customerprice)) ? $n++ : $n = 0;
    //             $n++;
    //         }
    //         $n=0;
    //         foreach ($request->subTotalNew as $total) {
    //             $arrayNew[$n]['total'] = $total;
    //             // ($n <= count($total)) ? $n++ : $n = 0;
    //             $n++;
    //         }
    //     }
    //     // echo "<pre>";
    //     //     print_r($arrayNew);
    //     // echo "</pre>";
    //     // echo "<pre>";
    //     //     print_r($arrayUpdate);
    //     // echo "</pre>";
    //     // dd($request->all(),$oldDetailSO);
    //     // die();
    //     // update detail so
    //     DB::beginTransaction();
    //     try{
    //
    //         //update t_sales_order
    //         DB::table('t_sales_order')->where('so_code',$request->so_code)->update([
    //             'description' => $request->description
    //         ]);
    //
    //             foreach ($oldDetailSO as $key => $so) {
    //
    //                 $cekIdSo = 0; //flag id so
    //
    //                 for($x=0; $x<count($arrayUpdate); $x++){
    //
    //                     //kondisi-update
    //                     if( $so->id == $arrayUpdate[$x]['id'] ){
    //
    //                         $cekIdSo = 1;
    //
    //                         DB::table('d_sales_order')->where('id',$arrayUpdate[$x]['id'])->update([
    //                             'qty' => $arrayUpdate[$x]['qty'],
    //                             'total' => $arrayUpdate[$x]['subTotal'],
    //                         ]);
    //                     }
    //                 } //endfor
    //
    //                 //kondisi hapus
    //                 if( $cekIdSo == 0 ){
    //                     DB::table('d_sales_order')->where('id',$so->id)->delete();
    //                 }
    //             }
    //
    //
    //         //update so
    //         // DB::table('t_sales_order')->where('so_code',$request->so_code)->update([
    //         //     'status_aprove' => $request->status_aprove,
    //         // ]);
    //
    //         //insert new produk
    //         if( count($arrayNew) > 0){
    //             for($y=0; $y<count($arrayNew); $y++){
    //                 DB::table('d_sales_order')->insert([
    //                     'so_code' => $request->so_code,
    //                     'produk' => $arrayNew[$y]['produk'],
    //                     'qty' => $arrayNew[$y]['qty'],
    //                     'customer_price' => $arrayNew[$y]['customer_price'],
    //                     'total' => $arrayNew[$y]['total'],
    //                     'diskon_potongan' => $request->potonganHarga,
    //                     'diskon_persen' => $request->potonganPersen,
    //                 ]);
    //             }
    //         }
    //
    //         DB::commit();
    //     }catch(\Exception $e)
    //     {
    //         DB::rollback();
    //         dd($e);
    //     }
    //     return redirect('admin/transaksi-sales-order/');
    // }

    //new-edit
    public function edit($soCode)
    {
        //data-so
        $copyDataSO = DB::table('t_sales_order')
            ->join('m_customer','m_customer.id','t_sales_order.customer')
            ->select('m_customer.id as id_customer','m_customer.code','m_customer.gudang','m_customer.wilayah_sales','m_customer.gudang as gudang_customer','m_customer.credit_limit','m_customer.head_office','m_customer.gh_code','m_customer.price_variant','m_customer.credit_limit_days','t_sales_order.*')
            ->where('t_sales_order.so_code',$soCode)
            ->first();

        //dd($copyDataSO);

        $barangCopyS0 = DB::table('d_sales_order')
                    ->join('m_produk','m_produk.id','=','d_sales_order.produk')
                    ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
                    ->select('*','m_produk.id as produk_id','m_produk.code','m_satuan_unit.code as satuan_unit')
                    ->where('so_code',$soCode)
                    ->get();

        $totalHarga = 0; //varible for totalHarga
        foreach ($barangCopyS0 as $produk) {
            $main_price_data = DB::table('m_harga_produk')
            ->where('produk', $produk->produk_id)
            ->where('m_harga_produk.gh_code', $copyDataSO->gh_code)
            ->where('date_start', '<=' , date('Y-m-d'))
            ->where('date_end', '>=' , date('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();
            if ($main_price_data !== null) {
                $main_price = $main_price_data->price;
            }else{
                $main_price_data_last = DB::table('m_harga_produk')
                ->where('m_harga_produk.gh_code', $copyDataSO->gh_code)
                ->where('produk', $produk->produk_id)
                ->where('date_end', '<=' , date('Y-m-d'))
                ->orderBy('created_at', 'desc')
                ->orderBy('date_end', 'desc')
                ->first();

                if($main_price_data_last !== null){
                    $main_price = $main_price_data_last->price;
                }else{
                    $main_price = 0;
                }
            }


            //cek price_variant customer
            if($copyDataSO->price_variant > 0){
                $main_price = $main_price + $copyDataSO->price_variant;
            }else{
                $main_price = $main_price - $copyDataSO->price_variant;
            }
            //get-stok-produk

        $date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

        $balance = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $produk->code)
            ->where('m_stok_produk.gudang', $copyDataSO->gudang)
            ->where('type', 'closing')
            ->whereMonth('periode',date('m', strtotime($date_last_month)))
            ->whereYear('periode',date('Y', strtotime($date_last_month)))
            ->sum('balance');

        $getStokProduk = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $produk->code)
            ->where('m_stok_produk.gudang', $copyDataSO->gudang)
            ->whereMonth('created_at',date('m', strtotime($date_now)))
            ->whereYear('created_at',date('Y', strtotime($date_now)))
            ->groupBy('m_stok_produk.produk_code')
            ->sum('stok');

        $hpp = DB::table('t_closing_hpp')->where('status','open')->where('id_barang', $produk->produk_id)->whereMonth('closing_date',date('m'))->first();

        (!empty($hpp)) ? $oldHpp = $hpp->old_hpp : $oldHpp = 0;
        //dd($balance);

        $getStokProduk = $getStokProduk + $balance;

            $totalHarga = $totalHarga + $produk->total;
            //add new array
            $produk->gh_code = $copyDataSO->gh_code;
            $produk->stok = $getStokProduk;
            $produk->produkPrice = $main_price;
            $produk->oldHpp = $oldHpp;

        }
        // dd($barangCopyS0,$totalHarga);

        //all-customer
        $getCustomer = MCustomerModel::orderBy('name')->where('status',true)->get();

        //sales
        $dataSales = MWilayahPembagianSalesModel::join('m_user','m_user.id','m_wilayah_pembagian_sales.sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_wilayah_pembagian_sales.wilayah_sales',$copyDataSO->wilayah_sales)
        ->orderBy('m_wilayah_pembagian_sales.id','ASC')
        ->first();

        //sales_counter
        $query = MWilayahPembagianSalesModel::join('m_user','m_user.id','m_wilayah_pembagian_sales.sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_wilayah_pembagian_sales.wilayah_sales',$copyDataSO->wilayah_sales)
        ->orderBy('m_wilayah_pembagian_sales.id','ASC')
        ->take(1);

        //tambah sales Counter
        ///
        $salesCounter = $query->get();

        $dataCover = DB::table('m_cover_sales')
        ->join('m_user','m_user.id','=','m_cover_sales.cover_sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_cover_sales.sales',$salesCounter[0]->sales_id)
        ->get();

        $salesCounter = array_merge($salesCounter->toArray(),$dataCover->toArray());
        // $salesCounter1 = $salesCounter->merge($dataCover);
        // $salesCounter = Response::json($salesCounter);

        //$salesCounter =  array_merge( (array)$salesCounter, (array)$dataCover );
        foreach ($salesCounter as $key => $raw_data) {
            if ($key == 1) {
                array_push($salesCounter, [ "sales_id" => $raw_data->sales_id, "sales_name" => $raw_data->sales_name]);
                unset($salesCounter[$key]);
            }
        }

        $salesCounter = array_values($salesCounter);
        $salesCounter = (object)$salesCounter;

        //dd($salesCounter);

        //wilayah
        $wilayah = DB::table('m_wilayah_sales')->where('id',$copyDataSO->wilayah_sales)->first();

        $gudangAll = DB::table('m_gudang')->orderBy('id','DESC')->get();
        //data-produk
        $dataProduk = DB::table('m_stok_produk')
        ->join('m_produk','m_produk.code','=','m_stok_produk.produk_code')
        ->select('m_produk.id','m_produk.code','m_produk.name',DB::raw('SUM(m_stok_produk.stok) as stok'))
        ->groupBy('m_produk.id','m_produk.code','m_produk.name')
        ->where('gudang',$copyDataSO->gudang)
        ->where(function ($query) {
            $query->where('m_stok_produk.stok','!=', 0)
            ->orWhere('m_stok_produk.balance','!=',0);
        })
        ->get();
        // dd($dataProduk);
        // detail-customer
        $data_credit_customer = DB::table('t_sales_order')
        //->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
        ->where('t_sales_order.customer', $copyDataSO->id_customer)
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
        ->where('customer', $copyDataSO->id_customer)
        ->where('status_payment', 'unpaid')
        ->sum('total');

        $piutang_dibayar = DB::table('t_pembayaran')
        ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
        ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
        ->where('t_pembayaran.customer', $copyDataSO->id_customer)
        ->where('t_faktur.status_payment', 'unpaid')
        ->where('t_pembayaran.status', 'approved')
        ->sum('d_pembayaran.total');

        $oldest_piutang = DB::table('t_faktur')
        ->where('customer', $copyDataSO->id_customer)
        ->where('status_payment', 'unpaid')
        ->orderBy('created_at', 'asc')
        ->first();

        if ($oldest_piutang != null ) {
            $tanggalPiutangTerlama = date('d-m-Y',strtotime($oldest_piutang->created_at));
            $totalPiutangTerlama = ($oldest_piutang->jumlah_yg_dibayarkan == 0) ? $oldest_piutang->total : $oldest_piutang->total - $oldest_piutang->jumlah_yg_dibayarkan;
        }else{
            $tanggalPiutangTerlama = '';
            $totalPiutangTerlama = 0;
        }

        //total piutang
        $piutang = $piutang - $piutang_dibayar;

        //$sisaCredit = $copyDataSO->credit_limit - $credit_customer - $piutang;
        $sisaCredit = $copyDataSO->credit_limit - $piutang;

        $detailCustomer = [
            'piutang'               => $piutang,
            'wilayah'               => $wilayah->name,
            'gudang'                => $copyDataSO->gudang_customer,
            'sisacredit'            => $sisaCredit,
            'kodeCustomer'          => $copyDataSO->code,
            'creditlimit'           => $copyDataSO->credit_limit,
            'totalPiutangTerlama'   => $totalPiutangTerlama,
            'tanggalPiutangTerlama' => $tanggalPiutangTerlama,
            'topCustomer'           => $copyDataSO->credit_limit_days,
        ];

        //faktur
        $data_tail = [];
        $data_head = DB::table("m_customer")
        ->select('id','name')
        ->where('id', $copyDataSO->id_customer)
        ->orwhere('head_office', $copyDataSO->id_customer)
        ->get();

        if ($copyDataSO->head_office != null) {
            $data_tail = DB::table("m_customer")
            ->select('id','name')
            ->where('id', $copyDataSO->head_office)
            ->orwhere('head_office', $copyDataSO->head_office)
            ->get();

            foreach ($data_tail as $i=>$raw) {
                if ($raw->id == $copyDataSO->id_customer) {
                    unset($data_tail[$i]);
                }
            }

            $data_tail = $data_tail->toArray();
        }

        $dataFaktur = array_merge($data_head->toArray(), $data_tail);

        //alamat-lainnya
        $dataAlamat = [];
        //ambil alamat asli customer
        $mainCustomer = DB::table('m_customer')->select(DB::raw("CONCAT(m_customer.main_address,' - ',m_kelurahan_desa.name,' - ',m_kecamatan.name,' - ',m_kota_kab.type,' ',m_kota_kab.name) as address"), DB::raw("'main' as type"),'m_customer.id')
        ->join('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
        ->join('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->where('m_customer.id',$copyDataSO->id_customer)
        ->get();

        //mencari alamat lainnya customer di m_alamat_customer
        $otherAddress = DB::table('m_alamat_customer')->select(DB::raw("CONCAT(m_alamat_customer.address,' - ',m_kelurahan_desa.name,' - ',m_kecamatan.name,' - ',m_kota_kab.type,' ',m_kota_kab.name) as address"),DB::raw("'other' as type"),"m_alamat_customer.id")
        ->join('m_kelurahan_desa','m_kelurahan_desa.id','=','m_alamat_customer.kelurahan')
        ->join('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->where('m_alamat_customer.customer',$copyDataSO->id_customer)
        ->get();
        //merge array
        $dataAlamat = array_merge($mainCustomer->toArray(),$otherAddress->toArray());

        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();
        $company = DB::table('m_company_profile')->get();
        $biaya_kirim = DB::table('m_biaya_kirim')->get();
        $method_bayar = DB::table('m_metode_bayar')->get();

        return view('admin.transaksi.sales-order.update-new',compact('copyDataSO','dataSales','dataProduk','detailCustomer','dataFaktur','dataAlamat','getCustomer','gudangAll','barangCopyS0','totalHarga','salesCounter','jangkaWaktu', 'company','biaya_kirim','method_bayar'));

    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'customer' => 'required',
            'sending_address' => 'required',
            'sending_date' => 'required',
        ]);

        // dd($request->all());

        $id = $request->id;
        $sending_date = date('Y-m-d', strtotime($request->sending_date));
        $so_date = date('Y-m-d', strtotime($request->so_date));
        ( $request->cod == 'on' ) ? $request->cod = true : $request->cod = false ;
        ($request->diskon_potongan == '') ? $diskon_potongan = 0 : $diskon_potongan = $request->diskon_potongan ;
        ($request->diskon_persen == '') ? $diskon_persen = 0 : $diskon_persen = $request->diskon_persen ;


        //insert new d_transaksi
        $array = [];
        $i = 0;
        $getIdProduk = $request->id_produk;
        $getQty = $request->jumlah;
        $getTotal = str_replace(array('.', ','), '' , $request->subTotal);
        // $getHarga = $request->hargaProduk;
        $getHarga = str_replace(array('.', ','), '' , $request->hargaDasar);

        //getValueProduk insert To array

        //dd($getIdProduk);
        foreach($getIdProduk as $rowProduk)
        {
            $array[$i]['invoice_t'] = $request->invoice;
            $array[$i]['produk_id'] = $rowProduk;

            $i++;
            //($i <= count($rowProduk)) ? $i++ : $i = 0;
        }

        // $i = 0;

        // foreach($request->qty_free as $qty_free){
        //     $array[$i]['free_qty'] = $qty_free;
        //     $i++;
        // }

        $i = 0;

        foreach($getQty as $rowQty){
            $array[$i]['qty'] = $rowQty;
            $i++;
            //($i <= count($rowQty)) ? $i++ : $i = 0;
        }

        $i = 0;

        $grand_total = 0;
        foreach($getTotal as $rowTotal){
            $array[$i]['total'] = str_replace(array('.', ','), '' ,$rowTotal);

            $i++;
            //($i <= count($rowTotal)) ? $i++ : $i = 0;
            //grand total
            $grand_total = $grand_total + $rowTotal;
        }

        $i = 0;

        foreach($request->potongan as $rowpotongan){
            $array[$i]['potongan'] = str_replace(array('.', ','), '' ,$rowpotongan);
            $i++;
        }

        $i = 0;

        foreach($request->persen as $rowpersen){
            $array[$i]['persen'] = $rowpersen;
            $i++;
        }

        $i = 0;

        foreach($request->markup as $rowmarkup){
            $array[$i]['markup'] = str_replace(array('.', ','), '' ,$rowmarkup);
            $i++;
        }

        $i = 0;

        foreach($request->markup_persen as $rowmarkuppersen){
            $array[$i]['markup_persen'] = $rowmarkuppersen;
            $i++;
        }


        $i = 0;

        foreach($getHarga as $rowHarga){
            $array[$i]['harga'] = str_replace(array('.', ','), '' ,$rowHarga);
            $i++;
            //($i <= count($rowHarga)) ? $i++ : $i = 0;
        }

        // echo "<pre>";
        //     print_r($array);
        // die();

        if($request->type_shipping_address == "main"){
            $shipping = DB::table('m_customer')
                        ->select('m_customer.*', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_customer.main_kelurahan')
                        ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                        ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                        ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                        ->where('m_customer.id', $request->sending_address)->first();
            $type_shipping_address = "main";

        }else{
            $shipping = DB::table('m_alamat_customer')
                            ->select('m_alamat_customer.*','m_alamat_customer.address as main_address', 'm_kota_kab.name as nama_kota','m_provinsi.name as nama_prov')
                            ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','m_alamat_customer.kelurahan')
                            ->leftjoin('m_kecamatan','m_kecamatan.id','m_kelurahan_desa.kecamatan')
                            ->leftjoin('m_kota_kab','m_kota_kab.id','m_kecamatan.kota_kab')
                            ->leftjoin('m_provinsi','m_provinsi.id','m_kota_kab.provinsi')
                            ->where('m_alamat_customer.id', $request->sending_address)->first();
            $type_shipping_address = "other";
        }
        //dd($shipping, $request->sending_address,$request->type_shipping_address);
        if($request->method_kirim == null || $request->method_kirim == ""){
            $all_biaya_kirim_id = 0;
            $all_biaya_kirim_biaya = 0;
        }else{
            $all_biaya_kirim = DB::table('m_biaya_kirim')->where('id',$request->method_kirim)->first();
            $all_biaya_kirim_id = $all_biaya_kirim->id;
            $all_biaya_kirim_biaya = $all_biaya_kirim->harga_biaya_kirim;
        }
        if($request->method_bayar == null || $request->method_bayar == ""){
            $metode_bayar_id = 0;
        }else{
            $metode_bayar = DB::table('m_metode_bayar')->where('id',$request->method_bayar)->first();
            $metode_bayar_id = $metode_bayar->id;
        }
        if($request->sales == null || $request->sales == ""){
            $sales = 3;
        }else{
            $sales = $request->sales;
        }

        // echo "<pre>";
        // print_r($array);
        // die();
        //
        // dd($request->all(),$request->cod,$request->total);


        DB::beginTransaction();
        try{

            // update t_transaksi
            $update = TSalesOrderModel::find($id);
            $update->atas_nama = $request->atas_nama;
            $update->company_code = $request->company_code;
            $update->sales = $sales;
            $update->sending_date = $sending_date;
            $update->so_date = $so_date;
            $update->type_atas_nama = 'main';
            $update->sending_address = $shipping->main_address.','.$shipping->nama_kota.','.$shipping->nama_prov;
            $update->type_sending = $type_shipping_address;
            $update->id_sending = $request->sending_address;
            $update->description = $request->description;
            $update->user_receive = $request->user_receive;
            $update->top_hari = $request->top_hari;
            $update->top_toleransi = $request->top_toleransi;
            $update->gudang = $request->gudang;
            $update->ppn = $request->ppn;
            $update->amount_ppn = $request->amount_ppn;
            $update->grand_total = str_replace(array('.', ','), '' , $request->grand_total);
            $update->cod = $request->cod;
            $update->metode_kirim = $all_biaya_kirim_id;
            $update->biaya_kirim = $all_biaya_kirim_biaya;
            // $update->metode_kirim = 0;
            // $update->biaya_kirim = 0;
            $update->metode_bayar = $metode_bayar_id;
            $update->diskon_header_potongan = $request->diskon_total_rp;
            $update->diskon_header_persen = $request->diskon_total_persen;
            $update->save();

            //delete-all-d-so from-so_code
            DSalesOrder::where('so_code',$request->invoice)->delete();

            //insert-new-detail-so
            for($n=0; $n<count($array); $n++){
                $insertNewDetailTransaksi = new DSalesOrder;
                $insertNewDetailTransaksi->so_code = $array[$n]['invoice_t'];
                $insertNewDetailTransaksi->produk = $array[$n]['produk_id'];
                $insertNewDetailTransaksi->qty = $array[$n]['qty'];
                // $insertNewDetailTransaksi->free_qty = $array[$n]['free_qty'];
                $insertNewDetailTransaksi->total = $array[$n]['total'];
                $insertNewDetailTransaksi->diskon_potongan = $array[$n]['potongan'];
                $insertNewDetailTransaksi->diskon_persen = $array[$n]['persen'];
                $insertNewDetailTransaksi->markup = $array[$n]['markup'];
                $insertNewDetailTransaksi->markup_persen = $array[$n]['markup_persen'];
                $insertNewDetailTransaksi->customer_price = $array[$n]['harga'];
                $insertNewDetailTransaksi->save();
            }

            DB::commit();

            $success = true;

        } catch (\Exception $e) {
            dd($e);
            $success = false;
            DB::rollback();
        }

        return redirect('admin/transaksi-sales-order');
    }

    public function delete($socode)
    {
        DB::beginTransaction();
        try {
            $cek_so = DB::table('t_sales_order')->where('so_code',$socode)->first();
            $headerSj = DB::table('t_sales_order')->where('so_code',$socode)->whereIn('status_aprove',['in process','pending','hold'])->delete();

            if($headerSj){
                if($cek_so->so_from == "marketplace"){
                    $cek_detail = DB::table("d_sales_order")->where('so_code', $socode)->get();
                    foreach ($cek_detail as $key => $value) {
                        
                        $jumlahStok = DB::table('m_stok_produk')
                        ->where('produk_id',$value->produk)
                        ->where('gudang',2)
                        ->sum('stok');

                        $produk = DB::table("m_produk")->where('id',$value->produk)->first();

                        $insertStokModel = new MStokProdukModel;
                        $insertStokModel->produk_id =  $value->produk;
                        $insertStokModel->produk_code =  $produk->code;
                        $insertStokModel->transaksi =  $socode;
                        $insertStokModel->tipe_transaksi   = 'Delete SO Marketplace '.$socode;
                        $insertStokModel->stok_awal   =  $jumlahStok;
                        $insertStokModel->gudang      =  2;
                        $insertStokModel->stok        =  $value->qty;
                        $insertStokModel->type        =  'in';
                        $insertStokModel->save();
                    }
                }
                DB::table('d_sales_order')->where('so_code',$socode)->delete();
                DB::commit();
                return redirect()->back()->with('message-success','data berhasil dihapus');

            }
            return redirect()->back()->with('message','data tidak bisa dihapus');
        }catch (Exception $e) {
            DB::rollback();
            dd($e);
        }
    }

    // public function addProduk(Request $request)
    // {
    //     // return dd($request->all());
    //     // die();
    //     $result = MProdukModel::find($request->id);
    //     $id_product = $request->id;
    //     $id_customer = $request->id_customer;
    //     $produk = $request->produk;
    //
    //     //ambil price_variant customer
    //     $dataCustomer= DB::table('m_customer')->where('id',$id_customer)->first();
    //     //ambil harga price_list
    //     $produkName = $result->name;
    //     $produkCode = $result->code;
    //     $main_price_data = DB::table('m_harga_produk')
    // 			->where('produk', $result->id)
    //             ->where('m_harga_produk.gh_code', $dataCustomer->gh_code)
    // 			->where('date_start', '<=' , date('Y-m-d'))
    // 			->where('date_end', '>=' , date('Y-m-d'))
    // 			->orderBy('created_at', 'desc')
    // 			->first();
    // 		if ($main_price_data !== null) {
    // 			$main_price = $main_price_data->price;
    // 		}else{
    // 			$main_price_data_last = DB::table('m_harga_produk')
    // 			->where('produk', $result->id)
    //             ->where('m_harga_produk.gh_code', $dataCustomer->gh_code)
    // 			->where('date_end', '<=' , date('Y-m-d'))
    // 			->orderBy('created_at', 'desc')
    // 			->orderBy('date_end', 'desc')
    // 			->first();
    //
    // 			if($main_price_data_last !== null){
    // 				$main_price = $main_price_data_last->price;
    // 			}else{
    // 				$main_price = 0;
    // 			}
    //         }
    //
    //     //cek array
    //     if( count($dataCustomer) > 0 ){
    //         //cek positive
    //         $main_price = $main_price + $dataCustomer->price_variant;
    //     }
    //
    //     //potongan diskon
    //         $potonganHarga = 0;
    //         $potonganPersen = 0;
    //
    //         if ($request->potonganHarga != null) {
    //             $potonganHarga = $request->potonganHarga;
    //         }
    //         if ($request->potonganPersen != null) {
    //             $potonganPersen = $request->potonganPersen;
    //         }
    //
    //         $hargaDasar = $main_price;
    //
    //         $hargaSetelahDiskon = $hargaDasar - $potonganHarga;
    //         $jmlDiskonPersen = ($hargaSetelahDiskon * $potonganPersen) / 100;
    //         $hargaSetelahDiskon = $hargaSetelahDiskon - $jmlDiskonPersen;
    //
    //     //set produk-price - with diskon
    //         // $produkPrice = $main_price;
    //         $produkPrice = $hargaSetelahDiskon;
    //
    //     $code_product = DB::table('m_produk')
    //             ->where('id', $id_product)
    //             ->first();
    //
    //     $data_customer = DB::table('m_customer')
    //             ->select('id','price_variant','gudang')
    //             ->where('id', $id_customer)
    //             ->first();
    //
    //     //ambil stok
    //     $gudang = $data_customer->gudang;
    //
    //     $stok = DB::table('m_stok_produk')
    //             ->where('m_stok_produk.produk_code', $code_product->code)
    //             ->where('m_stok_produk.gudang', $gudang)
    //             ->groupBy('m_stok_produk.produk_code')
    //             ->sum('stok');
    //     $lengthProduk = $request->lengthProduk;
    //
    //         $isi = "<tr>";
    //
    //             $isi .= "<input type='hidden' value='". $request->id ."'name='id_produkNew[".$request->length."]'>";
    //
    //             $isi .= "<td> <input type='text' disabled class='form-control input-sm' value='".$produkCode."'></td>";
    //
    //             $isi .= "<td> <input type='text' class='form-control input-sm' value='".$produkName."' name='produk[".$request->length."]'></td>";
    //
    //             $isi .= "<td> <input type='text' class='form-control input-sm' id='".$lengthProduk."_produkPrice' readonly value='".$produkPrice."' name='hargaProdukNew[".$request->length."]'></td>";
    //
    //             $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$stok."' name='stok[".$request->length."]'></td>";
    //
    //
    //             $isi .= "<td>
    //             <input type='number' min='0' max='".$stok."' id='".$lengthProduk."_jumlah1' class='form-control input-sm' onkeyup='hitungSub(".$lengthProduk.")' name='jumlahNew[".$request->length."]' value=''>
    //             </td>";
    //
    //             $isi .= "<td> <input type='text' class='form-control input-sm' readonly value='".$code_product->satuan_kemasan."'></td>";
    //
    //             $isi .= "<td> <input type='text' readonly class='form-control input-sm ".$lengthProduk."_subTotal1' value='' name='subTotalNew[".$lengthProduk."]' id='".$lengthProduk."_subTotal1'></td>";
    //
    //         $isi .= "</tr>";
    //
    //     return $isi;
    // }
    public function laporanSO()
    {
        $dataCustomer = DB::table('m_customer')
        ->join('t_sales_order', 'm_customer.id', '=', 't_sales_order.customer')
        ->select('m_customer.id as customer_id','name')
        ->groupBy('m_customer.id','m_customer.name')
        ->get();

        $dataBarang = DB::table('m_produk')
        ->select('id as barang_id','name')
        ->groupBy('id','name')
        ->get();

        return view('admin.transaksi.sales-order.laporan',compact('dataCustomer','dataBarang'));
    }

    public function getCustomerByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataCustomer = DB::table('m_customer')
        ->join('t_sales_order', 'm_customer.id', '=', 't_sales_order.customer')
        ->select('m_customer.id as customer_id','name','main_address')
        ->where('t_sales_order.so_date','>=',date('Y-m-d', strtotime($tglmulai)))
        ->where('t_sales_order.so_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
        ->groupBy('m_customer.id')
        ->get();
        return Response::json($dataCustomer);
    }

    public function getSOByCustomer($customerID)
    {
        $dataSO = DB::table('t_sales_order')
        ->where('customer',$customerID)
        ->orderBy('so_code')
        ->get();
        return Response::json($dataSO);
    }

    public function getBarangBySo($soId)
    {
        if ($soId == '0') {
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->groupBy('m_produk.id')
            ->get();
        }else{
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('d_sales_order.so_code',$soId)
            ->groupBy('m_produk.id')
            ->get();
        }

        return Response::json($dataBarang);
    }

    public function getBarangSOByCustomer($customer)
    {
        if ($customer == '0') {
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
            ->join('t_sales_order', 'd_sales_order.so_code', '=', 't_sales_order.so_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            //->where('t_sales_order.customer',$customer)
            ->groupBy('m_produk.id')
            ->get();
        }else{
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
            ->join('t_sales_order', 'd_sales_order.so_code', '=', 't_sales_order.so_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('t_sales_order.customer',$customer)
            ->groupBy('m_produk.id')
            ->get();
        }

        return Response::json($dataBarang);
    }

    public function getBarangSOSJBySo($soId)
    {
        if ($soId == '0') {
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->groupBy('m_produk.id')
            ->get();
        }else{
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('d_sales_order.so_code',$soId)
            ->groupBy('m_produk.id')
            ->get();
        }

        return Response::json($dataBarang);
    }

    public function getBarangSOSJByCustomer($customer)
    {
        if ($customer == '0') {
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
            ->join('t_sales_order', 'd_sales_order.so_code', '=', 't_sales_order.so_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            //->where('t_sales_order.customer',$customer)
            ->groupBy('m_produk.id')
            ->get();
        }else{
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
            ->join('t_sales_order', 'd_sales_order.so_code', '=', 't_sales_order.so_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('t_sales_order.customer',$customer)
            ->groupBy('m_produk.id')
            ->get();
        }

        return Response::json($dataBarang);
    }

    public function laporanSOSJ()
    {
        $dataCustomer = DB::table('m_customer')
        ->join('t_sales_order', 'm_customer.id', '=', 't_sales_order.customer')
        ->select('m_customer.id as customer_id','name')
        //->where
        ->groupBy('m_customer.id','m_customer.name')
        ->get();

        $dataBarang = DB::table('m_produk')
        ->rightjoin('d_sales_order', 'd_sales_order.produk', '=', 'm_produk.id')
        ->join('t_sales_order', 'd_sales_order.so_code', '=', 't_sales_order.so_code')
        ->select('m_produk.id as barang_id','m_produk.name')
        //->where('t_sales_order.customer',$customer)
        ->groupBy('m_produk.id','m_produk.name')
        ->get();

        return view('admin.transaksi.sales-order.laporansosj',compact('dataCustomer','dataBarang'));
    }

    public function getSisaCreditLimit($customerID)
    {
        $dataCustomer = DB::table('m_customer')
        ->join('m_gudang', 'm_gudang.id', '=', 'm_customer.gudang')
        ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
        ->select('m_customer.*','m_gudang.name as gudang','m_wilayah_sales.name as wilayah')
        ->where('m_customer.id',$customerID)
        ->first();

        // return Response::json($dataCustomer);
        // die();
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
        ->where('t_sales_order.customer', $customerID)
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
        // foreach ($data_credit_customer as $raw_data) {
        //     $qty = $raw_data->qty;
        //     $sj_qty = $raw_data->sj_qty;
        //     $sisa_qty = $qty - $sj_qty;
        //     $total = $raw_data->total;
        //
        //     $total_credit = ($total / $qty) * $sisa_qty;
        //
        //     $credit_customer = $credit_customer + $total_credit;
        //
        //     $raw_data->grand_total
        //     $credit_customer = $credit_customer
        // }
        // dd($credit_customer);
        $piutang = DB::table('t_faktur')
        ->where('customer', $customerID)
        ->where('status_payment', 'unpaid')
        ->sum('total');

        $piutang_dibayar = DB::table('t_pembayaran')
        ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
        ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
        ->where('t_pembayaran.customer', $customerID)
        ->where('t_faktur.status_payment', 'unpaid')
        ->where('t_pembayaran.status', 'approved')
        ->sum('d_pembayaran.total');

        $oldest_piutang = DB::table('t_faktur')
        ->where('customer', $customerID)
        ->where('status_payment', 'unpaid')
        ->orderBy('created_at', 'asc')
        ->first();

        if ($oldest_piutang != null ) {
            $tanggalPiutangTerlama = date('d-m-Y',strtotime($oldest_piutang->created_at));
            $totalPiutangTerlama = ($oldest_piutang->jumlah_yg_dibayarkan == 0) ? $oldest_piutang->total : $oldest_piutang->total - $oldest_piutang->jumlah_yg_dibayarkan;
        }else{
            $tanggalPiutangTerlama = '';
            $totalPiutangTerlama = 0;
        }

        //total piutang
        $piutang = $piutang - $piutang_dibayar;

        $sisaCredit = $credit_limit - $credit_customer - $piutang;
        //$sisaCredit = $credit_customer;
        $data = [
            'piutang'               => $piutang,
            'wilayah'               => $wilayah,
            'gudang'                => $gudang,
            'sisacredit'            => $sisaCredit,
            'kodeCustomer'          => $dataCustomer->code,
            'creditlimit'           => $credit_limit,
            'totalPiutangTerlama'   => $totalPiutangTerlama,
            'tanggalPiutangTerlama' => $tanggalPiutangTerlama,
            'topCustomer'           => $dataCustomer->credit_limit_days,
        ];
        // return $data;
        return Response::json($data);
    }

    public function sendApproval()
    {
        $dataSales = DB::table('t_sales_order')
        ->select('m_user.name as user_name','m_customer.name as customer_name','t_sales_order.*')
        ->join('m_user','m_user.id','t_sales_order.sales')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->where('t_sales_order.status_aprove', '=', 'in process')
        ->orderBy('t_sales_order.so_code','desc')
        ->get();
        // dd($dataSales);
        return view('admin.transaksi.sales-order.send-approve.index',compact('dataSales'));
    }

    public function copySalesOrder($soCode)
    {
        //data-so
        $copyDataSO = DB::table('t_sales_order')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->select('m_customer.id as id_customer','m_customer.code','m_customer.gudang','m_customer.wilayah_sales','m_customer.gudang as gudang_customer','m_customer.credit_limit','m_customer.head_office','m_customer.gh_code','m_customer.price_variant','t_sales_order.*')
        ->where('so_code',$soCode)
        ->first();

        //ambil harga price_list
        // $produkName = $result->name;
        // $produkCode = $result->code;


        $barangCopyS0 = DB::table('d_sales_order')
        ->join('m_produk','m_produk.id','=','d_sales_order.produk')
        ->select('*','m_produk.id as produk_id')
        ->where('so_code',$soCode)
        ->get();
        $totalHarga = 0; //varible for totalHarga
        foreach ($barangCopyS0 as $produk) {
            $main_price_data = DB::table('m_harga_produk')
            ->where('produk', $produk->produk_id)
            ->where('m_harga_produk.gh_code', $copyDataSO->gh_code)
            ->where('date_start', '<=' , date('Y-m-d'))
            ->where('date_end', '>=' , date('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();
            if ($main_price_data !== null) {
                $main_price = $main_price_data->price;
            }else{
                $main_price_data_last = DB::table('m_harga_produk')
                ->where('m_harga_produk.gh_code', $copyDataSO->gh_code)
                ->where('produk', $produk->produk_id)
                ->where('date_end', '<=' , date('Y-m-d'))
                ->orderBy('created_at', 'desc')
                ->orderBy('date_end', 'desc')
                ->first();

                if($main_price_data_last !== null){
                    $main_price = $main_price_data_last->price;
                }else{
                    $main_price = 0;
                }
            }

            //cek price_variant customer
            if($copyDataSO->price_variant > 0){
                $main_price = $main_price + $copyDataSO->price_variant;
            }else{
                $main_price = $main_price - $copyDataSO->price_variant;
            }
            //get-stok-produk
            $getStokProduk = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $produk->code)
            ->where('m_stok_produk.gudang', $copyDataSO->gudang)
            ->groupBy('m_stok_produk.produk_code')
            ->sum('stok');

            $totalHarga = $totalHarga + $produk->total;
            //add new array
            $produk->gh_code = $copyDataSO->gh_code;
            $produk->stok = $getStokProduk;
            $produk->produkPrice = $main_price;

        }
        // dd($barangCopyS0,$totalHarga);
        //data semua Customer
        $getCustomer = MCustomerModel::orderBy('name')->where('status',true)->get();

        //Generate new code sales-order
        $dataDate =date("ym");
        $getLastCode = DB::table('t_sales_order')->select('id')->orderBy('id', 'desc')->pluck('id')->first();
        $getLastCode = $getLastCode +1;
        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}
        //newInvoiceGenerate
        $setInvoice = 'SOWA'.$dataDate.$nol.$getLastCode;

        //sales
        $dataSales = MWilayahPembagianSalesModel::join('m_user','m_user.id','m_wilayah_pembagian_sales.sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_wilayah_pembagian_sales.wilayah_sales',$copyDataSO->wilayah_sales)
        ->orderBy('m_wilayah_pembagian_sales.id','ASC')
        ->first();

        //sales_counter
        $query = MWilayahPembagianSalesModel::join('m_user','m_user.id','m_wilayah_pembagian_sales.sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_wilayah_pembagian_sales.wilayah_sales',$copyDataSO->wilayah_sales)
        ->orderBy('m_wilayah_pembagian_sales.id','ASC')
        ->take(1);

        //tambah sales Counter
        ///
        $salesCounter = $query->get();

        $dataCover = DB::table('m_cover_sales')
        ->join('m_user','m_user.id','=','m_cover_sales.cover_sales')
        ->select('m_user.id as sales_id','m_user.name as sales_name')
        ->where('m_cover_sales.sales',$salesCounter[0]->sales_id)
        ->get();

        $salesCounter = array_merge($salesCounter->toArray(),$dataCover->toArray());
        // $salesCounter1 = $salesCounter->merge($dataCover);
        // $salesCounter = Response::json($salesCounter);

        //$salesCounter =  array_merge( (array)$salesCounter, (array)$dataCover );
        foreach ($salesCounter as $key => $raw_data) {
            if ($key == 1) {
                array_push($salesCounter, [ "sales_id" => $raw_data->sales_id, "sales_name" => $raw_data->sales_name]);
                unset($salesCounter[$key]);
            }
        }

        $salesCounter = array_values($salesCounter);
        $salesCounter = (object)$salesCounter;

        //wilayah
        $wilayah = DB::table('m_wilayah_sales')->where('id',$copyDataSO->wilayah_sales)->first();

        $gudangAll = DB::table('m_gudang')->orderBy('id','DESC')->get();
        //data-produk
        $dataProduk = DB::table('m_stok_produk')
        ->join('m_produk','m_produk.code','=','m_stok_produk.produk_code')
        ->select('m_produk.id','m_produk.code','m_produk.name',DB::raw('SUM(m_stok_produk.stok) as stok'))
        ->groupBy('m_produk.id','m_produk.code','m_produk.name')
        ->where('gudang',$copyDataSO->gudang)
       ->where(function ($query) {
            $query->where('m_stok_produk.stok','!=', 0)
            ->orWhere('m_stok_produk.balance','!=',0);
        })
        ->get();
        // dd($dataProduk);
        // detail-customer
        $data_credit_customer = DB::table('t_sales_order')
        //->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
        ->where('t_sales_order.customer', $copyDataSO->id_customer)
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
        ->where('customer', $copyDataSO->id_customer)
        ->where('status_payment', 'unpaid')
        ->sum('total');

        $piutang_dibayar = DB::table('t_pembayaran')
        ->join("d_pembayaran", "d_pembayaran.pembayaran_code", "=" , "t_pembayaran.pembayaran_code")
        ->join("t_faktur", "t_faktur.faktur_code", "=" , "d_pembayaran.faktur_code")
        ->where('t_pembayaran.customer', $copyDataSO->id_customer)
        ->where('t_faktur.status_payment', 'unpaid')
        ->where('t_pembayaran.status', 'approved')
        ->sum('d_pembayaran.total');

        $oldest_piutang = DB::table('t_faktur')
        ->where('customer', $copyDataSO->id_customer)
        ->where('status_payment', 'unpaid')
        ->orderBy('created_at', 'asc')
        ->first();

        if ($oldest_piutang != null ) {
            $tanggalPiutangTerlama = date('d-m-Y',strtotime($oldest_piutang->created_at));
            $totalPiutangTerlama = ($oldest_piutang->jumlah_yg_dibayarkan == 0) ? $oldest_piutang->total : $oldest_piutang->total - $oldest_piutang->jumlah_yg_dibayarkan;
        }else{
            $tanggalPiutangTerlama = '';
            $totalPiutangTerlama = 0;
        }

        //total piutang
        $piutang = $piutang - $piutang_dibayar;

        $sisaCredit = $copyDataSO->credit_limit - $credit_customer - $piutang;

        $detailCustomer = [
            'piutang'               => $piutang,
            'wilayah'               => $wilayah->name,
            'gudang'                => $copyDataSO->gudang_customer,
            'sisacredit'            => $sisaCredit,
            'kodeCustomer'          => $copyDataSO->code,
            'creditlimit'           => $copyDataSO->credit_limit,
            'totalPiutangTerlama'   => $totalPiutangTerlama,
            'tanggalPiutangTerlama' => $tanggalPiutangTerlama,
            'topCustomer'           => $copyDataSO->credit_limit_days,
        ];

        //faktur
        $data_tail = [];
        $data_head = DB::table("m_customer")
        ->select('id','name')
        ->where('id', $copyDataSO->id_customer)
        ->orwhere('head_office', $copyDataSO->id_customer)
        ->get();

        if ($copyDataSO->head_office != null) {
            $data_tail = DB::table("m_customer")
            ->select('id','name')
            ->where('id', $copyDataSO->head_office)
            ->orwhere('head_office', $copyDataSO->head_office)
            ->get();

            foreach ($data_tail as $i=>$raw) {
                if ($raw->id == $copyDataSO->id_customer) {
                    unset($data_tail[$i]);
                }
            }

            $data_tail = $data_tail->toArray();
        }

        $dataFaktur = array_merge($data_head->toArray(), $data_tail);

        //alamat-lainnya
        $dataAlamat = [];
        //ambil alamat asli customer
        $mainCustomer = DB::table('m_customer')->select(DB::raw("CONCAT(m_customer.main_address,' - ',m_kelurahan_desa.name,' - ',m_kecamatan.name,' - ',m_kota_kab.type,' ',m_kota_kab.name) as address"))
        ->join('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
        ->join('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->where('m_customer.id',$copyDataSO->id_customer)
        ->get();

        //mencari alamat lainnya customer di m_alamat_customer
        $otherAddress = DB::table('m_alamat_customer')->select(DB::raw("CONCAT(m_alamat_customer.address,' - ',m_kelurahan_desa.name,' - ',m_kecamatan.name,' - ',m_kota_kab.type,' ',m_kota_kab.name) as address"))
        ->join('m_kelurahan_desa','m_kelurahan_desa.id','=','m_alamat_customer.kelurahan')
        ->join('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->where('m_alamat_customer.customer',$copyDataSO->id_customer)
        ->get();
        //merge array
        $dataAlamat = array_merge($mainCustomer->toArray(),$otherAddress->toArray());


        // dd($copyDataSO,$setInvoice,$dataSales,$dataProduk,$detailCustomer,$dataFaktur,$dataAlamat,$gudangAll,$barangCopyS0,$totalHarga,$salesCounter);

        return view('admin.transaksi.sales-order.copy-so',compact('copyDataSO','setInvoice','dataSales','dataProduk','detailCustomer','dataFaktur','dataAlamat','getCustomer','gudangAll','barangCopyS0','totalHarga','salesCounter'));
    }

    public function cancelSO($id)
    {
        $dataSO = TSalesOrderModel::findOrFail($id);
        $reason = MReasonModel::orderBy('id','DESC')->get();

        return view('admin.transaksi.sales-order.cancel',compact('dataSO','reason'));
    }

    public function cancelSOPost(Request $request)
    {
        // dd($request->all(),auth()->user()->id);
        DB::beginTransaction();
        try {
            DB::table('t_sales_order')->where('so_code',$request->so_code)->update([
                'cancel_reason' => $request->cancel_reason,
                'cancel_description' => $request->cancel_description,
                'user_cancel' => auth()->user()->id,
                'status_aprove' => 'cancel',
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi-sales-order');
    }

    protected function setCodeSO()
    {
        $dataDate =date("ym");

        $getLastCode = DB::table('t_sales_order')
        ->select('id')
        ->orderBy('id', 'desc')
        ->pluck('id')
        ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return $setInvoice = 'SO'.$dataDate.$nol.$getLastCode;
    }

    public function apiDataSalesOrder()
    {
        $dataSalesOrder = DB::table('t_sales_order')
        ->select('m_user.name as user_name','m_customer.name as customer_name','t_sales_order.*')
        ->join('m_user','m_user.id','t_sales_order.sales')
        ->join('m_customer','m_customer.id','t_sales_order.customer')
        ->orderBy('t_sales_order.so_code','desc')
        ->get();

        foreach ($dataSalesOrder as $dataSO) {
            $sj = true;
            $cekSj = TSuratJalanModel::where('so_code',$dataSO->so_code)->first();
            // dd($cekSj);
            if (count($cekSj) > 0 ) {
                $sj = false; // jika ada false
            }
            $dataSO->sj = $sj;
        }
        return Datatables::of($dataSalesOrder)
        ->editColumn('status_aprove', function($data){
            if ($data->status_aprove == 'in process') return '<span class="label label-primary">Aktif</span></td>';
            if ($data->status_aprove == 'in approval') return '<span class="label label-warning">Pasif</span>';
            if ($data->status_aprove == 'approved') return '<span class="label label-warning">Pasif</span>';
            if ($data->status_aprove == 'reject') return '<span class="label label-warning">Pasif</span>';
            if ($data->status_aprove == 'cancel') return '<span class="label label-warning">Pasif</span>';
        })
        ->addIndexColumn()
        ->rawColumns(['code','action','status'])
        ->make(true);
    }

    public function apiSo($type = null)
    {
        $query = DB::table('t_sales_order')
                ->select('m_user.name as user_name','m_customer.name as customer_name','t_sales_order.*')
                ->leftjoin('m_user','m_user.id','t_sales_order.sales')
                ->leftjoin('m_customer','m_customer.id','t_sales_order.customer')
                ->orderBy('t_sales_order.so_code','desc');

        if(strtolower($type) == 'marketplace'){
            $query->where('so_from', $type);
        }else{
            $query->where('so_from', '!=', $type);
        }

        $dataSales = $query->get();

        foreach ($dataSales as $dataSO) {
            $sj = true;
            $cekSj = TSuratJalanModel::where('so_code',$dataSO->so_code)->get();
            if (count($cekSj) > 0 ) {
                $sj = false; // jika ada false
            }
            $dataSO->sj = $sj;
        }

        $roleUser = \DB::table('m_role')
                ->where('id',Auth::user()->role)
                ->first();

        return Datatables::of($dataSales)
            ->addColumn('action', function ($dataSales) use ($roleUser) {
                if(in_array($dataSales->so_from, ['web','apps'])){
                    if(  $dataSales->status_aprove == 'in process'){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/transaksi-sales-order/'.$dataSales->so_code.'/update') .'" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ubah '. $dataSales->so_code .'"> <span class="fa fa-edit"></span> </a>'.'&nbsp;'.
                                '<a href="'. url('admin/transaksi-so/'.$dataSales->so_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus '. $dataSales->so_code .'"> <span class="fa fa-trash"></span> </a>'.'&nbsp;'.
                                '<a href="'. url('/admin/transaksi-sales-order/in-approve/'.$dataSales->so_code).'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Kirim Persetujuan '. $dataSales->so_code .'"> <i class="fa fa-paper-plane"></i> </a>'.'&nbsp;'.
                                // '<a href="'. url('admin/transaksi-sales-order/copy/'.$dataSales->so_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                            '</td>'.
                        '</tr>'.
                        '</table>';
                    }elseif(  $dataSales->status_aprove == 'in approval'){
                        if(  $roleUser->status_approval == 1){
                            return '<table id="tabel-in-opsi">'.
                            '<tr>'.
                                '<td>'.
                                    '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                    '<a href="'.url('/admin/transaksi-sales-order/approve/'.$dataSales->so_code).'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Setujui '. $dataSales->so_code .'"> <i class="fa fa-check"></i> </a>'.'&nbsp;'.
                                    '<a href="'.url('/admin/transaksi-sales-order/reject/'.$dataSales->so_code).'" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Tolak '. $dataSales->so_code .'"> <i class="fa fa-minus-circle" aria-hidden="true"></i></a>'.'&nbsp;'.
                                    // '<a href="'. url('admin/transaksi-sales-order/copy/'.$dataSales->so_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                                '</td>'.
                            '</tr>'.
                            '</table>';
                        }

                    }elseif($dataSales->status_aprove == 'approved' && $dataSales->sj == true){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/sales-order/cancel/'.$dataSales->id) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $dataSales->so_code .'"><span class="fa fa-times"></span></ra>'.'&nbsp;'.
                                // '<a href="'. url('admin/transaksi-sales-order/copy/'.$dataSales->so_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'. '</tr>'.
                            '</td>'.
                        '</tr>'.
                        '</table>';
                    }elseif($dataSales->status_aprove == 'pending'){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/transaksi-so/'.$dataSales->so_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus '. $dataSales->so_code .'"> <span class="fa fa-trash"></span> </a>'.'&nbsp;'.
                            '</td>'.
                        '</tr>'.
                        '</table>';
                    }else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                // '<a href="'. url('admin/transaksi-sales-order/copy/'.$dataSales->so_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'</tr>'.
                            '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                }elseif($dataSales->so_from == 'marketplace'){
                    if(  $dataSales->status_aprove == 'hold'){
                        return '<table id="tabel-in-opsi">'.
                            '<tr>'.
                                '<td>'.
                                    '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.

                                    '<a href="javascript::void(0)" class="btn btn-primary btn-sm btn-open" data-toggle="tooltip" data-placement="top" title="Tambah Ongkir '.$dataSales->so_code .'" data-so_code="'.$dataSales->so_code .'" data-customer="'.$dataSales->customer_name .'" data-address="'.$dataSales->sending_address .'"><span class="fa fa-external-link"></span></a>'.'&nbsp;'.
                                '</td>'.
                            '</tr>'.
                        '</table>';
                    }elseif(  $dataSales->status_aprove == 'in process'){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                // '<a href="'. url('admin/transaksi-sales-order/'.$dataSales->so_code.'/update') .'" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ubah '. $dataSales->so_code .'"> <span class="fa fa-edit"></span> </a>'.'&nbsp;'.
                                '<a href="'. url('admin/transaksi-so/'.$dataSales->so_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus '. $dataSales->so_code .'"> <span class="fa fa-trash"></span> </a>'.'&nbsp;'.
                                '<a href="'. url('/admin/transaksi-sales-order/in-approve/'.$dataSales->so_code).'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Kirim Persetujuan '. $dataSales->so_code .'"> <i class="fa fa-paper-plane"></i> </a>'.'&nbsp;'.
                                // '<a href="'. url('admin/transaksi-sales-order/copy/'.$dataSales->so_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                            '</td>'.
                        '</tr>'.
                        '</table>';
                    }elseif(  $dataSales->status_aprove == 'in approval'){
                        if(  $roleUser->status_approval == 1){
                            return '<table id="tabel-in-opsi">'.
                            '<tr>'.
                                '<td>'.
                                    '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                    '<a href="'.url('/admin/transaksi-sales-order/approve/'.$dataSales->so_code).'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Setujui '. $dataSales->so_code .'"> <i class="fa fa-check"></i> </a>'.'&nbsp;'.
                                    '<a href="'.url('/admin/transaksi-sales-order/reject/'.$dataSales->so_code).'" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Tolak '. $dataSales->so_code .'"> <i class="fa fa-minus-circle" aria-hidden="true"></i></a>'.'&nbsp;'.
                                    // '<a href="'. url('admin/transaksi-sales-order/copy/'.$dataSales->so_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                                '</td>'.
                            '</tr>'.
                            '</table>';
                        }

                    }elseif($dataSales->status_aprove == 'approved' && $dataSales->sj == true){
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/sales-order/cancel/'.$dataSales->id) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $dataSales->so_code .'"><span class="fa fa-times"></span></ra>'.'&nbsp;'.
                                // '<a href="'. url('admin/transaksi-sales-order/copy/'.$dataSales->so_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'. '</tr>'.
                            '</td>'.
                        '</tr>'.
                        '</table>';
                    }elseif($dataSales->status_aprove == 'pending'){
                        $cancel = '';

                        if(Carbon::parse($dataSales->transfer_deadline)->lte(date('now')) ){
                            $cancel = '<a href="" onclick="return confirm('."'Apakah Anda Yakin Membatalkan ?'".')" class="btn btn-danger btn-sm btn-close" data-toggle="tooltip" data-placement="top" title="Cancel '. $dataSales->so_code .'" data-so="'. $dataSales->so_code .'"> <span class="fa fa-minus-circle"></span> </a>'.'&nbsp;';
                        }

                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/transaksi-so/'.$dataSales->so_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus '. $dataSales->so_code .'"> <span class="fa fa-trash"></span> </a>'.'&nbsp;'.$cancel.
                            '</td>'.
                        '</tr>'.
                        '</table>';
                    }else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-so/'.$dataSales->so_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$dataSales->so_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                // '<a href="'. url('admin/transaksi-sales-order/copy/'.$dataSales->so_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'</tr>'.
                            '</td>'.
                        '</tr>'.
                        '</table>';
                    }
                }
            })
            ->editColumn('user_name', function($self){
                return ($self->user_name == null ? '-' : $self->user_name);
            })
            ->editColumn('transfer_deadline', function($dataSales){
                return ($dataSales->transfer_deadline != null)
                    ? Carbon::parse($dataSales->transfer_deadline)->format('d-m-Y H:i:s')
                    : '';
            })
            ->editColumn('code', function($dataSales){
                return '<a href="'. url('admin/transaksi-sales-order/detail/'.$dataSales->so_code) .'">'.$dataSales->so_code.'</a> ';
            })
            ->editColumn('so_date', function($dataSales){
                return date('d-m-Y',strtotime($dataSales->so_date));
            })
            ->editColumn('so_from', function($dataSales){
                if( $dataSales->so_from == 'web' ){
                    return '<i class="fa fa-desktop" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Webmin"></i></td>';
                }elseif($dataSales->so_from == 'marketplace'){
                    return '<i class="fa fa-dropbox" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Market Place"></i></td>';
                }
                // elseif($dataSales->so_from == 'apps'){
                //     return '<i class="fa fa-mobile" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="android"></i></td>';
                // }

            })
            ->editColumn('status_aprove', function($dataSales){
                if( $dataSales->status_aprove == 'in process' ){
                    return '<span class="label label-default">in process</span>';}
                elseif(  $dataSales->status_aprove == 'in approval'){
                    return '<span class="label label-info">in approval</span>';}
                elseif ($dataSales->status_aprove == 'approved'){
                    return '<span class="label label-success">approved</span>';}
                elseif ($dataSales->status_aprove == 'reject'){
                    return '<span class="label label-warning">reject</span>';}
                elseif ($dataSales->status_aprove == 'cancel'){
                    return '<span class="label label-warning">cancel</span>';}
                elseif ($dataSales->status_aprove == 'pending'){
                    return '<span class="label label-default">pending</span>';}
                elseif ($dataSales->status_aprove == 'hold'){
                    return '<span class="label label-default">hold</span>';}
                else{
                    return '<span class="label label-danger">close</span>';}
                })
            ->addIndexColumn()
            ->rawColumns(['code','action','status_aprove','so_from','so_date'])
            ->make(true);
    }
    public function escape_like($string)
    {
        $search = array('%', '_','"');
        $replace   = array('\%', '\_','\"');
        return str_replace($search, $replace, $string);
    }
}
