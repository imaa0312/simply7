<?php

namespace App\Http\Controllers;

use DB;
use PDF;
use Excel;
use Config;
use Illuminate\Http\Request;
use App\Models\MStokProdukModel;
use App\Models\MCustomerModel;
use App\Models\MSupplierModel;
use App\Models\MRoleModel;
use App\Models\MHargaProdukModel;



class ReportExcelController extends Controller
{
    public function reportSOExcel(request $request)
    {
        // $this->validate($request, [
        //        'customer' => 'required',
        //    ]);

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
            $so_code = 'ALL';
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

        if($request->type == 'summary'){
            $query = DB::table('t_sales_order');
            $query->select('t_sales_order.so_date','t_sales_order.so_code','m_user.name as sales_name','m_customer.name as customer_name','t_sales_order.status_aprove','grand_total as total','t_sales_order.diskon_header_potongan','t_sales_order.diskon_header_persen');
            $query->join('m_customer', 'm_customer.id', '=', 't_sales_order.customer');
            $query->join('m_user', 'm_user.id', '=', 't_sales_order.sales');
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

            $query->orderBy('so_code');

            $dataSO = $query->get();

            foreach ($dataSO as $raw_data) {
                $total = DB::table('d_sales_order')
                ->where('so_code', $raw_data->so_code)
                ->sum('total');

                $raw_data->total_awal = $total;
            }

            //dd($dataSO);
            //mulai write excel
            $sheetArray = array();
            $sheetArray[] = array('Laporan Sales Order (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('SO No. : '.$so_code);
            $sheetArray[] = array('Item : '.$barang);
            $sheetArray[] = array('Customer : '.$customer);
            $sheetArray[] = array('Status : '.$status);

            $sheetArray[] = array(); // Add an empty row

            $totalHarga = 0;
            //Header
            $sheetArray[] = array('Tanggal', 'No. SO', 'Nama Sales', 'Nama Customer', 'Status','Total','Disc %','Disc Rp','Total Order');
            // Tambah data tabel
            foreach($dataSO  as $raw_data){
                $sheetArray[] = array(date('d-m-Y',strtotime($raw_data->so_date)),$raw_data->so_code,$raw_data->sales_name,$raw_data->customer_name,$raw_data->status_aprove,$raw_data->total_awal,$raw_data->diskon_header_persen,$raw_data->diskon_header_potongan,$raw_data->total,);
                $totalHarga = $totalHarga + $raw_data->total;
            }

            $jmlRow = count($dataSO);

            Excel::create('Sales-order-'.$so_code.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow,$totalHarga)
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
                        'I'     =>  20
                    ));

                    $sheet->cell('A9:I9', function($cell) {
                        $cell->setBackground('#C0C0C0');
                    });

                    $sheet->cell('A9:I9', function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->setBorder('A9:I'.(9+$jmlRow), 'thin');

                    $sheet->cell('A9:A'.(9+$jmlRow), function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->cell('E9:E'.(9+$jmlRow), function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->cell('F10:I'.(10+$jmlRow), function($cell) {
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
            $query = DB::table('d_sales_order');
            $query->select('t_sales_order.so_code','d_sales_order.produk as id_produk','d_sales_order.qty','t_sales_order.so_date','m_produk.name as produk_name','m_customer.name as customer_name','m_produk.code as produk_code','m_user.name as sales_name','d_sales_order.customer_price','d_sales_order.total as total_price');
            $query->join('t_sales_order', 't_sales_order.so_code', '=', 'd_sales_order.so_code');
            $query->join('m_produk', 'm_produk.id', '=', 'd_sales_order.produk');
            $query->join('m_customer', 'm_customer.id', '=', 't_sales_order.customer');
            $query->join('m_user', 'm_user.id', '=', 't_sales_order.sales');
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

            $query->orderBy('d_sales_order.so_code');

            $dataSO = $query->get();

            //dd($dataSO);

            $sheetArray = array();
            $sheetArray[] = array('Laporan Sales Order (Detail)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('SO No. : '.$so_code);
            $sheetArray[] = array('Item : '.$barang);
            $sheetArray[] = array('Customer : '.$customer);
            $sheetArray[] = array('Status : '.$status);

            $sheetArray[] = array(); // Add an empty row

            $totalHarga = 0;
            //Header
            $sheetArray[] = array('Tanggal', 'No. SO','Nama Customer','Kode','Nama Barang','Sales','QTY', 'Harga', 'Total Harga');

            foreach($dataSO  as $raw_data){
                $sheetArray[] = array(date('d-m-Y',strtotime($raw_data->so_date)),$raw_data->so_code,$raw_data->customer_name,$raw_data->produk_code,$raw_data->produk_name,$raw_data->sales_name,$raw_data->qty,$raw_data->customer_price,$raw_data->total_price,);
            }

            $jmlRow = count($dataSO);

            Excel::create('Sales-order-'.$so_code.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow)
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
                        'I'     =>  15
                    ));

                    $sheet->cell('A9:I9', function($cell) {
                        $cell->setBackground('#C0C0C0');
                    });

                    $sheet->cell('A9:I9', function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->setBorder('A9:I'.(9+$jmlRow), 'thin');

                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function reportSJExcel(request $request)
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
                    $query->select('produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as satuan_kemasan','qty_delivery','last_so_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_surat_jalan.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
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
        //dd($dataSJ);

        $sheetArray = array();
        $sheetArray[] = array('Laporan Surat Jalan');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
        $sheetArray[] = array('SO No. : '.$so_code);
        $sheetArray[] = array('SJ No. : '.$sj_code);
        $sheetArray[] = array('Item : '.$barang);
        $sheetArray[] = array('Customer : '.$customer);
        if ($status == 'save') {
            $status_print = 'In Process';
        }else{
            $status_print = $status;
        }
        $sheetArray[] = array('Status : '.$status_print);

        $sheetArray[] = array(); // Add an empty row

        foreach($dataSJ  as $raw_data){
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
            foreach ($raw_data->data_customer as $raw_data2) {
                $sheetArray[] = array('Customer : '.$raw_data2->customer_name);
                foreach ($raw_data2->data_sjcode as $raw_data3) {
                    $sheetArray[] = array('No. SJ : '.$raw_data3->sj_code, 'Status : '.$raw_data3->status);
                    $sheetArray[] = array('Nama Barang', 'Kode Barang', 'Satuan', 'QTY SO', 'QTY SJ');
                    foreach ($raw_data3->detail_sj as $raw_data4) {
                        $sheetArray[] = array(
                            $raw_data4->produk_name,
                            $raw_data4->produk_code,
                            $raw_data4->satuan_kemasan,
                            $raw_data4->SOQty,
                            $raw_data4->qty_delivery,
                            $raw_data4->produk_code,
                        );
                    }
                    $sheetArray[] = array();
                }
                $sheetArray[] = array();
            }
        }

        Excel::create('Laporan-sj-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function reportSOSJExcel(request $request)
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
                    $query->select('produk as produk_id','m_produk.name as produk_name','m_satuan_unit.code as satuan_kemasan','qty as qty_so');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_sales_order.produk');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
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

        $sheetArray = array();
        $sheetArray[] = array('Laporan Status SO SJ');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
        $sheetArray[] = array('SO No. : '.$so_code);
        $sheetArray[] = array('Item : '.$barang);
        $sheetArray[] = array('Customer : '.$customer);

        $sheetArray[] = array(); // Add an empty row
        foreach($dataSOSJ  as $raw_data){
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
            foreach ($raw_data->data_customer as $raw_data2) {
                $sheetArray[] = array('Customer : '.$raw_data2->customer_name);
                foreach ($raw_data2->data_socode as $raw_data3) {
                    $sheetArray[] = array('No SO : '.$raw_data3->so_code, 'Status : '.$raw_data3->status_aprove);
                    foreach ($raw_data3->detail_so as $raw_data4) {
                        $sheetArray[] = array('Barang : '.$raw_data4->produk_name, 'QTY SO : '.$raw_data4->qty_so, 'Satuan : '.$raw_data4->satuan_kemasan);
                        $sheetArray[] = array('No. SJ', 'Tgl SJ', 'O/S SO', 'QTY SJ', 'Sisa');
                        $totalawal = $raw_data4->qty_so;
                        foreach ($raw_data4->data_sj as $raw_data5) {
                            $totalakhir = $totalawal - $raw_data5->qty_delivery;
                            $sheetArray[] = array(
                                $raw_data5->sj_code,
                                date('d-m-Y',strtotime($raw_data5->sj_date)),
                                $totalawal,
                                $raw_data5->qty_delivery,
                                $totalakhir
                            );
                            $totalawal = $totalawal - $raw_data5->qty_delivery;
                        }
                        $sheetArray[] = array();
                    }
                    $sheetArray[] = array();
                }
                $sheetArray[] = array();
            }
        }

        Excel::create('Laporan-sosj-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function reportStokExcel(request $request)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        $type_barang = $request->type_barang;

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
            $query0->select('m_produk.*',DB::raw('SUM(m_stok_produk.stok) as stok'));
            $query0->groupBy('m_produk.id','m_produk.code');
            if ($type_barang != '') {
                $query0->where('m_produk.type_barang',$type_barang);
            }
            $query0->where('m_stok_produk.gudang',$request->gudang);
            $query0->where('m_stok_produk.stok','!=', 0);

            $dataProduk = $query0->get();

            //dd($dataProduk);

            foreach ($dataProduk as $raw_data) {
                $data_stok = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $raw_data->code)
                ->where('m_stok_produk.gudang', $request->gudang)
                ->where('m_stok_produk.created_at','>', date('Y-m-d', strtotime($tglmulai)))
                ->where('m_stok_produk.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->get();

                $total_stok_periode = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $raw_data->code)
                ->where('m_stok_produk.gudang', $request->gudang)
                ->where('m_stok_produk.created_at','>', date('Y-m-d', strtotime($tglmulai)))
                ->where('m_stok_produk.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->sum('stok');

                $jml_data_stok = count($data_stok);

                if ($jml_data_stok > 0) {
                    $stokAwal = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    //->where('m_stok_produk.created_at','<', $request->gudang)
                    ->orderBy('created_at','desc')
                    ->first();
                    $raw_data->stok_awal = $stokAwal->stok_awal;
                    $raw_data->stok_akhir = $stokAwal->stok_awal + $total_stok_periode;
                }else{
                    $stokTotal = DB::table('m_stok_produk')
                    ->where('m_stok_produk.produk_code', $raw_data->code)
                    ->where('m_stok_produk.gudang', $request->gudang)
                    ->groupBy('m_stok_produk.produk_code')
                    ->sum('m_stok_produk.stok');
                    $raw_data->stok_awal = $stokTotal;
                    $raw_data->stok_akhir = $stokTotal;
                }

                $stokMasuk = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $raw_data->code)
                ->where('m_stok_produk.gudang', $request->gudang)
                ->where('type', 'in')
                ->where('m_stok_produk.created_at','>', date('Y-m-d', strtotime($tglmulai)))
                ->where('m_stok_produk.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->groupBy('m_stok_produk.produk_code')
                ->sum('m_stok_produk.stok');
                $raw_data->stok_masuk = $stokMasuk;

                $stokKeluar = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code', $raw_data->code)
                ->where('m_stok_produk.gudang', $request->gudang)
                ->where('type', 'out')
                ->where('m_stok_produk.created_at','>', date('Y-m-d', strtotime($tglmulai)))
                ->where('m_stok_produk.created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->groupBy('m_stok_produk.produk_code')
                ->sum('m_stok_produk.stok');
                $raw_data->stok_keluar = $stokKeluar*-1;
            }

            //dd($dataProduk);

            $sheetArray = array();
            $sheetArray[] = array('Laporan Stok Barang (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Gudang : '.$dataGudang->name);
            $sheetArray[] = array('Nama Barang : '.'All');

            $sheetArray[] = array(); // Add an empty row

            $sheetArray[] = array('No', 'Kode Barang', 'Nama Barang', 'Stok Awal', 'Masuk','Keluar', 'Stok Akhir', 'Satuan');

            $no = 0;
            foreach($dataProduk  as $raw_data){
                $no++;
                $sheetArray[] = array($no,$raw_data->code,$raw_data->name,$raw_data->stok_awal,$raw_data->stok_masuk,$raw_data->stok_keluar,$raw_data->stok_akhir,$raw_data->satuan_terkecil);
            }

            $jmlRow = count($dataProduk);

            Excel::create('Stok-'.$dataGudang->name.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray,$jmlRow)
                {
                    $sheet->cell('A2', function($cell) {
                        $cell->setFont(array(
                            'size'       => '16',
                        ));
                    });

                    $sheet->setWidth(array(
                        'A'     =>  5,
                        'B'     =>  20,
                        'C'     =>  30,
                        'D'     =>  15,
                        'E'     =>  15,
                        'F'     =>  15,
                        'G'     =>  15,
                        'H'     =>  15,
                    ));

                    $sheet->cell('A7:H7', function($cell) {
                        $cell->setBackground('#C0C0C0');
                    });

                    $sheet->cell('A7:H7', function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->setBorder('A7:H'.(7+$jmlRow), 'thin');

                    $sheet->setColumnFormat(array(
                        'D' => '@',
                        'E' => '@',
                        'F' => '@',
                        'G' => '@',
                    ));

                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }else{
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

            //dd($detailBarang);

            $sum = DB::table('m_stok_produk')
            ->where('produk_code',$request->barang)
            ->where('gudang',$request->gudang)
            ->sum('stok');

            $dataGudang = DB::table('m_stok_produk')
            ->where('produk_code',$request->barang)
            ->where('gudang',$request->gudang)
            ->where('created_at','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->orderBy('created_at')
            ->get();

            $stok_awal = 0;
            $dataStokAwal = DB::table('m_stok_produk')
            ->where('produk_code',$request->barang)
            ->where('gudang',$request->gudang)
            ->where('created_at','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('created_at','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->orderBy('created_at')
            ->first();

            if ($dataStokAwal !== null) {
                $stok_awal = $dataStokAwal->stok_awal;
            }else{
                $dataStokAwalLast = DB::table('m_stok_produk')
                ->where('produk_code',$request->barang)
                ->where('gudang',$request->gudang)
                ->where('created_at','<=', date('Y-m-d', strtotime($tglmulai)))
                ->orderBy('created_at', 'desc')
                ->first();

                if($dataStokAwalLast !== null){
                    $stok_awal = $dataStokAwalLast->stok_awal + $dataStokAwalLast->stok;
                }else{
                    $stok_awal = 0;
                }
            }

            $sheetArray = array();
            $sheetArray[] = array('Laporan Stok Barang (Detail)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Gudang : '.$detailBarang->gudang_name);
            $sheetArray[] = array('Nama Barang : '.$detailBarang->produk);
            $sheetArray[] = array('Kode Barang : '.$detailBarang->produk_code);

            $sheetArray[] = array(); // Add an empty row

            $sheetArray[] = array('No', 'Transaksi','Tipe Transaksi', 'Tgl Transaksi', 'Saldo Awal', 'Masuk','Keluar', 'Saldo Akhir');

            $sheetArray[] = array('1', 'SALDO AWAL','SALDO AWAL', '', $stok_awal, '','', $stok_awal);
            $no = 0;
            foreach($dataGudang  as $raw_data){
                $no++;
                ($raw_data->type == 'in') ? $masuk = $raw_data->stok : $masuk = '';
                ($raw_data->type == 'out') ? $keluar = $raw_data->stok*-1 : $keluar = '';

                $sheetArray[] = array(
                    $no + 1,
                    $raw_data->transaksi,
                    $raw_data->tipe_transaksi,date('d-m-Y',strtotime($raw_data->created_at)),
                    '',
                    $masuk,
                    $keluar,
                    $raw_data->stok_awal + $raw_data->stok);
            }

            $jmlRow = count($dataGudang);

            Excel::create('Stok-'.$detailBarang->produk.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray,$jmlRow)
                {
                    $sheet->cell('A2', function($cell) {
                        $cell->setFont(array(
                            'size'       => '16',
                        ));
                    });

                    $sheet->setWidth(array(
                        'A'     =>  5,
                        'B'     =>  20,
                        'C'     =>  30,
                        'D'     =>  15,
                        'E'     =>  15,
                        'F'     =>  15,
                        'G'     =>  15,
                        'H'     =>  15,
                    ));

                    $sheet->cell('A8:H8', function($cell) {
                        $cell->setBackground('#C0C0C0');
                    });

                    $sheet->cell('A8:H8', function($cell) {
                        $cell->setAlignment('center');
                    });

                    $sheet->setBorder('A9:H'.(9+$jmlRow), 'thin');

                    // $sheet->setColumnFormat(array(
                    //     'D' => '@',
                    //     'E' => '@',
                    //     'F' => '@',
                    //     'G' => '@',
                    // ));

                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }


    public function reportSalesExcel(request $request)
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

            $sheetArray = array();
            $sheetArray[] = array('Laporan SO Sales');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);

            $sheetArray[] = array(); // Add an empty row

            foreach($dataSales  as $raw_data){
                $sheetArray[] = array('Sales : '.$raw_data->name);
                $sheetArray[] = array('No. SO', 'Customer', 'Tgl. SO', 'Status', 'Total');
                foreach ($raw_data->detail as $key => $raw_data2) {
                    $sheetArray[] = array($raw_data2->so_code,$raw_data2->name,date('d-m-Y',strtotime($raw_data2->so_date)),$raw_data2->status_aprove,'Rp. '.number_format($raw_data2->grand_total,0,'.','.'));
                }
                $sheetArray[] = array(); // Add an empty row
            }

            Excel::create('Sales-'.$sales.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');

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

            $sheetArray = array();
            $sheetArray[] = array('Laporan Penagihan Sales');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);

            $sheetArray[] = array(); // Add an empty row

            foreach($dataSales  as $raw_data){
                $sheetArray[] = array('Sales : '.$raw_data->name);
                $sheetArray[] = array('No. Pembayaran', 'Customer', 'Tgl. Pembayaran', 'Status', 'Total');
                foreach ($raw_data->detail as $key => $raw_data2) {
                    $sheetArray[] = array($raw_data2->pembayaran_code,$raw_data2->name,date('d-m-Y',strtotime($raw_data2->payment_date)),$raw_data2->status,'Rp. '.number_format($raw_data2->total_bayar,0,'.','.'));
                }
                $sheetArray[] = array(); // Add an empty row
            }

            Excel::create('Sales-'.$sales.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function reportTagihanExcel(request $request)
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
        $query->where('t_faktur.status_payment','unpaid');
        if ($request->customer != null) {
            $query->where('m_customer.id', $request->customer);
        }
        $query->groupBy('m_customer.id');
        $datacustomer = $query->get();

        if ($request->type == 'summary') {
            foreach ($datacustomer as $raw_data) {
                $saldoawal = DB::table('t_faktur')
                ->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                ->where('customer',$raw_data->id)
                ->sum('total');

                $penjualan = DB::table('t_faktur')
                ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('customer',$raw_data->id)
                ->sum('total');

                $pembayaran = DB::table('d_pembayaran')
                ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                ->where('t_pembayaran.payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('t_pembayaran.payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('t_pembayaran.customer',$raw_data->id)
                ->where('status','approved')
                ->sum('d_pembayaran.total');

                $raw_data->saldoawal = $saldoawal;
                $raw_data->penjualan = $penjualan;
                $raw_data->pembayaran = $pembayaran;
                $raw_data->saldoakhir = $saldoawal + $penjualan - $pembayaran;
            }

            //dd($datacustomer);

            $sheetArray = array();
            $sheetArray[] = array('Kartu Piutang (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Customer : '.$customer);

            $sheetArray[] = array(); // Add an empty row

            $sheetArray[] = array('Customer', 'Saldo Awal', 'Penjualan', 'Pembayaran', 'Saldo Akhir');

            $TSaldoAwal = 0;
            $TPenjualan = 0;
            $TPembayaran = 0;
            $TSaldoAkhir = 0;
            foreach($datacustomer  as $raw_data){
                $sheetArray[] = array($raw_data->name,number_format($raw_data->saldoawal,0,'.','.'),number_format($raw_data->penjualan,0,'.','.'),number_format($raw_data->pembayaran,0,'.','.'),number_format($raw_data->saldoakhir,0,'.','.'));

                $TSaldoAwal = $TSaldoAwal + $raw_data->saldoawal;
                $TPenjualan = $TPenjualan + $raw_data->penjualan;
                $TPembayaran = $TPembayaran + $raw_data->pembayaran;
                $TSaldoAkhir = $TSaldoAkhir + $raw_data->saldoakhir;
            }

            $sheetArray[] = array('TOTAL =', number_format($TSaldoAwal,0,'.','.'), number_format($TPenjualan,0,'.','.'), number_format($TPembayaran,0,'.','.'), number_format($TSaldoAkhir,0,'.','.'));

            Excel::create('Kartu-Piutang-Summary-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');

        }else{
            foreach ($datacustomer as $raw_data) {
                // $saldoawal = DB::table('t_faktur')
                //     ->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                //     ->where('customer',$raw_data->id)
                //     ->sum('total');

                $saldoawal = DB::table('m_saldo_awal_piutang')
                    //->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                    ->where('customer',$raw_data->id)
                    ->sum('total_piutang');

                $penjualan = DB::table('t_faktur')
                ->select('created_at as tanggal','faktur_code as keterangan','jatuh_tempo','total as piutang', DB::raw("'0' as pembayaran"))
                ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('customer',$raw_data->id);
                //->get();

                $pembayaran = DB::table('d_pembayaran')
                ->select('t_pembayaran.created_at as tanggal','d_pembayaran.pembayaran_code as keterangan','t_pembayaran.payment_date as jatuh_tempo', DB::raw("'0' as piutang"),'total as pembayaran')
                ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                ->where('t_pembayaran.payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('t_pembayaran.payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('t_pembayaran.customer',$raw_data->id)
                ->where('status','approved')
                ->union($penjualan)
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

            //dd($datacustomer);

            $sheetArray = array();
            $sheetArray[] = array('Laporan Piutang (Detail)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Customer : '.$customer);

            $sheetArray[] = array(); // Add an empty row

            foreach($datacustomer  as $raw_data){
                $balance = 0;
                $sheetArray[] = array('Customer : '.$raw_data->name);
                $sheetArray[] = array('Tanggal', 'Keterangan', 'Jatuh Tempo', 'Piutang', 'Pembayaran','Balance');

                $TPiutang = 0;
                $TPembayaran = 0;
                $TBalance = 0;

                foreach ($raw_data->detail as $key => $raw_data2) {
                    if ($key == 0) {
                        $tanggal = $raw_data2['tanggal'];
                        $keterangan = $raw_data2['keterangan'];
                        $jatuh_tempo = $raw_data2['jatuh_tempo'];
                        $piutang = $raw_data2['piutang'];
                        $pembayaran = $raw_data2['pembayaran'];
                        $balance = $balance + $piutang - $pembayaran;
                    }else{
                        $tanggal = $raw_data2->tanggal;
                        $keterangan = $raw_data2->keterangan;
                        $jatuh_tempo = $raw_data2->jatuh_tempo;
                        $piutang = $raw_data2->piutang;
                        $pembayaran = $raw_data2->pembayaran;
                        $balance = $balance + $piutang - $pembayaran;
                    }
                    $sheetArray[] = array(date('d-m-Y',strtotime($tanggal)),$keterangan,date('d-m-Y',strtotime($jatuh_tempo)),number_format($piutang,0,'.','.'),number_format($pembayaran,0,'.','.'),number_format($balance,0,'.','.'));

                    $TPiutang = $TPiutang + $piutang;
                    $TPembayaran = $TPembayaran + $pembayaran;
                    $TBalance = $balance;
                }
                $sheetArray[] = array('', '', 'TOTAL = ', number_format($TPiutang,0,'.','.'), number_format($TPembayaran,0,'.','.'),number_format($TBalance,0,'.','.'));
                $sheetArray[] = array(); // Add an empty row
            }

            Excel::create('Customer-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function masterProvinsi()
    {
        //mulai write excel
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Provinsi');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Kode','Provinsi');

        //ambil data dari database
        $dataProvinsi = DB::table('m_provinsi')->orderBy('code')->get();
        $i=1;

        foreach ($dataProvinsi as $provinsi) {
            $sheetArray[] = array( '',$i++, $provinsi->code,$provinsi->name );
        }

        Excel::create('Master Provinsi', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterKota()
    {
        //mulai write excel
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Kota');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Kode','Kota/Kab','Provinsi');

        //ambil data dari database
        $dataKotaKab = DB::table('m_kota_kab')
        ->join('m_provinsi', 'm_provinsi.id', '=', 'm_kota_kab.provinsi')
        ->select('m_kota_kab.*', 'm_provinsi.id as id_provinsi', 'm_provinsi.name as provinsi')
        ->orderBy('code')
        ->get();
        $i=1;

        foreach ($dataKotaKab as $kota) {
            $sheetArray[] = array('',$i++, $kota->code,$kota->type.' '.$kota->name,$kota->provinsi );
        }

        Excel::create('Master Kota', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterKecamatan(Request $request)
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Kecamatan');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Kode','Kecamatan','Kota / Kabupaten');

        //ambil data dari database
        $dataKecamatan = DB::table('m_kecamatan')
        ->join('m_kota_kab', 'm_kota_kab.id', '=', 'm_kecamatan.kota_kab')
        ->join('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
        ->select('m_kecamatan.*', 'm_kota_kab.id as id_kota_kab', 'm_kota_kab.name as kota_kab','m_kota_kab.type')
        ->where('m_kota_kab.id',$request->kota)
        ->where('m_provinsi.id',$request->provinsi)
        ->orderBy('code')
        ->get();
        $i=1;

        foreach ($dataKecamatan as $kecamatan) {
            $sheetArray[] = array('',$i++, $kecamatan->code,$kecamatan->name,$kecamatan->type.' '.$kecamatan->kota_kab );
        }

        Excel::create('Master Kecamatan', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterKelurahan(Request $request)
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Kelurahan');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Kode','Kecamatan','Kelurahan / Desa','Kode Pos');
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

        $i=1;

        foreach ($dataKelurahan as $kelurahan) {
            $sheetArray[] = array('',$i++,$kelurahan->code,$kelurahan->kecamatan,$kelurahan->name,$kelurahan->zipcode );
        }

        Excel::create('Master Kelurahan', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function wilayahSales()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Wilayah Sales');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Wilayah');
        $dataWilayahSales = DB::table('m_wilayah_sales')->get();
        $i=1;

        foreach ($dataWilayahSales as $wilayahsales) {
            $sheetArray[] = array('',$i++, $wilayahsales->name);
        }

        Excel::create('Master Wilayah Sales', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function sales()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Sales');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Nama','Email','Alamat');
        $roleSales = DB::table('m_role')->where('name', 'Sales')->first();
        $dataSemuaSales = DB::table('m_user')->where('role', '=', $roleSales->id)->get();

        $i=1;

        foreach ($dataSemuaSales as $sales) {
            $sheetArray[] = array('',$i++, $sales->name,$sales->email,$sales->address);
        }

        Excel::create('Master Sales', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterTargetSales()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Target Sales');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Nama','Bulan Target','Target');
        $dataTargetSales = DB::table('m_target_sales')->join('m_user','m_user.id', '=',
        'm_target_sales.sales')->select('m_target_sales.*','m_user.id as id_user','m_user.name as sales_name')->get();
        $i=1;

        foreach ($dataTargetSales as $targetSales) {
            $sheetArray[] = array('',$i++, $targetSales->sales_name, date('m-Y',strtotime($targetSales->month)), $targetSales->monthly_target);
        }

        Excel::create('Master Target Sales', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterCustomer(Request $request)
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Customer');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Kode','Nama','Email','Telepon','Yg Bisa Dihubungi','Alamat','Kota/Kab','Kecamatan','Kelurahan /Desa','Wilayah Sales');
        $query = MCustomerModel::select('m_customer.*','m_kota_kab.name as kota',
        'm_kecamatan.name as kecamatan','m_kelurahan_desa.name as kelurahan',
        'm_wilayah_sales.name as wilayah')
        ->leftjoin('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_customer.main_kelurahan')
        ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->leftjoin('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
        ->where('m_kota_kab.id',$request->kota)
        ->where('m_provinsi.id',$request->provinsi);

        if( $request->kecamatan != null ){
            $query->where('m_kecamatan.id',$request->kecamatan);
        }
        $dataCustomer = $query->orderBy('m_customer.code')->get();
        $i=1;

        foreach ($dataCustomer as $customer) {
            $sheetArray[] = array('',$i++,$customer->code,$customer->name,$customer->main_email,$customer->main_phone_1,$customer->main_cp_name,$customer->main_address,$customer->kota,$customer->kecamatan,$customer->kelurahan,$customer->wilayah);
        }
        Excel::create('Master Customer', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterSupplier(Request $request)
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Supplier');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Kode','Nama','Email','Telepon','Yg Bisa Dihubungi','Alamat','Kota/Kab','Kecamatan','Kelurahan /Desa');
        $query = MSupplierModel::select('m_supplier.*','m_kota_kab.name as kota',
        'm_kecamatan.name as kecamatan','m_kelurahan_desa.name as kelurahan'
        )
        ->leftjoin('m_kelurahan_desa','m_kelurahan_desa.id','=','m_supplier.main_kelurahan')
        ->leftjoin('m_kecamatan','m_kecamatan.id','=','m_kelurahan_desa.kecamatan')
        ->leftjoin('m_kota_kab','m_kota_kab.id','=','m_kecamatan.kota_kab')
        ->leftjoin('m_provinsi','m_provinsi.id','=','m_kota_kab.provinsi')
        ->where('m_kota_kab.id',$request->kota)
        ->where('m_provinsi.id',$request->provinsi);

        if( $request->kecamatan != null ){
            $query->where('m_kecamatan.id',$request->kecamatan);
        }
        $dataSupplier = $query->orderBy('m_supplier.code')->get();
        $i=1;

        foreach ($dataSupplier as $supplier) {
            $sheetArray[] = array('',$i++,$supplier->code,$supplier->name,$supplier->main_email,$supplier->main_phone_1,$supplier->main_cp_name_1,$supplier->main_address,$supplier->kota,$supplier->kecamatan,$supplier->kelurahan);
        }
        Excel::create('Master Supplier', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function userrole()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Role');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Name','Role','Approval','Master','Rencana','Komplain','Stok','SO','SJ','Tagihan');
        $dataRole = MRoleModel::all();
        $i=1;

        foreach ($dataRole as $role) {
            $sheetArray[] = array('',$i++,$role->name,($role->status_role  == 1) ? 'Ya' : 'Tidak',($role->status_approval == 1) ? 'Ya' : 'Tidak',($role->status_master == 1) ? 'Ya' : 'Tidak',($role->status_plan == 1) ? 'Ya' : 'Tidak',($role->status_komplain == 1) ? 'Ya' : 'Tidak',($role->status_stok == 1) ? 'Ya' : 'Tidak',($role->status_so == 1) ? 'Ya' : 'Tidak',($role->status_sj == 1) ? 'Ya' : 'Tidak',($role->status_tagihan == 1) ? 'Ya' : 'Tidak');
        }
        Excel::create('Master Role', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterJenisBarang()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Jenis Barang');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Jenis Barang');
        $dataJenisBarang = DB::table('m_jenis_produk')->get();
        $i=1;

        foreach ($dataJenisBarang as $jenisBarang) {
            $sheetArray[] = array('',$i++,$jenisBarang->name);
        }
        Excel::create('Master Jenis Barang', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterBahanBarang()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Bahan Barang');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Bahan Barang');
        $dataBahanBarang = DB::table('m_bahan_produk')->get();
        $i=1;

        foreach ($dataBahanBarang as $bahanBarang) {
            $sheetArray[] = array('',$i++,$bahanBarang->name);
        }
        Excel::create('Master Bahan Barang', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function masterMerekBarang()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Merek Barang');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Merek Barang');
        $dataMerekBarang = DB::table('m_merek_produk')->get();
        $i=1;

        foreach ($dataMerekBarang as $merekBarang) {
            $sheetArray[] = array('',$i++,$merekBarang->name);
        }

        Excel::create('Master Merek Barang', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');

    }

    public function barang()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Barang');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Kode','Nama','Jenis','Bahan','Merek','Lebar','Panjang','Tinggi','Berat');
        $dataBarang = DB::table('m_produk')
        ->join('m_jenis_produk', 'm_jenis_produk.id', '=', 'm_produk.jenis')
        ->join('m_bahan_produk', 'm_bahan_produk.id', '=', 'm_produk.bahan')
        ->join('m_merek_produk', 'm_merek_produk.id', '=', 'm_produk.merek')
        ->select('m_produk.id','m_produk.code','m_produk.name','m_produk.lebar','m_produk.panjang','m_produk.tinggi','m_produk.berat','m_jenis_produk.name as jenis','m_bahan_produk.name as bahan','m_merek_produk.name as merek')
        ->get();

        $i=1;

        foreach ($dataBarang as $data) {
            $sheetArray[] = array('',$i++,$data->code,$data->name,$data->jenis,$data->bahan,$data->merek,$data->lebar,$data->panjang,$data->tinggi,$data->berat);
        }

        Excel::create('Master Barang', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function hargabarang()
    {
        $sheetArray = array();
        $sheetArray[] = array('Laporan Master Harga Barang');
        $sheetArray[] = array('Tanggal : '.date('d-m-Y'));

        $sheetArray[] = array(); // Add an empty row

        //Header
        $sheetArray[] = array('','No','Kode Barang','Barang','Harga','Tanggal Mulai','Tanggal Selesai');
        $dataHarga = MHargaProdukModel::with('produkRelation')->get();

        $i=1;

        foreach ($dataHarga as $data) {
            $sheetArray[] = array('',$i++,$data->produkRelation->code,$data->produkRelation->name,$data->price,$data->date_start,$data->date_end);
        }

        Excel::create('Master Harga Barang', function($excel) use ($sheetArray)
        {
            $excel->sheet('sheet 1', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function reportDp(Request $request)
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

        // dd($request->all(),$status);

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

            $sheetArray = array();
            $sheetArray[] = array('Laporan Down Payment');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Customer : '.$customer);
            $sheetArray[] = array('Status : '.$status);

            $sheetArray[] = array(); // Add an empty row

            foreach ($dataDp as $data) {
                $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($data->tgl)));

                foreach($data->data_customer as $data2){
                    $sheetArray[] = array('Nama Customer : '.$data2->customer_name);

                    $sheetArray[] = array(); // Add an empty row

                    $sheetArray[] = array('Nomor Down Payment','Status','Type','Nominal','Dipakai','Sisa');
                    $sisa = 0;
                    foreach ($data2->dataDpHeader as $data3) {
                        $sisa = $data3->dp_total - $data3->jumlah_yg_dipakai;
                        $sheetArray[] = array(
                            $data3->dp_code,
                            ucfirst($data3->status),
                            ucfirst($data3->type),
                            'Rp. '.number_format($data3->dp_total,0,'.','.'),
                            'Rp. '.number_format($data3->jumlah_yg_dipakai,0,'.','.'),
                            'Rp. '.number_format($sisa,0,'.','.'),
                        );
                    }

                }
            }

            $jmlRow = count($dataDp);

            Excel::create('Laporan-dp-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow)
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
                        'C'     =>  20,
                        'D'     =>  30,
                        'E'     =>  30,
                        'F'     =>  15,
                        'G'     =>  12,
                        'H'     =>  12,
                        'I'     =>  12
                    ));

                    // $sheet->cell('A10:I10', function($cell) {
                    //     $cell->setBackground('#C0C0C0');
                    // });
                    //
                    // $sheet->cell('A10:I10', function($cell) {
                    //     $cell->setAlignment('center');
                    // });
                    //
                    // $sheet->setBorder('A10:I'.(10+$jmlRow), 'thin');

                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }else{
            //detail

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

            $sheetArray = array();
            $sheetArray[] = array('Laporan Down Payment');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Customer : '.$customer);
            $sheetArray[] = array('Status : '.$status);

            $sheetArray[] = array(); // Add an empty row

            foreach ($dataDp as $data) {
                $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($data->tgl)));

                foreach($data->data_customer as $data2){
                    $sheetArray[] = array('Nama Customer : '.$data2->customer_name);

                    $sheetArray[] = array(); // Add an empty row

                    foreach($data2->data_dpcode as $data3){
                        $sheetArray[] = array('No. DP :'.$data3->dp_code);
                        $sheetArray[] = array('Status : '.ucfirst($data3->status));

                        $sheetArray[] = array(); // Add an empty row
                        $sheetArray[] = array(); // Add an empty row

                        $sheetArray[] = array('Tanggal','Nomor','In','Out','Balance');
                        foreach($data3->detail as $data4){

                            $sheetArray[] = array(
                                date('d-m-Y',strtotime($data4->created_at)),
                                $data4->transaksi,
                                'Rp. '.number_format($data4->in,0,'.','.'),
                                'Rp. '.number_format($data4->out,0,'.','.'),
                                'Rp. '.number_format($data4->saldo_akhir,0,'.','.'),
                            );
                        }
                    }

                }
            }

            $jmlRow = count($dataDp);

            Excel::create('Laporan-dp-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow)
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
                        'C'     =>  20,
                        'D'     =>  30,
                        'E'     =>  30,
                        'F'     =>  15,
                        'G'     =>  12,
                        'H'     =>  12,
                        'I'     =>  12
                    ));

                    // $sheet->cell('A10:I10', function($cell) {
                    //     $cell->setBackground('#C0C0C0');
                    // });
                    //
                    // $sheet->cell('A10:I10', function($cell) {
                    //     $cell->setAlignment('center');
                    // });
                    //
                    // $sheet->setBorder('A10:I'.(10+$jmlRow), 'thin');

                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function reportDpPurchase(Request $request)
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

        // dd($request->all(),$status);

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

            $sheetArray = array();
            $sheetArray[] = array('Laporan Down Payment Purchase');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Supplier : '.$supplier);
            $sheetArray[] = array('Status : '.$status);

            $sheetArray[] = array(); // Add an empty row

            foreach ($dataDp as $data) {
                $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($data->tgl)));

                foreach($data->data_supplier as $data2){
                    $sheetArray[] = array('Nama Supplier : '.$data2->supplier_name);

                    $sheetArray[] = array(); // Add an empty row

                    $sheetArray[] = array('Nomor Down Payment','Status','Type','Nominal','Dipakai','Sisa');
                    $sisa = 0;
                    foreach ($data2->dataDpHeader as $data3) {
                        $sisa = $data3->dp_total - $data3->jumlah_yg_dipakai;
                        $sheetArray[] = array(
                            $data3->dp_code,
                            ucfirst($data3->status),
                            ucfirst($data3->type),
                            'Rp. '.number_format($data3->dp_total,0,'.','.'),
                            'Rp. '.number_format($data3->jumlah_yg_dipakai,0,'.','.'),
                            'Rp. '.number_format($sisa,0,'.','.'),
                        );
                    }

                }
            }

            $jmlRow = count($dataDp);

            Excel::create('Laporan-dp-purchase-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow)
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
                        'C'     =>  20,
                        'D'     =>  30,
                        'E'     =>  30,
                        'F'     =>  15,
                        'G'     =>  12,
                        'H'     =>  12,
                        'I'     =>  12
                    ));

                    // $sheet->cell('A10:I10', function($cell) {
                    //     $cell->setBackground('#C0C0C0');
                    // });
                    //
                    // $sheet->cell('A10:I10', function($cell) {
                    //     $cell->setAlignment('center');
                    // });
                    //
                    // $sheet->setBorder('A10:I'.(10+$jmlRow), 'thin');

                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }else{
            //detail

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
            // dd($dataDp,'detail');

            $sheetArray = array();
            $sheetArray[] = array('Laporan Down Payment Purchase');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Supplier : '.$supplier);
            $sheetArray[] = array('Status : '.$status);

            $sheetArray[] = array(); // Add an empty row

            foreach ($dataDp as $data) {
                $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($data->tgl)));

                foreach($data->data_supplier as $data2){
                    $sheetArray[] = array('Nama Supplier : '.$data2->supplier_name);

                    $sheetArray[] = array(); // Add an empty row

                    foreach($data2->data_dpcode as $data3){
                        $sheetArray[] = array('No. DP :'.$data3->dp_code);
                        $sheetArray[] = array('Status : '.ucfirst($data3->status));

                        $sheetArray[] = array(); // Add an empty row
                        $sheetArray[] = array(); // Add an empty row

                        $sheetArray[] = array('Tanggal','Nomor','In','Out','Balance');
                        foreach($data3->detail as $data4){

                            $sheetArray[] = array(
                                date('d-m-Y',strtotime($data4->created_at)),
                                $data4->transaksi,
                                'Rp. '.number_format($data4->in,0,'.','.'),
                                'Rp. '.number_format($data4->out,0,'.','.'),
                                'Rp. '.number_format($data4->saldo_akhir,0,'.','.'),
                            );
                        }
                    }

                }
            }

            $jmlRow = count($dataDp);

            Excel::create('Laporan-dp-purchase-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray,$jmlRow)
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
                        'C'     =>  20,
                        'D'     =>  30,
                        'E'     =>  30,
                        'F'     =>  15,
                        'G'     =>  12,
                        'H'     =>  12,
                        'I'     =>  12
                    ));

                    // $sheet->cell('A10:I10', function($cell) {
                    //     $cell->setBackground('#C0C0C0');
                    // });
                    //
                    // $sheet->cell('A10:I10', function($cell) {
                    //     $cell->setAlignment('center');
                    // });
                    //
                    // $sheet->setBorder('A10:I'.(10+$jmlRow), 'thin');

                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }
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

            $query->orderBy('po_code');

            $dataPO = $query->get();

            foreach ($dataPO as $raw_data) {
                $total = DB::table('d_purchase_order')
                ->where('po_code', $raw_data->po_code)
                ->sum('total_neto');

                $raw_data->total_awal = $total;
            }

            $sheetArray = array();
            $sheetArray[] = array('Laporan Purchase Order (Summary)');
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
                $sheetArray[] = array(date('d-m-Y',strtotime($raw_data->po_date)),$raw_data->po_code,$raw_data->supplier_name,$raw_data->status_aprove,$raw_data->total_awal,$raw_data->diskon_header_persen,$raw_data->diskon_header_potongan,$raw_data->total,);
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
            $query = DB::table('d_purchase_order');
            $query->select('t_purchase_order.po_code','d_purchase_order.produk as id_produk','d_purchase_order.qty','t_purchase_order.po_date','m_produk.name as produk_name','m_supplier.name as supplier_name','m_produk.code as produk_code','d_purchase_order.price','d_purchase_order.total_neto');
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

            $query->orderBy('d_purchase_order.po_code');

            $dataPO = $query->get();

            //dd($dataSO);

            $sheetArray = array();
            $sheetArray[] = array('Laporan Purchase Order (Detail)');
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
                $sheetArray[] = array(date('d-m-Y',strtotime($raw_data->po_date)),$raw_data->po_code,$raw_data->supplier_name,$raw_data->produk_code,$raw_data->produk_name,$raw_data->qty,$raw_data->price,$raw_data->total_neto,);
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

    public function reportPDExcel(request $request)
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
            //dd($request->so);
            $query->where('t_surat_jalan_masuk.po_code',$request->po);
        }
        if ($request->pd != '0') {
            $query->where('t_surat_jalan_masuk.sj_masuk_code',$request->pd);
        }

        if ($request->barang != null) {
            $query->where('d_surat_jalan_masuk.produk_id',$request->barang);
        }

        if ($request->status == 'in process') {
            $query->where('t_surat_jalan_masuk.status','in process');
        }
        if ($request->status == 'post') {
            $query->where('t_surat_jalan_masuk.status','post');
        }
        if ($request->status == 'cancel') {
            $query->where('t_surat_jalan_masuk.status','cancel');
        }
        $query->groupBy('tgl');

        $dataSJ = $query->get();

        foreach ($dataSJ as $raw_data) {
            $query = DB::table('t_surat_jalan_masuk');
            $query->select('supplier','m_supplier.name as supplier_name');
            $query->join('d_surat_jalan_masuk', 'd_surat_jalan_masuk.sj_masuk_code', '=', 't_surat_jalan_masuk.sj_masuk_code');
            $query->join('m_supplier', 'm_supplier.id', '=', 't_surat_jalan_masuk.supplier');
            // $query->where('t_surat_jalan_masuk.sj_masuk_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
            // $query->where('t_surat_jalan_masuk.sj_masuk_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));
            if ($request->supplier != null) {
                $query->where('t_surat_jalan_masuk.supplier', $request->supplier);
            }
            if ($request->po != null) {
                //dd($request->so);
                $query->where('t_surat_jalan_masuk.po_code',$request->oo);
            }
            if ($request->pd != '0') {
                $query->where('t_surat_jalan_masuk.sj_masuk_code',$request->pd);
            }

            if ($request->barang != null) {
                $query->where('d_surat_jalan_masuk.produk_id',$request->barang);
            }

            if ($request->status == 'in process') {
                $query->where('t_surat_jalan_masuk.status','in process');
            }
            if ($request->status == 'post') {
                $query->where('t_surat_jalan_masuk.status','post');
            }
            if ($request->status == 'cancel') {
                $query->where('t_surat_jalan_masuk.status','cancel');
            }
            $query->groupBy('supplier','m_supplier.name');

            $data_supplier = $query->get();
            $raw_data->data_supplier = $data_supplier;

            foreach ($data_supplier as $raw_data2) {
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

                if ($request->status == 'in process') {
                    $query->where('t_surat_jalan_masuk.status','in process');
                }
                if ($request->status == 'post') {
                    $query->where('t_surat_jalan_masuk.status','post');
                }
                if ($request->status == 'cancel') {
                    $query->where('t_surat_jalan_masuk.status','cancel');
                }
                $query->groupBy('t_surat_jalan_masuk.sj_masuk_code','status');

                $dataSJH = $query->get();
                $raw_data2->data_sjcode = $dataSJH;

                foreach ($dataSJH as $raw_data3) {
                    $query = DB::table('d_surat_jalan_masuk');
                    $query->select('produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as satuan_kemasan','qty','last_po_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_surat_jalan_masuk.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
                    $query->where('sj_masuk_code',$raw_data3->sj_masuk_code);
                    if ($request->barang != null) {
                        $query->where('d_surat_jalan_masuk.produk_id',$request->barang);
                    }
                    $dataProduk = $query->get();
                    $raw_data3->detail_sj = $dataProduk;

                    foreach ($dataProduk as $raw_data4) {
                        $getpocode = DB::table('t_surat_jalan_masuk')
                        ->where('sj_masuk_code',$raw_data3->sj_masuk_code)
                        ->pluck('po_code')
                        ->first();

                        $totalPOQty = DB::table('d_purchase_order')
                        ->where('po_code', $getpocode)
                        ->where('produk', $raw_data4->produk_id)
                        ->pluck('qty')
                        ->first();
                        $raw_data4->POQty = $totalPOQty;
                    }
                }
            }
        }
        //dd($dataSJ);

        $sheetArray = array();
        $sheetArray[] = array('Laporan Purchase Delivery');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
        $sheetArray[] = array('PO No. : '.$po_code);
        $sheetArray[] = array('PD No. : '.$sj_masuk_code);
        $sheetArray[] = array('Item : '.$barang);
        $sheetArray[] = array('Supplier : '.$supplier);
        $sheetArray[] = array('Status : '.$status);

        $sheetArray[] = array(); // Add an empty row

        foreach($dataSJ  as $raw_data){
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
            foreach ($raw_data->data_supplier as $raw_data2) {
                $sheetArray[] = array('Supplier : '.$raw_data2->supplier_name);
                foreach ($raw_data2->data_sjcode as $raw_data3) {
                    $sheetArray[] = array('No. PD : '.$raw_data3->sj_masuk_code, 'Status : '.$raw_data3->status);
                    $sheetArray[] = array('Nama Barang', 'Kode Barang', 'Satuan', 'QTY PO', 'QTY PD');
                    foreach ($raw_data3->detail_sj as $raw_data4) {
                        $sheetArray[] = array(
                            $raw_data4->produk_name,
                            $raw_data4->produk_code,
                            $raw_data4->satuan_kemasan,
                            $raw_data4->POQty,
                            $raw_data4->qty,
                        );
                    }
                    $sheetArray[] = array();
                }
                $sheetArray[] = array();
            }
        }

        Excel::create('Laporan-pd-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }


    public function reportPurchaseInvoiceExcel(request $request)
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
        $query->where('t_purchase_invoice.status','unpaid');
        if ($request->supplier != null) {
            $query->where('m_supplier.id', $request->supplier);
        }
        $query->groupBy('m_supplier.id');
        $query->orderBy('m_supplier.name');
        $datasupplier = $query->get();

        //dd($datasupplier);

        if ($request->type == 'summary') {
            foreach ($datasupplier as $raw_data) {
                $saldoawal = DB::table('t_purchase_invoice')
                ->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                ->where('supplier',$raw_data->id)
                ->sum('total');

                $pembelian = DB::table('t_purchase_invoice')
                ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('supplier',$raw_data->id)
                ->sum('total');

                $pembayaran = DB::table('d_pi_pembayaran')
                ->join('t_pi_pembayaran', 't_pi_pembayaran.pembayaran_code', '=', 'd_pi_pembayaran.pembayaran_code')
                ->where('t_pi_pembayaran.payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('t_pi_pembayaran.payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('t_pi_pembayaran.supplier',$raw_data->id)
                ->where('status','approved')
                ->sum('d_pi_pembayaran.total');

                $raw_data->saldoawal = $saldoawal;
                $raw_data->pembelian = $pembelian;
                $raw_data->pembayaran = $pembayaran;
                $raw_data->saldoakhir = $saldoawal + $pembelian - $pembayaran;
            }

            //dd($datasupplier);

            $sheetArray = array();
            $sheetArray[] = array('Kartu Hutang (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Supplier : '.$supplier);

            $sheetArray[] = array(); // Add an empty row

            $sheetArray[] = array('Supplier', 'Saldo Awal', 'Hutang', 'Pembayaran', 'Saldo Akhir');

            $TSaldoAwal = 0;
            $TPembelian = 0;
            $TPembayaran = 0;
            $TSaldoAkhir = 0;

            foreach($datasupplier  as $raw_data){
                $sheetArray[] = array($raw_data->name,number_format($raw_data->saldoawal,0,'.','.'),number_format($raw_data->pembelian,0,'.','.'),number_format($raw_data->pembayaran,0,'.','.'),number_format($raw_data->saldoakhir,0,'.','.'));

                $TSaldoAwal = $TSaldoAwal + $raw_data->saldoawal;
                $TPembelian = $TPembelian + $raw_data->pembelian;
                $TPembayaran = $TPembayaran + $raw_data->pembayaran;
                $TSaldoAkhir = $TSaldoAkhir + $raw_data->saldoakhir;
            }

            $sheetArray[] = array('TOTAL =', number_format($TSaldoAwal,0,'.','.'), number_format($TPembelian,0,'.','.'), number_format($TPembayaran,0,'.','.'), number_format($TSaldoAkhir,0,'.','.'));

            Excel::create('Supplier-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');

        }else{
            foreach ($datasupplier as $raw_data) {
                // $saldoawal = DB::table('t_purchase_invoice')
                //     ->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                //     ->where('supplier',$raw_data->id)
                //     ->sum('total');

                $saldoawal = DB::table('m_saldo_awal_hutang')
                    //->where('created_at','<',date('Y-m-d', strtotime($tglmulai)))
                    ->where('supplier',$raw_data->id)
                    ->sum('total_hutang');

                $pembelian = DB::table('t_purchase_invoice')
                    ->select('created_at as tanggal','pi_code as keterangan','jatuh_tempo','total as hutang', DB::raw("'0' as pembayaran"))
                    ->where('created_at','>=',date('Y-m-d', strtotime($tglmulai)))
                    ->where('created_at','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                    ->where('supplier',$raw_data->id);
                    //->get();

                $pembayaran = DB::table('d_pi_pembayaran')
                ->select('t_pi_pembayaran.created_at as tanggal','d_pi_pembayaran.pembayaran_code as keterangan','t_pi_pembayaran.payment_date as jatuh_tempo', DB::raw("'0' as hutang"),'total as pembayaran')
                ->join('t_pi_pembayaran', 't_pi_pembayaran.pembayaran_code', '=', 'd_pi_pembayaran.pembayaran_code')
                ->where('t_pi_pembayaran.payment_date','>=',date('Y-m-d', strtotime($tglmulai)))
                ->where('t_pi_pembayaran.payment_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
                ->where('t_pi_pembayaran.supplier',$raw_data->id)
                ->where('status','approved')
                ->union($pembelian)
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

                $raw_data->detail = $data;
            }

            //dd($datasupplier);

            $sheetArray = array();
            $sheetArray[] = array('Kartu Hutang (Detail)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Supplier : '.$supplier);

            $sheetArray[] = array(); // Add an empty row

            foreach($datasupplier  as $raw_data){
                $balance = 0;
                $sheetArray[] = array('Supplier : '.$raw_data->name);
                $sheetArray[] = array('Tanggal', 'Keterangan', 'Jatuh Tempo', 'Hutang', 'Pembayaran','Balance');

                $THutang = 0;
                $TPembayaran = 0;
                $TBalance = 0;

                foreach ($raw_data->detail as $key => $raw_data2) {
                    if ($key == 0) {
                        $tanggal = $raw_data2['tanggal'];
                        $keterangan = $raw_data2['keterangan'];
                        $jatuh_tempo = $raw_data2['jatuh_tempo'];
                        $hutang = $raw_data2['hutang'];
                        $pembayaran = $raw_data2['pembayaran'];
                        $balance = $balance + $hutang - $pembayaran;
                    }else{
                        $tanggal = $raw_data2->tanggal;
                        $keterangan = $raw_data2->keterangan;
                        $jatuh_tempo = $raw_data2->jatuh_tempo;
                        $hutang = $raw_data2->hutang;
                        $pembayaran = $raw_data2->pembayaran;
                        $balance = $balance + $hutang - $pembayaran;
                    }
                    $sheetArray[] = array(date('d-m-Y',strtotime($tanggal)),$keterangan,date('d-m-Y',strtotime($jatuh_tempo)),number_format($hutang,0,'.','.'),number_format($pembayaran,0,'.','.'),number_format($balance,0,'.','.'));

                    $THutang = $THutang + $hutang;
                    $TPembayaran = $TPembayaran + $pembayaran;
                    $TBalance = $balance;
                }

                $sheetArray[] = array('', '', 'TOTAL = ', number_format($THutang,0,'.','.'), number_format($TPembayaran,0,'.','.'),number_format($TBalance,0,'.','.'));
                $sheetArray[] = array(); // Add an empty row
            }

            Excel::create('Supplier-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function reportPOSJMExcel(request $request)
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

        $dataPOSJ = $query->get();

        foreach ($dataPOSJ as $raw_data) {
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

            $data_supplier = $query->get();
            $raw_data->data_supplier = $data_supplier;

            foreach ($data_supplier as $raw_data2) {
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
                    $query->select('produk as produk_id','m_produk.name as produk_name','m_satuan_unit.code as satuan_kemasan','qty as qty_po');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_purchase_order.produk');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
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

        //dd($dataSOSJ);

        $sheetArray = array();
        $sheetArray[] = array('Laporan Status PO PD');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
        $sheetArray[] = array('PO No. : '.$po_code);
        $sheetArray[] = array('Item : '.$barang);
        $sheetArray[] = array('Supplier : '.$supplier);

        $sheetArray[] = array(); // Add an empty row
        foreach($dataPOSJ  as $raw_data){
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
            foreach ($raw_data->data_supplier as $raw_data2) {
                $sheetArray[] = array('Supplier : '.$raw_data2->supplier_name);
                foreach ($raw_data2->data_pocode as $raw_data3) {
                    $sheetArray[] = array('No PO : '.$raw_data3->po_code, 'Status : '.$raw_data3->status_aprove);
                    foreach ($raw_data3->detail_po as $raw_data4) {
                        $sheetArray[] = array('Barang : '.$raw_data4->produk_name, 'QTY PO : '.$raw_data4->qty_po, 'Satuan : '.$raw_data4->satuan_kemasan);
                        $sheetArray[] = array('No. PD', 'Tgl PD', 'O/S PO', 'QTY PD', 'Sisa');
                        $totalawal = $raw_data4->qty_po;
                        foreach ($raw_data4->data_sj as $raw_data5) {
                            $totalakhir = $totalawal - $raw_data5->qty;
                            $sheetArray[] = array(
                                $raw_data5->sj_masuk_code,
                                date('d-m-Y',strtotime($raw_data5->sj_masuk_date)),
                                $totalawal,
                                $raw_data5->qty,
                                $totalakhir
                            );
                            $totalawal = $totalawal - $raw_data5->qty;
                        }
                        $sheetArray[] = array();
                    }
                    $sheetArray[] = array();
                }
                $sheetArray[] = array();
            }
        }

        Excel::create('Laporan-popd-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function ReturSJ(request $request)
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
            $query->select('t_retur_sj.*','m_customer.id','m_customer.name','t_faktur.faktur_code');
            $query->groupBy('m_customer.id');
            $query->groupBy('t_retur_sj.id');
            $query->groupBy('t_faktur.id');
            $datacustomer = $query->get();
            //dd($datacustomer);

            $sheetArray = array();
            $sheetArray[] = array('Retur SJ (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Customer : '.$customer);

            $sheetArray[] = array(); // Add an empty row

            $sheetArray[] = array('Tanggal','Customer','NO. Retur','NO. SO','NO. Faktur','Netto','Catatan');

            foreach($datacustomer  as $data){
                $sheetArray[] = array($data->retur_dates,$data->name,$data->rt_code,$data->so_code,$data->faktur_code,number_format($data->grand_total,0,'.','.'),$data->description);


            }

            Excel::create('Retur_SJ-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
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
                ->groupBy('m_customer.id','customer');
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
            $sheetArray = array();
            $sheetArray[] = array('Retur SJ (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Customer : '.$customer);

            $sheetArray[] = array(); // Add an empty row

            foreach($datadetail  as $raw_data){
                $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
                foreach ($raw_data->customer as $raw_data2) {
                    $sheetArray[] = array('Customer : '.$raw_data2->customer);
                    foreach ($raw_data2->so as $raw_data3) {
                        $sheetArray[] = array('No. SO : '.$raw_data3->so_code);
                        foreach ($raw_data3->retur as $raw_data4){
                            $sheetArray[] = array('No. Retur : '.$raw_data3->rt_code);
                            foreach ($raw_data4->barang as $raw_data5) {
                                $sheetArray[] = array('Nama Barang','Harga','QTY','Total');
                                $sheetArray[] = array(
                                    $raw_data5->produk,
                                    $raw_data5->harga,
                                    $raw_data5->qty,
                                    $raw_data5->total
                                );
                            }
                        }
                        $sheetArray[] = array();
                    }
                    $sheetArray[] = array();
                }
            }

            Excel::create('Retur_SJ-Detail'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function ReturSJM(request $request)
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

            $sheetArray = array();
            $sheetArray[] = array('Retur SJ (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Supplier : '.$supplier);

            $sheetArray[] = array(); // Add an empty row

            $sheetArray[] = array('Tanggal','Supplier','NO. Retur','NO. SO','NO. PI','Netto','Catatan');

            foreach($datasupplier  as $data){
                $sheetArray[] = array($data->retur_dates,$data->name,$data->rt_code,$data->po_code,$data->faktur_code,number_format($data->grand_total,0,'.','.'),$data->description);


            }

            Excel::create('Retur_SJM-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
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
                $raw_data->supplier=$datasupplier;

                foreach($datasupplier as $raw_data2){
                    $query2=DB::table('t_retur_sjm')
                    ->join('t_sales_order','t_retur_sjm.po_code','=','t_sales_order.po_code')
                    ->select('t_retur_sjm.po_code as so')
                    ->where('t_retur_sjm.supplier','=',$raw_data2->id_supplier)
                    ->groupBy('so');

                    if ($request->status != null) {
                        $query2->where('t_retur_sjm.status', $request->status);
                    }
                    if ($request->no_retur != null) {
                        $query2->where('t_retur_sjm.rt_code', $request->no_retur);
                    }
                    $dataso = $query2->get();
                    $raw_data2->so = $dataso;

                    foreach($dataso as $raw_data3){
                        $query3=DB::table('t_retur_sjm')
                        ->select('t_retur_sjm.rt_code as retur')
                        ->where('t_retur_sjm.po_code','=',$raw_data3->so)
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

            //dd($datadetail);
            $sheetArray = array();
            $sheetArray[] = array('Retur PD (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Supplier : '.$supplier);

            $sheetArray[] = array(); // Add an empty row

            foreach($datadetail  as $raw_data){
                $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
                foreach ($raw_data->supplier as $raw_data2) {
                    $sheetArray[] = array('Supplier : '.$raw_data2->supplier);
                    foreach ($raw_data2->so as $raw_data3) {
                        $sheetArray[] = array('No. PO : '.$raw_data3->po_code);
                        foreach ($raw_data3->retur as $raw_data4){
                            $sheetArray[] = array('No. Retur : '.$raw_data3->rt_code);
                            foreach ($raw_data4->barang as $raw_data5) {
                                $sheetArray[] = array('Nama Barang','Harga','QTY','Total');
                                $sheetArray[] = array(
                                    $raw_data5->produk,
                                    $raw_data5->harga,
                                    $raw_data5->qty,
                                    $raw_data5->total
                                );
                            }
                        }
                        $sheetArray[] = array();
                    }
                    $sheetArray[] = array();
                }
            }

            Excel::create('Retur_SJM-Detail-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray)
            {
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function laporanWarehouse(Request $request)
    {
        $status='ALL';
        if ($request->status != null) {
            $status=$request->status;
        }

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);
        if($request->type=='out'){
            $query1 = DB::table('t_transfer_warehouse')
            ->select(DB::raw("DATE(tw_date) as tgl"))
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
                ->where('t_transfer_warehouse.tw_date','<',date('Y-m-d',strtotime($raw_data->tgl. ' + 1 days')))
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
        }elseif($request->type=='in'){
            $query1 = DB::table('t_transfer_warehouse')
            ->select(DB::raw("DATE(tw_date) as tgl"))
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
        }
        //dd($data);
        $sheetArray = array();
        $sheetArray[] = array('Laporan Transfer Warehouse');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
        $sheetArray[] = array('Status : '.$status);
        $sheetArray[] = array('Type : '.$request->type);

        $sheetArray[] = array(); // Add an empty row

        foreach($data  as $raw_data)
        {
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
            foreach ($raw_data->gudang as $raw_data2) {
                if($request->type =='out'){
                $sheetArray[] = array('Gudang Asal : '.$raw_data2->gudang);}
                if($request->type=='in'){
                    $sheetArray[] = array('Gudang Tujuan : '.$raw_data2->gudang);}

                }
                foreach ($raw_data2->tw_code as $raw_data3) {
                    $sheetArray[] = array('No. TW : '.$raw_data3->tw_code);
                    if($request->type=='out'){
                    $sheetArray[] = array('Gudang Tujuan : '.$raw_data3->gudang_tujuan);}
                    if($request->type=='in'){
                    $sheetArray[] = array('Gudang Asal : '.$raw_data3->gudang_tujuan);}
                    $sheetArray[] = array('Nama Barang','QTY');
                    foreach ($raw_data3->barang as $raw_data5) {
                        $sheetArray[] = array(
                            $raw_data5->barang,
                            $raw_data5->qty
                        );
                    }
                }
                $sheetArray[] = array();
            }
            $sheetArray[] = array();

        Excel::create('Laporan Transfer Warehouse-'.date('dmyhis'), function($excel) use ($sheetArray)
        {
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function laporanAdjusment(Request $request)
    {
        $status='ALL';
        if ($request->status != null) {
            $status=$request->status;
        }

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        $query1 = DB::table('t_adjusment')
        ->select(DB::raw("DATE(ta_date) as tgl"))
        ->groupBy('tgl');
        if ($request->status != null) {
            $query1->where('t_adjusment.status_aprove', $request->status);
        }
        if ($request->ta != null) {
            $query1->where('t_adjusment.ta_code', $request->ta_code);
        }
        if ($request->gudang != null) {
            $query1->where('t_adjusment.gudang', $request->gudang);
        }
        $data = $query1->get();

        foreach($data as $raw_data) {
            $query2=DB::table('t_adjusment')
            ->join('m_gudang','m_gudang.id','=','t_adjusment.gudang')
            ->select('m_gudang.name as gudang','m_gudang.id as id_gudang')
            ->where('t_adjusment.ta_date','<',date('Y-m-d',strtotime($raw_data->tgl. ' + 1 days')))
            ->groupBy('id_gudang','gudang');
            if ($request->status != null) {
                $query2->where('t_adjusment.status_aprove', $request->status);
            }
            if ($request->tw != null) {
                $query2->where('t_adjusment.ta_code', $request->ta_code);
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
                ->groupBy('ta_code');
                if ($request->status != null) {
                    $query3->where('t_adjusment.status_aprove', $request->status);
                }
                if ($request->tw != null) {
                    $query3->where('t_adjusment.ta_code', $request->ta_code);
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
        $sheetArray = array();
        $sheetArray[] = array('Laporan Stok Adjustment');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
        $sheetArray[] = array('Status : '.$status);

        $sheetArray[] = array(); // Add an empty row

        foreach($data  as $raw_data)
        {
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
            foreach ($raw_data->gudang as $raw_data2) {
                $sheetArray[] = array('Gudang : '.$raw_data2->gudang);
                foreach ($raw_data2->ta_code as $raw_data3) {
                    $sheetArray[] = array('No. TA : '.$raw_data3->ta_code);
                    $sheetArray[] = array('Nama Barang','QTY Asal','QTY Selisih','QTY Akhir');
                    foreach ($raw_data3->barang as $raw_data5) {
                        $sheetArray[] = array(
                            $raw_data5->barang,
                            $raw_data5->qty_awal,
                            $raw_data5->qty_selisih,
                            $raw_data5->qty
                        );
                    }
                }
                $sheetArray[] = array();
            }
            $sheetArray[] = array();
        }

        Excel::create('Laporan Stok Adjustment-'.date('dmyhis'), function($excel) use ($sheetArray)
        {
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }



    public function reportUmurPiutangExcel(request $request)
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

                //$raw_data->penjualan = $penjualan;
                $amt_0_14 = 0;
                $amt_15_30 = 0;
                $amt_31_89 = 0;
                $amt_90 = 0;

                foreach ($penjualan as $raw_data2) {
                    $post_date = strtotime($request->periode);
                    $pembelian_date = strtotime($raw_data2->created_at);
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

            //dd($datacustomer);

            $sheetArray = array();
            $sheetArray[] = array('Laporan Umur Piutang (Summary)');
            $sheetArray[] = array('Periode : '.$periode);
            $sheetArray[] = array('Customer : '.$customer);

            $sheetArray[] = array(); // Add an empty row
            $sheetArray[] = array('Customer', '0-14', '15-30', '31-89', '>=91','Total');

            foreach ($datacustomer as $raw_data) {
                $sheetArray[] = array(
                    $raw_data->name,
                    number_format($raw_data->amt_0_14,0,'.','.'),
                    number_format($raw_data->amt_15_30,0,'.','.'),
                    number_format($raw_data->amt_31_89,0,'.','.'),
                    number_format($raw_data->amt_90,0,'.','.'),
                    number_format($raw_data->grand_total,0,'.','.')
                );
            }

            Excel::create('Laporan-Umur-Piutang-Summary-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');

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

            //dd($datacustomer);

            $sheetArray = array();
            $sheetArray[] = array('Laporan Umur Piutang (Detail)');
            $sheetArray[] = array('Periode : '.$periode);
            $sheetArray[] = array('Customer : '.$customer);

            $sheetArray[] = array(); // Add an empty row

            foreach($datacustomer  as $raw_data){
                $sheetArray[] = array('Customer : '.$raw_data->name);
                $sheetArray[] = array('No Faktur','Tgl Faktur','Jatuh Tempo', '0-14', '15-30', '31-89', '>=91','Total');

                $Tamt_0_14 = 0;
                $Tamt_15_30 = 0;
                $Tamt_31_89 = 0;
                $Tamt_90 = 0;
                $Tgrand_total = 0;
                foreach ($raw_data->penjualan as $raw_data2) {
                    $sheetArray[] = array(
                        $raw_data2->faktur_code,
                        date('d-m-Y',strtotime($raw_data2->created_at)),
                        date('d-m-Y',strtotime($raw_data2->jatuh_tempo)),
                        number_format($raw_data2->amt_0_14,0,'.','.'),
                        number_format($raw_data2->amt_15_30,0,'.','.'),
                        number_format($raw_data2->amt_31_89,0,'.','.'),
                        number_format($raw_data2->amt_90,0,'.','.'),
                        number_format($raw_data2->grand_total,0,'.','.')
                    );
                    $Tamt_0_14 = $Tamt_0_14 + $raw_data2->amt_0_14;
                    $Tamt_15_30 = $Tamt_15_30 + $raw_data2->amt_15_30;
                    $Tamt_31_89 = $Tamt_31_89 + $raw_data2->amt_31_89;
                    $Tamt_90 = $Tamt_90 + $raw_data2->amt_90;
                    $Tgrand_total = $Tgrand_total + $raw_data2->grand_total;
                }
                $sheetArray[] = array('','','Total Customer =', number_format($Tamt_0_14,0,'.','.'), number_format($Tamt_15_30,0,'.','.'), number_format($Tamt_31_89,0,'.','.'), number_format($Tamt_90,0,'.','.'),number_format($Tgrand_total,0,'.','.'));
                $sheetArray[] = array();
            }

            Excel::create('Laporan-Umur-Piutang-Detail-'.$customer.'-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function reportUmurHutangExcel(request $request)
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

            $sheetArray = array();
            $sheetArray[] = array('Laporan Umur Hutang (Summary)');
            $sheetArray[] = array('Periode : '.$periode);
            $sheetArray[] = array('Supplier : '.$supplier);

            $sheetArray[] = array(); // Add an empty row
            $sheetArray[] = array('Supplier', '0-14', '15-30', '31-89', '>=91','Total');

            foreach ($datasupplier as $raw_data) {
                $sheetArray[] = array(
                    $raw_data->name,
                    number_format($raw_data->amt_0_14,0,'.','.'),
                    number_format($raw_data->amt_15_30,0,'.','.'),
                    number_format($raw_data->amt_31_89,0,'.','.'),
                    number_format($raw_data->amt_90,0,'.','.'),
                    number_format($raw_data->grand_total,0,'.','.')
                );
            }

            Excel::create('Laporan-Umur-Hutang-Summary-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');

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

            $sheetArray = array();
            $sheetArray[] = array('Laporan Umur Hutang (Detail)');
            $sheetArray[] = array('Periode : '.$periode);
            $sheetArray[] = array('Supplier : '.$supplier);

            $sheetArray[] = array(); // Add an empty row

            foreach($datasupplier  as $raw_data){
                $sheetArray[] = array('Supplier : '.$raw_data->name);
                $sheetArray[] = array('No PI','Tgl PI','Jatuh Tempo', '0-14', '15-30', '31-89', '>=91','Total');

                $Tamt_0_14 = 0;
                $Tamt_15_30 = 0;
                $Tamt_31_89 = 0;
                $Tamt_90 = 0;
                $Tgrand_total = 0;
                foreach ($raw_data->pembelian as $raw_data2) {
                    $sheetArray[] = array(
                        $raw_data2->pi_code,
                        date('d-m-Y',strtotime($raw_data2->created_at)),
                        date('d-m-Y',strtotime($raw_data2->jatuh_tempo)),
                        number_format($raw_data2->amt_0_14,0,'.','.'),
                        number_format($raw_data2->amt_15_30,0,'.','.'),
                        number_format($raw_data2->amt_31_89,0,'.','.'),
                        number_format($raw_data2->amt_90,0,'.','.'),
                        number_format($raw_data2->grand_total,0,'.','.')
                    );
                    $Tamt_0_14 = $Tamt_0_14 + $raw_data2->amt_0_14;
                    $Tamt_15_30 = $Tamt_15_30 + $raw_data2->amt_15_30;
                    $Tamt_31_89 = $Tamt_31_89 + $raw_data2->amt_31_89;
                    $Tamt_90 = $Tamt_90 + $raw_data2->amt_90;
                    $Tgrand_total = $Tgrand_total + $raw_data2->grand_total;
                }
                $sheetArray[] = array('','','Total Supplier =', number_format($Tamt_0_14,0,'.','.'), number_format($Tamt_15_30,0,'.','.'), number_format($Tamt_31_89,0,'.','.'), number_format($Tamt_90,0,'.','.'),number_format($Tgrand_total,0,'.','.'));
                $sheetArray[] = array();
            }

            Excel::create('Laporan-Umur-Hutang-Detail-'.$supplier.'-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function laporanKas(Request $request)
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

            $sheetArray   = array();
            $sheetArray[] = array('Laporan Kas Harian (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Akun : '.$akun);

            $sheetArray[] = array(); // Add an empty row

            $sheetArray[] = array('Tanggal', 'No Bukti', 'Keterangan', 'Kode COA', 'Debet','Credit');

            $i = 1;
            $TDebet = 0;
            $TCredit = 0;
            $TSaldoAwal = $saldoawal;
            $TSaldoAkhir = 0;

            foreach($data as $raw_data){
                if ($raw_data->cash_bank_type == 'BKK') {
                    $debet = 0;
                    $credit = $raw_data->cash_bank_total;
                }else{
                    $debet = $raw_data->cash_bank_total;
                    $credit = 0;
                }

                $TDebet = $TDebet + $debet;
                $TCredit = $TCredit + $credit;

                $sheetArray[] = array(
                    date('d-m-Y',strtotime($raw_data->cash_bank_date)),
                    $raw_data->cash_bank_code,
                    $raw_data->cash_bank_keterangan,
                    $raw_data->code,
                    number_format($debet,0,'.','.'),
                    number_format($credit,0,'.','.'),
                );
            }

            $sheetArray[] = array('', '', '', 'Jumlah', number_format($TDebet,0,'.','.'),number_format($TCredit,0,'.','.'));
            $sheetArray[] = array('', '', '', 'Saldo Awal', number_format($TSaldoAwal,0,'.','.'),'');

            $TSaldoAkhir = $TSaldoAwal + $TDebet - $TCredit;
            $TTotalDebet = $TSaldoAwal + $TDebet;
            $TTotalCredit = $TSaldoAkhir + $TCredit;

            $sheetArray[] = array('', '', '', 'Saldo Akhir', '',number_format($TSaldoAkhir,0,'.','.'));
            $sheetArray[] = array('', '', '', 'Total', number_format($TTotalDebet,0,'.','.'),number_format($TTotalCredit,0,'.','.'));

            Excel::create('Laporan-kas-summary-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');

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

            $sheetArray   = array();
            $sheetArray[] = array('Laporan Kas Harian (Detail)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Akun : '.$akun);

            $sheetArray[] = array(); // Add an empty row

            foreach ($data as $raw_data) {
                $i = 1;
                $total_debet = 0;
                $total_credit = 0;
                $saldo_awal = $raw_data->saldo_awal;

                $sheetArray[] = array('Account : '.$raw_data->code.' '.$raw_data->desc);
                $sheetArray[] = array('No','Kas / Bank','','','Supplier / Customer', '', 'Perkiraan','','','','Nilai Transaksi');
                $sheetArray[] = array('','','', '','','', 'Transaksi','','Lawan Transaksi','','','');
                $sheetArray[] = array('','Tanggal','No Bukti','', 'Kode', 'Nama','Kode','Keterangan','Kode','Keterangan','Debet','Credit');

                $sheetArray[] = array(
                    $i,
                    date('d-m-Y',strtotime($tglmulai)),
                    'Saldo Awal',
                    '',
                    '',
                    '',
                    $raw_data->code,
                    $raw_data->desc,
                    '',
                    '',
                    number_format($saldo_awal,0,'.','.'),
                    number_format(0,0,'.','.'),
                );

                foreach ($raw_data->data_trans as $raw_data2) {
                    $debet = 0;
                    $credit = 0;
                    if ($raw_data2->cash_bank_type == 'BKK') {
                        $credit = $raw_data2->cash_bank_total;
                    }else if ($raw_data2->cash_bank_type == 'BKM') {
                        $debet = $raw_data2->cash_bank_total;
                    }

                    //$total_debet = $total_debet + $debet;
                    $total_credit = $total_credit + $credit;
                    $i++;

                    $sheetArray[] = array(
                        $i,
                        date('d-m-Y',strtotime($raw_data2->cash_bank_date)),
                        $raw_data2->cash_bank_code,
                        '',
                        $raw_data2->code_person,
                        $raw_data2->name_person,
                        $raw_data2->code,
                        $raw_data2->desc,
                        '',
                        '',
                        number_format($debet,0,'.','.'),
                        number_format($credit,0,'.','.'),
                    );

                    foreach ($raw_data2->detail as $detail) {
                        $detail_debet = 0;
                        $detail_credit = 0;
                        if ($raw_data2->cash_bank_type == 'BKK') {
                            $detail_debet = $detail->total;
                        }else if ($raw_data2->cash_bank_type == 'BKM') {
                            $detail_credit = $detail->total;
                        }
                        //$total_debet = $total_debet + $detail_debet;
                        $total_credit = $total_credit + $detail_credit;

                        $sheetArray[] = array(
                            '',
                            '',
                            '',
                            '',
                            '',
                            '',
                            '',
                            '',
                            $detail->code,
                            $detail->desc,
                            number_format($detail_debet,0,'.','.'),
                            number_format($detail_credit,0,'.','.'),
                        );
                    }
                }

                $sheetArray[] = array('','','', '','','', '','','','Jumlah',number_format($total_debet,0,'.','.'),number_format($total_credit,0,'.','.'));
                $sheetArray[] = array('','','', '','','', '','','','Saldo Awal',number_format($saldo_awal,0,'.','.'),'');

                $saldo_akhir = $saldo_awal - $total_credit;
                $grand_total_debet = $total_debet + $saldo_awal;
                $grand_total_credit = $total_credit + $saldo_akhir;

                $sheetArray[] = array('','','', '','','', '','','','Saldo Akhir','',number_format($saldo_akhir,0,'.','.'));
                $sheetArray[] = array('','','', '','','', '','','','Total',number_format($grand_total_debet,0,'.','.'),number_format($grand_total_credit,0,'.','.'));

                $sheetArray[] = array(); // Add an empty row
            }

            Excel::create('Laporan-kas-detail-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function laporanBank(Request $request)
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

            $sheetArray   = array();
            $sheetArray[] = array('Laporan Bank Harian (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Akun : '.$akun);

            $sheetArray[] = array(); // Add an empty row

            $sheetArray[] = array('Tanggal', 'No Bukti', 'Keterangan', 'Kode COA', 'Debet','Credit');

            $i = 1;
            $TDebet = 0;
            $TCredit = 0;
            $TSaldoAwal = $saldoawal;
            $TSaldoAkhir = 0;

            foreach($data as $raw_data){
                if ($raw_data->cash_bank_type == 'BBK') {
                    $debet = 0;
                    $credit = $raw_data->cash_bank_total;
                }else{
                    $debet = $raw_data->cash_bank_total;
                    $credit = 0;
                }

                $TDebet = $TDebet + $debet;
                $TCredit = $TCredit + $credit;

                $sheetArray[] = array(
                    date('d-m-Y',strtotime($raw_data->cash_bank_date)),
                    $raw_data->cash_bank_code,
                    $raw_data->cash_bank_keterangan,
                    $raw_data->code,
                    number_format($debet,0,'.','.'),
                    number_format($credit,0,'.','.'),
                );
            }

            $sheetArray[] = array('', '', '', 'Jumlah', number_format($TDebet,0,'.','.'),number_format($TCredit,0,'.','.'));
            $sheetArray[] = array('', '', '', 'Saldo Awal', number_format($TSaldoAwal,0,'.','.'),'');

            $TSaldoAkhir = $TSaldoAwal + $TDebet - $TCredit;
            $TTotalDebet = $TSaldoAwal + $TDebet;
            $TTotalCredit = $TSaldoAkhir + $TCredit;

            $sheetArray[] = array('', '', '', 'Saldo Akhir', '',number_format($TSaldoAkhir,0,'.','.'));
            $sheetArray[] = array('', '', '', 'Total', number_format($TTotalDebet,0,'.','.'),number_format($TTotalCredit,0,'.','.'));

            Excel::create('Laporan-Bank-summary-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');

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

            $sheetArray   = array();
            $sheetArray[] = array('Laporan Bank Harian (Detail)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Akun : '.$akun);

            $sheetArray[] = array(); // Add an empty row

            foreach ($data as $raw_data) {
                $i = 1;
                $total_debet = 0;
                $total_credit = 0;
                $saldo_awal = $raw_data->saldo_awal;

                $sheetArray[] = array('Account : '.$raw_data->code.' '.$raw_data->desc);
                $sheetArray[] = array('No','Kas / Bank','','','Supplier / Customer', '', 'Perkiraan','','','','Nilai Transaksi');
                $sheetArray[] = array('','','', '','','', 'Transaksi','','Lawan Transaksi','','','');
                $sheetArray[] = array('','Tanggal','No Bukti','', 'Kode', 'Nama','Kode','Keterangan','Kode','Keterangan','Debet','Credit');

                $sheetArray[] = array(
                    $i,
                    date('d-m-Y',strtotime($tglmulai)),
                    'Saldo Awal',
                    '',
                    '',
                    '',
                    $raw_data->code,
                    $raw_data->desc,
                    '',
                    '',
                    number_format($saldo_awal,0,'.','.'),
                    number_format(0,0,'.','.'),
                );

                foreach ($raw_data->data_trans as $raw_data2) {
                    $debet = 0;
                    $credit = 0;
                    if ($raw_data2->cash_bank_type == 'BBK') {
                        $credit = $raw_data2->cash_bank_total;
                    }else if ($raw_data2->cash_bank_type == 'BBM') {
                        $debet = $raw_data2->cash_bank_total;
                    }

                    //$total_debet = $total_debet + $debet;
                    $total_credit = $total_credit + $credit;
                    $i++;

                    $sheetArray[] = array(
                        $i,
                        date('d-m-Y',strtotime($raw_data2->cash_bank_date)),
                        $raw_data2->cash_bank_code,
                        '',
                        $raw_data2->code_person,
                        $raw_data2->name_person,
                        $raw_data2->code,
                        $raw_data2->desc,
                        '',
                        '',
                        number_format($debet,0,'.','.'),
                        number_format($credit,0,'.','.'),
                    );

                    foreach ($raw_data2->detail as $detail) {
                        $detail_debet = 0;
                        $detail_credit = 0;
                        if ($raw_data2->cash_bank_type == 'BBK') {
                            $detail_debet = $detail->total;
                        }else if ($raw_data2->cash_bank_type == 'BBM') {
                            $detail_credit = $detail->total;
                        }
                        //$total_debet = $total_debet + $detail_debet;
                        $total_credit = $total_credit + $detail_credit;

                        $sheetArray[] = array(
                            '',
                            '',
                            '',
                            '',
                            '',
                            '',
                            '',
                            '',
                            $detail->code,
                            $detail->desc,
                            number_format($detail_debet,0,'.','.'),
                            number_format($detail_credit,0,'.','.'),
                        );
                    }
                }

                $sheetArray[] = array('','','', '','','', '','','','Jumlah',number_format($total_debet,0,'.','.'),number_format($total_credit,0,'.','.'));
                $sheetArray[] = array('','','', '','','', '','','','Saldo Awal',number_format($saldo_awal,0,'.','.'),'');

                $saldo_akhir = $saldo_awal - $total_credit;
                $grand_total_debet = $total_debet + $saldo_awal;
                $grand_total_credit = $total_credit + $saldo_akhir;

                $sheetArray[] = array('','','', '','','', '','','','Saldo Akhir','',number_format($saldo_akhir,0,'.','.'));
                $sheetArray[] = array('','','', '','','', '','','','Total',number_format($grand_total_debet,0,'.','.'),number_format($grand_total_credit,0,'.','.'));

                $sheetArray[] = array(); // Add an empty row
            }

            Excel::create('Laporan-Bank-detail-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function laporanGeneralJournal(Request $request)
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

        $sheetArray   = array();
        $sheetArray[] = array('Laporan General Journal');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);

        $sheetArray[] = array(); // Add an empty row

        foreach($data  as $raw_data){
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
            $sheetArray[] = array('COA','Description','No Ref', 'Debet', 'Credit');

            foreach ($raw_data->data_gj as $raw_data2) {
                foreach ($raw_data2->detail as $raw_data3) {
                    $debet = 0;
                    $credit = 0;
                    if ($raw_data3->debet_credit == 'debet') {
                        $debet = $raw_data3->total;
                    }else{
                        $credit = $raw_data3->total;
                    }

                    $sheetArray[] = array(
                        $raw_data3->code,
                        $raw_data3->desc,
                        $raw_data3->ref,
                        //date('d-m-Y',strtotime($raw_data2->created_at)),
                        number_format($debet,0,'.','.'),
                        number_format($credit,0,'.','.'),
                    );
                }
                $sheetArray[] = array();
            }
            $sheetArray[] = array();
        }

        Excel::create('Laporan-General-Journal-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function laporanGeneralLedger(Request $request)
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

            $sheetArray   = array();
            $sheetArray[] = array('Laporan General Ledger (Summary)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Akun : '.$akun);

            $sheetArray[] = array(); // Add an empty row
            $sheetArray[] = array('COA','Description','Saldo Awal', 'Debet', 'Credit','Saldo Akhir');

            foreach($list_coa  as $raw_data){
                $saldo_awal = $raw_data->saldo_awal;
                $saldo_akhir = $saldo_awal + $raw_data->debet - $raw_data->credit;

                if ($saldo_akhir < 0) {
                    $show_saldo_akhir = '('.number_format(($saldo_akhir * -1),0,'.','.').')';
                }else{
                    $show_saldo_akhir = number_format($saldo_akhir,0,'.','.');
                }

                $sheetArray[] = array(
                    $raw_data->code,
                    $raw_data->desc,
                    //date('d-m-Y',strtotime($raw_data2->created_at)),
                    number_format($saldo_awal,0,'.','.'),
                    number_format($raw_data->debet,0,'.','.'),
                    number_format($raw_data->credit,0,'.','.'),
                    $show_saldo_akhir,
                );
            }

            Excel::create('Laporan-General-Ledger-Summary-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
        else{
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
                        if (stripos($raw_data2->code, $raw_data->code) == 0) {
                            $jumlah++;
                        }
                    }
                }
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

            $sheetArray   = array();
            $sheetArray[] = array('Laporan General Ledger (Detail)');
            $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
            $sheetArray[] = array('Akun : '.$akun);

            $sheetArray[] = array(); // Add an empty row

            foreach($list_coa  as $raw_data){
                $saldo_awal = $raw_data->saldo_awal;

                $sheetArray[] = array('Account : '.$raw_data->code.' '.$raw_data->desc);
                $sheetArray[] = array('Tanggal','No Ref','Keterangan', 'Debet', 'Credit', 'Saldo');
                $sheetArray[] = array('','SALDO AWAL','', number_format($saldo_awal,0,'.','.'), number_format(0,0,'.','.'), number_format($saldo_awal,0,'.','.'));

                $total = 0 + $saldo_awal;

                foreach ($raw_data->detail as $raw_data2) {
                    $debet = 0;
                    $credit = 0;
                    if ($raw_data2->debet_credit == 'debet') {
                        $debet = $raw_data2->total;
                    }else{
                        $credit = $raw_data2->total;
                    }

                    $total = $total + $debet - $credit;
                    if ($total < 0) {
                        $show_total = '('.number_format(($total * -1),0,'.','.').')';
                    }else{
                        $show_total = number_format($total,0,'.','.');
                    }
                    $sheetArray[] = array(
                        date('d-m-Y',strtotime($raw_data2->general_ledger_date)),
                        $raw_data2->ref,
                        $raw_data2->keterangan,
                        number_format($debet,0,'.','.'),
                        number_format($credit,0,'.','.'),
                        number_format($total,0,'.','.'),
                    );


                }
                $sheetArray[] = array();
            }

            Excel::create('Laporan-General-Ledger-Summary-'.date('dmyhis'), function($excel) use ($sheetArray){
                $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
                {
                    $sheet->fromArray($sheetArray);
                });
            })->export('xlsx');
        }
    }

    public function laporanTrialBalance(Request $request)
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

        $sheetArray   = array();
        $sheetArray[] = array('Laporan Trial Balance');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);

        $sheetArray[] = array(); // Add an empty row
        $sheetArray[] = array('COA','Description','Saldo Awal', 'Debet', 'Credit','Balance');

        $total_debet = 0;
        $total_credit = 0;

        foreach($list_coa  as $raw_data){
            $saldo_awal = $raw_data->saldo_awal;
            $balance = $saldo_awal + $raw_data->debet - $raw_data->credit;

            if ($balance < 0) {
                $show_balance = '('.number_format(($balance * -1),0,'.','.').')';
            }else{
                $show_balance = number_format($balance,0,'.','.');
            }

            $total_debet = $total_debet + $raw_data->debet;
            $total_credit = $total_credit + $raw_data->credit;

            $sheetArray[] = array(
                $raw_data->code,
                $raw_data->desc,
                //date('d-m-Y',strtotime($raw_data2->created_at)),
                number_format($saldo_awal,0,'.','.'),
                number_format($raw_data->debet,0,'.','.'),
                number_format($raw_data->credit,0,'.','.'),
                $show_balance,
            );
        }
        $sheetArray[] = array('','','', number_format($total_debet,0,'.','.'), number_format($total_credit,0,'.','.'));

        Excel::create('Laporan-Trial-Balance-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function laporanHpp(Request $request)
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

        $sheetArray   = array();
        $sheetArray[] = array('Laporan Harga Pokok Penjualan');
        $sheetArray[] = array('Periode : '.$request->periode);
        $sheetArray[] = array('Barang : '.$barang);

        $sheetArray[] = array(); // Add an empty row
        $sheetArray[] = array('Kode','Barang','Hpp Lama', 'Hpp Baru', 'Stok Lama','Qty Masuk');

        foreach($data  as $raw_data){
            $sheetArray[] = array(
                $raw_data->code,
                $raw_data->name,
                number_format($raw_data->old_hpp,0,'.','.'),
                number_format($raw_data->new_hpp,0,'.','.'),
                $raw_data->old_stok,
                $raw_data->qty_masuk,
            );
        }

        Excel::create('Laporan-HPP-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }
}
