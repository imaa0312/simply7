<?php

namespace App\Http\Controllers;

use DB;
use PDF;
use Excel;
use Config;
use Illuminate\Http\Request;



class ReportProductionController extends Controller
{
    public function reportMRExcel(request $request)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->mr == '0') {
            $mr_code = 'All';
        }else{
            $mr_code = $request->mr;
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

            $query = DB::table('t_material_request');
            $query->select(DB::raw("DATE(t_material_request.mr_date) as tgl"));
            $query->join('d_material_request', 'd_material_request.mr_code', '=', 't_material_request.mr_code');
            $query->where('t_material_request.mr_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('t_material_request.mr_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->mr != '0') {
                $query->where('t_material_request.mr_code',$request->mr);
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

            $query->groupBy('tgl');
            $dataMr = $query->get();

            foreach ($dataMr as $raw_data) {
                $query = DB::table('t_material_request');
                $query->select('t_material_request.mr_code','status');
                $query->join('d_material_request', 'd_material_request.mr_code', '=', 't_material_request.mr_code');
                $query->where('t_material_request.mr_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_material_request.mr_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));

                if ($request->mr != '0') {
                    $query->where('t_material_request.mr_code',$request->mr);
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

                $query->groupBy('t_material_request.mr_code','status');

                $dataMrc = $query->get();
                $raw_data->data_mrcode = $dataMrc;

                foreach ($dataMrc as $raw_data2) {
                    $query = DB::table('d_material_request');
                    $query->select('d_material_request.produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty_request','last_wo_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_material_request.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
                    $query->where('d_material_request.mr_code',$raw_data2->mr_code);

                    $dataProduk = $query->get();
                    $raw_data2->detail_mr = $dataProduk;
                }
            }

        // dd($dataMr);

        $sheetArray = array();
        $sheetArray[] = array('Laporan Material Request');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
        $sheetArray[] = array('MR No. : '.$mr_code);
        $sheetArray[] = array('Status : '.$status);

        $sheetArray[] = array(); // Add an empty row

        foreach($dataMr  as $raw_data){
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
                foreach ($raw_data->data_mrcode as $raw_data3) {
                    $sheetArray[] = array('No. MR : '.$raw_data3->mr_code, 'Status : '.$raw_data3->status);
                    $sheetArray[] = array('Nama Barang', 'Kode Barang', 'Satuan', 'QTY MR');
                    foreach ($raw_data3->detail_mr as $raw_data4) {
                        $sheetArray[] = array(
                            $raw_data4->produk_name,
                            $raw_data4->produk_code,
                            $raw_data4->code_unit,
                            $raw_data4->qty_request,
                        );
                    }
                    $sheetArray[] = array();
                }
                $sheetArray[] = array();
            }

        Excel::create('Laporan-mr-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }

    public function reportMUExcel(request $request)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->mu == '0') {
            $mu_code = 'All';
        }else{
            $mu_code = $request->mu;
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

            $query = DB::table('t_material_usage');
            $query->select(DB::raw("DATE(t_material_usage.mu_date) as tgl"));
            $query->join('d_material_usage', 'd_material_usage.mu_code', '=', 't_material_usage.mu_code');
            $query->where('t_material_usage.mu_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('t_material_usage.mu_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->mu != '0') {
                $query->where('t_material_usage.mu_code',$request->mu);
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

            $query->groupBy('tgl');
            $dataMu = $query->get();

            foreach ($dataMu as $raw_data) {
                $query = DB::table('t_material_usage');
                $query->select('t_material_usage.mu_code','t_material_usage.status','t_material_usage.mr_code');
                $query->join('d_material_usage', 'd_material_usage.mu_code', '=', 't_material_usage.mu_code');
                $query->where('t_material_usage.mu_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_material_usage.mu_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));

                if ($request->mu != '0') {
                    $query->where('t_material_usage.mu_code',$request->mu);
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

                $query->groupBy('t_material_usage.mu_code','status','mr_code');

                $dataMuc = $query->get();
                $raw_data->data_mucode = $dataMuc;

                foreach ($dataMuc as $raw_data2) {
                    $query = DB::table('d_material_usage');
                    $query->select('d_material_usage.produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty_usage','last_mr_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_material_usage.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
                    $query->where('d_material_usage.mu_code',$raw_data2->mu_code);

                    $dataProduk = $query->get();
                    $raw_data2->detail_mu = $dataProduk;
                }
            }

        // dd($dataMu);

        $sheetArray = array();
        $sheetArray[] = array('Laporan Material Usage');
        $sheetArray[] = array('Periode : '.$tglmulai.' s.d. '.$tglsampai);
        $sheetArray[] = array('MU No. : '.$mu_code);
        $sheetArray[] = array('Status : '.$status);

        $sheetArray[] = array(); // Add an empty row

        foreach($dataMu  as $raw_data){
            $sheetArray[] = array('Tanggal : '.date('d-m-Y',strtotime($raw_data->tgl)));
                foreach ($raw_data->data_mucode as $raw_data3) {
                    $sheetArray[] = array('No. MU : '.$raw_data3->mu_code,'No. MR : '.$raw_data3->mr_code ,'Status : '.$raw_data3->status);
                    $sheetArray[] = array('Nama Barang', 'Kode Barang', 'Satuan', 'QTY MU');
                    foreach ($raw_data3->detail_mu as $raw_data4) {
                        $sheetArray[] = array(
                            $raw_data4->produk_name,
                            $raw_data4->produk_code,
                            $raw_data4->code_unit,
                            $raw_data4->qty_usage,
                        );
                    }
                    $sheetArray[] = array();
                }
                $sheetArray[] = array();
            }

        Excel::create('Laporan-mu-'.date('dmyhis'), function($excel) use ($sheetArray){
            $excel->sheet('Nama Sheet', function($sheet) use ($sheetArray)
            {
                $sheet->fromArray($sheetArray);
            });
        })->export('xlsx');
    }


    public function LaporanProductionWo(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->wo == '0') {
            $wo_code = 'All';
        }else{
            $wo_code = $request->wo;
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

        if ($request->type == 'summary') {
            $query = DB::table('t_work_order');
            $query->select('t_work_order.*');
            $query->where('wo_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('wo_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->wo != '0') {
                $query->where('wo_code',$request->wo);
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

            $query->orderBy('wo_code');
            $dataPO = $query->get();

            // dd($dataPO);

            $pdf = PDF::loadview('admin.report.laporan-wo-production-summary',['dataPO' => $dataPO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'wo_code' => $wo_code,'status' => $status]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-po-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }else{
            $query = DB::table('d_work_order');
            $query->select('t_work_order.wo_code','d_work_order.material_id as id_produk','d_work_order.material_qty',
                           't_work_order.wo_date','m_produk.name as produk_name',
                           'm_produk.code as produk_code','t_work_order.sdlc','t_work_order.soc');
            $query->join('t_work_order', 't_work_order.wo_code', '=', 'd_work_order.wo_code');
            // $query->join('m_routing', 'm_routing.id', '=', 't_work_order.id');
            $query->join('m_produk', 'm_produk.id', '=', 'd_work_order.material_id');
            $query->where('wo_date','>=',date('Y-F-d', strtotime($tglmulai)));
            $query->where('wo_date','<',date('Y-F-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->wo != '0') {
                $query->where('d_work_order.wo_code',$request->wo);
            }

            if ($request->status == 'in proccess') {
                $query->where('t_work_order.status','in process');
            }
            if ($request->status == 'post') {
                $query->where('t_work_order.status','post');
            }
            if ($request->status == 'cancel') {
                $query->where('t_work_order.status','cancel');
            }
            if ($request->status == 'close') {
                $query->where('t_work_order.status','close');
            }

            $query->orderBy('t_work_order.wo_date');
            $query->orderBy('d_work_order.wo_code');

            $dataPO = $query->get();

            // dd($dataPO);

            $pdf = PDF::loadview('admin.report.laporan-wo-production-detail',['dataPO' => $dataPO,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'wo_code' => $wo_code,'status' => $status]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-po-'.$supplier.'-'.date('dmyhis').'.pdf');
            }
        }
    }

    public function LaporanProductionMr(request $request, $type)
    {
        // $this->validate($request, [
        //     'supplier' => 'required',
        // ]);

        // dd($request->all());

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->mr == '0') {
            $mr_code = 'All';
        }else{
            $mr_code = $request->mr;
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

            $query = DB::table('t_material_request');
            $query->select(DB::raw("DATE(t_material_request.mr_date) as tgl"));
            $query->join('d_material_request', 'd_material_request.mr_code', '=', 't_material_request.mr_code');
            $query->where('t_material_request.mr_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('t_material_request.mr_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->mr != '0') {
                $query->where('t_material_request.mr_code',$request->mr);
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

            $query->groupBy('tgl');
            $dataMr = $query->get();

            foreach ($dataMr as $raw_data) {
                $query = DB::table('t_material_request');
                $query->select('t_material_request.mr_code','status');
                $query->join('d_material_request', 'd_material_request.mr_code', '=', 't_material_request.mr_code');
                $query->where('t_material_request.mr_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_material_request.mr_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));

                if ($request->mr != '0') {
                    $query->where('t_material_request.mr_code',$request->mr);
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

                $query->groupBy('t_material_request.mr_code','status');

                $dataMrc = $query->get();
                $raw_data->data_mrcode = $dataMrc;

                foreach ($dataMrc as $raw_data2) {
                    $query = DB::table('d_material_request');
                    $query->select('d_material_request.produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty_request','last_wo_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_material_request.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
                    $query->where('d_material_request.mr_code',$raw_data2->mr_code);

                    $dataProduk = $query->get();
                    $raw_data2->detail_mr = $dataProduk;
                }
            }

            // dd($dataMr);

            $pdf = PDF::loadview('admin.report.laporan-mr-production-summary',['dataMr' => $dataMr,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'mr_code' => $mr_code,'status' => $status]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-po-'.date('dmyhis').'.pdf');
            }
        }

    public function LaporanProductionMu(request $request, $type)
    {
        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        if ($request->mu == '0') {
            $mu_code = 'All';
        }else{
            $mu_code = $request->mu;
        }

        if ($request->status == null) {
            $status = 'All';
        }else{
            $status = $request->status;
        }

            $query = DB::table('t_material_usage');
            $query->select(DB::raw("DATE(t_material_usage.mu_date) as tgl"));
            $query->join('d_material_usage', 'd_material_usage.mu_code', '=', 't_material_usage.mu_code');
            $query->where('t_material_usage.mu_date','>=',date('Y-m-d', strtotime($tglmulai)));
            $query->where('t_material_usage.mu_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

            if ($request->mu != '0') {
                $query->where('t_material_usage.mu_code',$request->mu);
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

            $query->groupBy('tgl');
            $dataMu = $query->get();

            foreach ($dataMu as $raw_data) {
                $query = DB::table('t_material_usage');
                $query->select('t_material_usage.mu_code','status','mr_code');
                $query->join('d_material_usage', 'd_material_usage.mu_code', '=', 't_material_usage.mu_code');
                $query->where('t_material_usage.mu_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                $query->where('t_material_usage.mu_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));

                if ($request->mu != '0') {
                    $query->where('t_material_usage.mu_code',$request->mu);
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

                $query->groupBy('t_material_usage.mu_code','status','mr_code');

                $dataMuc = $query->get();
                $raw_data->data_mucode = $dataMuc;

                foreach ($dataMuc as $raw_data2) {
                    $query = DB::table('d_material_usage');
                    $query->select('d_material_usage.produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty_usage','last_mr_qty');
                    $query->join('m_produk', 'm_produk.id', '=', 'd_material_usage.produk_id');
                    $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
                    $query->where('d_material_usage.mu_code',$raw_data2->mu_code);

                    $dataProduk = $query->get();
                    $raw_data2->detail_mu = $dataProduk;
                }
            }

            // dd($dataMu);

            $pdf = PDF::loadview('admin.report.laporan-mu-production-summary',['dataMu' => $dataMu,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'mu_code' => $mu_code,'status' => $status]);

            $pdf->setPaper('legal', 'landscape');

            if( $type == 'view' ){
                return $pdf->stream();
            }else{
                return $pdf->download('laporan-po-'.date('dmyhis').'.pdf');
            }
        }

        public function MaterialRequest($mrCode)
        {
            $header = DB::table('t_material_request')
            ->join('m_gudang','m_gudang.id','t_material_request.gudang')
            ->join('m_user','t_material_request.user_input','m_user.id')
            ->select('*','m_user.name as user_input','m_gudang.name as gudang')
            ->where('t_material_request.mr_code',$mrCode)
            ->first();

            $detail = DB::table('d_material_request')
            ->join('m_produk','d_material_request.produk_id','m_produk.id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->select('m_produk.*','d_material_request.*','m_satuan_unit.code as code_unit')
            ->where('mr_code',$mrCode)
            ->get();

            //dd($header);

            $company = DB::table('m_company_profile')->first();

            $pdf = PDF::loadview('admin.report.material-request',['company' => $company,'header'=>$header,'detail'=>$detail]);
            $customPaper = array(0,0,21.84,13.97);
            $pdf->setPaper($customPaper);
            // $pdf->setPaper('A4', 'landscape');
            return $pdf->stream();
        }

        public function MaterialUsage($muCode)
        {
            $header = DB::table('t_material_usage')
            ->join('m_user','t_material_usage.user_input','m_user.id')
            ->select('*','m_user.name as user_input','t_material_usage.status')
            ->where('t_material_usage.mu_code',$muCode)
            ->first();

            // dd($header);

            $detail = DB::table('d_material_usage')
            ->join('m_produk','d_material_usage.produk_id','m_produk.id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->select('m_produk.*','d_material_usage.*','m_satuan_unit.code as code_unit')
            ->where('mu_code',$muCode)
            ->get();


            $company = DB::table('m_company_profile')->first();

            $pdf = PDF::loadview('admin.report.material-usage',['company' => $company,'header'=>$header,'detail'=>$detail]);
            $customPaper = array(0,0,21.84,13.97);
            $pdf->setPaper($customPaper);
            // $pdf->setPaper('A4', 'landscape');
            return $pdf->stream();
        }

        public function workorder($poCode)
        {
            $header = DB::table('t_work_order')
            ->join('m_user','t_work_order.user_input','m_user.id')
            ->select('*','m_user.name as user_input','t_work_order.status')
            ->where('t_work_order.wo_code',$poCode)
            ->first();
            // dd($header);

            $detail = DB::table('d_work_order')
            ->join('m_produk','d_work_order.material_id','m_produk.id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->select('m_produk.*','d_work_order.*','m_satuan_unit.code as code_unit',DB::raw('(material_qty + material_request_qty)as material_qty2 '))
            ->where('wo_code',$poCode)
            ->get();

            $company = DB::table('m_company_profile')->first();

            $pdf = PDF::loadview('admin.report.work-order',['company' => $company,'header'=>$header,'detail'=>$detail]);
            $customPaper = array(0,0,21.84,13.97);
            $pdf->setPaper($customPaper);
            // $pdf->setPaper('A4', 'landscape');
            return $pdf->stream();
        }

        public function LaporanProductionPr(request $request, $type)
        {
            $tglmulai = substr($request->periode,0,10);
            $tglsampai = substr($request->periode,13,10);

            if ($request->pr == '0') {
                $pr_code = 'All';
            }else{
                $pr_code = $request->pr;
            }

            if ($request->status == null) {
                $status = 'All';
            }else{
                $status = $request->status;
            }

                $query = DB::table('t_production_result');
                $query->select(DB::raw("DATE(t_production_result.pr_date) as tgl"));
                $query->join('d_production_result', 'd_production_result.pr_code', '=', 't_production_result.pr_code');
                $query->where('t_production_result.pr_date','>=',date('Y-m-d', strtotime($tglmulai)));
                $query->where('t_production_result.pr_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')));

                if ($request->pr != '0') {
                    $query->where('t_production_result.pr_code',$request->pr);
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

                $query->groupBy('tgl');
                $dataPr = $query->get();

                foreach ($dataPr as $raw_data) {
                    $query = DB::table('t_production_result');
                    $query->select('t_production_result.pr_code','status','wo_code');
                    $query->join('d_production_result', 'd_production_result.pr_code', '=', 't_production_result.pr_code');
                    $query->where('t_production_result.pr_date','>=',date('Y-m-d', strtotime($raw_data->tgl)));
                    $query->where('t_production_result.pr_date','<',date('Y-m-d', strtotime($raw_data->tgl. ' + 1 days')));

                    if ($request->pr != '0') {
                        $query->where('t_production_result.pr_code',$request->pr);
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

                    $query->groupBy('t_production_result.pr_code','status','wo_code');
                    $query->orderBy('wo_code','ASC');

                    $dataPrc = $query->get();
                    $raw_data->data_prcode = $dataPrc;

                    foreach ($dataPrc as $raw_data2) {
                        $query = DB::table('d_production_result');
                        $query->select('d_production_result.produk_id','m_produk.code as produk_code','m_produk.name as produk_name','m_satuan_unit.code as code_unit','qty_result');
                        $query->join('m_produk', 'm_produk.id', '=', 'd_production_result.produk_id');
                        $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
                        $query->where('d_production_result.pr_code',$raw_data2->pr_code);

                        $dataProduk = $query->get();
                        $raw_data2->detail_pr = $dataProduk;
                    }
                }

                // dd($dataPr);

                $pdf = PDF::loadview('admin.report.laporan-production-result',['dataPr' => $dataPr,'tglmulai' => $tglmulai,'tglsampai' => $tglsampai,'pr_code' => $pr_code,'status' => $status]);

                $pdf->setPaper('legal', 'landscape');

                if( $type == 'view' ){
                    return $pdf->stream();
                }else{
                    return $pdf->download('laporan-po-'.date('dmyhis').'.pdf');
                }
            }

    }
