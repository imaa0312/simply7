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




class ReportAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
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
        //
    }
    public function assetLaporanPo(request $request, $type)
    {
        // $this->validate($request, [
        //     'supplier' => 'required',
        // ]);

        // dd($request->all());

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
            $query = DB::table('t_fixed_asset_po');
            $query->select('t_fixed_asset_po.po_date','t_fixed_asset_po.po_code',
                           'm_supplier.name as supplier_name','t_fixed_asset_po.status_aprove',
                           'grand_total as total','t_fixed_asset_po.diskon_header_potongan',
                           't_fixed_asset_po.diskon_header_persen');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_fixed_asset_po.supplier');
            $query->where('po_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('po_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

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
                $total = DB::table('d_fixed_asset_po')
                ->where('po_code', $raw_data->po_code)
                ->sum('total_neto');

                $raw_data->total_awal = $total;
            }

            // dd($dataPO);

            $pdf = PDF::loadview('admin.report.laporan-po-asset-summary',['dataPO' => $dataPO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'po_code' => $po_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-po-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }else{
            $query = DB::table('d_fixed_asset_po');
            $query->select('t_fixed_asset_po.po_code','d_fixed_asset_po.produk as id_produk','d_fixed_asset_po.qty','d_fixed_asset_po.free_qty','t_fixed_asset_po.po_date','m_produk.name as produk_name','m_supplier.name as supplier_name','m_produk.code as produk_code','d_fixed_asset_po.price','d_fixed_asset_po.total_neto as total_price','d_fixed_asset_po.diskon_potongan','d_fixed_asset_po.diskon_persen');
            $query->join('t_fixed_asset_po', 't_fixed_asset_po.po_code', '=', 'd_fixed_asset_po.po_code');
            $query->join('m_produk', 'm_produk.id', '=', 'd_fixed_asset_po.produk');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_fixed_asset_po.supplier');
            $query->where('po_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('po_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->supplier != null) {
                $query->where('supplier', $request->supplier);
            }

            if ($request->po != '0') {
                $query->where('d_fixed_asset_po.po_code',$request->po);
            }

            if ($request->barang != null) {
                $query->where('d_fixed_asset_po.produk',$request->barang);
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
            $query->orderBy('t_fixed_asset_po.po_date');
            $query->orderBy('d_fixed_asset_po.po_code');

            $dataPO = $query->get();

            //dd($dataPO);

            $pdf = PDF::loadview('admin.report.laporan-po-asset-detail',['dataPO' => $dataPO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'po_code' => $po_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-po-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }
    }
                                          // {{--PRINT TO EXCELL--}} //
                                          //                        //
                                          //                        //
                                          //                        //
                                          ////////////////////////////
    public function reportPOExcel(request $request)
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
            $po_code = 'ALL';
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

        if($request->type == 'summary'){
            $query = DB::table('t_fixed_asset_po');
            $query->select('t_fixed_asset_po.po_date','t_fixed_asset_po.po_code','m_supplier.name as supplier_name','t_fixed_asset_po.status_aprove','grand_total as total','t_fixed_asset_po.diskon_header_potongan','t_fixed_asset_po.diskon_header_persen');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_fixed_asset_po.supplier');
            $query->where('po_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('po_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

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

            $query->orderBy('po_code');

            $dataPO = $query->get();

            foreach ($dataPO as $raw_data) {
                $total = DB::table('d_fixed_asset_po')
                ->where('po_code', $raw_data->po_code)
                ->sum('total_neto');

                $raw_data->total_awal = $total;
            }

            $sheetArray = array();
            $sheetArray[] = array('Laporan Asset PO (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('PO No. : '.$po_code);
            $sheetArray[] = array('Item : '.$barang);
            $sheetArray[] = array('Supplier : '.$supplier);
            $sheetArray[] = array('Status : '.$status);

            $sheetArray[] = array(); // Add an empty row

            $totalHarga = 0;
            //Header
            $sheetArray[] = array('Tanggal', 'No. PO', 'Nama Supplier', 'Status','Total','Disc %','Disc Rp','Total Order');
            // Tambah data tabel
            foreach($dataPO  as $raw_data){
                $sheetArray[] = array(date('d-m-Y',strtotime($raw_data->po_date)),$raw_data->po_code,$raw_data->supplier_name,$raw_data->status_aprove,number_format($raw_data->total_awal,0,'.','.'),$raw_data->diskon_header_persen,$raw_data->diskon_header_potongan,number_format($raw_data->total,0,'.','.'),);
                $totalHarga = $totalHarga + $raw_data->total;
            }

            $jmlRow = count($dataPO);

            Excel::create('Purchase-order-'.$po_code.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow,$totalHarga)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray,$jmlRow,$totalHarga)
                {
                    $sheet->cell('A2', function($cell) {
                        $cell->setFont(array(
                            'size'       => '16',
                        ));
                    });

                    $sheet->setWidth(array(
                        'A'     =>  15,
                        'B'     =>  20,
                        'C'     =>  30,
                        'D'     =>  30,
                        'E'     =>  15,
                        'F'     =>  20,
                        'G'     =>  10,
                        'H'     =>  20,
                    ));

                    $sheet->cell('A9:H9', function($cell) {
                        $cell->setBackground('#C0C0C0');
                    });

                    $sheet->cell('A9:H9', function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->setBorder('A9:H'.(9+$jmlRow), 'thin');

                    $sheet->cell('A9:A'.(9+$jmlRow), function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->cell('E9:E'.(9+$jmlRow), function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->cell('F10:H'.(10+$jmlRow), function($cell) {
                        $cell->setAlignment('right');
                    });

                    $sheet->fromArray($sheetArray);

                    $sheet->cell('E'.(11+$jmlRow), function($cell) {
                        $cell->setValue('Grand Total :');
                        $cell->setFont(array(
                            'bold'       => 'true',
                        ));
                        $cell->setAlignment('right');
                    });

                    $sheet->cell('F'.(11+$jmlRow), function($cell) use ($totalHarga){
                        $cell->setValue('Rp. '.$totalHarga);
                        $cell->setFont(array(
                            'bold'       => 'true',
                        ));
                        $cell->setAlignment('right');
                    });
                });
            })->export('xlsx');
        }else{
            $query = DB::table('d_fixed_asset_po');
            $query->select('t_fixed_asset_po.po_code','d_fixed_asset_po.produk as id_produk','d_fixed_asset_po.qty','t_fixed_asset_po.po_date','m_produk.name as produk_name','m_supplier.name as supplier_name','m_produk.code as produk_code','d_fixed_asset_po.price','d_fixed_asset_po.total_neto');
            $query->join('t_fixed_asset_po', 't_fixed_asset_po.po_code', '=', 'd_fixed_asset_po.po_code');
            $query->join('m_produk', 'm_produk.id', '=', 'd_fixed_asset_po.produk');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_fixed_asset_po.supplier');
            $query->where('po_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('po_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->supplier != null) {
                $query->where('supplier', $request->supplier);
            }

            if ($request->po != '0') {
                $query->where('d_fixed_asset_po.po_code',$request->po);
            }

            if ($request->barang != null) {
                $query->where('d_fixed_asset_po.produk',$request->barang);
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

            $query->orderBy('d_fixed_asset_po.po_code');

            $dataPO = $query->get();

            //dd($dataSO);

            $sheetArray = array();
            $sheetArray[] = array('Laporan Asset PO (Detail)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('PO No. : '.$po_code);
            $sheetArray[] = array('Item : '.$barang);
            $sheetArray[] = array('Supplier : '.$supplier);
            $sheetArray[] = array('Status : '.$status);

            $sheetArray[] = array(); // Add an empty row

            $totalHarga = 0;
            //Header
            $sheetArray[] = array('Tanggal', 'No. PO','Nama Supplier','Kode','Nama Barang','QTY', 'Harga', 'Total Harga');

            foreach($dataPO  as $raw_data){
                $sheetArray[] = array(date('d-m-Y',strtotime($raw_data->po_date)),$raw_data->po_code,$raw_data->supplier_name,$raw_data->produk_code,$raw_data->produk_name,$raw_data->qty,number_format($raw_data->price,0,'.','.'),number_format($raw_data->total_neto,0,'.','.'),);
            }

            $jmlRow = count($dataPO);

            Excel::create('Purchase-order-'.$po_code.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray,$jmlRow)
                {
                    $sheet->cell('A2', function($cell) {
                        $cell->setFont(array(
                            'size'       => '16',
                        ));
                    });

                    $sheet->setWidth(array(
                        'A'     =>  15,
                        'B'     =>  20,
                        'C'     =>  30,
                        'D'     =>  20,
                        'E'     =>  30,
                        'F'     =>  20,
                        'G'     =>  10,
                        'H'     =>  15,
                    ));

                    $sheet->cell('A9:H9', function($cell) {
                        $cell->setBackground('#C0C0C0');
                    });

                    $sheet->cell('A9:H9', function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->setBorder('A9:H'.(9+$jmlRow), 'thin');

                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function assetLaporanConfirmation(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->ac == '0') {
            $asset_conf_code = 'All';
        }else{
            $asset_conf_code = $request->ac;
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        if ($request->type == 'summary') {
            $query = DB::table('t_asset_conf');
            $query->join('t_fixed_asset_pd', 't_fixed_asset_pd.sj_masuk_code', '=', 't_asset_conf.pd_code');
            $query->join('m_produk', 'm_produk.id', '=', 't_asset_conf.barang');
            $query->select('t_asset_conf.*','t_fixed_asset_pd.sj_masuk_date','m_produk.code as code_produk','m_produk.name as produk_name');
            $query->where('rec_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('rec_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->ac != '0') {
                $query->where('asset_conf_code',$request->ac);
            }
            if ($request->status == 'in proccess') {
                $query->where('t_asset_conf.status','in process');
            }
            if ($request->status == 'post') {
                $query->where('t_asset_conf.status','post');
            }
            if ($request->status == 'close') {
                $query->where('t_asset_conf.status','close');
            }
            if ($request->status == 'cancel') {
                $query->where('t_asset_conf.status','cancel');
            }

            $query->orderBy('asset_conf_code');

            $dataAsset = $query->get();

            // foreach ($dataPO as $raw_data) {
            //     $total = DB::table('d_asset')
            //     ->where('asset_code', $raw_data->asset_code)
            //     ->sum('total_neto');

            //     $raw_data->total_awal = $total;
            // }

            // dd($dataAsset);

            $pdf = PDF::loadview('admin.report.laporan-asset-confirmation-summary',['dataAsset' => $dataAsset,'tglmulai' => $tglmulai,'asset_conf_code' => $asset_conf_code,'tglsampai' => $tglsampai,'status' => $status]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-asset-confirmation-'.date('dmyhis').'.pdf');
            }
        }else{
            $query = DB::table('d_asset_conf');
            $query->select(DB::raw("DATE(t_asset_conf.rec_date) as tgl"));
            $query->join('t_asset_conf', 't_asset_conf.asset_conf_code', '=', 'd_asset_conf.asset_conf_code');
            $query->join('m_coa as coa_a', 'coa_a.id', '=', 'd_asset_conf.periode_acc');
            $query->join('m_coa as coa_b', 'coa_b.id', '=', 'd_asset_conf.periode_acum_acc');
            $query->where('rec_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('rec_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->ac != '0') {
                $query->where('d_asset_conf.asset_conf_code',$request->ac);
            }

            if ($request->status == 'in proccess') {
                $query->where('status','in process');
            }
            if ($request->status == 'post') {
                $query->where('status','post');
            }
            if ($request->status == 'close') {
                $query->where('status','close');
            }

            $query->groupBy('tgl');
            $dataAsset = $query->get();

            foreach ($dataAsset as $raw_data) {
                $query = DB::table('t_asset_conf');
                $query->select('t_asset_conf.asset_conf_code','status');
                $query->where('t_asset_conf.rec_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_asset_conf.rec_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));

                if ($request->ac != '0') {
                    $query->where('asset_conf_code',$request->ac);
                }

                if ($request->status == 'in proccess') {
                    $query->where('status','in process');
                }
                if ($request->status == 'post') {
                    $query->where('status','post');
                }
                if ($request->status == 'cancel') {
                    $query->where('status','cancel');
                }
                if ($request->status == 'close') {
                    $query->where('status','close');
                }

                $query->groupBy('asset_conf_code','status');

                $dataAcc = $query->get();
                $raw_data->data_accode = $dataAcc;

                foreach ($dataAcc as $raw_data2) {
                    $query = DB::table('d_asset_conf');
                    $query->select('d_asset_conf.asset_conf_code','t_asset_conf.rec_date','d_asset_conf.periode','d_asset_conf.periode_value','d_asset_conf.rec_periode_depretiation','d_asset_conf.periode_acc','coa_a.desc as coa_a','coa_b.desc as coa_b','d_asset_conf.periode_acum_acc','t_asset_conf.status');
                    $query->join('t_asset_conf', 't_asset_conf.asset_conf_code', '=', 'd_asset_conf.asset_conf_code');
                    $query->join('m_coa as coa_a', 'coa_a.id', '=', 'd_asset_conf.periode_acc');
                    $query->join('m_coa as coa_b', 'coa_b.id', '=', 'd_asset_conf.periode_acum_acc');
                    $query->where('d_asset_conf.asset_conf_code',$raw_data2->asset_conf_code);

                    $dataProduk = $query->get();
                    $raw_data2->detail_ac = $dataProduk;
                }
            }

            // dd($dataAsset);

            $pdf = PDF::loadview('admin.report.laporan-asset-confirmation-detail',['dataAsset' => $dataAsset,'tglmulai' => $tglmulai,'asset_conf_code' => $asset_conf_code,'tglsampai' => $tglsampai,'status' => $status]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-asset-confirmation-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function assetLaporanAll(request $request, $type)
    {
        // dd($request->all());

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        if ($request->type == 'summary') {
            $query = DB::table('t_asset');
            $query->select('t_asset.*','m_produk.name as produk_name');
            $query->join('m_produk', 'm_produk.id', '=', 't_asset.barang');
            $query->where('rec_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('rec_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->status == 'in proccess') {
                $query->where('status','in process');
            }
            if ($request->status == 'post') {
                $query->where('status','post');
            }
            if ($request->status == 'close') {
                $query->where('status','close');
            }

            $query->orderBy('id');

            $dataAsset = $query->get();

            // dd($dataAsset);

            $pdf = PDF::loadview('admin.report.laporan-asset-summary',['dataAsset' => $dataAsset,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'status' => $status]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-asset-list-'.date('dmyhis').'.pdf');
            }
        }else{
            $query = DB::table('d_asset');
            $query->select('t_asset.asset_code','t_asset.rec_date','m_supplier.name as supplier_name');
            $query->join('t_asset', 't_asset.asset_code', '=', 'd_asset.asset_code');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_asset.supplier');
            $query->where('rec_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('rec_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->supplier != null) {
                $query->where('supplier', $request->supplier);
            }

            if ($request->po != '0') {
                $query->where('d_asset.asset_code',$request->po);
            }

            if ($request->barang != null) {
                $query->where('d_asset.barang',$request->barang);
            }

            if ($request->status == 'in proccess') {
                $query->where('status','in process');
            }
            if ($request->status == 'in approval') {
                $query->where('status','in approval');
            }
            if ($request->status == 'approved') {
                $query->where('status','approved');
            }
            if ($request->status == 'reject') {
                $query->where('status','reject');
            }

            $query->orderBy('m_supplier.name');
            $query->orderBy('t_asset.rec_date');
            $query->orderBy('d_asset.asset_code');

            $dataPO = $query->get();

            //dd($dataPO);

            $pdf = PDF::loadview('admin.report.laporan-asset-detail',['dataPO' => $dataPO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'asset_code' => $asset_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-asset-confirmation-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function salesOrder($soCode)
    {
        $detailSalesOrder = DB::table('d_sales_order')
        ->join('t_sales_order','t_sales_order.so_code','=','d_sales_order.so_code')
        ->join('m_user','m_user.id','=','t_sales_order.sales')
        ->join('m_customer','m_customer.id','=','t_sales_order.customer')
        ->join('m_produk','m_produk.id','=','d_sales_order.produk')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
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
        ->join('m_user','m_user.id','=','t_sales_order.sales')
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

        $pdf = PDF::loadview('admin.report.sales-order',['company' => $company,'detailSalesOrder' => $detailSalesOrder, 'subTotal1' => $subTotal1, 'dataTransaksi' => $dataTransaksi,'userEntry' => $userEntry,'userOrder' => $userOrder]);
        // $customPaper = array(0,0,21.84,13.97);
        $customPaper = array(0,0,21.84,13.97);
        $pdf->setPaper($customPaper);
        // $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
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

        $detail = DB::table('d_purchase_order')
        ->join('m_produk','d_purchase_order.produk','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
        ->select('m_produk.*','d_purchase_order.*','m_satuan_unit.code as code_unit')
        ->where('po_code',$poCode)
        ->get();

        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.purchase-order',['company' => $company,'header'=>$header,'detail'=>$detail]);
        $customPaper = array(0,0,21.84,13.97);
        $pdf->setPaper($customPaper);
        // $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
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

        $query = DB::table('t_fixed_asset_po');
        $query->select(DB::raw("DATE(t_fixed_asset_po.po_date) as tgl"));
        $query->join('d_fixed_asset_po', 'd_fixed_asset_po.po_code', '=', 't_fixed_asset_po.po_code');
        $query->where('t_fixed_asset_po.po_date','>=',date('Y-m-d', strtotime($tglmulai)));
        $query->where('t_fixed_asset_po.po_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

        if ($request->supplier != null) {
            $query->where('t_fixed_asset_po.supplier', $request->supplier);
        }
        if ($request->po != '0') {
            $query->where('t_fixed_asset_po.po_code',$request->po);
        }
        if ($request->barang != null) {
            $query->where('d_fixed_asset_po.produk',$request->barang);
        }

        $query->groupBy('tgl');

        $dataPOPD = $query->get();

        foreach ($dataPOPD as $raw_data) {
            $query = DB::table('t_fixed_asset_po');
            $query->select('supplier','m_supplier.name as supplier_name');
            $query->join('d_fixed_asset_po', 'd_fixed_asset_po.po_code', '=', 't_fixed_asset_po.po_code');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_fixed_asset_po.supplier');
            $query->where('t_fixed_asset_po.po_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
            $query->where('t_fixed_asset_po.po_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
            if ($request->supplier != null) {
                $query->where('t_fixed_asset_po.supplier', $request->supplier);
            }
            if ($request->po != '0') {
                $query->where('t_fixed_asset_po.po_code',$request->po);
            }
            if ($request->barang != null) {
                $query->where('d_fixed_asset_po.produk',$request->barang);
            }
            $query->groupBy('supplier','m_supplier.name');

            $dataCustomer = $query->get();
            $raw_data->data_supplier = $dataCustomer;

            foreach ($dataCustomer as $raw_data2) {
                $query = DB::table('t_fixed_asset_po');
                $query->select('t_fixed_asset_po.po_code','status_aprove');
                $query->join('d_fixed_asset_po', 'd_fixed_asset_po.po_code', '=', 't_fixed_asset_po.po_code');
                $query->where('supplier',$raw_data2->supplier);
                $query->where('t_fixed_asset_po.po_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_fixed_asset_po.po_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
                if ($request->po != '0') {
                    $query->where('t_fixed_asset_po.po_code',$request->po);
                }
                if ($request->barang != null) {
                    $query->where('d_fixed_asset_po.produk',$request->barang);
                }

                $dataPO = $query->get();
                $raw_data2->data_pocode = $dataPO;

                foreach ($dataPO as $raw_data3) {
                    $query = DB::table('d_fixed_asset_po');
                    $query->select('produk as produk_id','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty as qty_po');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_fixed_asset_po.produk');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
                    $query->where('po_code',$raw_data3->po_code);
                    if ($request->barang != null) {
                        $query->where('d_fixed_asset_po.produk',$request->barang);
                    }
                    $dataProduk = $query->get();
                    $raw_data3->detail_po = $dataProduk;

                    foreach ($dataProduk as $raw_data4) {
                        $dataProduk = DB::table('d_fixed_asset_pd')
                        ->select('d_fixed_asset_pd.sj_masuk_code','t_fixed_asset_pd.sj_masuk_date','d_fixed_asset_pd.qty')
                        ->join('t_fixed_asset_pd', 't_fixed_asset_pd.sj_masuk_code', '=', 'd_fixed_asset_pd.sj_masuk_code')
                        ->where('t_fixed_asset_pd.po_code',$raw_data3->po_code)
                        ->where('d_fixed_asset_pd.produk_id',$raw_data4->produk_id)
                        ->orderBy('t_fixed_asset_pd.sj_masuk_date')
                        ->get();
                        $raw_data4->data_sj = $dataProduk;
                    }
                }
            }
        }

        //dd($dataPOPD);

        $pdf = PDF::loadview('admin.report.laporan-asset-po-pd',['dataPOPD' => $dataPOPD,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'po_code' => $po_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

        $pdf->setPaper('legal', 'potrait');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-po-pd-'.$supplier.'-'.date('dmyhis').'.pdf');
        }
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

        $query = DB::table('t_fixed_asset_pd');
        $query->select(DB::raw("DATE(t_fixed_asset_pd.sj_masuk_date) as tgl"));
        $query->join('d_fixed_asset_pd', 'd_fixed_asset_pd.sj_masuk_code', '=', 't_fixed_asset_pd.sj_masuk_code');
        $query->where('t_fixed_asset_pd.sj_masuk_date','>=',date('Y-m-d', strtotime($tglmulai)));
        $query->where('t_fixed_asset_pd.sj_masuk_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));
        if ($request->supplier != null) {
            $query->where('t_fixed_asset_pd.supplier', $request->supplier);
        }
        if ($request->po != null) {
            //dd($request->po);
            $query->where('t_fixed_asset_pd.po_code',$request->po);
        }
        if ($request->pd != '0') {
            $query->where('t_fixed_asset_pd.sj_masuk_code',$request->pd);
        }

        if ($request->barang != null) {
            $query->where('d_fixed_asset_pd.produk_id',$request->barang);
        }

        if ($request->status == 'save') {
            $query->where('t_fixed_asset_pd.status','save');
        }
        if ($request->status == 'post') {
            $query->where('t_fixed_asset_pd.status','post');
        }
        $query->groupBy('tgl');

        $dataPD = $query->get();

        foreach ($dataPD as $raw_data) {
            $query = DB::table('t_fixed_asset_pd');
            $query->select('supplier','m_supplier.name as supplier_name');
            $query->join('d_fixed_asset_pd', 'd_fixed_asset_pd.sj_masuk_code', '=', 't_fixed_asset_pd.sj_masuk_code');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_fixed_asset_pd.supplier');
            $query->where('t_fixed_asset_pd.sj_masuk_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
            $query->where('t_fixed_asset_pd.sj_masuk_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
            if ($request->supplier != null) {
                $query->where('t_fixed_asset_pd.supplier', $request->supplier);
            }
            if ($request->po != null) {
                //dd($request->po);
                $query->where('t_fixed_asset_pd.po_code',$request->po);
            }
            if ($request->pd != '0') {
                $query->where('t_fixed_asset_pd.sj_masuk_code',$request->pd);
            }

            if ($request->barang != null) {
                $query->where('d_fixed_asset_pd.produk_id',$request->barang);
            }

            if ($request->status == 'save') {
                $query->where('t_fixed_asset_pd.status','save');
            }
            if ($request->status == 'post') {
                $query->where('t_fixed_asset_pd.status','post');
            }
            $query->groupBy('supplier','m_supplier.name');

            $dataCustomer = $query->get();
            $raw_data->data_supplier = $dataCustomer;

            foreach ($dataCustomer as $raw_data2) {
                $query = DB::table('t_fixed_asset_pd');
                $query->select('t_fixed_asset_pd.sj_masuk_code','status');
                $query->join('d_fixed_asset_pd', 'd_fixed_asset_pd.sj_masuk_code', '=', 't_fixed_asset_pd.sj_masuk_code');
                $query->where('supplier',$raw_data2->supplier);
                $query->where('t_fixed_asset_pd.sj_masuk_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_fixed_asset_pd.sj_masuk_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
                if ($request->po != null) {
                    $query->where('t_fixed_asset_pd.po_code',$request->po);
                }
                if ($request->pd != '0') {
                    $query->where('t_fixed_asset_pd.sj_masuk_code',$request->pd);
                }

                if ($request->barang != null) {
                    $query->where('d_fixed_asset_pd.produk_id',$request->barang);
                }

                if ($request->status == 'save') {
                    $query->where('t_fixed_asset_pd.status','save');
                }
                if ($request->status == 'post') {
                    $query->where('t_fixed_asset_pd.status','post');
                }
                $query->groupBy('t_fixed_asset_pd.sj_masuk_code','status');

                $dataPDH = $query->get();
                $raw_data2->data_pdcode = $dataPDH;

                foreach ($dataPDH as $raw_data3) {
                    $query = DB::table('d_fixed_asset_pd');
                    $query->select('d_fixed_asset_pd.produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty','free_qty','last_po_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_fixed_asset_pd.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
                    $query->where('sj_masuk_code',$raw_data3->sj_masuk_code);
                    if ($request->barang != null) {
                        $query->where('d_fixed_asset_pd.produk_id',$request->barang);
                    }
                    $dataProduk = $query->get();
                    $raw_data3->detail_pd = $dataProduk;

                    foreach ($dataProduk as $raw_data4) {
                        $getpocode = DB::table('t_fixed_asset_pd')
                        ->where('sj_masuk_code',$raw_data3->sj_masuk_code)
                        ->pluck('po_code')
                        ->first();

                        $totalSOQty = DB::table('d_fixed_asset_po')
                        ->where('po_code', $getpocode)
                        ->where('produk', $raw_data4->produk_id)
                        ->pluck('qty')
                        ->first();
                        $raw_data4->SOQty = $totalSOQty;
                    }
                }
            }
        }
        $pdf = PDF::loadview('admin.report.asset-receipt',['dataPD' => $dataPD,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'po_code' => $po_code,'sj_masuk_code' => $sj_masuk_code,'status' => $status,'supplier' => $supplier,'barang' => $barang]);

        $pdf->setPaper('legal', 'potrait');

        if( $type == 'view' ){
            return $pdf->stream();
        }else{
            return $pdf->download('laporan-pd-'.$supplier.'-'.date('dmyhis').'.pdf');
        }
    }

}
