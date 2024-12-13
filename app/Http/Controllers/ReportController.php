<?php

namespace App\Http\Controllers;

use DB;
use PDF;
use Excel;
use Illuminate\Http\Request;
use App\Models\MStokProdukModel;
use App\Models\MCustomerModel;
use App\Models\MSupplierModel;
use App\Models\MRoleModel;
use App\Models\MHargaProdukModel;
use App\Models\MPrintLogModel;



class ReportController extends Controller
{
    //ReportController
    public function salesOrder($soCode)
    {
        $detailSalesOrder = DB::table('d_sales_order')
        ->join('t_sales_order','t_sales_order.so_code','=','d_sales_order.so_code')
        ->leftjoin('m_user','m_user.id','=','t_sales_order.sales')
        ->join('m_customer','m_customer.id','=','t_sales_order.customer')
        ->join('m_produk','m_produk.id','=','d_sales_order.produk')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('d_sales_order.*','m_produk.name as produk','m_produk.code as produk_code','m_produk.id as produkID','m_satuan_unit.code as code_unit',
        't_sales_order.so_date','t_sales_order.id as id_transaksi','t_sales_order.so_code','t_sales_order.user_input','t_sales_order.user_receive','t_sales_order.top_hari','t_sales_order.top_toleransi',
        't_sales_order.diskon_header_potongan','t_sales_order.diskon_header_persen','t_sales_order.grand_total',
        'm_user.name as sales','m_customer.name as customer','t_sales_order.ppn','t_sales_order.amount_ppn')
        ->where('d_sales_order.so_code','=',$soCode)
        ->get();

        $subTotal1 = DB::table('d_sales_order')->where('so_code', '=', $soCode)->sum('total');
        $dataTransaksi = DB::table('t_sales_order')
        ->select('t_sales_order.status_aprove as status','t_sales_order.so_code','t_sales_order.description as descript','t_sales_order.so_date','t_sales_order.sending_address','t_sales_order.top_hari',
        't_sales_order.top_toleransi',
        'm_customer.name as customer_name','m_user.name as sales_name','m_customer.code as code_customer','m_customer.main_office_phone_1','m_customer.name as customer','m_customer.credit_limit_days')
        ->join('m_customer','m_customer.id','=','t_sales_order.customer')
        ->leftjoin('m_user','m_user.id','=','t_sales_order.sales')
        ->where('so_code', '=', $soCode)->first();

        $userEntry = DB::table('t_sales_order')
        ->join('m_user','m_user.id','=','t_sales_order.user_input')
        ->where('so_code', '=', $soCode)->first();

        $userOrder = DB::table('t_sales_order')
        ->join('m_user','m_user.id','=','t_sales_order.user_receive')
        ->where('so_code', '=', $soCode)->first();

        MPrintLogModel::create([
            'code' => $soCode,
            'user' => auth()->user()->id,
            'type' => 'so',
        ]);

        $company = DB::table('m_company_profile')->first();

        //dd($detailSalesOrder);

        $pdf = PDF::loadview('admin.report.sales-order',['company' => $company,'detailSalesOrder' => $detailSalesOrder, 'subTotal1' => $subTotal1, 'dataTransaksi' => $dataTransaksi,'userEntry' => $userEntry,'userOrder' => $userOrder]);
        $paper = array(0,0,684,396);
        // $customPaper = array(0,0,21.84,13.97);
        // $customPaper = array(0,0,21.84,13.97);
        // //$pdf->setPaper([0, 0, 9.5, 5.5], 'landscape');
        // $pdf->setPaper($customPaper,'landscape');
        // //$pdf->setPaper('A4', 'landscape');
        // $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
        // $pdf->getCanvas()->page_text(72, 18, "Header: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
        return $pdf->setPaper($paper,'potrait')->stream();
        // return $pdf->download('sales-order.pdf');
        // return view('admin.report.sales-order')->with(['detailSalesOrder' => $detailSalesOrder, 'subTotal1' => $subTotal1, 'dataTransaksi' => $dataTransaksi,'userEntry' => $userEntry,'userOrder' => $userOrder]);
    }

    public function purchaseOrder($poCode)
    {

        $header = DB::table('t_purchase_order')
        ->join('m_supplier','t_purchase_order.supplier','m_supplier.id')
        ->join('m_user','t_purchase_order.user_input','m_user.id')
        ->select('*','m_supplier.name as supplier','m_user.name as user_input','t_purchase_order.status_aprove')
        ->where('t_purchase_order.po_code',$poCode)
        ->first();
        // dd($header);

        $detail = DB::table('d_purchase_order')
        ->join('m_produk','d_purchase_order.produk','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.*','d_purchase_order.*','m_satuan_unit.code as code_unit')
        ->where('po_code',$poCode)
        ->get();

        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.purchase-order',['company' => $company,'header'=>$header,'detail'=>$detail]);
        $customPaper = array(0,0,21.84,13.97);
        //$pdf->setPaper($customPaper);
        // $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
    }


    public function stokAdjustment($taCode)
    {
        $header = DB::table('t_adjusment')
        ->join('m_gudang','m_gudang.id','t_adjusment.gudang')
        ->join('m_user','m_user.id','t_adjusment.user_input')
        ->select('t_adjusment.*','m_gudang.name as gudangname','m_user.name as user')
        ->where('ta_code',$taCode)
        ->first();

        $detail = DB::table('d_adjusment')
        ->join('m_produk','m_produk.id','d_adjusment.produk')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('d_adjusment.*','m_produk.id as produk_id','m_produk.name as produk_name','m_satuan_unit.code as code_unit')
        ->where('d_adjusment.ta_code',$taCode)
        ->get();

        $pdf = PDF::loadview('admin.report.stok-adjustment',['header'=>$header,'detail'=>$detail]);
        $customPaper = array(0,0,21.84,13.97);
        //$pdf->setPaper($customPaper);
        // $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
    }

    public function purchaseDelivery($poCode)
    {
        $header = DB::table('t_surat_jalan_masuk')
        ->join('m_gudang','t_surat_jalan_masuk.gudang','m_gudang.id')
        ->join('m_supplier','t_surat_jalan_masuk.supplier','m_supplier.id')
        ->join('m_user','t_surat_jalan_masuk.user_input','m_user.id')
        ->select('*','m_supplier.name as supplier','m_user.name as user_input','m_gudang.name as gudang')
        ->where('t_surat_jalan_masuk.sj_masuk_code',$poCode)
        ->first();

        $detail = DB::table('d_surat_jalan_masuk')
        ->join('m_produk','d_surat_jalan_masuk.produk_id','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.*','d_surat_jalan_masuk.*','m_satuan_unit.code as code_unit')
        ->where('sj_masuk_code',$poCode)
        ->get();

        //dd($header);

        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.purchase-delivery',['company' => $company,'header'=>$header,'detail'=>$detail]);
        $customPaper = array(0,0,21.84,13.97);
        //$pdf->setPaper($customPaper);
        // $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
    }

    public function assetPo($poCode)
    {
        $header = DB::table('t_fixed_asset_po')
        ->join('m_supplier','t_fixed_asset_po.supplier','m_supplier.id')
        ->join('m_user','t_fixed_asset_po.user_input','m_user.id')
        ->select('*','m_supplier.name as supplier','m_user.name as user_input','t_fixed_asset_po.status_aprove')
        ->where('t_fixed_asset_po.po_code',$poCode)
        ->first();

        $detail = DB::table('d_fixed_asset_po')
        ->join('m_produk','d_fixed_asset_po.produk','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.*','d_fixed_asset_po.*','m_satuan_unit.code as code_unit')
        ->where('po_code',$poCode)
        ->get();

        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.asset-order',['company' => $company,'header'=>$header,'detail'=>$detail]);
        $customPaper = array(0,0,21.84,13.97);
        //$pdf->setPaper($customPaper);
        // $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
    }


    public function assetPd($poCode)
    {
        $header = DB::table('t_fixed_asset_pd')
        ->join('m_gudang','t_fixed_asset_pd.gudang','m_gudang.id')
        ->join('m_supplier','t_fixed_asset_pd.supplier','m_supplier.id')
        ->join('m_user','t_fixed_asset_pd.user_input','m_user.id')
        ->select('*','m_supplier.name as supplier','m_user.name as user_input','m_gudang.name as gudang','t_fixed_asset_pd.status')
        ->where('t_fixed_asset_pd.sj_masuk_code',$poCode)
        ->first();

        $detail = DB::table('d_fixed_asset_pd')
        ->join('m_produk','d_fixed_asset_pd.produk_id','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.*','d_fixed_asset_pd.*','m_satuan_unit.code as code_unit')
        ->where('sj_masuk_code',$poCode)
        ->get();



        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.asset-receipt',['company' => $company,'header'=>$header,'detail'=>$detail]);
        $customPaper = array(0,0,21.84,13.97);
        //$pdf->setPaper($customPaper);

        // $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
    }

    public function assetConfirmation($poCode)
    {
      $header = DB::table('t_asset')
      ->join('m_supplier','t_asset.supplier','m_supplier.id')
      ->join('m_produk','t_asset.barang','m_produk.id')
      ->join('m_user','t_asset.user_input','m_user.id')
      ->select('*','m_supplier.name as supplier','m_user.name as user_input','t_asset.status','m_produk.*')
      ->where('t_asset.asset_code',$poCode)
      ->first();

      $detail = DB::table('d_asset')
      ->select('d_asset.*')
      ->where('asset_code',$poCode)
      ->get();

      $company = DB::table('m_company_profile')->first();

      $pdf = PDF::loadview('admin.report.asset-confirmation',['company' => $company,'header'=>$header,'detail'=>$detail]);
      $customPaper = array(0,0,21.84,13.97);
      //$pdf->setPaper($customPaper);

      // $pdf->setPaper('A4', 'landscape');
      return $pdf->stream();
    }

    public function suratJalan(Request $request)
    {
        // return dd($request->all());
        // die();
        $sjCode = $request->sjCode;
        $status = $request->status;

        $detailSuratJalan = DB::table('d_surat_jalan')
        ->join('t_surat_jalan','t_surat_jalan.sj_code','=','d_surat_jalan.sj_code')
        ->join('m_produk','m_produk.id','=','d_surat_jalan.produk_id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.*','t_surat_jalan.*','d_surat_jalan.*','m_satuan_unit.code as code_unit')
        ->where('d_surat_jalan.sj_code',$sjCode)
        ->where('t_surat_jalan.status',$status)
        ->get();

        $datasuratJalan = DB::table('d_surat_jalan')
        ->join('t_surat_jalan','t_surat_jalan.sj_code','=','d_surat_jalan.sj_code')
        ->join('m_customer','m_customer.id','=','t_surat_jalan.customer')
        ->join('m_gudang','m_gudang.id','=','m_customer.gudang')
        ->join('t_sales_order','t_sales_order.so_code','=','t_surat_jalan.so_code')
        ->select('d_surat_jalan.*','t_surat_jalan.*','m_customer.main_kelurahan','m_customer.code','m_customer.name as customer',
        'm_gudang.name as gudang','m_customer.main_office_phone_1','m_customer.main_phone_1','m_gudang.name as gudang','t_sales_order.sending_address')
        ->where('d_surat_jalan.sj_code',$sjCode)
        ->where('t_surat_jalan.status',$status)
        ->first();
        //update flag print out
        if( $status == 'post'){
            DB::table('t_surat_jalan')->where('t_surat_jalan.sj_code',$sjCode)->update([ 'print' => true ]);
        }

        MPrintLogModel::create([
            'code' => $sjCode,
            'user' => auth()->user()->id,
            'type' => 'sj',
        ]);

        $company = DB::table('m_company_profile')->first();

        // dd($datasuratJalan,$detailSuratJalan);
        // $customPaper = array(0,0,360,360);
        $pdf = PDF::loadview('admin.report.surat-jalan',['company' => $company,'datasuratJalan' => $datasuratJalan, 'detailSuratJalan' => $detailSuratJalan]);
        $paper = array(0,0,684,396);
        //$customPaper = array(0,0,21.84,13.97);
        //$pdf->setPaper($customPaper);
        // $pdf->setPaper('A5', 'landscape');
        return $pdf->setPaper($paper)->stream();
        //return $pdf->download('surat-jalan-'.$datasuratJalan->so_code.'.pdf');
    }

    public function printoutReturSJ($rtCode)
    {

        // die();

        $header = DB::table('t_retur_sj')
        ->join('m_customer','t_retur_sj.customer','m_customer.id')
        ->join('t_faktur','t_faktur.sj_code','t_retur_sj.sj_code')
        ->join('m_user','m_user.id','t_retur_sj.user_input')
        ->select('t_retur_sj.*','m_customer.name','t_faktur.faktur_code','m_user.name as user')
        ->where('t_retur_sj.rt_code',$rtCode)
        ->first();

        $detail = DB::table('d_retur_sj')
        ->join('m_produk','d_retur_sj.produk_id','=','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('d_retur_sj.*','m_produk.name','m_satuan_unit.code as satuan_kemasan')
        ->where('d_retur_sj.rt_code',$rtCode)
        ->get();

        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.retur-surat-jalan',['company' => $company,'header' => $header, 'detail' => $detail]);
        $customPaper = array(0,0,21.84,13.97);
        ////$pdf->setPaper($customPaper);
        // $pdf->setPaper('A5', 'landscape');
        return $pdf->stream();

    }

    public function printoutReturSJM($rtCode)
    {
        // die();

        $header = DB::table('t_retur_sjm')
        ->join('m_supplier','t_retur_sjm.supplier','m_supplier.id')
        ->join('t_purchase_invoice','t_purchase_invoice.sj_masuk_code','t_retur_sjm.sjm_code')
        ->join('m_user','m_user.id','t_retur_sjm.user_input')
        ->select('t_retur_sjm.*','m_supplier.name','t_purchase_invoice.pi_code','m_user.name as user')
        ->where('t_retur_sjm.rt_code',$rtCode)
        ->first();

        $detail = DB::table('d_retur_sjm')
        ->join('m_produk','d_retur_sjm.produk_id','=','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('d_retur_sjm.*','m_produk.name','m_satuan_unit.code as satuan_kemasan')
        ->where('d_retur_sjm.rt_code',$rtCode)
        ->get();

        $pdf = PDF::loadview('admin.report.retur-pd',['header' => $header, 'detail' => $detail]);
        $customPaper = array(0,0,21.84,13.97);
        //$pdf->setPaper($customPaper);
        // $pdf->setPaper('A5', 'landscape');
        return $pdf->stream();

    }

    public function tagihan($faktur_code)
    {
        // dd($status);

        $pdf = PDF::loadview('admin.report.tagihan',[]);
        $pdf->setPaper('A5', 'landscape');
        return $pdf->download('tagihan.pdf');
    }

    public function stokGudang(Request $request, $idGudang)
    {
        $dataGudang = DB::table('m_gudang')
        ->where('id',$idGudang)
        ->first();
        $dataProduk = DB::table('m_produk')
        ->get();
        foreach ($dataProduk as $raw_data) {
            $stok = DB::table('m_stok_produk')
            ->where('m_stok_produk.produk_code', $raw_data->code)
            ->where('m_stok_produk.gudang', $idGudang)
            ->groupBy('m_stok_produk.produk_code')
            ->sum('m_stok_produk.stok');
            $raw_data->stok = $stok;
        }

        $pdf = PDF::loadview('admin.report.stok-gudang',['dataGudang' => $dataGudang,'dataProduk' => $dataProduk]);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('Stok '.$dataGudang->name.' '.date('dmyhis').'.pdf');
    }

    public function stokProduk(Request $request,$gudang,$produkCode)
    {
        ini_set('memory_limit', '512MB');
        ini_set('max_execution_time', 3000);
        
        $detailGudang = MStokProdukModel::join('m_produk', 'm_produk.code', '=', 'm_stok_produk.produk_code')
        ->join('m_gudang', 'm_gudang.id', '=', 'm_stok_produk.gudang')
        ->select('m_stok_produk.gudang', 'produk_code', 'm_produk.name as produk','m_produk.id as produk_id',
        'm_gudang.name as gudang')
        ->where('produk_code',$produkCode)
        ->where('gudang',$gudang)
        ->first();

        $sum = MStokProdukModel::where('produk_code',$produkCode)
        ->where('gudang',$gudang)
        ->sum('stok');

        if ($request->pilihanreport == '1') {
            $dataGudang = MStokProdukModel::where('produk_code',$produkCode)
            ->where('gudang',$gudang)
            ->orderBy('created_at')
            ->get();

            $status = 1;
            $tglmulai = 0;
            $tglsampai = 0;
        }elseif ($request->pilihanreport == '2') {
            $tglmulai = substr($request->tanggal,0,10);
            $tglsampai = substr($request->tanggal,13,10);

            //$sending_date = date('Y-m-d', strtotime($tglmulai));
            //dd($tglmulai.$tglsampai);
            $dataGudang = MStokProdukModel::where('produk_code',$produkCode)
            ->where('gudang',$gudang)
            ->where('created_at','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->orderBy('created_at')
            ->get();
            //dd($dataGudang);

            $status = 2;
        }

        $pdf = PDF::loadview('admin.report.stok-barang-gudang',['dataGudang' => $dataGudang,'detailGudang' => $detailGudang,'sum' => $sum,'status' => $status,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai]);

        return $pdf->download('Stok '.$detailGudang->produk.' '.date('dmyhis').'.pdf');
    }

    public function tagihanCustomer(Request $request)
    {
        $this->validate($request, [
            'customer' => 'required',
        ]);

        $dataTagihan = DB::table('t_faktur')
        ->where('customer', $request->customer)
        ->where('status_payment', '=', 'unpaid')
        ->get();

        $dataCustomer = DB::table('m_customer')
        ->where('id', $request->customer)
        ->first();

        $pdf = PDF::loadview('admin.report.tagihan-by-customer',['dataTagihan' => $dataTagihan,'dataCustomer' => $dataCustomer]);

        return $pdf->download('list-tagihan-'.$dataCustomer->name.' - '.date('dmyhis').'.pdf');
    }

    public function piSupplier(Request $request)
    {
        $this->validate($request, [
            'supplier' => 'required',
        ]);

        $dataPI = DB::table('t_purchase_invoice')
        ->where('supplier', $request->supplier)
        ->where('status', '=', 'unpaid')
        ->get();

        $dataSupplier = DB::table('m_supplier')
        ->where('id', $request->supplier)
        ->first();
        // dd('test');
        $pdf = PDF::loadview('admin.report.pi-by-supplier',['dataPI' => $dataPI,'dataSupplier' => $dataSupplier]);

        return $pdf->download('list-pi-'.$dataSupplier->name.' - '.date('dmyhis').'.pdf');
    }
    public function pifaSupplier(Request $request)
    {
        $this->validate($request, [
            'supplier' => 'required',
        ]);

        $dataPI = DB::table('t_purchase_invoice')
        ->where('supplier', $request->supplier)
        ->where('status', '=', 'unpaid')
        ->get();

        $dataSupplier = DB::table('m_supplier')
        ->where('id', $request->supplier)
        ->first();
        // dd('test');
        $pdf = PDF::loadview('admin.report.pifa-by-supplier',['dataPI' => $dataPI,'dataSupplier' => $dataSupplier]);

        return $pdf->download('list-pi-'.$dataSupplier->name.' - '.date('dmyhis').'.pdf');
    }

    public function masterProvinsi($type)
    {
        $dataProvinsi = DB::table('m_provinsi')->orderBy('code')->get();;
        $pdf = PDF::loadview('admin.report.master.provinsi',['dataProvinsi' => $dataProvinsi]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-provinsi.pdf');
    }

    public function reportSO(request $request, $type)
    {
        // $this->validate($request, [
        //     'customer' => 'required',
        // ]);

        //dd($request->all());

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->customer == null) {
            $customer = 'All';
        }else{
            $customer = $customer = DB::table('m_customer')
            ->where('id', $request->customer)
            ->pluck('name')
            ->first();
        }

        if ($request->so == '0') {
            $so_code = 'All';
        }else{
            $so_code = $request->so;
        }

        if ($request->barang == null) {
            $barang = 'All';
        }else{
            $barang = DB::table('m_produk')
            ->where('id', $request->barang)
            ->pluck('name')
            ->first();
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        if ($request->type == 'summary') {
            $query = DB::table('t_sales_order');
            $query->select('t_sales_order.so_date','t_sales_order.so_code','m_user.name as sales_name','m_customer.name as customer_name','t_sales_order.status_aprove','grand_total as total','t_sales_order.diskon_header_potongan','t_sales_order.diskon_header_persen');
            $query->join('m_customer', 'm_customer.id', '=', 't_sales_order.customer');
            $query->leftjoin('m_user', 'm_user.id', '=', 't_sales_order.sales');
            $query->where('so_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('so_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->customer != null) {
                $query->where('customer', $request->customer);
            }

            if ($request->so != '0') {
                $query->where('so_code',$request->so);
            }

            if ($request->status == 'in proccess') {
                $query->where('status_aprove','in process');
            }
            if ($request->status == 'in approval') {
                $query->where('status_aprove','in approval');
            }
            if ($request->status == 'approved') {
                $query->where('status_aprove','approved');
            }
            if ($request->status == 'reject') {
                $query->where('status_aprove','reject');
            }

            $query->orderBy('so_code');

            $dataSO = $query->get();

            foreach ($dataSO as $raw_data) {
                $total = DB::table('d_sales_order')
                ->where('so_code', $raw_data->so_code)
                ->sum('total');

                $raw_data->total_awal = $total;
            }

            //dd($dataSO);

            $pdf = PDF::loadview('admin.report.laporan-so-summary',['dataSO' => $dataSO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'so_code' => $so_code,'status' => $status,'customer' => $customer,'barang' => $barang]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-so-'.$customer.'-'.date('dmyhis').'.pdf');
            }
        }else{
            $query = DB::table('d_sales_order');
            $query->select('t_sales_order.so_code','d_sales_order.produk as id_produk','d_sales_order.qty','d_sales_order.free_qty','t_sales_order.so_date','m_produk.name as produk_name','m_customer.name as customer_name','m_produk.code as produk_code','m_user.name as sales_name','d_sales_order.customer_price','d_sales_order.total as total_price','d_sales_order.diskon_potongan','d_sales_order.diskon_persen','d_sales_order.markup');
            $query->join('t_sales_order', 't_sales_order.so_code', '=', 'd_sales_order.so_code');
            $query->join('m_produk', 'm_produk.id', '=', 'd_sales_order.produk');
            $query->join('m_customer', 'm_customer.id', '=', 't_sales_order.customer');
            $query->leftjoin('m_user', 'm_user.id', '=', 't_sales_order.sales');
            $query->where('so_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('so_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->customer != null) {
                $query->where('customer', $request->customer);
            }

            if ($request->so != '0') {
                $query->where('d_sales_order.so_code',$request->so);
            }

            if ($request->barang != null) {
                $query->where('d_sales_order.produk',$request->barang);
            }

            if ($request->status == 'in proccess') {
                $query->where('status_aprove','in process');
            }
            if ($request->status == 'in approval') {
                $query->where('status_aprove','in approval');
            }
            if ($request->status == 'approved') {
                $query->where('status_aprove','approved');
            }
            if ($request->status == 'reject') {
                $query->where('status_aprove','reject');
            }

            $query->orderBy('m_customer.name');
            $query->orderBy('t_sales_order.so_date');
            $query->orderBy('d_sales_order.so_code');

            $dataSO = $query->get();

            //dd($dataSO);

            $pdf = PDF::loadview('admin.report.laporan-so-detail',['dataSO' => $dataSO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'so_code' => $so_code,'status' => $status,'customer' => $customer,'barang' => $barang]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-so-'.$customer.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function reportPO(request $request, $type)
    {
        // $this->validate($request, [
        //     'supplier' => 'required',
        // ]);

        //dd($request->all());

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->supplier == null) {
            $supplier = 'All';
        }else{
            $supplier = $supplier = DB::table('m_supplier')
            ->where('id', $request->supplier)
            ->pluck('name')
            ->first();
        }

        if ($request->po == '0') {
            $po_code = 'All';
        }else{
            $po_code = $request->po;
        }

        if ($request->barang == null) {
            $barang = 'All';
        }else{
            $barang = DB::table('m_produk')
            ->where('id', $request->barang)
            ->pluck('name')
            ->first();
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        if ($request->type == 'summary') {
            $query = DB::table('t_purchase_order');
            $query->select('t_purchase_order.po_date','t_purchase_order.po_code','m_supplier.name as supplier_name','t_purchase_order.status_aprove','grand_total as total','t_purchase_order.diskon_header_potongan','t_purchase_order.diskon_header_persen');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_purchase_order.supplier');
            $query->where('po_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('po_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->supplier != null) {
                $query->where('supplier', $request->supplier);
            }

            if ($request->po != '0') {
                $query->where('po_code',$request->po);
            }

            if ($request->status == 'in proccess') {
                $query->where('status_aprove','in process');
            }
            if ($request->status == 'in approval') {
                $query->where('status_aprove','in approval');
            }
            if ($request->status == 'approved') {
                $query->where('status_aprove','approved');
            }
            if ($request->status == 'reject') {
                $query->where('status_aprove','reject');
            }

            $query->orderBy('po_code');

            $dataPO = $query->get();

            foreach ($dataPO as $raw_data) {
                $total = DB::table('d_purchase_order')
                ->where('po_code', $raw_data->po_code)
                ->sum('total_neto');

                $raw_data->total_awal = $total;
            }

            //dd($dataPO);

            $pdf = PDF::loadview('admin.report.laporan-po-summary',['dataPO' => $dataPO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'po_code' => $po_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-po-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }else{
            $query = DB::table('d_purchase_order');
            $query->select('t_purchase_order.po_code','d_purchase_order.produk as id_produk','d_purchase_order.qty','d_purchase_order.free_qty','t_purchase_order.po_date','m_produk.name as produk_name','m_supplier.name as supplier_name','m_produk.code as produk_code','d_purchase_order.price','d_purchase_order.total_neto as total_price','d_purchase_order.diskon_potongan','d_purchase_order.diskon_persen');
            $query->join('t_purchase_order', 't_purchase_order.po_code', '=', 'd_purchase_order.po_code');
            $query->join('m_produk', 'm_produk.id', '=', 'd_purchase_order.produk');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_purchase_order.supplier');
            $query->where('po_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('po_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->supplier != null) {
                $query->where('supplier', $request->supplier);
            }

            if ($request->po != '0') {
                $query->where('d_purchase_order.po_code',$request->po);
            }

            if ($request->barang != null) {
                $query->where('d_purchase_order.produk',$request->barang);
            }

            if ($request->status == 'in proccess') {
                $query->where('status_aprove','in process');
            }
            if ($request->status == 'in approval') {
                $query->where('status_aprove','in approval');
            }
            if ($request->status == 'approved') {
                $query->where('status_aprove','approved');
            }
            if ($request->status == 'reject') {
                $query->where('status_aprove','reject');
            }

            $query->orderBy('m_supplier.name');
            $query->orderBy('t_purchase_order.po_date');
            $query->orderBy('d_purchase_order.po_code');

            $dataPO = $query->get();

            //dd($dataPO);

            $pdf = PDF::loadview('admin.report.laporan-po-detail',['dataPO' => $dataPO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'po_code' => $po_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-po-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }
    }



    public function reportSJ(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->customer == null) {
            $customer = 'All';
        }else{
            $customer = $customer = DB::table('m_customer')
            ->where('id', $request->customer)
            ->pluck('name')
            ->first();
        }

        if ($request->so == null) {
            $so_code = 'All';
        }else{
            $so_code = $request->so;
        }

        if ($request->sj == '0') {
            $sj_code = 'All';
        }else{
            $sj_code = $request->sj;
        }

        if ($request->barang == null) {
            $barang = 'All';
        }else{
            $barang = DB::table('m_produk')
            ->where('id', $request->barang)
            ->pluck('name')
            ->first();
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        $query = DB::table('t_surat_jalan');
        $query->select(DB::raw("DATE(t_surat_jalan.sj_date) as tgl"));
        $query->join('d_surat_jalan', 'd_surat_jalan.sj_code', '=', 't_surat_jalan.sj_code');
        $query->where('t_surat_jalan.sj_date','>=',date('Y-m-d', strtotime($tglmulai)));
        $query->where('t_surat_jalan.sj_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
        if ($request->customer != null) {
            $query->where('t_surat_jalan.customer', $request->customer);
        }
        if ($request->so != null) {
            //dd($request->so);
            $query->where('t_surat_jalan.so_code',$request->so);
        }
        if ($request->sj != '0') {
            $query->where('t_surat_jalan.sj_code',$request->sj);
        }

        if ($request->barang != null) {
            $query->where('d_surat_jalan.produk_id',$request->barang);
        }

        if ($request->status == 'save') {
            $query->where('t_surat_jalan.status','save');
        }
        if ($request->status == 'post') {
            $query->where('t_surat_jalan.status','post');
        }
        $query->groupBy('tgl');

        $dataSJ = $query->get();

        foreach ($dataSJ as $raw_data) {
            $query = DB::table('t_surat_jalan');
            $query->select('customer','m_customer.name as customer_name');
            $query->join('d_surat_jalan', 'd_surat_jalan.sj_code', '=', 't_surat_jalan.sj_code');
            $query->join('m_customer', 'm_customer.id', '=', 't_surat_jalan.customer');
            $query->where('t_surat_jalan.sj_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
            $query->where('t_surat_jalan.sj_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
            if ($request->customer != null) {
                $query->where('t_surat_jalan.customer', $request->customer);
            }
            if ($request->so != null) {
                //dd($request->so);
                $query->where('t_surat_jalan.so_code',$request->so);
            }
            if ($request->sj != '0') {
                $query->where('t_surat_jalan.sj_code',$request->sj);
            }

            if ($request->barang != null) {
                $query->where('d_surat_jalan.produk_id',$request->barang);
            }

            if ($request->status == 'save') {
                $query->where('t_surat_jalan.status','save');
            }
            if ($request->status == 'post') {
                $query->where('t_surat_jalan.status','post');
            }
            $query->groupBy('customer','m_customer.name');

            $dataCustomer = $query->get();
            $raw_data->data_customer = $dataCustomer;

            foreach ($dataCustomer as $raw_data2) {
                $query = DB::table('t_surat_jalan');
                $query->select('t_surat_jalan.sj_code','status');
                $query->join('d_surat_jalan', 'd_surat_jalan.sj_code', '=', 't_surat_jalan.sj_code');
                $query->where('customer',$raw_data2->customer);
                $query->where('t_surat_jalan.sj_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_surat_jalan.sj_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
                if ($request->so != null) {
                    $query->where('t_surat_jalan.so_code',$request->so);
                }
                if ($request->sj != '0') {
                    $query->where('t_surat_jalan.sj_code',$request->sj);
                }

                if ($request->barang != null) {
                    $query->where('d_surat_jalan.produk_id',$request->barang);
                }

                if ($request->status == 'save') {
                    $query->where('t_surat_jalan.status','save');
                }
                if ($request->status == 'post') {
                    $query->where('t_surat_jalan.status','post');
                }
                $query->groupBy('t_surat_jalan.sj_code','status');

                $dataSJH = $query->get();
                $raw_data2->data_sjcode = $dataSJH;

                foreach ($dataSJH as $raw_data3) {
                    $query = DB::table('d_surat_jalan');
                    $query->select('d_surat_jalan.produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty_delivery','free_qty','last_so_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_surat_jalan.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id');
                    $query->where('sj_code',$raw_data3->sj_code);
                    if ($request->barang != null) {
                        $query->where('d_surat_jalan.produk_id',$request->barang);
                    }
                    $dataProduk = $query->get();
                    $raw_data3->detail_sj = $dataProduk;

                    foreach ($dataProduk as $raw_data4) {
                        $getsocode = DB::table('t_surat_jalan')
                        ->where('sj_code',$raw_data3->sj_code)
                        ->pluck('so_code')
                        ->first();

                        $totalSOQty = DB::table('d_sales_order')
                        ->where('so_code', $getsocode)
                        ->where('produk', $raw_data4->produk_id)
                        ->pluck('qty')
                        ->first();
                        $raw_data4->SOQty = $totalSOQty;
                    }
                }
            }
        }

        $pdf = PDF::loadview('admin.report.laporan-sj-summary',['dataSJ' => $dataSJ,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'so_code' => $so_code,'sj_code' => $sj_code,'status' => $status,'customer' => $customer,'barang' => $barang]);

        $pdf->setPaper('legal', 'potrait');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-sj-'.$customer.'-'.date('dmyhis').'.pdf');
        }
        //}
    }

    public function reportPD(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->supplier == null) {
            $supplier = 'All';
        }else{
            $supplier = $supplier = DB::table('m_supplier')
            ->where('id', $request->supplier)
            ->pluck('name')
            ->first();
        }

        if ($request->po == null) {
            $po_code = 'All';
        }else{
            $po_code = $request->po;
        }

        if ($request->pd == '0') {
            $sj_masuk_code = 'All';
        }else{
            $sj_masuk_code = $request->pd;
        }

        if ($request->barang == null) {
            $barang = 'All';
        }else{
            $barang = DB::table('m_produk')
            ->where('id', $request->barang)
            ->pluck('name')
            ->first();
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        $query = DB::table('t_surat_jalan_masuk');
        $query->select(DB::raw("DATE(t_surat_jalan_masuk.sj_masuk_date) as tgl"));
        $query->join('d_surat_jalan_masuk', 'd_surat_jalan_masuk.sj_masuk_code', '=', 't_surat_jalan_masuk.sj_masuk_code');
        $query->where('t_surat_jalan_masuk.sj_masuk_date','>=',date('Y-m-d', strtotime($tglmulai)));
        $query->where('t_surat_jalan_masuk.sj_masuk_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
        if ($request->supplier != null) {
            $query->where('t_surat_jalan_masuk.supplier', $request->supplier);
        }
        if ($request->po != null) {
            //dd($request->po);
            $query->where('t_surat_jalan_masuk.po_code',$request->po);
        }
        if ($request->pd != '0') {
            $query->where('t_surat_jalan_masuk.sj_masuk_code',$request->pd);
        }

        if ($request->barang != null) {
            $query->where('d_surat_jalan_masuk.produk_id',$request->barang);
        }

        if ($request->status == 'save') {
            $query->where('t_surat_jalan_masuk.status','save');
        }
        if ($request->status == 'post') {
            $query->where('t_surat_jalan_masuk.status','post');
        }
        $query->groupBy('tgl');

        $dataPD = $query->get();

        foreach ($dataPD as $raw_data) {
            $query = DB::table('t_surat_jalan_masuk');
            $query->select('supplier','m_supplier.name as supplier_name');
            $query->join('d_surat_jalan_masuk', 'd_surat_jalan_masuk.sj_masuk_code', '=', 't_surat_jalan_masuk.sj_masuk_code');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_surat_jalan_masuk.supplier');
            $query->where('t_surat_jalan_masuk.sj_masuk_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
            $query->where('t_surat_jalan_masuk.sj_masuk_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
            if ($request->supplier != null) {
                $query->where('t_surat_jalan_masuk.supplier', $request->supplier);
            }
            if ($request->po != null) {
                //dd($request->po);
                $query->where('t_surat_jalan_masuk.po_code',$request->po);
            }
            if ($request->pd != '0') {
                $query->where('t_surat_jalan_masuk.sj_masuk_code',$request->pd);
            }

            if ($request->barang != null) {
                $query->where('d_surat_jalan_masuk.produk_id',$request->barang);
            }

            if ($request->status == 'save') {
                $query->where('t_surat_jalan_masuk.status','save');
            }
            if ($request->status == 'post') {
                $query->where('t_surat_jalan_masuk.status','post');
            }
            $query->groupBy('supplier','m_supplier.name');

            $dataCustomer = $query->get();
            $raw_data->data_supplier = $dataCustomer;

            foreach ($dataCustomer as $raw_data2) {
                $query = DB::table('t_surat_jalan_masuk');
                $query->select('t_surat_jalan_masuk.sj_masuk_code','status');
                $query->join('d_surat_jalan_masuk', 'd_surat_jalan_masuk.sj_masuk_code', '=', 't_surat_jalan_masuk.sj_masuk_code');
                $query->where('supplier',$raw_data2->supplier);
                $query->where('t_surat_jalan_masuk.sj_masuk_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_surat_jalan_masuk.sj_masuk_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
                if ($request->po != null) {
                    $query->where('t_surat_jalan_masuk.po_code',$request->po);
                }
                if ($request->pd != '0') {
                    $query->where('t_surat_jalan_masuk.sj_masuk_code',$request->pd);
                }

                if ($request->barang != null) {
                    $query->where('d_surat_jalan_masuk.produk_id',$request->barang);
                }

                if ($request->status == 'save') {
                    $query->where('t_surat_jalan_masuk.status','save');
                }
                if ($request->status == 'post') {
                    $query->where('t_surat_jalan_masuk.status','post');
                }
                $query->groupBy('t_surat_jalan_masuk.sj_masuk_code','status');

                $dataPDH = $query->get();
                $raw_data2->data_pdcode = $dataPDH;

                foreach ($dataPDH as $raw_data3) {
                    $query = DB::table('d_surat_jalan_masuk');
                    $query->select('d_surat_jalan_masuk.produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty','free_qty','last_po_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_surat_jalan_masuk.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id');
                    $query->where('sj_masuk_code',$raw_data3->sj_masuk_code);
                    if ($request->barang != null) {
                        $query->where('d_surat_jalan_masuk.produk_id',$request->barang);
                    }
                    $dataProduk = $query->get();
                    $raw_data3->detail_pd = $dataProduk;

                    foreach ($dataProduk as $raw_data4) {
                        $getpocode = DB::table('t_surat_jalan_masuk')
                        ->where('sj_masuk_code',$raw_data3->sj_masuk_code)
                        ->pluck('po_code')
                        ->first();

                        $totalSOQty = DB::table('d_purchase_order')
                        ->where('po_code', $getpocode)
                        ->where('produk', $raw_data4->produk_id)
                        ->pluck('qty')
                        ->first();
                        $raw_data4->SOQty = $totalSOQty;
                    }
                }
            }
        }
        $pdf = PDF::loadview('admin.report.laporan-pd-summary',['dataPD' => $dataPD,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'po_code' => $po_code,'sj_masuk_code' => $sj_masuk_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

        $pdf->setPaper('legal', 'potrait');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-pd-'.$supplier.'-'.date('dmyhis').'.pdf');
        }
    }


    public function reportSOSJ(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->customer == null) {
            $customer = 'All';
        }else{
            $customer = $customer = DB::table('m_customer')
            ->where('id', $request->customer)
            ->pluck('name')
            ->first();
        }

        if ($request->so == '0') {
            $so_code = 'All';
        }else{
            $so_code = $request->so;
        }

        if ($request->barang == null) {
            $barang = 'All';
        }else{
            $barang = DB::table('m_produk')
            ->where('id', $request->barang)
            ->pluck('name')
            ->first();
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        $query = DB::table('t_sales_order');
        $query->select(DB::raw("DATE(t_sales_order.so_date) as tgl"));
        $query->join('d_sales_order', 'd_sales_order.so_code', '=', 't_sales_order.so_code');
        $query->where('t_sales_order.so_date','>=',date('Y-m-d', strtotime($tglmulai)));
        $query->where('t_sales_order.so_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

        if ($request->customer != null) {
            $query->where('t_sales_order.customer', $request->customer);
        }
        if ($request->so != '0') {
            $query->where('t_sales_order.so_code',$request->so);
        }
        if ($request->barang != null) {
            $query->where('d_sales_order.produk',$request->barang);
        }

        $query->groupBy('tgl');

        $dataSOSJ = $query->get();

        foreach ($dataSOSJ as $raw_data) {
            $query = DB::table('t_sales_order');
            $query->select('customer','m_customer.name as customer_name');
            $query->join('d_sales_order', 'd_sales_order.so_code', '=', 't_sales_order.so_code');
            $query->join('m_customer', 'm_customer.id', '=', 't_sales_order.customer');
            $query->where('t_sales_order.so_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
            $query->where('t_sales_order.so_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
            if ($request->customer != null) {
                $query->where('t_sales_order.customer', $request->customer);
            }
            if ($request->so != '0') {
                $query->where('t_sales_order.so_code',$request->so);
            }
            if ($request->barang != null) {
                $query->where('d_sales_order.produk',$request->barang);
            }
            $query->groupBy('customer','m_customer.name');

            $dataCustomer = $query->get();
            $raw_data->data_customer = $dataCustomer;

            foreach ($dataCustomer as $raw_data2) {
                $query = DB::table('t_sales_order');
                $query->select('t_sales_order.so_code','status_aprove');
                $query->join('d_sales_order', 'd_sales_order.so_code', '=', 't_sales_order.so_code');
                $query->where('customer',$raw_data2->customer);
                $query->where('t_sales_order.so_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_sales_order.so_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
                if ($request->so != '0') {
                    $query->where('t_sales_order.so_code',$request->so);
                }
                if ($request->barang != null) {
                    $query->where('d_sales_order.produk',$request->barang);
                }

                $dataSO = $query->get();
                $raw_data2->data_socode = $dataSO;

                foreach ($dataSO as $raw_data3) {
                    $query = DB::table('d_sales_order');
                    $query->select('produk as produk_id','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty as qty_so');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_sales_order.produk');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id');
                    $query->where('so_code',$raw_data3->so_code);
                    if ($request->barang != null) {
                        $query->where('d_sales_order.produk',$request->barang);
                    }
                    $dataProduk = $query->get();
                    $raw_data3->detail_so = $dataProduk;

                    foreach ($dataProduk as $raw_data4) {
                        $dataProduk = DB::table('d_surat_jalan')
                        ->select('d_surat_jalan.sj_code','t_surat_jalan.sj_date','d_surat_jalan.qty_delivery')
                        ->join('t_surat_jalan', 't_surat_jalan.sj_code', '=', 'd_surat_jalan.sj_code')
                        ->where('t_surat_jalan.so_code',$raw_data3->so_code)
                        ->where('d_surat_jalan.produk_id',$raw_data4->produk_id)
                        ->orderBy('t_surat_jalan.sj_date')
                        ->get();
                        $raw_data4->data_sj = $dataProduk;
                    }
                }
            }
        }

        //dd($dataSOSJ);

        $pdf = PDF::loadview('admin.report.laporan-so-sj',['dataSOSJ' => $dataSOSJ,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'so_code' => $so_code,'status' => $status,'customer' => $customer,'barang' => $barang]);

        $pdf->setPaper('legal', 'potrait');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-so-sj-'.$customer.'-'.date('dmyhis').'.pdf');
        }
        //}
    }

    public function reportPOPD(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->supplier == null) {
            $supplier = 'All';
        }else{
            $supplier = $supplier = DB::table('m_supplier')
            ->where('id', $request->supplier)
            ->pluck('name')
            ->first();
        }

        if ($request->po == '0') {
            $po_code = 'All';
        }else{
            $po_code = $request->po;
        }

        if ($request->barang == null) {
            $barang = 'All';
        }else{
            $barang = DB::table('m_produk')
            ->where('id', $request->barang)
            ->pluck('name')
            ->first();
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        $query = DB::table('t_purchase_order');
        $query->select(DB::raw("DATE(t_purchase_order.po_date) as tgl"));
        $query->join('d_purchase_order', 'd_purchase_order.po_code', '=', 't_purchase_order.po_code');
        $query->where('t_purchase_order.po_date','>=',date('Y-m-d', strtotime($tglmulai)));
        $query->where('t_purchase_order.po_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

        if ($request->supplier != null) {
            $query->where('t_purchase_order.supplier', $request->supplier);
        }
        if ($request->po != '0') {
            $query->where('t_purchase_order.po_code',$request->po);
        }
        if ($request->barang != null) {
            $query->where('d_purchase_order.produk',$request->barang);
        }

        $query->groupBy('tgl');

        $dataPOPD = $query->get();

        foreach ($dataPOPD as $raw_data) {
            $query = DB::table('t_purchase_order');
            $query->select('supplier','m_supplier.name as supplier_name');
            $query->join('d_purchase_order', 'd_purchase_order.po_code', '=', 't_purchase_order.po_code');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_purchase_order.supplier');
            $query->where('t_purchase_order.po_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
            $query->where('t_purchase_order.po_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
            if ($request->supplier != null) {
                $query->where('t_purchase_order.supplier', $request->supplier);
            }
            if ($request->po != '0') {
                $query->where('t_purchase_order.po_code',$request->po);
            }
            if ($request->barang != null) {
                $query->where('d_purchase_order.produk',$request->barang);
            }
            $query->groupBy('supplier','m_supplier.name');

            $dataCustomer = $query->get();
            $raw_data->data_supplier = $dataCustomer;

            foreach ($dataCustomer as $raw_data2) {
                $query = DB::table('t_purchase_order');
                $query->select('t_purchase_order.po_code','status_aprove');
                $query->join('d_purchase_order', 'd_purchase_order.po_code', '=', 't_purchase_order.po_code');
                $query->where('supplier',$raw_data2->supplier);
                $query->where('t_purchase_order.po_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_purchase_order.po_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
                if ($request->po != '0') {
                    $query->where('t_purchase_order.po_code',$request->po);
                }
                if ($request->barang != null) {
                    $query->where('d_purchase_order.produk',$request->barang);
                }

                $dataPO = $query->get();
                $raw_data2->data_pocode = $dataPO;

                foreach ($dataPO as $raw_data3) {
                    $query = DB::table('d_purchase_order');
                    $query->select('produk as produk_id','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty as qty_po');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_purchase_order.produk');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id');
                    $query->where('po_code',$raw_data3->po_code);
                    if ($request->barang != null) {
                        $query->where('d_purchase_order.produk',$request->barang);
                    }
                    $dataProduk = $query->get();
                    $raw_data3->detail_po = $dataProduk;

                    foreach ($dataProduk as $raw_data4) {
                        $dataProduk = DB::table('d_surat_jalan_masuk')
                        ->select('d_surat_jalan_masuk.sj_masuk_code','t_surat_jalan_masuk.sj_masuk_date','d_surat_jalan_masuk.qty')
                        ->join('t_surat_jalan_masuk', 't_surat_jalan_masuk.sj_masuk_code', '=', 'd_surat_jalan_masuk.sj_masuk_code')
                        ->where('t_surat_jalan_masuk.po_code',$raw_data3->po_code)
                        ->where('d_surat_jalan_masuk.produk_id',$raw_data4->produk_id)
                        ->orderBy('t_surat_jalan_masuk.sj_masuk_date')
                        ->get();
                        $raw_data4->data_sj = $dataProduk;
                    }
                }
            }
        }

        //dd($dataPOPD);

        $pdf = PDF::loadview('admin.report.laporan-po-pd',['dataPOPD' => $dataPOPD,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'po_code' => $po_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

        $pdf->setPaper('legal', 'potrait');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-po-pd-'.$supplier.'-'.date('dmyhis').'.pdf');
        }
        //}
    }

    public function reportTagihan(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->customer == null) {
            $customer = 'All';
        }else{
            $customer = DB::table('m_customer')
            ->where('id', $request->customer)
            ->pluck('name')
            ->first();
        }

        $query = DB::table('m_customer');
        $query->select('m_customer.id','m_customer.name');
        $query->join('t_faktur', 'm_customer.id', '=', 't_faktur.customer');
        //$query->where('t_faktur.status_payment','unpaid');
        $query->where('t_faktur.created_at','>=',date('Y-m-d', strtotime($tglmulai)));
        $query->where('t_faktur.created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
        if ($request->customer != null) {
            $query->where('m_customer.id', $request->customer);
        }
        $query->groupBy('m_customer.id');
        $datacustomer = $query->get();

        if ($request->type == 'summary') {
            foreach ($datacustomer as $raw_data) {
                // $saldoawal = DB::table('t_faktur')
                //     ->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                //     ->where('customer',$raw_data->id)
                //     ->sum('total');
                $saldoawal = DB::table('m_saldo_awal_piutang')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('customer',$raw_data->id)
                    ->where('status','post')
                    ->sum('total_piutang');

                $penjualan = DB::table('t_faktur')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('customer',$raw_data->id)
                    ->sum('total');

                $creditNote = DB::table('t_faktur')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('customer',$raw_data->id)
                    ->sum('credit_note');

                $pembayaran = DB::table('d_pembayaran')
                    ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                    ->where('t_pembayaran.payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_pembayaran.payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('t_pembayaran.customer',$raw_data->id)
                    ->where('status','approved')
                    ->sum('d_pembayaran.total');
                //calculate
                $subTotalPembayaran = $pembayaran + $creditNote;

                $raw_data->saldoawal = $saldoawal;
                $raw_data->penjualan = $penjualan;
                $raw_data->pembayaran = $subTotalPembayaran;
                $raw_data->saldoakhir = $saldoawal + $penjualan - $subTotalPembayaran;
            }

            //dd($datacustomer);

            $pdf = PDF::loadview('admin.report.laporan-tagihan-summary',['datacustomer' => $datacustomer,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'customer' => $customer]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('kartu-piutang-summary-'.$customer.'-'.date('dmyhis').'.pdf');
            }
        }else{
            foreach ($datacustomer as $raw_data) {
                // $saldoawal = DB::table('t_faktur')
                //     ->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                //     ->where('customer',$raw_data->id)
                //     ->sum('total');

                $saldoawal = DB::table('m_saldo_awal_piutang')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('customer',$raw_data->id)
                    ->where('status','post')
                    ->sum('total_piutang');

                $penjualan = DB::table('t_faktur')
                    ->select('created_at as tanggal','faktur_code as keterangan','jatuh_tempo','total_sesuai_sj as piutang', DB::raw("'0' as pembayaran"))
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('customer',$raw_data->id);
                    //->get();
                $debetNote = DB::table('t_debet_note')
                    ->select('created_at as tanggal','code as keterangan','date as jatuh_tempo','total as piutang',DB::raw("'0' as pembayaran"))
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('id_person',$raw_data->id);

                $creditNote = DB::table('t_credit_note')
                        ->select('created_at as tanggal','code as keterangan','date as jatuh_tempo',DB::raw("'0' as piutang"),'total as pembayaran')
                        ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                        ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                        ->where('id_person',$raw_data->id);

                $retur = DB::table('t_retur_sj')
                    ->select('created_at as tanggal','rt_code as keterangan','retur_dates', DB::raw("'0' as piutang"),'grand_total as pembayaran')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('customer',$raw_data->id);

                $pembayaran = DB::table('d_pembayaran')
                    ->select('t_pembayaran.created_at as tanggal','d_pembayaran.pembayaran_code as keterangan','t_pembayaran.payment_date as jatuh_tempo', DB::raw("'0' as piutang"),'total as pembayaran')
                    ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                    ->where('t_pembayaran.payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_pembayaran.payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('t_pembayaran.customer',$raw_data->id)
                    ->where('status','approved')
                    ->union($penjualan)
                    ->union($retur)
                    ->union($debetNote)
                    ->union($creditNote)
                    ->orderBy('tanggal')
                    ->get();

                $awal = [];
                $awal[0]["tanggal"] = $tglmulai;
                $awal[0]["keterangan"] = 'SALDO AWAL';
                $awal[0]["jatuh_tempo"] = $tglmulai;
                $awal[0]["piutang"] = $saldoawal;
                $awal[0]["pembayaran"] = 0;

                $data = array_merge($awal, $pembayaran->toArray());
                $data = array_values($data);

                $raw_data->detail = $data;
            }

            $pdf = PDF::loadview('admin.report.laporan-tagihan-detail',['datacustomer' => $datacustomer,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'customer' => $customer]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('kartu-piutang-detail-'.$customer.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function reportSales(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->sales == null) {
            $sales = 'All';
        }else{
            $sales = DB::table('m_user')
            ->where('id', $request->sales)
            ->pluck('name')
            ->first();
        }

        $query = DB::table('m_user');
        $query->select('m_user.id as sales_id','m_user.name');
        $query->join('m_role', 'm_role.id', '=', 'm_user.role');
        $query->where('m_role.name','Sales');
        if ($request->sales != null) {
            $query->where('m_user.id', $request->sales);
        }
        $dataSales = $query->get();

        if ($request->type == 'so') {
            foreach ($dataSales as $key => $raw_data) {
                $dataSO = DB::table('t_sales_order')
                ->select('so_code','name','so_date','status_aprove','grand_total')
                ->join('m_customer', 'm_customer.id', '=', 't_sales_order.customer')
                ->where('so_date','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('so_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('sales',$raw_data->sales_id)
                ->where(function ($query) {
                    $query->where('status_aprove','closed')
                    ->orWhere('status_aprove','approved');
                })
                ->get();

                $raw_data->detail = $dataSO;
                if (count($dataSO) < 1) {
                    unset($dataSales[$key]);
                }
            }

            //dd($dataSales);

            $pdf = PDF::loadview('admin.report.laporan-sales-so',['dataSales' => $dataSales,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'sales' => $sales]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-sales-so-'.$sales.'-'.date('dmyhis').'.pdf');
            }
        }else{
            foreach ($dataSales as $key => $raw_data) {
                $dataPembayaran = DB::table('t_pembayaran')
                ->select('pembayaran_code','name','payment_date','t_pembayaran.status')
                ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
                ->where('payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('sales',$raw_data->sales_id)
                ->where('t_pembayaran.status','approved')
                ->get();

                foreach ($dataPembayaran as $raw_bayar) {
                    $total = DB::table('d_pembayaran')
                    ->where('pembayaran_code',$raw_bayar->pembayaran_code)
                    ->sum('total');

                    $raw_bayar->total_bayar = $total;
                }

                $raw_data->detail = $dataPembayaran;

                if (count($dataPembayaran) < 1) {
                    unset($dataSales[$key]);
                }
            }

            //dd($dataSales);

            $pdf = PDF::loadview('admin.report.laporan-sales-pembayaran',['dataSales' => $dataSales,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'sales' => $sales]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-sales-so-'.$sales.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function faktur($faktur)
    {
        $dataFaktur = DB::table('t_faktur')
        ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
        ->join('t_sales_order', 't_sales_order.so_code', '=', 't_faktur.so_code')
        ->leftjoin('m_user', 'm_user.id', '=', 't_faktur.sales')
        ->select('*','m_user.name as sales','t_sales_order.sending_address','m_customer.name as customer_name','t_sales_order.company_code as header_faktur','t_sales_order.created_at as tgl_so','t_sales_order.description as description_so')
        ->where('faktur_code',$faktur)
        ->first();

        $dataBarang = DB::table('t_faktur')
        ->join('d_surat_jalan', 'd_surat_jalan.sj_code', '=', 't_faktur.sj_code')
        // ->join('d_sales_order', 'd_sales_order.so_code', '=', 't_faktur.so_code')
        ->join('m_produk', 'm_produk.id', '=', 'd_surat_jalan.produk_id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('d_surat_jalan.*','m_produk.name as produk','m_satuan_unit.code as code_unit','m_produk.id as produk_id','t_faktur.so_code')
        ->where('faktur_code',$faktur)
        ->get();
        // dd($dataBarang);

        foreach ($dataBarang as $key => $value) {
            $getHargaItemSo = DB::table('d_sales_order')->where('so_code',$value->so_code)
            ->where('produk',$value->produk_id)->first();

            $value->price_so = $getHargaItemSo->total;
            $value->qty_so = $getHargaItemSo->qty;
            $value->price_total_so = $getHargaItemSo->customer_price;
            $value->diskon_potongan = $getHargaItemSo->diskon_potongan;
            $value->diskon_persen = $getHargaItemSo->diskon_persen;
            $value->markup = $getHargaItemSo->markup;
        }

        $sumDso =  DB::table('d_sales_order')->where('so_code',$dataFaktur->so_code)->sum('total');
        $sumQtyDso =  DB::table('d_sales_order')->where('so_code',$dataFaktur->so_code)->sum('qty');
        $sumQtyDsj =  DB::table('d_surat_jalan')->where('sj_code',$dataBarang[0]->sj_code)->sum('qty_delivery');

        $jmlahDiskonHeader = $sumDso - ($dataFaktur->grand_total-$dataFaktur->amount_ppn);

        $diskonHeaderPerItem = $jmlahDiskonHeader / $sumQtyDso;

        $diskonHeaderFaktur = $sumQtyDsj * $diskonHeaderPerItem;

        //update flag-print-out
        DB::table('t_faktur')
        ->where('faktur_code',$faktur)
        ->update([
            'print' => true
        ]);

        MPrintLogModel::create([
            'code' => $faktur,
            'user' => auth()->user()->id,
            'type' => 'faktur',
        ]);

        $company = DB::table('m_company_profile')->where('id', $dataFaktur->header_faktur)->first();

        // dd($company, $dataFaktur);

        // return view('admin.report.faktur',['company' => $company,'dataFaktur' => $dataFaktur,'dataBarang' => $dataBarang, 'diskonHeaderFaktur' => $diskonHeaderFaktur]);
        $pdf = PDF::loadview('admin.report.faktur',['company' => $company,'dataFaktur' => $dataFaktur,'dataBarang' => $dataBarang, 'diskonHeaderFaktur' => $diskonHeaderFaktur]);
        //$paper = array(0,0,595.28,420.99);
        $paper = array(0,0,684,396);
        return $pdf->setPaper($paper,'potrait')->stream();
    }

    public function kuitansi($kuitansi)
    {
        $dataFaktur = DB::table('t_pembayaran')
                        ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
                        ->select('*',"t_pembayaran.company_code as header_faktur",'m_customer.name as customer_name','t_pembayaran.created_at as tgl_bayar')
                        ->where('t_pembayaran.pembayaran_code', $kuitansi)
                        ->first();
        $detailBayar = DB::table('d_pembayaran')
                        ->where("pembayaran_code",$kuitansi)
                        ->select("d_pembayaran.faktur_code",'d_pembayaran.total')
                        ->get();

        // get coa rek
        $bank =  $nameOf = $accountNumber = null;

        if($dataFaktur->bank != null){
            $destinationRek = DB::table('m_coa')->where('id', $dataFaktur->rekening_tujuan)->first();

            if (strpos($destinationRek->desc, '-') !== false) {
                list($bank, $nameOf, $accountNumber) = explode("-", $destinationRek->desc);
                // dd(trim($bank), trim($nameOf), trim($accountNumber));
            }
        }

        // $dataBarang = DB::table('t_faktur')
        // ->join('d_surat_jalan', 'd_surat_jalan.sj_code', '=', 't_faktur.sj_code')
        // // ->join('d_sales_order', 'd_sales_order.so_code', '=', 't_faktur.so_code')
        // ->join('m_produk', 'm_produk.id', '=', 'd_surat_jalan.produk_id')
        // ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        // ->select('d_surat_jalan.*','m_produk.name as produk','m_satuan_unit.code as code_unit','m_produk.id as produk_id','t_faktur.so_code')
        // ->where('faktur_code',$kuitansi)
        // ->get();
        // // dd($dataBarang);

        // foreach ($dataBarang as $key => $value) {
        //     $getHargaItemSo = DB::table('d_sales_order')->where('so_code',$value->so_code)
        //     ->where('produk',$value->produk_id)->first();

        //     $value->price_so = $getHargaItemSo->total;
        //     $value->qty_so = $getHargaItemSo->qty;
        //     $value->price_total_so = $getHargaItemSo->customer_price;
        //     $value->diskon_potongan = $getHargaItemSo->diskon_potongan;
        //     $value->diskon_persen = $getHargaItemSo->diskon_persen;
        //     $value->markup = $getHargaItemSo->markup;
        // }

        // $sumDso =  DB::table('d_sales_order')->where('so_code',$dataFaktur->so_code)->sum('total');
        // $sumQtyDso =  DB::table('d_sales_order')->where('so_code',$dataFaktur->so_code)->sum('qty');
        // $sumQtyDsj =  DB::table('d_surat_jalan')->where('sj_code',$dataBarang[0]->sj_code)->sum('qty_delivery');

        // $jmlahDiskonHeader = $sumDso - ($dataFaktur->grand_total-$dataFaktur->amount_ppn);

        // $diskonHeaderPerItem = $jmlahDiskonHeader / $sumQtyDso;

        // $diskonHeaderFaktur = $sumQtyDsj * $diskonHeaderPerItem;

        //update flag-print-out
        DB::table('t_pembayaran')
        ->where('pembayaran_code',$kuitansi)
        ->update([
            'print' => true
        ]);

        MPrintLogModel::create([
            'code' => $kuitansi,
            'user' => auth()->user()->id,
            'type' => 'kuitansi',
        ]);


        $company = DB::table('m_company_profile')->where('id', $dataFaktur->header_faktur)->first();

        // dd($company, $dataFaktur);

        // return view('admin.report.faktur',['company' => $company,'dataFaktur' => $dataFaktur,'dataBarang' => $dataBarang, 'diskonHeaderFaktur' => $diskonHeaderFaktur]);
        $pdf = PDF::loadview('admin.report.kuitansi',['company' => $company,'dataFaktur' => $dataFaktur, "bank" => $bank, "nameOf" => $nameOf, "accountNumber" => $accountNumber, "detailBayar" => $detailBayar]);
        $paper = array(0,0,684,396);
        return $pdf->setPaper($paper,'potrait')->stream();
    }

    public function purchaseInvoice($pi_code)
    {
        $dataPI = DB::table('t_purchase_invoice')
            ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->join('t_purchase_order', 't_purchase_order.po_code', '=', 't_purchase_invoice.po_code')
            ->select('*','t_purchase_invoice.jatuh_tempo as jatuh_tempo_pi')
            ->where('pi_code',$pi_code)
            ->first();

        $dataBarang = DB::table('t_purchase_invoice')
            ->join('d_surat_jalan_masuk', 'd_surat_jalan_masuk.sj_masuk_code', '=', 't_purchase_invoice.sj_masuk_code')
            ->join('m_produk', 'm_produk.id', '=', 'd_surat_jalan_masuk.produk_id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
            ->select('d_surat_jalan_masuk.*','m_produk.name as produk','m_satuan_unit.code as code_unit','m_produk.id as produk_id','t_purchase_invoice.po_code')
            ->where('pi_code',$pi_code)
            ->get();
        // dd($dataBarang);

        foreach ($dataBarang as $key => $value) {
            $getHargaItemPo = DB::table('d_purchase_order')->where('po_code',$value->po_code)
            ->where('produk',$value->produk_id)
            ->first();

            $value->price_po = $getHargaItemPo->total_neto;
            $value->qty_po = $getHargaItemPo->qty;
            $value->price_total_po = $getHargaItemPo->price;
            $value->diskon_potongan = $getHargaItemPo->diskon_potongan;
            $value->diskon_persen = $getHargaItemPo->diskon_persen;
            //$value->markup = $getHargaItemPo->markup;
        }

        $sumDpo =  DB::table('d_purchase_order')->where('po_code',$dataPI->po_code)->sum('total_neto');
        $sumQtyDpo =  DB::table('d_purchase_order')->where('po_code',$dataPI->po_code)->sum('qty');
        $sumQtyDsjm =  DB::table('d_surat_jalan_masuk')->where('sj_masuk_code',$dataBarang[0]->sj_masuk_code)->sum('qty');

        $jmlahDiskonHeader = $sumDpo - $dataPI->grand_total;

        $diskonHeaderPerItem = $jmlahDiskonHeader / $sumQtyDpo;

        $diskonHeaderPI = $sumQtyDsjm * $diskonHeaderPerItem;

        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.purchase-invoice',['company' => $company,'dataPI' => $dataPI,'dataBarang' => $dataBarang, 'diskonHeaderPI' => $diskonHeaderPI]);
        $customPaper = array(0,0,21.84,13.97);
        ////$pdf->setPaper($customPaper);
        return $pdf->stream();
    }

    public function purchaseInvoicePIFA($pi_code)
    {
        $dataPI = DB::table('t_purchase_invoice')
            ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_invoice.supplier')
            ->join('t_fixed_asset_po', 't_fixed_asset_po.po_code', '=', 't_purchase_invoice.po_code')
            ->select('*','t_purchase_invoice.jatuh_tempo as jatuh_tempo_pi')
            ->where('pi_code',$pi_code)
            ->first();

        $dataBarang = DB::table('t_purchase_invoice')
            ->join('d_fixed_asset_pd', 'd_fixed_asset_pd.sj_masuk_code', '=', 't_purchase_invoice.sj_masuk_code')
            ->join('m_produk', 'm_produk.id', '=', 'd_fixed_asset_pd.produk_id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
            ->select('d_fixed_asset_pd.*','m_produk.name as produk','m_satuan_unit.code as code_unit',
                     'm_produk.id as produk_id','t_purchase_invoice.po_code','t_purchase_invoice.total as total_barang')
            ->where('pi_code',$pi_code)
            // ->groupBy('m_produk.name')
            // ->where('')
            ->get();
        // dd($dataBarang);

        foreach ($dataBarang as $key => $value) {
            $getHargaItemPo = DB::table('d_fixed_asset_po')->where('po_code',$value->po_code)
            ->where('produk',$value->produk_id)
            ->first();

            $value->price_po = $getHargaItemPo->total_neto;
            $value->qty_po = $getHargaItemPo->qty;
            $value->price_total_po = $getHargaItemPo->price;
            $value->diskon_potongan = $getHargaItemPo->diskon_potongan;
            $value->diskon_persen = $getHargaItemPo->diskon_persen;
            //$value->markup = $getHargaItemPo->markup;
        }

        $sumDpo =  DB::table('d_fixed_asset_po')->where('po_code',$dataPI->po_code)->sum('total_neto');
        $sumQtyDpo =  DB::table('d_fixed_asset_po')->where('po_code',$dataPI->po_code)->sum('save_qty');
        $sumQtyDsjm =  DB::table('d_fixed_asset_pd')->where('sj_masuk_code',$dataBarang[0]->sj_masuk_code)->sum('qty');

        $jmlahDiskonHeader = $sumDpo - $dataPI->total;

        $diskonHeaderPerItem = $jmlahDiskonHeader / $sumQtyDpo;

        $diskonHeaderPI = $sumQtyDsjm * $diskonHeaderPerItem;

        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.purchase-invoice-pifa',['company' => $company,'dataPI' => $dataPI,'dataBarang' => $dataBarang, 'diskonHeaderPI' => $diskonHeaderPI]);
        $customPaper = array(0,0,21.84,13.97);
        ////$pdf->setPaper($customPaper);
        return $pdf->stream();
    }

    public function reportStok(request $request, $type)
    {
        ini_set('memory_limit', '512MB');
        ini_set('max_execution_time', 3000);
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);


        $type_barang = $request->type_barang;
        // dd($type_barang);

        if ($request->type == 'summary'){
            $this->validate($request, [
                'gudang' => 'required',
            ]);

            $dataGudang = DB::table('m_gudang')
            ->where('id',$request->gudang)
            ->first();

            // $dataProduk = DB::table('m_produk')
            //     ->get();

            $query0 = DB::table('m_stok_produk');
            $query0->join('m_produk','m_produk.code','=','m_stok_produk.produk_code');
            $query0->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id');
            $query0->select('m_produk.code','m_produk.name','m_produk.type_barang',DB::raw('SUM(m_stok_produk.stok) as stok'),'m_satuan_unit.code as code_unit');
            $query0->groupBy('m_produk.id','m_produk.code','m_satuan_unit.code','m_produk.name','m_produk.type_barang');
            $query0->where('m_stok_produk.gudang',$request->gudang);
            if ($type_barang != '') {
                $query0->where('m_produk.type_barang',$type_barang);
            }
            //->where('m_stok_produk.stok','!=', 0)
            $query0->where(function ($query) {
                $query->where('m_stok_produk.stok','!=', 0)
                ->orWhere('m_stok_produk.balance','!=',0);
            });
            $query0->orderBy('m_produk.name','asc');

            $dataProduk = $query0->get();

            //dd($dataProduk);

            foreach ($dataProduk as $raw_data) {
                $data_stok = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    ->where('m_stok_produk.created_at','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('m_stok_produk.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->get();

                $total_stok_periode = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    ->where('m_stok_produk.created_at','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('m_stok_produk.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->sum('stok');

                $jml_data_stok = count($data_stok);

                $date = '01-'.date('m-Y', strtotime($tglmulai));
                $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

                $stok_awal = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    ->where('type', 'closing')
                    ->whereMonth('periode',date('m', strtotime($date_last_month)))
                    ->whereYear('periode',date('Y', strtotime($date_last_month)))
                    ->sum('balance');

                $stok_bulan = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    ->whereMonth('created_at',date('m', strtotime($date_last_month)))
                    ->whereYear('created_at',date('Y', strtotime($date_last_month)))
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('stok');

                $raw_data->stok_awal = $stok_awal+$stok_bulan;
                $raw_data->stok_akhir = $stok_awal+ $stok_bulan + $total_stok_periode;

                $stokMasuk = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    ->where('type', 'in')
                    ->where('m_stok_produk.created_at','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('m_stok_produk.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('m_stok_produk.stok');
                $raw_data->stok_masuk = $stokMasuk;

                $stokKeluar = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    ->where('type', 'out')
                    ->where('m_stok_produk.created_at','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('m_stok_produk.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('m_stok_produk.stok');
                $raw_data->stok_keluar = $stokKeluar*-1;
            }

            //dd($dataProduk);

            $pdf = PDF::loadview('admin.report.stok-gudang',['dataGudang' => $dataGudang,'dataProduk' => $dataProduk,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('Stok '.$dataGudang->name.' '.date('dmyhis').'.pdf');
            }

        }elseif ($request->type == 'detail'){
            $this->validate($request, [
                'gudang' => 'required',
                'barang' => 'required',
            ]);

            $query = DB::table('m_stok_produk');
            $query->join('m_produk', 'm_produk.code', '=', 'm_stok_produk.produk_code');
            $query->join('m_gudang', 'm_gudang.id', '=', 'm_stok_produk.gudang');
            $query->select('produk_code', 'm_produk.name as produk','m_produk.id as produk_id','m_gudang.name as gudang_name','stok_awal');
            $query->where('gudang',$request->gudang);
            $query->where('produk_code',$request->barang);
            $detailBarang = $query->first();

            // dd($detailBarang);

            $sum = DB::table('m_stok_produk')
                ->where('produk_code',$request->barang)
                ->where('gudang',$request->gudang)
                ->sum('stok');

            $dataGudang = DB::table('m_stok_produk')
                ->where('produk_code',$request->barang)
                ->where('gudang',$request->gudang)
                ->where('type','!=', 'closing')
                ->where('created_at','>=', date('Y-m-d', strtotime($tglmulai)))
                ->where('created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->orderBy('created_at')
                ->get();

            foreach ($dataGudang as $raw_data) {
                if ($raw_data->tipe_transaksi == 'Purchase Delivery') {
                    $data_person = DB::table('m_supplier')
                        ->where('id',$raw_data->person)
                        ->first();

                    $raw_data->person_name = $data_person->name;
                }
                elseif($raw_data->tipe_transaksi == 'Surat Jalan'){
                    $data_person = DB::table('m_customer')
                        ->where('id',$raw_data->person)
                        ->first();

                    $raw_data->person_name = $data_person->name;
                }
                else{
                    $raw_data->person_name = '';
                }
            }

            // dd($dataGudang);

            $date = '01-'.date('m-Y', strtotime($tglmulai));
            $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

            $stok_awal = 0;
            $stok_awal = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $request->barang)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    ->where('type', 'closing')
                    ->whereMonth('periode',date('m', strtotime($date_last_month)))
                    ->whereYear('periode',date('Y', strtotime($date_last_month)))
                    ->sum('balance');

            //dd($dataGudang);

            $pdf = PDF::loadview('admin.report.stok-barang-gudang',['dataGudang' => $dataGudang,'detailBarang' => $detailBarang,'sum' => $sum,'stok_awal' => $stok_awal,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('Stok '.$detailBarang->produk.' '.date('dmyhis').'.pdf');
            }
        }
    }

    public function masterKotaKab($type)
    {
        $dataKotaKab = DB::table('m_kota_kab')->join('m_provinsi', 'm_provinsi.id', '=', 'm_kota_kab.provinsi')->select('m_kota_kab.*', 'm_provinsi.id as id_provinsi', 'm_provinsi.name as provinsi')->orderBy('code')->get();

        $pdf = PDF::loadview('admin/report/master/kota-kab',['dataKotaKab' => $dataKotaKab]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-kota.pdf');

    }

    public function masterKecamatan(Request $request)
    {
        $dataKecamatan = DB::table('m_kecamatan')
        ->join('m_kota_kab', 'm_kota_kab.id', '=', 'm_kecamatan.kota_kab')
        ->join('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
        ->select('m_kecamatan.*', 'm_kota_kab.id as id_kota_kab', 'm_kota_kab.name as kota_kab','m_kota_kab.type')
        ->where('m_kota_kab.id',$request->kota)
        ->where('m_provinsi.id',$request->provinsi)
        ->orderBy('code')
        ->get();

        $pdf = PDF::loadview('admin/report/master/kecamatan',['dataKecamatan' => $dataKecamatan]);

        if( $request->type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-kecamatan.pdf');
    }

    public function masterKelurahan(Request $request)
    {
        $query = DB::table('m_kelurahan_desa')
        ->join('m_kecamatan','m_kecamatan.id', '=', 'm_kelurahan_desa.kecamatan')
        ->join('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->join('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
        ->select('m_kelurahan_desa.*','m_kecamatan.id as id_kecamatan', 'm_kecamatan.name as kecamatan','m_kota_kab.type as type_kota','m_kota_kab.name as kota','m_provinsi.name as provinsi')
        ->where('m_kota_kab.id',$request->kota)
        ->where('m_provinsi.id',$request->provinsi);
        if( $request->kecamatan != null ){
            $query->where('m_kecamatan.id',$request->kecamatan);
        }
        $dataKelurahan = $query->orderBy('m_kelurahan_desa.code')->get();

        $pdf = PDF::loadview('admin/report/master/kelurahan',['dataKelurahan' => $dataKelurahan]);

        if( $request->type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-kelurahan.pdf');
    }

    public function masterWilayahSales($type)
    {
        $dataWilayahSales = DB::table('m_wilayah_sales')->get();

        $pdf = PDF::loadview('admin/report/master/wilayahsales',['dataWilayahSales' => $dataWilayahSales]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-wilayah-sales.pdf');
    }

    public function sales($type)
    {
        $roleSales = DB::table('m_role')->where('name', 'Sales')->first();

        $dataSemuaSales = DB::table('m_user')->where('role', '=', $roleSales->id)->get();

        $pdf = PDF::loadview('admin/report/master/semuasales',['dataSemuaSales' => $dataSemuaSales]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-sales.pdf');
    }
    public function masterTargetSales($type)
    {
        $dataTargetSales = DB::table('m_target_sales')->join('m_user','m_user.id', '=',
        'm_target_sales.sales')->select('m_target_sales.*','m_user.id as id_user','m_user.name as sales_name')->get();
        //dd($dataTargetSales);
        $pdf = PDF::loadview('admin/report/master/targetsales',['dataTargetSales' => $dataTargetSales]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-target-sales.pdf');
    }

    public function masterCustomer(Request $request)
    {
        $flagKecamatan = 0;
        $query = MCustomerModel::select('m_customer.*','m_kota_kab.name as kota','m_kecamatan.name as kecamatan',
        'm_kelurahan_desa.name as kelurahan','m_wilayah_sales.name as wilayah','m_provinsi.name as provinsi')
        ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
        ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->leftjoin('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
        ->where('m_kota_kab.id',$request->kota)
        ->where('m_provinsi.id',$request->provinsi);

        if( $request->kecamatan != null ){
            $query->where('m_kecamatan.id',$request->kecamatan);
            $flagKecamatan = 1;
        }
        $dataCustomer = $query->orderBy('m_customer.code')->get();
        $pdf =  PDF::loadview('admin/report/master/customer',['dataCustomer' => $dataCustomer,'flagKecamatan' => $flagKecamatan]);
        $pdf->setPaper('A4', 'landscape');

        if( $request->type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-customer.pdf');
    }

    public function masterSupplier(Request $request)
    {
        $flagKecamatan = 0;
        $query = MSupplierModel::select('m_supplier.*','m_kota_kab.name as kota','m_kecamatan.name as kecamatan',
        'm_kelurahan_desa.name as kelurahan','m_provinsi.name as provinsi')
        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_supplier.main_kelurahan')
        ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->leftjoin('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
        ->where('m_kota_kab.id',$request->kota)
        ->where('m_provinsi.id',$request->provinsi);

        if( $request->kecamatan != null ){
            $query->where('m_kecamatan.id',$request->kecamatan);
            $flagKecamatan = 1;
        }
        $dataSupplier = $query->orderBy('m_supplier.code')->get();
        $pdf =  PDF::loadview('admin/report/master/supplier',['dataSupplier' => $dataSupplier,'flagKecamatan' => $flagKecamatan]);
        $pdf->setPaper('A4', 'landscape');

        if( $request->type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-supplier.pdf');
    }

    public function userrole($type)
    {
        $dataRole = MRoleModel::all();

        $pdf =  PDF::loadview('admin/report/master/role',['dataRole' => $dataRole]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-role.pdf');
    }

    public function masterJenisBarang($type)
    {
        $dataJenisBarang = DB::table('m_jenis_produk')->get();
        //dd($dataJenisBarang);
        $pdf = PDF::loadview('admin/report/master/jenisbarang',['dataJenisBarang' => $dataJenisBarang]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-jenis-barang.pdf');
    }

    public function masterBahanBarang($type)
    {
        $dataBahanBarang = DB::table('m_bahan_produk')->get();

        $pdf = PDF::loadview('admin/report/master/bahanbarang',['dataBahanBarang' => $dataBahanBarang]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-bahan-barang.pdf');
    }

    public function masterMerekBarang($type)
    {
        $dataMerekBarang = DB::table('m_merek_produk')->get();
        //dd($dataMerekBarang);
        $pdf =  PDF::loadview('admin/report/master/merekbarang',['dataMerekBarang' => $dataMerekBarang]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-merek-barang.pdf');

    }

    public function barang($type)
    {
        $dataBarang = DB::table('m_produk')
        ->join('m_jenis_produk', 'm_jenis_produk.id', '=', 'm_produk.jenis')
        ->join('m_bahan_produk', 'm_bahan_produk.id', '=', 'm_produk.bahan')
        ->join('m_merek_produk', 'm_merek_produk.id', '=', 'm_produk.merek')
        ->select('m_produk.id','m_produk.code','m_produk.name','m_produk.lebar','m_produk.panjang','m_produk.tinggi','m_produk.berat','m_jenis_produk.name as jenis','m_bahan_produk.name as bahan','m_merek_produk.name as merek')
        ->get();
        $pdf =  PDF::loadview('admin/report/master/barang',['dataBarang' => $dataBarang]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-barang.pdf');
    }

    public function hargabarang($type)
    {
        $dataHarga = MHargaProdukModel::with('produkRelation')->get();
        $pdf =  PDF::loadview('admin/report/master/harga-barang',['dataHarga' => $dataHarga]);

        if( $type == 'view' ){
            return $pdf->stream();
        }
        return $pdf->download('master-harga-barang.pdf');
    }

    public function downPayment($id)
    {
        $dataDp = DB::table('t_down_payment')
        ->join('m_customer','m_customer.id','=','t_down_payment.customer')
        ->join('d_down_payment','d_down_payment.dp_code','=','t_down_payment.dp_code')
        ->select('t_down_payment.*','m_customer.name as customer','m_customer.id as customer_id','d_down_payment.*','d_down_payment.created_at as tgl_detail_dp')
        ->where('t_down_payment.id',$id)
        ->get();

        $pdf =  PDF::loadview('admin/report/dp',['dataDp' => $dataDp]);

        return $pdf->stream();
    }

    public function downPaymentPurchase($id)
    {
        $dataDp = DB::table('t_pi_down_payment')
                ->join('m_supplier','m_supplier.id','=','t_pi_down_payment.supplier')
                ->join('d_pi_down_payment','d_pi_down_payment.dp_code','=','t_pi_down_payment.dp_code')
                ->select('t_pi_down_payment.*','m_supplier.name as supplier','m_supplier.id as supplier_id','d_pi_down_payment.*','d_pi_down_payment.created_at as tgl_detail_dp')
                ->where('t_pi_down_payment.id',$id)
                ->get();

        // dd($dataDp);
        $pdf =  PDF::loadview('admin/report/dppurchase',['dataDp' => $dataDp]);

        return $pdf->stream();
    }

    public function reportDp(Request $request,$type)
    {

        //priode
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);
        // dd($request->all(),$tglsampai);

        //$customer
        ( $request->customer == null) ? $customer = 'All' : $customer = DB::table('m_customer')->where('id', $request->customer)->pluck('name')->first();

        //$dp_code
        ($request->dp == null ) ? $dp_code = 'ALL' : $dp_code = $request->dp;

        //status
        ($request->status == null ) ? $status = 'ALL' : $status = $request->status;

        // dd($request->all(),$request->status);

        if ($request->type == 'summary') {

            $query = DB::table('t_down_payment');
            $query->select(DB::raw("DATE(t_down_payment.dp_date) as tgl"));
            $query->join('m_customer', 'm_customer.id', '=', 't_down_payment.customer');
            $query->where('dp_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('dp_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if($request->customer != null) {$query->where('customer', $request->customer);}
            if($request->dp != null ){$query->where('t_down_payment.dp_code',$request->dp);}
            if($request->status == 'in process') {$query->where('t_down_payment.status','in process');}
            if($request->status == 'post') {$query->where('t_down_payment.status','post');}
            if($request->status == 'close') {$query->where('t_down_payment.status','close');}

            $query->groupBy('tgl');
            $dataDp = $query->get(); //groupBy tanggal

            foreach ($dataDp as $dp) {
                //get-customer
                $query = DB::table('t_down_payment');
                $query->select('customer','m_customer.name as customer_name');
                $query->join('m_customer', 'm_customer.id', '=', 't_down_payment.customer');
                $query->where('t_down_payment.dp_date','>=',date('Y-m-d', strtotime($dp->tgl)));
                $query->where('t_down_payment.dp_date','<',date('Y-m-d', strtotime($dp->tgl. ' + 1 days')));

                if($request->customer != null) {$query->where('customer', $request->customer);}
                if($request->dp != null ){$query->where('t_down_payment.dp_code',$request->dp);}
                if($request->status == 'in process') {$query->where('t_down_payment.status','in process');}
                if($request->status == 'post') {$query->where('t_down_payment.status','post');}
                if($request->status == 'close') {$query->where('t_down_payment.status','close');}

                $query->groupBy('customer','m_customer.name');

                $dataCustomer = $query->get();
                $dp->data_customer = $dataCustomer;

                foreach ($dataCustomer as $customerDp) {
                    //get-data-dp
                    $query = DB::table('t_down_payment');
                    $query->select('t_down_payment.*');
                    $query->where('customer',$customerDp->customer);
                    $query->where('t_down_payment.dp_date','>=',date('Y-m-d', strtotime($dp->tgl)));
                    $query->where('t_down_payment.dp_date','<',date('Y-m-d', strtotime($dp->tgl. ' + 1 days')));

                    if($request->dp != null ){$query->where('t_down_payment.dp_code',$request->dp);}
                    if($request->status == 'in process') {$query->where('t_down_payment.status','in process');}
                    if($request->status == 'post') {$query->where('t_down_payment.status','post');}
                    if($request->status == 'close') {$query->where('t_down_payment.status','close');}

                    $dataDpHeader = $query->get();
                    $customerDp->dataDpHeader = $dataDpHeader;
                }
            }
            // dd($dataDp);

            $pdf = PDF::loadview('admin.report.laporan-dp-summary',[
                'dataDp' => $dataDp,
                'dp_code' => $dp_code,
                'tglmulai' => $tglmulai,
                'tglsampai' => $tglsampai,
                'customer' => $customer,
                'status' => $status,
            ]);

        }else{
            //detail
            // dd($status);
            $query = DB::table('t_down_payment');
            $query->select(DB::raw("DATE(t_down_payment.dp_date) as tgl"));
            $query->join('m_customer', 'm_customer.id', '=', 't_down_payment.customer');
            $query->where('dp_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('dp_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if($request->customer != null) {$query->where('customer', $request->customer);}
            if($request->dp != null ){$query->where('t_down_payment.dp_code',$request->dp);}
            if($request->status == 'in process') {$query->where('t_down_payment.status','in process');}
            if($request->status == 'post') {$query->where('t_down_payment.status','post');}
            if($request->status == 'close') {$query->where('t_down_payment.status','close');}

            $query->groupBy('tgl');
            $dataDp = $query->get(); //groupBy tanggal

            foreach ($dataDp as $dp) {
                //get-customer
                $query = DB::table('t_down_payment');
                $query->select('customer','m_customer.name as customer_name');
                $query->join('m_customer', 'm_customer.id', '=', 't_down_payment.customer');
                $query->where('t_down_payment.dp_date','>=',date('Y-m-d', strtotime($dp->tgl)));
                $query->where('t_down_payment.dp_date','<',date('Y-m-d', strtotime($dp->tgl. ' + 1 days')));

                if($request->customer != null) {$query->where('customer', $request->customer);}
                if($request->dp != null ){$query->where('t_down_payment.dp_code',$request->dp);}
                if($request->status == 'in process') {$query->where('t_down_payment.status','in process');}
                if($request->status == 'post') {$query->where('t_down_payment.status','post');}
                if($request->status == 'close') {$query->where('t_down_payment.status','close');}

                $query->groupBy('customer','m_customer.name');

                $dataCustomer = $query->get();
                $dp->data_customer = $dataCustomer;

                foreach ($dataCustomer as $customerDetail) {
                    $query = DB::table('t_down_payment');
                    $query->select('t_down_payment.dp_code','status');
                    $query->where('customer',$customerDetail->customer);
                    $query->where('t_down_payment.dp_date','>=',date('Y-m-d', strtotime($dp->tgl)));
                    $query->where('t_down_payment.dp_date','<',date('Y-m-d', strtotime($dp->tgl. ' + 1 days')));

                    if($request->dp != null ){$query->where('t_down_payment.dp_code',$request->dp);}
                    if($request->status == 'in process') {$query->where('t_down_payment.status','in process');}
                    if($request->status == 'post') {$query->where('t_down_payment.status','post');}
                    if($request->status == 'close') {$query->where('t_down_payment.status','close');}

                    $query->groupBy('t_down_payment.dp_code','status');

                    $dataDPH = $query->get();
                    $customerDetail->data_dpcode = $dataDPH;

                    foreach ($dataDPH as $dph) {
                        $query = DB::table('d_down_payment');
                        $query->select('*');
                        $query->where('d_down_payment.dp_code',$dph->dp_code);

                        $detailDPh = $query->get();
                        $dph->detail = $detailDPh;
                    }
                }
            }
            // dd($dataDp,'detail');

            $pdf = PDF::loadview('admin.report.laporan-dp-detail',[
                'dataDp' => $dataDp,
                'dp_code' => $dp_code,
                'tglmulai' => $tglmulai,
                'tglsampai' => $tglsampai,
                'customer' => $customer,
                'status' => $status,
            ]);
        }

        $pdf->setPaper('legal', 'landscape');
        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-so-'.$customer.'-'.date('dmyhis').'.pdf');
        }
    }

    public function reportDpPurchase(Request $request,$type)
    {

        //priode
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);
        // dd($request->all(),$tglsampai);

        //$supplier
        ( $request->supplier == null) ? $supplier = 'All' : $supplier = DB::table('m_supplier')->where('id', $request->supplier)->pluck('name')->first();

        //$dp_code
        ($request->dp == null ) ? $dp_code = 'ALL' : $dp_code = $request->dp;

        //status
        ($request->status == null ) ? $status = 'ALL' : $status = $request->status;

        // dd($request->all(),$request->status);

        if ($request->type == 'summary') {

            $query = DB::table('t_pi_down_payment');
            $query->select(DB::raw("DATE(t_pi_down_payment.dp_date) as tgl"));
            $query->join('m_supplier', 'm_supplier.id', '=', 't_pi_down_payment.supplier');
            $query->where('dp_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('dp_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if($request->supplier != null) {$query->where('supplier', $request->supplier);}
            if($request->dp != null ){$query->where('t_pi_down_payment.dp_code',$request->dp);}
            if($request->status == 'in process') {$query->where('t_pi_down_payment.status','in process');}
            if($request->status == 'post') {$query->where('t_pi_down_payment.status','post');}
            if($request->status == 'close') {$query->where('t_pi_down_payment.status','close');}

            $query->groupBy('tgl');
            $dataDp = $query->get(); //groupBy tanggal

            foreach ($dataDp as $dp) {
                //get-supplier
                $query = DB::table('t_pi_down_payment');
                $query->select('supplier','m_supplier.name as supplier_name');
                $query->join('m_supplier', 'm_supplier.id', '=', 't_pi_down_payment.supplier');
                $query->where('t_pi_down_payment.dp_date','>=',date('Y-m-d', strtotime($dp->tgl)));
                $query->where('t_pi_down_payment.dp_date','<',date('Y-m-d', strtotime($dp->tgl. ' + 1 days')));

                if($request->supplier != null) {$query->where('supplier', $request->supplier);}
                if($request->dp != null ){$query->where('t_pi_down_payment.dp_code',$request->dp);}
                if($request->status == 'in process') {$query->where('t_pi_down_payment.status','in process');}
                if($request->status == 'post') {$query->where('t_pi_down_payment.status','post');}
                if($request->status == 'close') {$query->where('t_pi_down_payment.status','close');}

                $query->groupBy('supplier','m_supplier.name');

                $dataSupplier = $query->get();
                $dp->data_supplier = $dataSupplier;

                foreach ($dataSupplier as $supplierDp) {
                    //get-data-dp
                    $query = DB::table('t_pi_down_payment');
                    $query->select('t_pi_down_payment.*');
                    $query->where('supplier',$supplierDp->supplier);
                    $query->where('t_pi_down_payment.dp_date','>=',date('Y-m-d', strtotime($dp->tgl)));
                    $query->where('t_pi_down_payment.dp_date','<',date('Y-m-d', strtotime($dp->tgl. ' + 1 days')));

                    if($request->dp != null ){$query->where('t_pi_down_payment.dp_code',$request->dp);}
                    if($request->status == 'in process') {$query->where('t_pi_down_payment.status','in process');}
                    if($request->status == 'post') {$query->where('t_pi_down_payment.status','post');}
                    if($request->status == 'close') {$query->where('t_pi_down_payment.status','close');}

                    $dataDpHeader = $query->get();
                    $supplierDp->dataDpHeader = $dataDpHeader;
                }
            }
            // dd($dataDp);

            $pdf = PDF::loadview('admin.report.laporan-dpPurchase-summary',[
                'dataDp' => $dataDp,
                'dp_code' => $dp_code,
                'tglmulai' => $tglmulai,
                'tglsampai' => $tglsampai,
                'supplier' => $supplier,
                'status' => $status,
            ]);

        }else{
            //detail
            // dd($status);
            $query = DB::table('t_pi_down_payment');
            $query->select(DB::raw("DATE(t_pi_down_payment.dp_date) as tgl"));
            $query->join('m_supplier', 'm_supplier.id', '=', 't_pi_down_payment.supplier');
            $query->where('dp_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('dp_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if($request->supplier != null) {$query->where('supplier', $request->supplier);}
            if($request->dp != null ){$query->where('t_pi_down_payment.dp_code',$request->dp);}
            if($request->status == 'in process') {$query->where('t_pi_down_payment.status','in process');}
            if($request->status == 'post') {$query->where('t_pi_down_payment.status','post');}
            if($request->status == 'close') {$query->where('t_pi_down_payment.status','close');}

            $query->groupBy('tgl');
            $dataDp = $query->get(); //groupBy tanggal

            foreach ($dataDp as $dp) {
                //get-supplier
                $query = DB::table('t_pi_down_payment');
                $query->select('supplier','m_supplier.name as supplier_name');
                $query->join('m_supplier', 'm_supplier.id', '=', 't_pi_down_payment.supplier');
                $query->where('t_pi_down_payment.dp_date','>=',date('Y-m-d', strtotime($dp->tgl)));
                $query->where('t_pi_down_payment.dp_date','<',date('Y-m-d', strtotime($dp->tgl. ' + 1 days')));

                if($request->supplier != null) {$query->where('supplier', $request->supplier);}
                if($request->dp != null ){$query->where('t_pi_down_payment.dp_code',$request->dp);}
                if($request->status == 'in process') {$query->where('t_pi_down_payment.status','in process');}
                if($request->status == 'post') {$query->where('t_pi_down_payment.status','post');}
                if($request->status == 'close') {$query->where('t_pi_down_payment.status','close');}

                $query->groupBy('supplier','m_supplier.name');

                $dataSupplier = $query->get();
                $dp->data_supplier = $dataSupplier;

                foreach ($dataSupplier as $supplierDetail) {
                    $query = DB::table('t_pi_down_payment');
                    $query->select('t_pi_down_payment.dp_code','status');
                    $query->where('supplier',$supplierDetail->supplier);
                    $query->where('t_pi_down_payment.dp_date','>=',date('Y-m-d', strtotime($dp->tgl)));
                    $query->where('t_pi_down_payment.dp_date','<',date('Y-m-d', strtotime($dp->tgl. ' + 1 days')));

                    if($request->dp != null ){$query->where('t_pi_down_payment.dp_code',$request->dp);}
                    if($request->status == 'in process') {$query->where('t_pi_down_payment.status','in process');}
                    if($request->status == 'post') {$query->where('t_pi_down_payment.status','post');}
                    if($request->status == 'close') {$query->where('t_pi_down_payment.status','close');}

                    $query->groupBy('t_pi_down_payment.dp_code','status');

                    $dataDPH = $query->get();
                    $supplierDetail->data_dpcode = $dataDPH;

                    foreach ($dataDPH as $dph) {
                        $query = DB::table('d_pi_down_payment');
                        $query->select('*');
                        $query->where('d_pi_down_payment.dp_code',$dph->dp_code);

                        $detailDPh = $query->get();
                        $dph->detail = $detailDPh;
                    }
                }
            }
            //dd($dataDp,'detail');

            $pdf = PDF::loadview('admin.report.laporan-dpPurchase-detail',[
                'dataDp' => $dataDp,
                'dp_code' => $dp_code,
                'tglmulai' => $tglmulai,
                'tglsampai' => $tglsampai,
                'supplier' => $supplier,
                'status' => $status,
            ]);
        }

        $pdf->setPaper('legal', 'landscape');
        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-DpPurchase-'.$supplier.'-'.date('dmyhis').'.pdf');
        }
    }

    public function convertNumber($number)
    {
        $f = new \NumberFormatter("id", \NumberFormatter::SPELLOUT);
        $word = $f->format($number);

        return $word;
    }

    public function reportPurchaseInvoice(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->supplier == null) {
            $supplier = 'All';
        }else{
            $supplier = DB::table('m_supplier')
            ->where('id', $request->supplier)
            ->pluck('name')
            ->first();
        }

        $query = DB::table('m_supplier');
        $query->select('m_supplier.id','m_supplier.name');
        $query->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier');
        //$query->where('t_purchase_invoice.status','unpaid');
        $query->where('t_purchase_invoice.created_at','>=',date('Y-m-d', strtotime($tglmulai)));
        $query->where('t_purchase_invoice.created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
        if ($request->supplier != null) {
            $query->where('m_supplier.id', $request->supplier);
        }
        $query->groupBy('m_supplier.id');
        $query->orderBy('m_supplier.name');
        $datasupplier = $query->get();

        //dd($datasupplier);

        if ($request->type == 'summary') {
            foreach ($datasupplier as $raw_data) {
                // $saldoawal = DB::table('t_purchase_invoice')
                //     ->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                //     ->where('supplier',$raw_data->id)
                //     ->sum('total');

                $saldoawal = DB::table('m_saldo_awal_hutang')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('supplier',$raw_data->id)
                    ->where('status','post')
                    ->sum('total_hutang');

                $pembelian = DB::table('t_purchase_invoice')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('supplier',$raw_data->id)
                    ->sum('total');

                $debetNote = DB::table('t_purchase_invoice')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('supplier',$raw_data->id)
                    ->sum('debet_note');

                $creditNote = DB::table('t_purchase_invoice')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('supplier',$raw_data->id)
                    ->sum('credit_note');

                $pembayaran = DB::table('d_pi_pembayaran')
                    ->join('t_pi_pembayaran', 't_pi_pembayaran.pembayaran_code', '=', 'd_pi_pembayaran.pembayaran_code')
                    ->where('t_pi_pembayaran.payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_pi_pembayaran.payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('t_pi_pembayaran.supplier',$raw_data->id)
                    ->where('status','approved')
                    ->sum('d_pi_pembayaran.total');

                //calculate-debet-andcredit
                $subTotalPembelian = $pembelian + $creditNote;
                $subTotalPembayaran = $pembayaran + $debetNote;

                $raw_data->saldoawal = $saldoawal;
                $raw_data->pembelian = $subTotalPembelian;
                $raw_data->pembayaran = $subTotalPembayaran;
                $raw_data->saldoakhir = $saldoawal + $subTotalPembelian - $subTotalPembayaran;
            }

            // dd($datasupplier);

            $pdf = PDF::loadview('admin.report.laporan-pi-summary',['datasupplier' => $datasupplier,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'supplier' => $supplier]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('kartu-hutang-summary-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }else{
            foreach ($datasupplier as $raw_data) {
                // $saldoawal = DB::table('t_purchase_invoice')
                //     ->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                //     ->where('supplier',$raw_data->id)
                //     ->sum('total');

                $saldoawal = DB::table('m_saldo_awal_hutang')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('supplier',$raw_data->id)
                    ->where('status','post')
                    ->sum('total_hutang');

                $pembelian = DB::table('t_purchase_invoice')
                    ->select('created_at as tanggal','pi_code as keterangan','jatuh_tempo','total_sesuai_pd as hutang', DB::raw("'0' as pembayaran"))
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('supplier',$raw_data->id);
                    //->get();

                $retur = DB::table('t_retur_sjm')
                    ->select('created_at as tanggal','rt_code as keterangan','retur_dates as jatuh_tempo', DB::raw("'0' as hutang"),'grand_total as pembayaran')
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('supplier',$raw_data->id);

                $debetNote = DB::table('t_debet_note')
                            ->select('created_at as tanggal','code as keterangan','date as jatuh_tempo', DB::raw("'0' as hutang"),'total as pembayaran')
                            ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                            ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                            ->where('id_person',$raw_data->id);

                 $creditNote = DB::table('t_credit_note')
                            ->select('created_at as tanggal','code as keterangan','date as jatuh_tempo','total as hutang',DB::raw("'0' as pembayaran"))
                            ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                            ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                            ->where('id_person',$raw_data->id);


                $pembayaran = DB::table('d_pi_pembayaran')
                    ->select('t_pi_pembayaran.created_at as tanggal','d_pi_pembayaran.pembayaran_code as keterangan','t_pi_pembayaran.payment_date as jatuh_tempo', DB::raw("'0' as hutang"),'total as pembayaran')
                    ->join('t_pi_pembayaran', 't_pi_pembayaran.pembayaran_code', '=', 'd_pi_pembayaran.pembayaran_code')
                    ->where('t_pi_pembayaran.payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_pi_pembayaran.payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('t_pi_pembayaran.supplier',$raw_data->id)
                    ->where('status','approved')
                    ->union($pembelian)
                    ->union($retur)
                    ->union($debetNote)
                    ->union($creditNote)
                    ->orderBy('tanggal')
                    ->get();

                $awal = [];
                $awal[0]["tanggal"] = $tglmulai;
                $awal[0]["keterangan"] = 'SALDO AWAL';
                $awal[0]["jatuh_tempo"] = $tglmulai;
                $awal[0]["hutang"] = $saldoawal;
                $awal[0]["pembayaran"] = 0;

                //$awal = (object)$awal;
                $data = array_merge($awal, $pembayaran->toArray());
                $data = array_values($data);
                $data = (object)$data;

                // dd($data);

                $raw_data->detail = $data;
            }

            $pdf = PDF::loadview('admin.report.laporan-pi-detail',['datasupplier' => $datasupplier,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'supplier' => $supplier]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('kartu-hutang-detail-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function ReturSJ(request $request,$type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->customer == null) {
            $customer = 'All';
        }else{
            $customer = DB::table('m_customer')
            ->where('id', $request->customer)
            ->pluck('name')
            ->first();
        }


        if ($request->type == 'summary') {
            $query = DB::table('m_customer');
            $query->join('t_retur_sj', 'm_customer.id', '=', 't_retur_sj.customer');
            $query->join('t_faktur','t_retur_sj.sj_code','=','t_faktur.sj_code');
            if ($request->customer != null) {
                $query->where('m_customer.id', $request->customer);
            }
            if ($request->periode != null) {
                $query->where('t_retur_sj.retur_dates','>=',date('Y-m-d',strtotime($tglmulai)));
                $query->where('t_retur_sj.retur_dates','<',date('Y-m-d',strtotime($tglsampai. ' + 1 days')));
            }

            if ($request->status != null) {
                $query->where('t_retur_sj.status', $request->status);
            }
            if ($request->no_retur != null) {
                $query->where('t_retur_sj.rt_code', $request->no_retur);
            }
            $query->select('t_retur_sj.id','t_retur_sj.rt_code','t_retur_sj.sj_code','t_retur_sj.so_code','t_retur_sj.description','t_retur_sj.grand_total','m_customer.id','m_customer.name','t_faktur.faktur_code');
            $query->groupBy('m_customer.id','m_customer.name');
            $query->groupBy('t_retur_sj.id','t_retur_sj.rt_code','t_retur_sj.sj_code','t_retur_sj.so_code','t_retur_sj.description','t_retur_sj.grand_total');
            $query->groupBy('t_faktur.id','t_faktur.faktur_code');
            $datacustomer = $query->get();


            //dd($datacustomer);

            $pdf = PDF::loadview('admin.report.laporan-retur-sj-summary',['datacustomer' => $datacustomer,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'customer'=>$customer]);

            $pdf->setPaper('A4', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-retur-sj-'.$customer.'-'.date('dmyhis').'.pdf');
            }
        }
        if ($request->type == 'detail') {
            $query1=DB::table('t_retur_sj')
            ->select(DB::raw("DATE(retur_dates) as tgl"))
            ->groupBy('tgl');
            if ($request->periode != null) {
                $query1->where('t_retur_sj.retur_dates','>=',date('Y-m-d',strtotime($tglmulai)));
                $query1->where('t_retur_sj.retur_dates','<',date('Y-m-d',strtotime($tglsampai. ' + 1 days')));
            }
            if ($request->status != null) {
                $query1->where('t_retur_sj.status', $request->status);
            }
            if ($request->no_retur != null) {
                $query1->where('t_retur_sj.rt_code', $request->no_retur);
            }
            $datadetail=$query1->get();

            foreach($datadetail as $raw_data) {
                $query=DB::table('m_customer')
                ->join('t_retur_sj','t_retur_sj.customer','=','m_customer.id')
                ->select('m_customer.name as customer','m_customer.id as id_customer')
                ->where('t_retur_sj.retur_dates','>=',date('Y-m-d',strtotime($raw_data->tgl)))
                ->where('t_retur_sj.retur_dates','<',date('Y-m-d',strtotime($raw_data->tgl. ' + 1 days')))
                ->groupBy('m_customer.id','m_customer.name');
                if ($request->customer != null) {
                    $query->where('m_customer.id', $request->customer);
                }
                if ($request->status != null) {
                    $query->where('t_retur_sj.status', $request->status);
                }
                if ($request->no_retur != null) {
                    $query->where('t_retur_sj.rt_code', $request->no_retur);
                }
                $datacustomer=$query->get();
                $raw_data->customer=$datacustomer;

                foreach($datacustomer as $raw_data2){
                    $query2=DB::table('t_retur_sj')
                    ->join('t_sales_order','t_retur_sj.so_code','=','t_sales_order.so_code')
                    ->select('t_retur_sj.so_code as so')
                    ->where('t_retur_sj.customer','=',$raw_data2->id_customer)
                    ->groupBy('so');

                    if ($request->status != null) {
                        $query2->where('t_retur_sj.status', $request->status);
                    }
                    if ($request->no_retur != null) {
                        $query2->where('t_retur_sj.rt_code', $request->no_retur);
                    }
                    $dataso = $query2->get();
                    $raw_data2->so = $dataso;

                    foreach($dataso as $raw_data3){
                        $query3=DB::table('t_retur_sj')
                        ->select('t_retur_sj.rt_code as retur')
                        ->where('t_retur_sj.so_code','=',$raw_data3->so)
                        ->groupBy('retur');
                        if ($request->no_retur != null) {
                            $query3->where('t_retur_sj.rt_code', $request->no_retur);
                        }
                        if ($request->status != null) {
                            $query3->where('t_retur_sj.status', $request->status);
                        }
                        $datart=$query3->get();

                        $raw_data3->retur=$datart;

                        foreach($datart as $raw_data4){
                            $query4=DB::table('d_retur_sj')
                            ->join('t_retur_sj','t_retur_sj.rt_code','=','d_retur_sj.rt_code')
                            ->join('m_produk','d_retur_sj.produk_id','=','m_produk.id')
                            ->select('d_retur_sj.harga as harga','d_retur_sj.qty as qty','d_retur_sj.total as total','m_produk.name as produk')
                            ->where('d_retur_sj.rt_code','=',$raw_data4->retur)
                            ->groupBy('d_retur_sj.id','m_produk.id');

                            $databarang=$query4->get();

                            $raw_data4->barang=$databarang;
                        }
                    }
                }
            }

            //dd($datadetail);
            $pdf = PDF::loadview('admin.report.laporan-retur-sj-detail',['datadetail' => $datadetail,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'customer'=>$customer]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-retur-sj-'.$customer.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function ReturSJM(request $request,$type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->supplier == null) {
            $supplier = 'All';
        }else{
            $supplier = DB::table('m_supplier')
            ->where('id', $request->supplier)
            ->pluck('name')
            ->first();
        }


        if ($request->type == 'summary') {
            $query = DB::table('m_supplier');
            $query->join('t_retur_sjm', 'm_supplier.id', '=', 't_retur_sjm.supplier');
            $query->join('t_purchase_invoice','t_retur_sjm.sjm_code','=','t_purchase_invoice.sj_masuk_code');
            if ($request->supplier != null) {
                $query->where('m_supplier.id', $request->supplier);
            }
            if ($request->periode != null) {
                $query->where('t_retur_sjm.retur_dates','>=',date('Y-m-d',strtotime($tglmulai)));
                $query->where('t_retur_sjm.retur_dates','<',date('Y-m-d',strtotime($tglsampai. ' + 1 days')));
            }

            if ($request->status != null) {
                $query->where('t_retur_sjm.status', $request->status);
            }
            if ($request->no_retur != null) {
                $query->where('t_retur_sjm.rt_code', $request->no_retur);
            }
            $query->select('t_retur_sjm.*','m_supplier.id','m_supplier.name','t_purchase_invoice.pi_code');
            $query->groupBy('m_supplier.id');
            $query->groupBy('t_retur_sjm.id');
            $query->groupBy('t_purchase_invoice.id');
            $datasupplier = $query->get();


            //dd($datasupplier);

            $pdf = PDF::loadview('admin.report.laporan-retur-sjm-summary',['datasupplier' => $datasupplier,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'supplier'=>$supplier]);

            $pdf->setPaper('A4', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-retur-sjm-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }
        if ($request->type == 'detail') {
            $query1=DB::table('t_retur_sjm')
            ->select(DB::raw("DATE(retur_dates) as tgl"))
            ->groupBy('tgl');
            if ($request->periode != null) {
                $query1->where('t_retur_sjm.retur_dates','>=',date('Y-m-d',strtotime($tglmulai)));
                $query1->where('t_retur_sjm.retur_dates','<',date('Y-m-d',strtotime($tglsampai. ' + 1 days')));
            }

            if ($request->status != null) {
                $query1->where('t_retur_sjm.status', $request->status);
            }
            if ($request->no_retur != null) {
                $query1->where('t_retur_sjm.rt_code', $request->no_retur);
            }
            $datadetail=$query1->get();
            // dd($datadetail);
            foreach($datadetail as $raw_data) {
                $query=DB::table('m_supplier')
                ->join('t_retur_sjm','t_retur_sjm.supplier','=','m_supplier.id')
                ->select('m_supplier.name as supplier','m_supplier.id as id_supplier')
                ->where('t_retur_sjm.retur_dates','>=',date('Y-m-d',strtotime($raw_data->tgl)))
                ->where('t_retur_sjm.retur_dates','<',date('Y-m-d',strtotime($raw_data->tgl. ' + 1 days')))
                ->groupBy('m_supplier.id','supplier');
                if ($request->supplier != null) {
                    $query->where('m_supplier.id', $request->supplier);
                }
                if ($request->status != null) {
                    $query->where('t_retur_sjm.status', $request->status);
                }
                if ($request->no_retur != null) {
                    $query->where('t_retur_sjm.rt_code', $request->no_retur);
                }
                $datasupplier=$query->get();
                // dd($request->all());
                // dd($datasupplier);
                $raw_data->supplier=$datasupplier;

                foreach($datasupplier as $raw_data2){
                    $query2=DB::table('t_retur_sjm')
                    ->join('t_purchase_order','t_retur_sjm.po_code','=','t_purchase_order.po_code')
                    ->select('t_retur_sjm.po_code as po')
                    ->where('t_retur_sjm.supplier','=',$raw_data2->id_supplier)
                    ->groupBy('po');

                    if ($request->status != null) {
                        $query2->where('t_retur_sjm.status', $request->status);
                    }
                    if ($request->no_retur != null) {
                        $query2->where('t_retur_sjm.rt_code', $request->no_retur);
                    }
                    $datapo = $query2->get();
                    $raw_data2->po = $datapo;

                    foreach($datapo as $raw_data3){
                        $query3=DB::table('t_retur_sjm')
                        ->select('t_retur_sjm.rt_code as retur')
                        ->where('t_retur_sjm.po_code','=',$raw_data3->po)
                        ->groupBy('retur');
                        if ($request->no_retur != null) {
                            $query3->where('t_retur_sjm.rt_code', $request->no_retur);
                        }
                        if ($request->status != null) {
                            $query3->where('t_retur_sjm.status', $request->status);
                        }
                        $datart=$query3->get();

                        $raw_data3->retur=$datart;

                        foreach($datart as $raw_data4){
                            $query4=DB::table('d_retur_sjm')
                            ->join('t_retur_sjm','t_retur_sjm.rt_code','=','d_retur_sjm.rt_code')
                            ->join('m_produk','d_retur_sjm.produk_id','=','m_produk.id')
                            ->select('d_retur_sjm.harga as harga','d_retur_sjm.qty as qty','d_retur_sjm.total as total','m_produk.name as produk')
                            ->where('d_retur_sjm.rt_code','=',$raw_data4->retur)
                            ->groupBy('d_retur_sjm.id','m_produk.id');

                            $databarang=$query4->get();

                            $raw_data4->barang=$databarang;
                        }
                    }
                }
            }

            // dd($request->all());
            $pdf = PDF::loadview('admin.report.laporan-retur-sjm-detail',['datadetail' => $datadetail,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'supplier'=>$supplier]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-retur-sjm-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function warehouse($twcode)
    {
        $header = DB::table('t_transfer_warehouse')
        ->select('t_transfer_warehouse.*','gudang_asal.name as gudang_asal','gudang_tujuan.name as gudang_tujuan','user_input.name as user_input')
        ->join('m_gudang as gudang_asal','gudang_asal.id','t_transfer_warehouse.gudang_asal')
        ->join('m_gudang as gudang_tujuan','gudang_tujuan.id','t_transfer_warehouse.gudang_tujuan')
        ->join('m_user as user_input','user_input.id','t_transfer_warehouse.user_input')
        ->orderBy('id','DESC')
        ->where('t_transfer_warehouse.tw_code',$twcode)
        ->first();

        $detail = DB::table('d_transfer_warehouse')
        ->join('m_produk','m_produk.id','d_transfer_warehouse.produk')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.code','m_produk.name','m_satuan_unit.code as code_unit','d_transfer_warehouse.qty')
        ->where('d_transfer_warehouse.tw_code',$twcode)
        ->get();

        //dd($header,$detail);

        // return view('admin.report.warehouse',compact('header','detail'));
        $pdf = PDF::loadview('admin.report.warehouse',['header' => $header,'detail' => $detail]);
        $pdf->setPaper('legal', 'potrait');
        return $pdf->stream();
    }

    public function laporanWarehouse(Request $request, $type)
    {
        $status='ALL';
        if ($request->status != null) {
            $status=$request->status;

        }

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);
        if($request->type == 'out'){
            $query1 = DB::table('t_transfer_warehouse')
            ->select(DB::raw("DATE(tw_date) as tgl"))
            ->where('tw_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('tw_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('tgl');
            if ($request->status != null) {
                $query1->where('t_transfer_warehouse.status_aprove', $request->status);
            }
            if ($request->tw != null) {
                $query1->where('t_transfer_warehouse.tw_code', $request->tw_code);
            }
            if ($request->gudang_asal != null) {
                $query1->where('t_transfer_warehouse.gudang_asal', $request->gudang_asal);
            }
            $data = $query1->get();

            foreach($data as $raw_data) {
                $query2=DB::table('t_transfer_warehouse')
                ->join('m_gudang','m_gudang.id','=','t_transfer_warehouse.gudang_asal')
                ->select('m_gudang.name as gudang','m_gudang.id as id_gudang_asal')
                ->where('t_transfer_warehouse.tw_date','>=', date('Y-m-d', strtotime($tglmulai)))
                ->where('t_transfer_warehouse.tw_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('t_transfer_warehouse.tw_date','=',date('Y-m-d',strtotime($raw_data->tgl)))
                ->groupBy('id_gudang_asal','gudang');
                if ($request->status != null) {
                    $query2->where('t_transfer_warehouse.status_aprove', $request->status);
                }
                if ($request->tw != null) {
                    $query2->where('t_transfer_warehouse.tw_code', $request->tw_code);
                }
                if ($request->gudang_asal != null) {
                    $query2->where('t_transfer_warehouse.gudang_asal', $request->gudang_asal);
                }

                $datagudang = $query2->get();
                $raw_data->gudang = $datagudang;

                foreach($datagudang as $raw_data2) {
                    $query3 = DB::table('t_transfer_warehouse')
                    ->join('m_gudang','m_gudang.id','=','t_transfer_warehouse.gudang_tujuan')
                    ->select('tw_code','m_gudang.name as gudang_tujuan','m_gudang.id as id_gudang_tujuan')
                    ->where('t_transfer_warehouse.tw_date','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_transfer_warehouse.tw_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('t_transfer_warehouse.gudang_asal',$raw_data2->id_gudang_asal)
                    ->groupBy('tw_code','m_gudang.name','m_gudang.id');
                    if ($request->status != null) {
                        $query3->where('t_transfer_warehouse.status_aprove', $request->status);
                    }
                    if ($request->tw != null) {
                        $query3->where('t_transfer_warehouse.tw_code', $request->tw_code);
                    }
                    if ($request->gudang_asal != null) {
                        $query3->where('t_transfer_warehouse.gudang_asal', $request->gudang_asal);
                    }

                    $datatw = $query3->get();
                    $raw_data2->tw_code = $datatw;

                    foreach($datatw as $raw_data3) {
                        $query4 = DB::table('d_transfer_warehouse')
                        ->join('m_produk','m_produk.id','=','d_transfer_warehouse.produk')
                        ->where('d_transfer_warehouse.tw_code','=',$raw_data3->tw_code)
                        ->select('m_produk.name as barang','m_produk.id as id_barang','d_transfer_warehouse.qty')
                        ->groupBy('m_produk.id','d_transfer_warehouse.id');

                        if ($request->barang != null) {
                            $query4->where('d_transfer_warehouse.produk', $request->barang);
                        }

                        $databarang=$query4->get();
                        $raw_data3->barang=$databarang;

                    }
                }
            }
            //dd($data);
            $typein='OUT';

            $pdf = PDF::loadview('admin.report.laporan-transfer-warehouse',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'status' => $status,'type'=>$request->type,'typetransfer'=>$typein]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-transfer-warehouse/periode : '.$tglmulai.'-'.$tglsampai.'_'.date('dmyhis').'.pdf');
            }
        }
        elseif($request->type == 'in'){
            $query1 = DB::table('t_transfer_warehouse')
            ->select(DB::raw("DATE(tw_date) as tgl"))
            ->where('tw_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('tw_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('tgl');
            if ($request->status != null) {
                $query1->where('t_transfer_warehouse.status_aprove', $request->status);
            }
            if ($request->tw != null) {
                $query1->where('t_transfer_warehouse.tw_code', $request->tw_code);
            }
            if ($request->gudang_asal != null) {
                $query1->where('t_transfer_warehouse.gudang_tujuan', $request->gudang_asal);
            }
            $data = $query1->get();

            foreach($data as $raw_data) {
                $query2=DB::table('t_transfer_warehouse')
                ->join('m_gudang','m_gudang.id','=','t_transfer_warehouse.gudang_tujuan')
                ->select('m_gudang.name as gudang','m_gudang.id as id_gudang_asal')
                ->where('t_transfer_warehouse.tw_date','>=', date('Y-m-d', strtotime($tglmulai)))
                ->where('t_transfer_warehouse.tw_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('t_transfer_warehouse.tw_date','<',date('Y-m-d',strtotime($raw_data->tgl. ' + 1 days')))
                ->groupBy('id_gudang_asal','gudang');
                if ($request->status != null) {
                    $query2->where('t_transfer_warehouse.status_aprove', $request->status);
                }
                if ($request->tw != null) {
                    $query2->where('t_transfer_warehouse.tw_code', $request->tw_code);
                }
                if ($request->gudang_asal != null) {
                    $query2->where('t_transfer_warehouse.gudang_tujuan', $request->gudang_asal);
                }

                $datagudang = $query2->get();
                $raw_data->gudang = $datagudang;

                foreach($datagudang as $raw_data2) {
                    $query3 = DB::table('t_transfer_warehouse')
                    ->join('m_gudang','m_gudang.id','=','t_transfer_warehouse.gudang_asal')
                    ->select('tw_code','m_gudang.name as gudang_tujuan','m_gudang.id as id_gudang_tujuan')
                    ->where('t_transfer_warehouse.tw_date','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_transfer_warehouse.tw_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('t_transfer_warehouse.gudang_tujuan',$raw_data2->id_gudang_asal)
                    ->groupBy('tw_code','m_gudang.name','m_gudang.id');
                    if ($request->status != null) {
                        $query3->where('t_transfer_warehouse.status_aprove', $request->status);
                    }
                    if ($request->tw != null) {
                        $query3->where('t_transfer_warehouse.tw_code', $request->tw_code);
                    }
                    if ($request->gudang_asal != null) {
                        $query3->where('t_transfer_warehouse.gudang_tujuan', $request->gudang_asal);
                    }

                    $datatw = $query3->get();
                    $raw_data2->tw_code = $datatw;

                    foreach($datatw as $raw_data3) {
                        $query4 = DB::table('d_transfer_warehouse')
                        ->join('m_produk','m_produk.id','=','d_transfer_warehouse.produk')
                        ->where('d_transfer_warehouse.tw_code','=',$raw_data3->tw_code)
                        ->select('m_produk.name as barang','m_produk.id as id_barang','d_transfer_warehouse.qty')
                        ->groupBy('m_produk.id','d_transfer_warehouse.id');

                        if ($request->barang != null) {
                            $query4->where('d_transfer_warehouse.produk', $request->barang);
                        }

                        $databarang=$query4->get();
                        $raw_data3->barang=$databarang;

                    }
                }
            }
            //dd($data);
$typein='IN';
            $pdf = PDF::loadview('admin.report.laporan-transfer-warehouse',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'status' => $status,'type'=>$request->type,'typetransfer'=>$typein]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-transfer-warehouse/periode : '.$tglmulai.'-'.$tglsampai.'_'.date('dmyhis').'.pdf');
            }
        }
    }

    public function reportUmurHutang(request $request, $type)
    {
        if ($request->supplier == null) {
            $supplier = 'All';
        }else{
            $supplier = DB::table('m_supplier')
            ->where('id', $request->supplier)
            ->pluck('name')
            ->first();
        }

        $periode = $request->periode;

        $query = DB::table('m_supplier');
        $query->select('m_supplier.id','m_supplier.name');
        $query->join('t_purchase_invoice', 'm_supplier.id', '=', 't_purchase_invoice.supplier');
        $query->where('t_purchase_invoice.status','unpaid');
        $query->where('t_purchase_invoice.created_at','<',date('Y-m-d', strtotime($request->periode. ' + 1 days')));
        if ($request->supplier != null) {
            $query->where('m_supplier.id', $request->supplier);
        }
        $query->groupBy('m_supplier.id');
        $query->orderBy('m_supplier.name');
        $datasupplier = $query->get();

        // dd($datasupplier);

        if ($request->type == 'summary') {
            foreach ($datasupplier as $raw_data) {
                $pembelian = DB::table('t_purchase_invoice')
                ->where('status','unpaid')
                ->where('created_at','<',date('Y-m-d', strtotime($request->periode. ' + 1 days')))
                ->where('supplier',$raw_data->id)
                ->get();

                //$raw_data->pembelian = $pembelian;
                $amt_0_14 = 0;
                $amt_15_30 = 0;
                $amt_31_89 = 0;
                $amt_90 = 0;

                foreach ($pembelian as $raw_data2) {
                    $post_date = strtotime($request->periode);
                    $pembelian_date = strtotime($raw_data2->pi_date);
                    $datediff = $post_date - $pembelian_date;

                    $jumlah_hari = (int)round($datediff / (60 * 60 * 24));

                    $raw_data2->jumlah_hari = $jumlah_hari;
                    if ($jumlah_hari >= 0 && $jumlah_hari <= 14) {
                        $amt_0_14 = $amt_0_14 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 15 && $jumlah_hari <= 30) {
                        $amt_15_30 = $amt_15_30 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 31 && $jumlah_hari <= 89) {
                        $amt_31_89 = $amt_31_89 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 90) {
                        $amt_90 = $amt_90 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                }

                $raw_data->amt_0_14 = $amt_0_14;
                $raw_data->amt_15_30 = $amt_15_30;
                $raw_data->amt_31_89 = $amt_31_89;
                $raw_data->amt_90 = $amt_90;
                $raw_data->grand_total = $amt_0_14 + $amt_15_30 + $amt_31_89 + $amt_90;
            }

            //dd($datasupplier);

            $pdf = PDF::loadview('admin.report.laporan-umur-hutang-summary',['datasupplier' => $datasupplier,'periode' => $periode,'supplier' => $supplier]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-umur-hutang-summary-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }else{
            foreach ($datasupplier as $raw_data) {
                $pembelian = DB::table('t_purchase_invoice')
                ->where('status','unpaid')
                ->where('created_at','<',date('Y-m-d', strtotime($request->periode. ' + 1 days')))
                ->where('supplier',$raw_data->id)
                ->get();

                $raw_data->pembelian = $pembelian;
                foreach ($pembelian as $raw_data2) {
                    $amt_0_14 = 0;
                    $amt_15_30 = 0;
                    $amt_31_89 = 0;
                    $amt_90 = 0;

                    $post_date = strtotime($request->periode);
                    $pembelian_date = strtotime($raw_data2->pi_date);
                    $datediff = $post_date - $pembelian_date;

                    $jumlah_hari = (int)round($datediff / (60 * 60 * 24));

                    $raw_data2->jumlah_hari = $jumlah_hari;
                    if ($jumlah_hari >= 0 && $jumlah_hari <= 14) {
                        $amt_0_14 = $amt_0_14 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 15 && $jumlah_hari <= 30) {
                        $amt_15_30 = $amt_15_30 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 31 && $jumlah_hari <= 89) {
                        $amt_31_89 = $amt_31_89 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 90) {
                        $amt_90 = $amt_90 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }

                    $raw_data2->amt_0_14 = $amt_0_14;
                    $raw_data2->amt_15_30 = $amt_15_30;
                    $raw_data2->amt_31_89 = $amt_31_89;
                    $raw_data2->amt_90 = $amt_90;
                    $raw_data2->grand_total = $amt_0_14 + $amt_15_30 + $amt_31_89 + $amt_90;
                }
            }

            //dd($datasupplier);

            $pdf = PDF::loadview('admin.report.laporan-umur-hutang-detail',['datasupplier' => $datasupplier,'periode' => $periode,'supplier' => $supplier]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-umur-hutang-detail-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function reportUmurPiutang(request $request, $type)
    {
        if ($request->customer == null) {
            $customer = 'All';
        }else{
            $customer = DB::table('m_customer')
                ->where('id', $request->customer)
                ->pluck('name')
                ->first();
        }

        $periode = $request->periode;

        $query = DB::table('m_customer');
        $query->select('m_customer.id','m_customer.name');
        $query->join('t_faktur', 'm_customer.id', '=', 't_faktur.customer');
        $query->where('t_faktur.status_payment','unpaid');
        $query->where('t_faktur.created_at','<',date('Y-m-d', strtotime($request->periode. ' + 1 days')));
        if ($request->customer != null) {
            $query->where('m_customer.id', $request->customer);
        }
        $query->groupBy('m_customer.id');
        $query->orderBy('m_customer.name');
        $datacustomer = $query->get();

        //dd($datacustomer);

        if ($request->type == 'summary') {
            foreach ($datacustomer as $raw_data) {
                $penjualan = DB::table('t_faktur')
                ->where('status_payment','unpaid')
                ->where('created_at','<',date('Y-m-d', strtotime($request->periode. ' + 1 days')))
                ->where('customer',$raw_data->id)
                ->get();

                $raw_data->penjualan = $penjualan;
                $amt_0_14 = 0;
                $amt_15_30 = 0;
                $amt_31_89 = 0;
                $amt_90 = 0;

                foreach ($penjualan as $raw_data2) {
                    $post_date = strtotime($request->periode);
                    $pembelian_date = strtotime($raw_data2->created_at);
                    $datediff = $post_date - $pembelian_date;

                    $jumlah_hari = (int)round($datediff / (60 * 60 * 24)) + 1;

                    $raw_data2->jumlah_hari = $jumlah_hari;
                    if ($jumlah_hari >= 0 && $jumlah_hari <= 14) {
                        $amt_0_14 = $amt_0_14 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 15 && $jumlah_hari <= 30) {
                        $amt_15_30 = $amt_15_30 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 31 && $jumlah_hari <= 89) {
                        $amt_31_89 = $amt_31_89 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 90) {
                        $amt_90 = $amt_90 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                }

                //dd($penjualan);

                $raw_data->amt_0_14 = $amt_0_14;
                $raw_data->amt_15_30 = $amt_15_30;
                $raw_data->amt_31_89 = $amt_31_89;
                $raw_data->amt_90 = $amt_90;
                $raw_data->grand_total = $amt_0_14 + $amt_15_30 + $amt_31_89 + $amt_90;
            }

            //dd($datacustomer);

            $pdf = PDF::loadview('admin.report.laporan-umur-piutang-summary',['datacustomer' => $datacustomer,'periode' => $periode,'customer' => $customer]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-umur-piutang-summary-'.$customer.'-'.date('dmyhis').'.pdf');
            }
        }else{
            foreach ($datacustomer as $raw_data) {
                $penjualan = DB::table('t_faktur')
                ->where('status_payment','unpaid')
                ->where('created_at','<',date('Y-m-d', strtotime($request->periode. ' + 1 days')))
                ->where('customer',$raw_data->id)
                ->get();

                $raw_data->penjualan = $penjualan;
                foreach ($penjualan as $raw_data2) {
                    $amt_0_14 = 0;
                    $amt_15_30 = 0;
                    $amt_31_89 = 0;
                    $amt_90 = 0;

                    $post_date = strtotime($request->periode);
                    $pembelian_date = strtotime($raw_data2->created_at);
                    $datediff = $post_date - $pembelian_date;

                    $jumlah_hari = (int)round($datediff / (60 * 60 * 24)) +1;

                    $raw_data2->jumlah_hari = $jumlah_hari;
                    if ($jumlah_hari >= 0 && $jumlah_hari <= 14) {
                        $amt_0_14 = $amt_0_14 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 15 && $jumlah_hari <= 30) {
                        $amt_15_30 = $amt_15_30 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 31 && $jumlah_hari <= 89) {
                        $amt_31_89 = $amt_31_89 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }
                    else if ($jumlah_hari >= 90) {
                        $amt_90 = $amt_90 + ($raw_data2->total - $raw_data2->jumlah_yg_dibayarkan);
                    }

                    $raw_data2->amt_0_14 = $amt_0_14;
                    $raw_data2->amt_15_30 = $amt_15_30;
                    $raw_data2->amt_31_89 = $amt_31_89;
                    $raw_data2->amt_90 = $amt_90;
                    $raw_data2->grand_total = $amt_0_14 + $amt_15_30 + $amt_31_89 + $amt_90;
                }
            }

            //dd($datacustomer);

            $pdf = PDF::loadview('admin.report.laporan-umur-piutang-detail',['datacustomer' => $datacustomer,'periode' => $periode,'customer' => $customer]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-umur-piutang-detail-'.$customer.'-'.date('dmyhis').'.pdf');
            }
        }
    }
    public function laporanAdjusment(Request $request, $type)
    {
        ini_set('memory_limit', '512MB');
        ini_set('max_execution_time', 3000);
        $status='ALL';
        if ($request->status != null) {
            $status=$request->status;
        }

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        $query1 = DB::table('t_adjusment')
            ->select(DB::raw("DATE(ta_date) as tgl"))
            ->where('ta_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('ta_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('tgl');

        if ($request->status != null) {
            $query1->where('t_adjusment.status_aprove', $request->status);
        }
        if ($request->ta != null) {
            $query1->where('t_adjusment.ta_code', $request->ta);
        }
        if ($request->gudang != null) {
            $query1->where('t_adjusment.gudang', $request->gudang);
        }
        $data = $query1->get();

        //dd($data);

        foreach($data as $raw_data) {
            $query2=DB::table('t_adjusment')
            ->join('m_gudang','m_gudang.id','=','t_adjusment.gudang')
            ->select('m_gudang.name as gudang','m_gudang.id as id_gudang')
            ->where('t_adjusment.ta_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_adjusment.ta_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->where('t_adjusment.ta_date','=',date('Y-m-d',strtotime($raw_data->tgl)))
            ->groupBy('id_gudang','gudang');
            if ($request->status != null) {
                $query2->where('t_adjusment.status_aprove', $request->status);
            }
            if ($request->ta != null) {
                $query2->where('t_adjusment.ta_code', $request->ta);
            }
            if ($request->gudang != null) {
                $query2->where('t_adjusment.gudang', $request->gudang);
            }

            $datagudang = $query2->get();
            $raw_data->gudang = $datagudang;

            foreach($datagudang as $raw_data2) {
                $query3 = DB::table('t_adjusment')
                ->select('ta_code')
                ->where('t_adjusment.gudang',$raw_data2->id_gudang)
                ->where('t_adjusment.ta_date','>=', date('Y-m-d', strtotime($tglmulai)))
                ->where('t_adjusment.ta_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->groupBy('ta_code');
                if ($request->status != null) {
                    $query3->where('t_adjusment.status_aprove', $request->status);
                }
                if ($request->ta != null) {
                    $query3->where('t_adjusment.ta_code', $request->ta);
                }
                if ($request->gudang != null) {
                    $query3->where('t_adjusment.gudang', $request->gudang);
                }

                $datatw = $query3->get();
                $raw_data2->ta_code = $datatw;

                foreach($datatw as $raw_data3) {
                    $query4 = DB::table('d_adjusment')
                        ->join('m_produk','m_produk.id','=','d_adjusment.produk')
                        ->where('d_adjusment.ta_code','=',$raw_data3->ta_code)
                        ->select('m_produk.name as barang','m_produk.id as id_barang','d_adjusment.*')
                        ->groupBy('m_produk.id','d_adjusment.id');

                    if ($request->barang != null) {
                        $query4->where('d_adjusment.produk', $request->barang);
                    }

                    $databarang=$query4->get();
                    $raw_data3->barang=$databarang;

                }
            }
        }

        //dd($data);

        $pdf = PDF::loadview('admin.report.laporan-stok-adjusment',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'status' => $status]);

        $pdf->setPaper('legal', 'potrait');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-stok-adjusment/periode : '.$tglmulai.'-'.$tglsampai.'_'.date('dmyhis').'.pdf');
        }
    }

    public function laporanKas(Request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        //dd($request->all());

        if ($request->akun == null) {
            $akun = 'ALL';

            //get semua id kas
            $data_interface = DB::table('m_interface')
                ->where('var','VAR_CASH')
                ->first();

            $code_coa = explode(",", $data_interface->code_coa);

            if ($code_coa[0]=='') {
                $code_coa = [];
                $dataKas = [];
            }else{
                $query = [];
                for ($i=0; $i < count($code_coa); $i++) {
                    $query[$i] = DB::table('m_coa');
                    $query[$i]->select('id','code','desc');
                    $query[$i]->where('code', 'like', $code_coa[$i].'%');
                    if ($i>0) {
                        $query[$i]->union($query[$i-1]);
                    }
                }
                $query[count($code_coa)-1]->orderBy('id');
                $query[count($code_coa)-1]->groupBy('id');
                $dataKas = $query[count($code_coa)-1]->get();

                //cek code coa paling bawah
                $length = 0;
                foreach($dataKas as $raw_data) {
                    $lengthCode = strlen($raw_data->code);
                    if ($lengthCode > $length) {
                        $length =$lengthCode;
                    }
                    $raw_data->test = $lengthCode;
                }

                //remove coa parent
                foreach ($dataKas as $key => $raw_data) {
                    $lengthCode = strlen($raw_data->code);
                    if ($lengthCode < $length) {
                        unset($dataKas[$key]);
                    }
                }
            }
        }else{
            $coa = DB::table('m_coa')
                ->where('id', $request->akun)
                ->first();
            $akun = $coa->code.' '.$coa->desc;
        }

        if ($request->type == 'summary') {
            $saldoawal = 0;

            $query = DB::table('t_cash_bank');
                $query->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');
                $query->select('t_cash_bank.*', 'm_coa.code');

            if ($request->akun != null) {
                $query->where('id_coa', $request->akun);

                $saldoawal = DB::table('m_saldo_awal_coa')
                    ->where('id_coa', $request->akun)
                    ->sum('total');
            }else{
                $query->where(function ($query2) use ($dataKas) {
                    foreach ($dataKas as $raw_data) {
                        $query2->orwhere('id_coa', $raw_data->id);
                    }
                });

                foreach ($dataKas as $raw_data) {
                    $jumlah = 0;
                    $jumlah = DB::table('m_saldo_awal_coa')
                        ->where('id_coa', $raw_data->id)
                        ->sum('total');
                    $saldoawal = $saldoawal + $jumlah;
                }
            }

            $query->where('cash_bank_status', 'post');
            $query->where('cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
            $query->where('cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            $data = $query->get();

            //dd($saldoawal);

            $pdf = PDF::loadview('admin.report.laporan-kas-summary',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'akun' => $akun,'saldoawal' => $saldoawal]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-kas-summary-'.$akun.'-'.date('dmyhis').'.pdf');
            }
        }else{
            $query = DB::table('t_cash_bank');
                $query->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');
                $query->select('t_cash_bank.id_coa', 'm_coa.code','m_coa.desc');

            if ($request->akun != null) {
                $query->where('id_coa', $request->akun);
            }else{
                $query->where(function ($query2) use ($dataKas) {
                    foreach ($dataKas as $raw_data) {
                        $query2->orwhere('id_coa', $raw_data->id);
                    }
                });
            }

            $query->where('cash_bank_status', 'post');
            $query->where('cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
            $query->where('cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
            $query->groupBy('t_cash_bank.id_coa', 'm_coa.code','m_coa.desc');

            $data = $query->get();

            foreach ($data as $raw_data) {
                $saldoawal = DB::table('m_saldo_awal_coa')
                    ->where('id_coa', $raw_data->id_coa)
                    ->sum('total');

                $raw_data->saldo_awal = $saldoawal;

                $query = DB::table('t_cash_bank');
                $query->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');
                $query->select('t_cash_bank.*', 'm_coa.code','m_coa.desc');

                if ($request->akun != null) {
                    $query->where('id_coa', $request->akun);
                }else{
                    $query->where(function ($query2) use ($dataKas) {
                        foreach ($dataKas as $raw_data) {
                            $query2->orwhere('id_coa', $raw_data->id);
                        }
                    });
                }

                $query->where('cash_bank_status', 'post');
                $query->where('cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
                $query->where('cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
                $query->where('t_cash_bank.id_coa',$raw_data->id_coa);

                $data_trans = $query->get();
                $raw_data->data_trans = $data_trans;

                foreach ($data_trans as $raw_data2) {
                    if ($raw_data2->id_person == null) {
                        $raw_data2->code_person = '';
                        $raw_data2->name_person = '';
                    }else{
                        $raw_data2->code_person = $raw_data2->id_person;
                        $raw_data2->name_person = $raw_data2->id_person;
                    }

                    $detail = DB::table('d_cb_expense_receipt')
                        ->select('d_cb_expense_receipt.*', 'm_coa.code','m_coa.desc')
                        ->where('cash_bank_code', $raw_data2->cash_bank_code)
                        ->join('m_coa','m_coa.id','=','d_cb_expense_receipt.id_coa')
                        ->get();

                    $raw_data2->detail = $detail;
                }
            }

            // dd($data);

            $pdf = PDF::loadview('admin.report.laporan-kas-detail',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'akun' => $akun]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-kas-summary-'.$akun.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function laporanBank(Request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        //dd($request->all());

        if ($request->akun == null) {
            $akun = 'ALL';

            //get semua id bank
            $data_interface = DB::table('m_interface')
                ->where('var','VAR_BANK')
                ->first();

            $code_coa = explode(",", $data_interface->code_coa);

            if ($code_coa[0]=='') {
                $code_coa = [];
                $dataKas = [];
            }else{
                $query = [];
                for ($i=0; $i < count($code_coa); $i++) {
                    $query[$i] = DB::table('m_coa');
                    $query[$i]->select('id','code','desc');
                    $query[$i]->where('code', 'like', $code_coa[$i].'%');
                    if ($i>0) {
                        $query[$i]->union($query[$i-1]);
                    }
                }
                $query[count($code_coa)-1]->orderBy('id');
                $query[count($code_coa)-1]->groupBy('id');
                $dataKas = $query[count($code_coa)-1]->get();

                //cek code coa paling bawah
                $length = 0;
                foreach($dataKas as $raw_data) {
                    $lengthCode = strlen($raw_data->code);
                    if ($lengthCode > $length) {
                        $length =$lengthCode;
                    }
                    $raw_data->test = $lengthCode;
                }

                //remove coa parent
                foreach ($dataKas as $key => $raw_data) {
                    $lengthCode = strlen($raw_data->code);
                    if ($lengthCode < $length) {
                        unset($dataKas[$key]);
                    }
                }
            }
        }else{
            $coa = DB::table('m_coa')
                ->where('id', $request->akun)
                ->first();
            $akun = $coa->code.' '.$coa->desc;
        }

        if ($request->type == 'summary') {
            $saldoawal = 0;

            $query = DB::table('t_cash_bank');
                $query->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');
                $query->select('t_cash_bank.*', 'm_coa.code');

            if ($request->akun != null) {
                $query->where('id_coa', $request->akun);

                $saldoawal = DB::table('m_saldo_awal_coa')
                    ->where('id_coa', $request->akun)
                    ->sum('total');
            }else{
                $query->where(function ($query2) use ($dataKas) {
                    foreach ($dataKas as $raw_data) {
                        $query2->orwhere('id_coa', $raw_data->id);
                    }
                });

                foreach ($dataKas as $raw_data) {
                    $jumlah = 0;
                    $jumlah = DB::table('m_saldo_awal_coa')
                        ->where('id_coa', $raw_data->id)
                        ->sum('total');
                    $saldoawal = $saldoawal + $jumlah;
                }
            }

            $query->where('cash_bank_status', 'post');
            $query->where('cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
            $query->where('cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            $data = $query->get();

            //dd($data);

            $pdf = PDF::loadview('admin.report.laporan-bank-summary',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'akun' => $akun,'saldoawal' => $saldoawal]);

            $pdf->setPaper('legal', 'potrait');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-bank-summary-'.$akun.'-'.date('dmyhis').'.pdf');
            }
        }else{
            $query = DB::table('t_cash_bank');
                $query->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');
                $query->select('t_cash_bank.id_coa', 'm_coa.code','m_coa.desc');

            if ($request->akun != null) {
                $query->where('id_coa', $request->akun);
            }else{
                $query->where(function ($query2) use ($dataKas) {
                    foreach ($dataKas as $raw_data) {
                        $query2->orwhere('id_coa', $raw_data->id);
                    }
                });
            }

            $query->where('cash_bank_status', 'post');
            $query->where('cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
            $query->where('cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
            $query->groupBy('t_cash_bank.id_coa', 'm_coa.code','m_coa.desc');

            $data = $query->get();

            foreach ($data as $raw_data) {
                $saldoawal = DB::table('m_saldo_awal_coa')
                    ->where('id_coa', $raw_data->id_coa)
                    ->sum('total');

                $raw_data->saldo_awal = $saldoawal;

                $query = DB::table('t_cash_bank');
                $query->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');
                $query->select('t_cash_bank.*', 'm_coa.code','m_coa.desc');

                if ($request->akun != null) {
                    $query->where('id_coa', $request->akun);
                }else{
                    $query->where(function ($query2) use ($dataKas) {
                        foreach ($dataKas as $raw_data) {
                            $query2->orwhere('id_coa', $raw_data->id);
                        }
                    });
                }

                $query->where('cash_bank_status', 'post');
                $query->where('cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
                $query->where('cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
                $query->where('t_cash_bank.id_coa',$raw_data->id_coa);

                $data_trans = $query->get();
                $raw_data->data_trans = $data_trans;

                foreach ($data_trans as $raw_data2) {
                    if ($raw_data2->id_person == null) {
                        $raw_data2->code_person = '';
                        $raw_data2->name_person = '';
                    }else{
                        $raw_data2->code_person = $raw_data2->id_person;
                        $raw_data2->name_person = $raw_data2->id_person;
                    }

                    $detail = DB::table('d_cb_expense_receipt')
                        ->select('d_cb_expense_receipt.*', 'm_coa.code','m_coa.desc')
                        ->where('cash_bank_code', $raw_data2->cash_bank_code)
                        ->join('m_coa','m_coa.id','=','d_cb_expense_receipt.id_coa')
                        ->get();

                    $raw_data2->detail = $detail;
                }
            }

            //dd($data);

            $pdf = PDF::loadview('admin.report.laporan-bank-detail',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'akun' => $akun]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-kas-summary-'.$akun.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function laporanGeneralJournal(Request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        //dd($request->all());
        if ($request->akun == null) {
            $akun = 'ALL';
        }else{
            $coa = DB::table('m_coa')
                ->where('id', $request->akun)
                ->first();
            $akun = $coa->code.' '.$coa->desc;
        }

        $query = DB::table('t_general_ledger')
            ->select(DB::raw("DATE(general_ledger_date) as tgl"))
            ->join('d_general_ledger','d_general_ledger.t_gl_id','=','t_general_ledger.id')
            ->where('general_ledger_status', 'post')
            ->where('general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

        if ($request->akun != null) {
            $query->where('d_general_ledger.id_coa', $request->akun);
        }

        $query->groupBy('tgl');

        $data = $query->get();

        foreach ($data as $raw_data) {
            $query2 = DB::table('t_general_ledger')
                ->join('d_general_ledger','d_general_ledger.t_gl_id','=','t_general_ledger.id')
                ->select('t_general_ledger.*')
                ->where('general_ledger_status', 'post')
                ->where('general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
                ->where('general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('general_ledger_date',date('Y-m-d',strtotime($raw_data->tgl)));

            if ($request->akun != null) {
                $query2->where('d_general_ledger.id_coa', $request->akun);
            }

            $query2->groupBy('t_general_ledger.id');
            $data_gj = $query2->get();

            $raw_data->data_gj = $data_gj;

            foreach ($data_gj as $raw_data2) {
                $data_detail = DB::table('d_general_ledger')
                    ->select('d_general_ledger.*','m_coa.desc','m_coa.code')
                    ->join('m_coa','m_coa.id','=','d_general_ledger.id_coa')
                    ->where('t_gl_id', $raw_data2->id)
                    ->orderBy('sequence')
                    ->get();

                $raw_data2->detail = $data_detail;
            }
        }

        //dd($data);

        $pdf = PDF::loadview('admin.report.laporan-general-journal',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai]);

        $pdf->setPaper('legal', 'landscape');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-general-journal-'.date('dmyhis').'.pdf');
        }
    }

    public function laporanGeneralLedger(Request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        //dd($request->all());
        if ($request->akun == null) {
            $akun = 'ALL';
        }else{
            $coa = DB::table('m_coa')
                ->where('id', $request->akun)
                ->first();
            $akun = $coa->code.' '.$coa->desc;
        }

        if ($request->type == 'summary') {
            // $query = DB::table('d_general_ledger')
            //     ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
            //     ->join('m_coa','m_coa.id','=','d_general_ledger.id_coa')
            //     ->select('d_general_ledger.id_coa','m_coa.desc','m_coa.code','t_general_ledger.general_ledger_date')
            //     ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
            //     ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            // if ($request->akun != null) {
            //     $query->where('d_general_ledger.id_coa', $request->akun);
            // }

            // $query->groupBy('id_coa','desc','code','general_ledger_date');
            // $query->orderBy('id_coa');

            // $list_coa = $query->get();

            $query = DB::table('m_coa');
            $query->select('id as id_coa','code','desc');

            if ($request->akun != null) {
                $query->where('m_coa.id', $request->akun);
            }

            $list_coa = $query->get();

            $data_pembanding = DB::table('m_coa')->get();

            foreach ($list_coa as $key => $raw_data) {
                $count = $raw_data->code.'=';
                $pos = '';
                $jumlah = 0;
                foreach ($data_pembanding as $raw_data2) {
                    if (stripos($raw_data2->code, $raw_data->code) !== false) {
                        // $pos = $pos.stripos($raw_data2->code, $raw_data->code);
                        // $count = $count.'-'.$raw_data2->code;
                        if (stripos($raw_data2->code, $raw_data->code) == 0) {
                            $jumlah++;
                        }
                    }
                }
                // $raw_data->pos = $jumlah;
                // $raw_data->count = $count;
                if ($jumlah > 1) {
                    unset($list_coa[$key]);
                }
            }

            foreach ($list_coa as $raw_data) {
                $saldoawal = DB::table('m_saldo_awal_coa')
                    ->where('id_coa', $raw_data->id_coa)
                    ->sum('total');

                $raw_data->saldo_awal = $saldoawal;

                $debet = DB::table('d_general_ledger')
                    ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
                    ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('d_general_ledger.debet_credit', 'debet')
                    ->where('d_general_ledger.id_coa', $raw_data->id_coa)
                    ->sum('total');

                $raw_data->debet = $debet;

                $credit = DB::table('d_general_ledger')
                    ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
                    ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('d_general_ledger.debet_credit', 'credit')
                    ->where('d_general_ledger.id_coa', $raw_data->id_coa)
                    ->sum('total');

                $raw_data->credit = $credit;
            }

            //dd($list_coa);

            $pdf = PDF::loadview('admin.report.laporan-general-ledger-summary',['list_coa' => $list_coa,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai, 'akun' => $akun]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-general-ledger-summary-'.date('dmyhis').'.pdf');
            }
        }
        else{
            // $query = DB::table('d_general_ledger')
            //     ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
            //     ->join('m_coa','m_coa.id','=','d_general_ledger.id_coa')
            //     ->select('d_general_ledger.id_coa','m_coa.desc','m_coa.code','t_general_ledger.general_ledger_date')
            //     ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
            //     ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            // if ($request->akun != null) {
            //     $query->where('d_general_ledger.id_coa', $request->akun);
            // }

            // $query->groupBy('id_coa','desc','code','general_ledger_date');
            // $query->orderBy('id_coa');
            // $data = $query->get();

            $query = DB::table('m_coa');
            $query->select('id as id_coa','code','desc');

            if ($request->akun != null) {
                $query->where('m_coa.id', $request->akun);
            }
            $query->orderBy('m_coa.id');

            $list_coa = $query->get();

            $data_pembanding = DB::table('m_coa')->get();

            foreach ($list_coa as $key => $raw_data) {
                $count = $raw_data->code.'=';
                $pos = '';
                $jumlah = 0;
                foreach ($data_pembanding as $raw_data2) {
                    if (stripos($raw_data2->code, $raw_data->code) !== false) {
                        // $pos = $pos.stripos($raw_data2->code, $raw_data->code);
                        // $count = $count.'-'.$raw_data2->code;
                        if (stripos($raw_data2->code, $raw_data->code) == 0) {
                            $jumlah++;
                        }
                    }
                }
                // $raw_data->pos = $jumlah;
                // $raw_data->count = $count;
                if ($jumlah > 1) {
                    unset($list_coa[$key]);
                }
            }

            foreach ($list_coa as $raw_data) {
                $saldoawal = DB::table('m_saldo_awal_coa')
                    ->where('id_coa', $raw_data->id_coa)
                    ->sum('total');

                $raw_data->saldo_awal = $saldoawal;

                $detail = DB::table('d_general_ledger')
                    ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
                    ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
                    ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('d_general_ledger.id_coa', $raw_data->id_coa)
                    ->select('d_general_ledger.*','t_general_ledger.general_ledger_date')
                    ->orderBy('d_general_ledger.created_at')
                    ->get();

                $raw_data->detail = $detail;
            }

            // dd($list_coa);

            $pdf = PDF::loadview('admin.report.laporan-general-ledger-detail',['list_coa' => $list_coa,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai, 'akun' => $akun]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-general-ledger-detail-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function laporanTrialBalance(Request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        //dd($request->all());

        $list_coa = DB::table('d_general_ledger')
            ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
            ->join('m_coa','m_coa.id','=','d_general_ledger.id_coa')
            ->select('d_general_ledger.id_coa','m_coa.desc','m_coa.code')
            ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('id_coa','desc','code')
            ->orderBy('id_coa')
            ->get();

        // $list_coa = DB::table('m_coa')->get();

        // $data_pembanding = DB::table('m_coa')->get();

        // foreach ($list_coa as $key => $raw_data) {
        //     $count = $raw_data->code.'=';
        //     $pos = '';
        //     $jumlah = 0;
        //     foreach ($data_pembanding as $raw_data2) {
        //         if (stripos($raw_data2->code, $raw_data->code) !== false) {
        //             // $pos = $pos.stripos($raw_data2->code, $raw_data->code);
        //             // $count = $count.'-'.$raw_data2->code;
        //             if (stripos($raw_data2->code, $raw_data->code) == 0) {
        //                 $jumlah++;
        //             }
        //         }
        //     }
        //     // $raw_data->pos = $jumlah;
        //     // $raw_data->count = $count;
        //     if ($jumlah > 1) {
        //         unset($list_coa[$key]);
        //     }
        // }

        foreach ($list_coa as $raw_data) {
            $saldoawal = DB::table('m_saldo_awal_coa')
                ->where('id_coa', $raw_data->id_coa)
                ->sum('total');

            $raw_data->saldo_awal = $saldoawal;

            $debet = DB::table('d_general_ledger')
                ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
                ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
                ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('d_general_ledger.debet_credit', 'debet')
                ->where('d_general_ledger.id_coa', $raw_data->id_coa)
                ->sum('total');

            $raw_data->debet = $debet;

            $credit = DB::table('d_general_ledger')
                ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
                ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
                ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('d_general_ledger.debet_credit', 'credit')
                ->where('d_general_ledger.id_coa', $raw_data->id_coa)
                ->sum('total');

            $raw_data->credit = $credit;
        }

        //dd($list_coa);

        $pdf = PDF::loadview('admin.report.laporan-trial-balance',['list_coa' => $list_coa,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai]);

        $pdf->setPaper('legal', 'landscape');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-trial-balance-'.date('dmyhis').'.pdf');
        }
    }

    public function laporanPencairanGiroMasuk(Request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        //dd($request->all());

        $query = DB::table('d_cb_expense_receipt');
            $query->select('t_cash_bank.id_coa','m_coa.code','m_coa.desc');
            $query->where('t_cash_bank.cash_bank_group', 'PENCAIRANIN');
            $query->where('t_cash_bank.cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
            $query->where('t_cash_bank.cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
            if ($request->cash_bank != null) {
                $query->where('t_cash_bank.cash_bank_code', $request->cash_bank);
            }
            if ($request->akun != null) {
                $query->where('t_cash_bank.id_coa', $request->akun);
            }
            $query->join('t_cash_bank','t_cash_bank.cash_bank_code','=','d_cb_expense_receipt.cash_bank_code');
            $query->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');
            $query->groupBy('t_cash_bank.id_coa','m_coa.code','m_coa.desc');

        $data = $query->get();

        foreach ($data as $raw_data) {
            $query2 = DB::table('d_cb_expense_receipt');
                $query2->select('t_cash_bank.cash_bank_date','t_cash_bank.cash_bank_code','t_cash_bank.cash_bank_ref as no_pencairan','d_cb_expense_receipt.ref as no_giro','d_cb_expense_receipt.total');
                $query2->where('t_cash_bank.cash_bank_group', 'PENCAIRANIN');
                $query2->where('t_cash_bank.cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
                $query2->where('t_cash_bank.cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
                if ($request->cash_bank != null) {
                    $query2->where('t_cash_bank.cash_bank_code', $request->cash_bank);
                }
                if ($request->akun != null) {
                    $query2->where('t_cash_bank.id_coa', $request->akun);
                }else{
                    $query2->where('t_cash_bank.id_coa', $raw_data->id_coa);
                }
                $query2->join('t_cash_bank','t_cash_bank.cash_bank_code','=','d_cb_expense_receipt.cash_bank_code');
                $query2->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');

            $detail = $query2->get();

            $raw_data->detail = $detail;
        }

        //dd($data);

        $pdf = PDF::loadview('admin.report.laporan-pencairan-giro-masuk',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai]);

        $pdf->setPaper('legal', 'landscape');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-pencairan-giro-masuk-'.date('dmyhis').'.pdf');
        }
    }

    public function laporanPencairanGiroKeluar(Request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        //dd($request->all());

        $query = DB::table('d_cb_expense_receipt');
            $query->select('t_cash_bank.id_coa','m_coa.code','m_coa.desc');
            $query->where('t_cash_bank.cash_bank_group', 'PENCAIRANOUT');
            $query->where('t_cash_bank.cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
            $query->where('t_cash_bank.cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
            if ($request->cash_bank != null) {
                $query->where('t_cash_bank.cash_bank_code', $request->cash_bank);
            }
            if ($request->akun != null) {
                $query->where('t_cash_bank.id_coa', $request->akun);
            }
            $query->join('t_cash_bank','t_cash_bank.cash_bank_code','=','d_cb_expense_receipt.cash_bank_code');
            $query->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');
            $query->groupBy('t_cash_bank.id_coa','m_coa.code','m_coa.desc');

        $data = $query->get();

        foreach ($data as $raw_data) {
            $query2 = DB::table('d_cb_expense_receipt');
                $query2->select('t_cash_bank.cash_bank_date','t_cash_bank.cash_bank_code','t_cash_bank.cash_bank_ref as no_pencairan','d_cb_expense_receipt.ref as no_giro','d_cb_expense_receipt.total');
                $query2->where('t_cash_bank.cash_bank_group', 'PENCAIRANOUT');
                $query2->where('t_cash_bank.cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)));
                $query2->where('t_cash_bank.cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
                if ($request->cash_bank != null) {
                    $query2->where('t_cash_bank.cash_bank_code', $request->cash_bank);
                }
                if ($request->akun != null) {
                    $query2->where('t_cash_bank.id_coa', $request->akun);
                }else{
                    $query2->where('t_cash_bank.id_coa', $raw_data->id_coa);
                }
                $query2->join('t_cash_bank','t_cash_bank.cash_bank_code','=','d_cb_expense_receipt.cash_bank_code');
                $query2->join('m_coa','m_coa.id','=','t_cash_bank.id_coa');

            $detail = $query2->get();

            $raw_data->detail = $detail;
        }

        //dd($data);

        $pdf = PDF::loadview('admin.report.laporan-pencairan-giro-keluar',['data' => $data,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai]);

        $pdf->setPaper('legal', 'landscape');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-pencairan-giro-keluar-'.date('dmyhis').'.pdf');
        }
    }

    public function laporanNeraca(Request $request, $type)
    {
        //dd($request->all());

        if ($request->type == 'summary') {
            $data = array(
                (object)array("name" => "KAS","code_coa"=>$this->getCOA('10101'),"total"=>[]),
                (object)array("name" => "PIUTANG USAHA","code_coa"=>$this->getCOA('1010401'),"total"=>[]),
                (object)array("name" => "PIUTANG GIRO","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "BANK","code_coa"=>$this->getCOA('10102'),"total"=>[]),
                (object)array("name" => "DEPOSITO","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "PIUTANG YANG LAIN-LAIN","code_coa"=>$this->getCOA('1010403,1010402,1010404,1010405,1010406'),"total"=>[]),
                (object)array("name" => "PERSEDIAAN","code_coa"=>$this->getCOA('10105'),"total"=>[]),
                (object)array("name" => "BIAYA DIBAYAR DIMUKA","code_coa"=>$this->getCOA('10107'),"total"=>[]),
                (object)array("name" => "UANG MUKA PEMBELIAN","code_coa"=>$this->getCOA('10106'),"total"=>[]),
                (object)array("name" => "PPN MASUKAN","code_coa"=>$this->getCOA('10110'),"total"=>[]),
                //(object)array("name" => "SUBTOTAL AKTIVA LANCAR","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "INVESTASI JANGKA PENDEK","code_coa"=>$this->getCOA(''),"total"=>[]),
                //(object)array("name" => "SUBTOTAL AKTIVA TETAP","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "HARGA PEROLEHAN","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "AKUMULASI PENYUSUTAN","code_coa"=>$this->getCOA('103'),"total"=>[]),
                //(object)array("name" => "SUBTOTAL AKTIVA LAIN-LAIN","code_coa"=>$this->getCOA(''),"total"=>[]),
                //(object)array("name" => "TOTAL AKTIVA","code_coa"=>$this->getCOA(''),"total"=>[]),

                (object)array("name" => "HUTANG USAHA","code_coa"=>$this->getCOA('20101'),"total"=>[]),
                (object)array("name" => "BIAYA YANG MASIH HARUS DIBAYAR","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "UANG MUKA PENJUALAN","code_coa"=>$this->getCOA('20102'),"total"=>[]),
                (object)array("name" => "HUTANG GIRO","code_coa"=>$this->getCOA('20103'),"total"=>[]),
                (object)array("name" => "HUTANG PAJAK","code_coa"=>$this->getCOA(''),"total"=>[]),
                //(object)array("name" => "SUBTOTAL PASIVA LANCAR","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "MODAL","code_coa"=>$this->getCOA('301'),"total"=>[]),
                (object)array("name" => "LABA/RUGI DITAHAN","code_coa"=>$this->getCOA('303'),"total"=>[]),
                (object)array("name" => "LABA/RUGI TAHUN BERJALAN","code_coa"=>$this->getCOA('30401'),"total"=>[]),
                (object)array("name" => "LABA/RUGI BULAN BERJALAN","code_coa"=>$this->getCOA('30402'),"total"=>[]),
                (object)array("name" => "PRIVE","code_coa"=>$this->getCOA('305'),"total"=>[]),
                //(object)array("name" => "SUBTOTAL MODAL","code_coa"=>$this->getCOA(''),"total"=>[]),
                //(object)array("name" => "TOTAL PASIVA","code_coa"=>$this->getCOA(''),"total"=>[]),
            );

            //dd($data);

            if ($request->range == 'tahunan') {
                $total_aktiva_lancar = [];
                $total_aktiva_tetap = [];
                $total_aktiva_lain = [];
                $grandtotal_aktiva = [];

                $total_pasiva_lancar = [];
                $total_modal = [];
                $grandtotal_pasiva = [];

                for ($i=1; $i < 13; $i++) {
                    $total_aktiva_lancar[$i] = 0;
                    $total_aktiva_tetap[$i] = 0;
                    $total_aktiva_lain[$i] = 0;
                    $grandtotal_aktiva[$i] = 0;

                    $total_pasiva_lancar[$i] = 0;
                    $total_modal[$i] = 0;
                    $grandtotal_pasiva[$i] = 0;
                }

                foreach ($data as $key => $raw_data) {
                    for ($i=1; $i < 13; $i++) {
                        $pick = date('Y', strtotime('01-'.$request->periode));

                        $date = '01-'.$i.'-'.$pick;

                        //$date = '01-'.$i.'-'.$request->periode;

                        $cek = DB::table('m_periode_closing')
                            ->whereMonth('periode',date('m', strtotime($date)))
                            ->whereYear('periode',date('Y', strtotime($date)))
                            ->where('type','accounting')
                            ->count();

                        $total = 0;
                        if ($cek > 0) {
                            foreach ($raw_data->code_coa as $raw_data2) {
                                $nilai = DB::table('t_closing_accounting')
                                    ->whereMonth('periode',date('m', strtotime($date)))
                                    ->whereYear('periode',date('Y', strtotime($date)))
                                    ->where('id_coa',$raw_data2->id)
                                    ->sum('total_balance');

                                $total = $total + $nilai;
                            }
                            $raw_data->total[$i] = $total;
                        }else{
                            $raw_data->total[$i] = 0;
                        }

                        //total aktiva lancar
                        if ($key >= 0 && $key < 10) {
                            $total_aktiva_lancar[$i] = $total_aktiva_lancar[$i] + $total;
                        }
                        elseif ($key == 10) {
                            $total_aktiva_tetap[$i] = $total_aktiva_tetap[$i] + $total;
                        }
                        elseif ($key >= 11 && $key < 13) {
                            $total_aktiva_lain[$i] = $total_aktiva_lain[$i] + $total;
                        }
                        elseif ($key >= 13 && $key < 18) {
                            $total_pasiva_lancar[$i] = $total_pasiva_lancar[$i] + $total;
                        }
                        elseif ($key >= 18 && $key < 23) {
                            $total_modal[$i] = $total_modal[$i] + $total;
                        }
                    }
                }

                for ($i=1; $i < 13; $i++) {
                    $grandtotal_aktiva[$i] = $total_aktiva_lancar[$i] + $total_aktiva_tetap[$i] + $total_aktiva_lain[$i];
                    $grandtotal_pasiva[$i] = $total_pasiva_lancar[$i] + $total_modal[$i];
                }

                //dd($data);

                $pdf = PDF::loadview('admin.report.laporan-neraca-summary',[
                    'data' => $data,
                    'periode' => $request->periode,
                    'total_aktiva_lancar' => $total_aktiva_lancar,
                    'total_aktiva_tetap' => $total_aktiva_tetap,
                    'total_aktiva_lain' => $total_aktiva_lain,
                    'grandtotal_aktiva' => $grandtotal_aktiva,
                    'total_pasiva_lancar' => $total_pasiva_lancar,
                    'total_modal' => $total_modal,
                    'grandtotal_pasiva' => $grandtotal_pasiva,
                ]);

                $pdf->setPaper('legal', 'landscape');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-neraca-summary-'.date('dmyhis').'.pdf');
                }
            }
            else{
                $total_aktiva_lancar = 0;
                $total_aktiva_tetap = 0;
                $total_aktiva_lain = 0;
                $grandtotal_aktiva = 0;

                $total_pasiva_lancar = 0;
                $total_modal = 0;
                $grandtotal_pasiva = 0;

                foreach ($data as $key => $raw_data) {
                    // $pick = date('Y', strtotime('01-'.$request->periode));
                    // $date = '01-'.$i.'-'.$pick;

                    $date = '01-'.$request->periode;

                    $cek = DB::table('m_periode_closing')
                        ->whereMonth('periode',date('m', strtotime($date)))
                        ->whereYear('periode',date('Y', strtotime($date)))
                        ->where('type','accounting')
                        ->count();

                    $total = 0;
                    if ($cek > 0) {
                        foreach ($raw_data->code_coa as $raw_data2) {
                            $nilai = DB::table('t_closing_accounting')
                                ->whereMonth('periode',date('m', strtotime($date)))
                                ->whereYear('periode',date('Y', strtotime($date)))
                                ->where('id_coa',$raw_data2->id)
                                ->sum('total_balance');

                            $total = $total + $nilai;
                        }
                        $raw_data->total = $total;
                    }else{
                        $raw_data->total = 0;
                    }

                    //total aktiva lancar
                    if ($key >= 0 && $key < 10) {
                        $total_aktiva_lancar = $total_aktiva_lancar + $total;
                    }
                    elseif ($key == 10) {
                        $total_aktiva_tetap = $total_aktiva_tetap + $total;
                    }
                    elseif ($key >= 11 && $key < 13) {
                        $total_aktiva_lain = $total_aktiva_lain + $total;
                    }
                    elseif ($key >= 13 && $key < 18) {
                        $total_pasiva_lancar = $total_pasiva_lancar + $total;
                    }
                    elseif ($key >= 18 && $key < 23) {
                        $total_modal = $total_modal + $total;
                    }

                }

                $grandtotal_aktiva = $total_aktiva_lancar + $total_aktiva_tetap + $total_aktiva_lain;
                $grandtotal_pasiva = $total_pasiva_lancar + $total_modal;

                //dd($data);

                $pdf = PDF::loadview('admin.report.laporan-neraca-summary-bln',[
                    'data' => $data,
                    'periode' => $request->periode,
                    'total_aktiva_lancar' => $total_aktiva_lancar,
                    'total_aktiva_tetap' => $total_aktiva_tetap,
                    'total_aktiva_lain' => $total_aktiva_lain,
                    'grandtotal_aktiva' => $grandtotal_aktiva,
                    'total_pasiva_lancar' => $total_pasiva_lancar,
                    'total_modal' => $total_modal,
                    'grandtotal_pasiva' => $grandtotal_pasiva,
                ]);

                $pdf->setPaper('legal', 'potrait');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-neraca-summary-'.date('dmyhis').'.pdf');
                }
            }
        }
        else{
            $data = array(
                (object)array("name" => "KAS","code_coa"=>$this->getCOA('10101'),"total"=>[]),
                (object)array("name" => "PIUTANG USAHA","code_coa"=>$this->getCOA('1010401'),"total"=>[]),
                (object)array("name" => "PIUTANG GIRO","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "BANK","code_coa"=>$this->getCOA('10102'),"total"=>[]),
                (object)array("name" => "DEPOSITO","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "PIUTANG YANG LAIN-LAIN","code_coa"=>$this->getCOA('1010403,1010402,1010404,1010405,1010406'),"total"=>[]),
                (object)array("name" => "PERSEDIAAN","code_coa"=>$this->getCOA('10105'),"total"=>[]),
                (object)array("name" => "BIAYA DIBAYAR DIMUKA","code_coa"=>$this->getCOA('10107'),"total"=>[]),
                (object)array("name" => "UANG MUKA PEMBELIAN","code_coa"=>$this->getCOA('10106'),"total"=>[]),
                (object)array("name" => "PPN MASUKAN","code_coa"=>$this->getCOA('10110'),"total"=>[]),
                //(object)array("name" => "SUBTOTAL AKTIVA LANCAR","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "INVESTASI JANGKA PENDEK","code_coa"=>$this->getCOA(''),"total"=>[]),
                //(object)array("name" => "SUBTOTAL AKTIVA TETAP","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "HARGA PEROLEHAN","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "AKUMULASI PENYUSUTAN","code_coa"=>$this->getCOA('103'),"total"=>[]),
                //(object)array("name" => "SUBTOTAL AKTIVA LAIN-LAIN","code_coa"=>$this->getCOA(''),"total"=>[]),
                //(object)array("name" => "TOTAL AKTIVA","code_coa"=>$this->getCOA(''),"total"=>[]),

                (object)array("name" => "HUTANG USAHA","code_coa"=>$this->getCOA('20101'),"total"=>[]),
                (object)array("name" => "BIAYA YANG MASIH HARUS DIBAYAR","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "UANG MUKA PENJUALAN","code_coa"=>$this->getCOA('20102'),"total"=>[]),
                (object)array("name" => "HUTANG GIRO","code_coa"=>$this->getCOA('20103'),"total"=>[]),
                (object)array("name" => "HUTANG PAJAK","code_coa"=>$this->getCOA(''),"total"=>[]),
                //(object)array("name" => "SUBTOTAL PASIVA LANCAR","code_coa"=>$this->getCOA(''),"total"=>[]),
                (object)array("name" => "MODAL","code_coa"=>$this->getCOA('301'),"total"=>[]),
                (object)array("name" => "LABA/RUGI DITAHAN","code_coa"=>$this->getCOA('303'),"total"=>[]),
                (object)array("name" => "LABA/RUGI TAHUN BERJALAN","code_coa"=>$this->getCOA('30401'),"total"=>[]),
                (object)array("name" => "LABA/RUGI BULAN BERJALAN","code_coa"=>$this->getCOA('30402'),"total"=>[]),
                (object)array("name" => "PRIVE","code_coa"=>$this->getCOA('305'),"total"=>[]),
                //(object)array("name" => "SUBTOTAL MODAL","code_coa"=>$this->getCOA(''),"total"=>[]),
                //(object)array("name" => "TOTAL PASIVA","code_coa"=>$this->getCOA(''),"total"=>[]),
            );

            //dd($data);

            if ($request->range == 'tahunan') {
                $total_aktiva_lancar = [];
                $total_aktiva_tetap = [];
                $total_aktiva_lain = [];
                $grandtotal_aktiva = [];

                $total_pasiva_lancar = [];
                $total_modal = [];
                $grandtotal_pasiva = [];

                for ($i=1; $i < 13; $i++) {
                    $total_aktiva_lancar[$i] = 0;
                    $total_aktiva_tetap[$i] = 0;
                    $total_aktiva_lain[$i] = 0;
                    $grandtotal_aktiva[$i] = 0;

                    $total_pasiva_lancar[$i] = 0;
                    $total_modal[$i] = 0;
                    $grandtotal_pasiva[$i] = 0;
                }

                foreach ($data as $key => $raw_data) {
                    for ($i=1; $i < 13; $i++) {
                        $date = '01-'.$i.'-'.$request->periode;

                        $cek = DB::table('m_periode_closing')
                            ->whereMonth('periode',date('m', strtotime($date)))
                            ->whereYear('periode',date('Y', strtotime($date)))
                            ->where('type','accounting')
                            ->count();

                        $total = 0;
                        if ($cek > 0) {
                            foreach ($raw_data->code_coa as $raw_data2) {
                                $nilai = DB::table('t_closing_accounting')
                                    ->whereMonth('periode',date('m', strtotime($date)))
                                    ->whereYear('periode',date('Y', strtotime($date)))
                                    ->where('id_coa',$raw_data2->id)
                                    ->sum('total_balance');

                                $total = $total + $nilai;
                            }
                            $raw_data->total[$i] = $total;
                        }else{
                            $raw_data->total[$i] = 0;
                        }

                        //total aktiva lancar
                        if ($key >= 0 && $key < 10) {
                            $total_aktiva_lancar[$i] = $total_aktiva_lancar[$i] + $total;
                        }
                        elseif ($key == 10) {
                            $total_aktiva_tetap[$i] = $total_aktiva_tetap[$i] + $total;
                        }
                        elseif ($key >= 11 && $key < 13) {
                            $total_aktiva_lain[$i] = $total_aktiva_lain[$i] + $total;
                        }
                        elseif ($key >= 13 && $key < 18) {
                            $total_pasiva_lancar[$i] = $total_pasiva_lancar[$i] + $total;
                        }
                        elseif ($key >= 18 && $key < 23) {
                            $total_modal[$i] = $total_modal[$i] + $total;
                        }
                    }
                }

                for ($i=1; $i < 13; $i++) {
                    $grandtotal_aktiva[$i] = $total_aktiva_lancar[$i] + $total_aktiva_tetap[$i] + $total_aktiva_lain[$i];
                    $grandtotal_pasiva[$i] = $total_pasiva_lancar[$i] + $total_modal[$i];
                }

                //dd($data);

                $pdf = PDF::loadview('admin.report.laporan-neraca-summary',[
                    'data' => $data,
                    'periode' => $request->periode,
                    'total_aktiva_lancar' => $total_aktiva_lancar,
                    'total_aktiva_tetap' => $total_aktiva_tetap,
                    'total_aktiva_lain' => $total_aktiva_lain,
                    'grandtotal_aktiva' => $grandtotal_aktiva,
                    'total_pasiva_lancar' => $total_pasiva_lancar,
                    'total_modal' => $total_modal,
                    'grandtotal_pasiva' => $grandtotal_pasiva,
                ]);

                // $pdf = PDF::loadview('admin.report.laporan-neraca-detail',['data' => $data,'periode' => $request->periode]);

                $pdf->setPaper('legal', 'landscape');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-neraca-detail-'.date('dmyhis').'.pdf');
                }
            }
            else{
                $total_aktiva_lancar = 0;
                $total_aktiva_tetap = 0;
                $total_aktiva_lain = 0;
                $grandtotal_aktiva = 0;

                $total_pasiva_lancar = 0;
                $total_modal = 0;
                $grandtotal_pasiva = 0;

                foreach ($data as $key => $raw_data) {
                    // $pick = date('Y', strtotime('01-'.$request->periode));
                    // $date = '01-'.$i.'-'.$pick;

                    $date = '01-'.$request->periode;

                    $cek = DB::table('m_periode_closing')
                        ->whereMonth('periode',date('m', strtotime($date)))
                        ->whereYear('periode',date('Y', strtotime($date)))
                        ->where('type','accounting')
                        ->count();

                    $total = 0;
                    if ($cek > 0) {
                        foreach ($raw_data->code_coa as $raw_data2) {
                            $nilai = DB::table('t_closing_accounting')
                                ->whereMonth('periode',date('m', strtotime($date)))
                                ->whereYear('periode',date('Y', strtotime($date)))
                                ->where('id_coa',$raw_data2->id)
                                ->sum('total_balance');

                            $total = $total + $nilai;
                        }
                        $raw_data->total = $total;
                    }else{
                        $raw_data->total = 0;
                    }

                    //total aktiva lancar
                    if ($key >= 0 && $key < 10) {
                        $total_aktiva_lancar = $total_aktiva_lancar + $total;
                    }
                    elseif ($key == 10) {
                        $total_aktiva_tetap = $total_aktiva_tetap + $total;
                    }
                    elseif ($key >= 11 && $key < 13) {
                        $total_aktiva_lain = $total_aktiva_lain + $total;
                    }
                    elseif ($key >= 13 && $key < 18) {
                        $total_pasiva_lancar = $total_pasiva_lancar + $total;
                    }
                    elseif ($key >= 18 && $key < 23) {
                        $total_modal = $total_modal + $total;
                    }

                }

                $grandtotal_aktiva = $total_aktiva_lancar + $total_aktiva_tetap + $total_aktiva_lain;
                $grandtotal_pasiva = $total_pasiva_lancar + $total_modal;

                //dd($data);

                $pdf = PDF::loadview('admin.report.laporan-neraca-summary-bln',[
                    'data' => $data,
                    'periode' => $request->periode,
                    'total_aktiva_lancar' => $total_aktiva_lancar,
                    'total_aktiva_tetap' => $total_aktiva_tetap,
                    'total_aktiva_lain' => $total_aktiva_lain,
                    'grandtotal_aktiva' => $grandtotal_aktiva,
                    'total_pasiva_lancar' => $total_pasiva_lancar,
                    'total_modal' => $total_modal,
                    'grandtotal_pasiva' => $grandtotal_pasiva,
                ]);

                // $pdf = PDF::loadview('admin.report.laporan-neraca-detail',['data' => $data,'periode' => $request->periode]);

                $pdf->setPaper('legal', 'potrait');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-neraca-detail-'.date('dmyhis').'.pdf');
                }
            }
        }
    }

    public function laporanLabaRugi(Request $request, $type)
    {
        //sub coa
        $data = array(
            (object)array("name" => "PENJUALAN BERSIH","code_coa"=>$this->getCOA('4')),
            (object)array("name" => "HARGA POKOK PENJUALAN","code_coa"=>$this->getCOA('601')),

            (object)array("name" => "BIAYA OPERASIONAL","code_coa"=>$this->getCOA('7')),
            (object)array("name" => "BIAYA NON OPERASIONAL","code_coa"=>$this->getCOA('9')),

            (object)array("name" => "PENDAPATAN BUNGA BANK","code_coa"=>$this->getCOA('801')),
            (object)array("name" => "PENDAPATAN BUNGA LAIN-LAIN","code_coa"=>$this->getCOA('802')),
            (object)array("name" => "PENDAPATAN NON OPERASI LAIN","code_coa"=>$this->getCOA('804')),
        );

        //dd($data);

        if ($request->range == 'tahunan') {
            foreach ($data as $raw_data) {
                //untuk sub total
                $sub_total = array();
                for ($i=1; $i < 13; $i++) {
                    $sub_total[$i] = 0;
                }
                foreach ($raw_data->code_coa as $raw_data2) {
                    $total = array();
                    $sum = 0;
                    for ($i=1; $i < 13; $i++) {
                        $pick = date('Y', strtotime('01-'.$request->periode));

                        $date = '01-'.$i.'-'.$pick;

                        $cek = DB::table('m_periode_closing')
                            ->whereMonth('periode',date('m', strtotime($date)))
                            ->whereYear('periode',date('Y', strtotime($date)))
                            ->where('type','accounting')
                            ->count();

                        //untuk total per coa
                        $total[$i] = 0;
                        if ($cek > 0) {
                            $nilai = DB::table('t_closing_accounting')
                                ->whereMonth('periode',date('m', strtotime($date)))
                                ->whereYear('periode',date('Y', strtotime($date)))
                                ->where('id_coa',$raw_data2->id)
                                ->sum('total_balance');

                            if ($nilai < 0 ) {
                                $nilai = $nilai * -1;
                            }

                            $total[$i] = $nilai;
                        }else{
                            $total[$i] = 0;
                        }
                        //count subtotal per sub coa
                        $sub_total[$i] = $sub_total[$i] + $total[$i];
                        $sum = $sum + $total[$i];
                    }
                    $raw_data2->total = $total;
                    $raw_data2->sum = $sum;
                }
                $raw_data->sub_total = $sub_total;
            }

            //dd($data);

            if ($request->type == 'summary') {
                $pdf = PDF::loadview('admin.report.laporan-laba-rugi-summary',['data' => $data,'periode' => $pick]);

                $pdf->setPaper('legal', 'landscape');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-laba-rugi-summary-'.date('dmyhis').'.pdf');
                }
            }else{
                $pdf = PDF::loadview('admin.report.laporan-laba-rugi-detail',['data' => $data,'periode' => $pick]);

                $pdf->setPaper('legal', 'landscape');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-laba-rugi-detail-'.date('dmyhis').'.pdf');
                }
            }
        }else{
            //bulanan
            foreach ($data as $raw_data) {
                //untuk sub total
                $sub_total = 0;
                foreach ($raw_data->code_coa as $raw_data2) {
                    $total = 0;
                    $sum = 0;

                    $date = '01-'.$request->periode;

                    $cek = DB::table('m_periode_closing')
                        ->whereMonth('periode',date('m', strtotime($date)))
                        ->whereYear('periode',date('Y', strtotime($date)))
                        ->where('type','accounting')
                        ->count();

                    //untuk total per coa
                    $total = 0;
                    if ($cek > 0) {
                        $nilai = DB::table('t_closing_accounting')
                            ->whereMonth('periode',date('m', strtotime($date)))
                            ->whereYear('periode',date('Y', strtotime($date)))
                            ->where('id_coa',$raw_data2->id)
                            ->sum('total_balance');

                        if ($nilai < 0 ) {
                            $nilai = $nilai * -1;
                        }

                        $total = $nilai;
                    }else{
                        $total = 0;
                    }
                    //count subtotal per sub coa
                    $sub_total = $sub_total + $total;
                    $sum = $sum + $total;

                    $raw_data2->total = $total;
                    $raw_data2->sum = $sum;
                }
                $raw_data->sub_total = $sub_total;
            }

            // dd($data);

            if ($request->type == 'summary') {
                $pdf = PDF::loadview('admin.report.laporan-laba-rugi-summary-bln',['data' => $data,'periode' => $request->periode]);

                $pdf->setPaper('legal', 'potrait');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-laba-rugi-summary-bln-'.date('dmyhis').'.pdf');
                }
            }else{
                $pdf = PDF::loadview('admin.report.laporan-laba-rugi-detail-bln',['data' => $data,'periode' => $request->periode]);

                $pdf->setPaper('legal', 'potrait');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-laba-rugi-detail-bln-'.date('dmyhis').'.pdf');
                }
            }
        }

    }

    public function laporanHpp(Request $request, $type)
    {
        // dd($request->all());

        if ($request->barang == null) {
            $barang = 'All';
        }else{
            $barang = DB::table('m_produk')
                ->where('id', $request->barang)
                ->pluck('name')
                ->first();
        }

        $query = DB::table('m_produk');
        if ($request->barang != null) {
            $query->where('id', $request->barang);
        }
        $data = $query->get();

        $date = '01-'.$request->periode;

        foreach ($data as $raw_data) {
            $cek = DB::table('m_periode_closing')
                ->whereMonth('periode',date('m', strtotime($date)))
                ->whereYear('periode',date('Y', strtotime($date)))
                ->where('type','hpp')
                ->count();

            if ($cek > 0) {
                $hpp = DB::table('t_closing_hpp')
                    ->whereMonth('periode',date('m', strtotime($date)))
                    ->whereYear('periode',date('Y', strtotime($date)))
                    ->where('id_barang',$raw_data->id)
                    ->first();

                if ($hpp) {
                    $raw_data->old_hpp = $hpp->old_hpp;
                    $raw_data->new_hpp = $hpp->new_hpp;
                    $raw_data->old_stok = $hpp->old_stok;
                    $raw_data->qty_masuk = $hpp->qty_masuk;
                }else{
                    $raw_data->old_hpp = 0;
                    $raw_data->new_hpp = 0;
                    $raw_data->old_stok = 0;
                    $raw_data->qty_masuk = 0;
                }
            }else{
                $raw_data->old_hpp = 0;
                $raw_data->new_hpp = 0;
                $raw_data->old_stok = 0;
                $raw_data->qty_masuk = 0;
            }
        }

        // dd($data);

        $pdf = PDF::loadview('admin.report.laporan-hpp',[
            'periode' => $request->periode,
            'barang' => $barang,
            'data' => $data,
        ]);

        $pdf->setPaper('legal', 'landscape');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-hpp-'.date('dmyhis').'.pdf');
        }
    }

    protected function getCOA($code){
        $code_coa = explode(",", $code);

        if ($code_coa[0]=='') {
            $code_coa = [];
            $data = [];
        }else{
            $query = [];
            for ($i=0; $i < count($code_coa); $i++) {
                $query[$i] = DB::table('m_coa');
                $query[$i]->select('id','code','desc');
                $query[$i]->where('code', 'like', $code_coa[$i].'%');
                if ($i>0) {
                    $query[$i]->union($query[$i-1]);
                }
            }
            $query[count($code_coa)-1]->orderBy('id');

            $data = $query[count($code_coa)-1]->get();

            //show only child
            $data_pembanding = $query[count($code_coa)-1]->get();

            foreach ($data as $key => $raw_data) {
                $count = $raw_data->code.'=';
                $pos = '';
                $jumlah = 0;
                foreach ($data_pembanding as $raw_data2) {
                    if (stripos($raw_data2->code, $raw_data->code) !== false) {
                        // $pos = $pos.stripos($raw_data2->code, $raw_data->code);
                        // $count = $count.'-'.$raw_data2->code;
                        if (stripos($raw_data2->code, $raw_data->code) == 0) {
                            $jumlah++;
                        }
                    }
                }
                // $raw_data->pos = $jumlah;
                // $raw_data->count = $count;
                if ($jumlah > 1) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    public function printoutExpense($code)
    {
        $header = DB::table('t_cash_bank')
            ->join('m_coa','m_coa.id','t_cash_bank.id_coa')
            ->where('cash_bank_code',$code)
            ->first();
        if($header->cash_bank_type=='BBK'){
            $type='BANK';
        }
        if($header->cash_bank_type=='BKK'){
            $type='KAS';
        }
        $detail = DB::table('d_cb_expense_receipt')
            ->join('m_coa','m_coa.id','d_cb_expense_receipt.id_coa')
            ->where('cash_bank_code',$code)
            ->get();

    // dd($header);
        $pdf = PDF::loadview('admin.report.expense',['header'=>$header,'detail'=>$detail,'type'=>$type]);

        $pdf->setPaper('legal', 'potrait');


            return $pdf->stream();

            // return $pdf->download('laporan-stok-adjusment/periode : '.$tglmulai.'-'.$tglsampai.'_'.date('dmyhis').'.pdf');

    }

    public function printoutReceipt($code)
    {
        $header = DB::table('t_cash_bank')
        ->join('m_coa','m_coa.id','t_cash_bank.id_coa')
        ->where('cash_bank_code',$code)
        ->first();
        if($header->cash_bank_type=='BBM'){
            $type='BANK';
        }
        if($header->cash_bank_type=='BKM'){
            $type='KAS';
        }
        $detail = DB::table('d_cb_expense_receipt')
        ->join('m_coa','m_coa.id','d_cb_expense_receipt.id_coa')
        ->where('cash_bank_code',$code)
        ->get();

         // dd($header);
        $pdf = PDF::loadview('admin.report.receipt',['header'=>$header,'detail'=>$detail,'type'=>$type]);

        $pdf->setPaper('legal', 'potrait');


            return $pdf->stream();

            // return $pdf->download('laporan-stok-adjusment/periode : '.$tglmulai.'-'.$tglsampai.'_'.date('dmyhis').'.pdf');
    }
    public function printoutMutation($code)
    {
        $header = DB::table('t_cash_bank')
        ->join('m_coa','m_coa.id','t_cash_bank.id_coa')
        ->where('cash_bank_code',$code)
        ->first();
        if($header->cash_bank_type=='BBM'){
            $type='BANK';
        }
        if($header->cash_bank_type=='BKM'){
            $type='KAS';
        }
        $detail = DB::table('d_cb_expense_receipt')
        ->join('m_coa','m_coa.id','d_cb_expense_receipt.id_coa')
        ->where('cash_bank_code',$code)
        ->get();

         // dd($header);
        $pdf = PDF::loadview('admin.report.mutation',['header'=>$header,'detail'=>$detail]);

        $pdf->setPaper('legal', 'potrait');


            return $pdf->stream();

            // return $pdf->download('laporan-stok-adjusment/periode : '.$tglmulai.'-'.$tglsampai.'_'.date('dmyhis').'.pdf');
    }
}
