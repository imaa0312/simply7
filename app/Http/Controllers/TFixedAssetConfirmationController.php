<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TPurchaseOrderModel;
use App\Models\TSuratJalanMasukModel;
use App\Models\DSuratJalanMasukModel;
use App\Models\MStokProdukModel;
use App\Models\MReasonModel;
use Yajra\Datatables\Datatables;

class TFixedAssetConfirmationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $dataAsset = DB::table('t_asset_conf')
          ->get();

      return view('admin.fixed-asset.confirmation.index', compact('dataAsset'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
     {
       $dataPo = DB::table('t_fixed_asset_pd')
           ->join('d_fixed_asset_pd','d_fixed_asset_pd.sj_masuk_code','=','t_fixed_asset_pd.sj_masuk_code')
           ->select('t_fixed_asset_pd.sj_masuk_code','t_fixed_asset_pd.id')
           ->orderBy('t_fixed_asset_pd.sj_masuk_code','DESC')
           ->groupBy('t_fixed_asset_pd.sj_masuk_code','t_fixed_asset_pd.id')
           ->get();

       $dataSupplier = DB::table('t_fixed_asset_pd')
           ->join('d_fixed_asset_pd','d_fixed_asset_pd.sj_masuk_code','=','t_fixed_asset_pd.sj_masuk_code')
           ->leftjoin('m_supplier','m_supplier.id','=','t_fixed_asset_pd.supplier')
           ->select('t_fixed_asset_pd.supplier','m_supplier.name')
           // ->whereRaw('d_fixed_asset_pd.qty != d_fixed_asset_pd.last_po_qty')
           ->where('t_fixed_asset_pd.status','post')
           ->orderBy('t_fixed_asset_pd.supplier','DESC')
           ->groupBy('t_fixed_asset_pd.supplier','m_supplier.name')
           ->get();

       // dd($dataSupplier);
       //dd($dataPo);
       $gudang = DB::table('m_gudang')->get();
       $setSj = $this->setCode();

       $COAAsset = $this->getCoaInInterface('102');
       $COAAccumDep = $this->getCoaInInterface('103');
       $COADep = $this->getCoaInInterface('706');

       // dd($dataCoaExpense);

       //'dataPo',
       return view('admin.fixed-asset.confirmation.create', compact('dataPo','setSj','gudang','dataSupplier','COAAsset','COAAccumDep','COADep'));
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

        $setCode = $this->setCode();
        $rec_date = date('Y-m-d', strtotime($request->conf_date));

        $array = [];

        $i = 0;
        foreach($request->bulan as $rawBulan)
        {
            $array[$i]['bulan'] = $rawBulan;
            $i++;
        }

        $i = 0;
        foreach($request->asset_val_bulan as $rawAssetValBulan)
        {
            $array[$i]['asset_val_bulan'] = $rawAssetValBulan;
            $i++;
        }

        $i = 0;
        foreach($request->note as $rawNote)
        {
            $array[$i]['note'] = $rawNote;
            $i++;
        }

        DB::beginTransaction();
        try{
            DB::table('t_asset_conf')
                ->insert([
                    'asset_conf_code' => $setCode,
                    'rec_date' => date('Y-m-d', strtotime($request->conf_date)),
                    'barang'=> $request->id_barang,
                    'supplier'=> $request->supplier,
                    'asset_acc'=> $request->asset_acc,
                    'asset_value'=> $request->nilai_asset,
                    'accum_acc'=> $request->accum_acc,
                    'jml_bulan'=> $request->jml_bulan,
                    'dep_value'=> $request->nilai_depresiasi,
                    'dep_acc'=> $request->dep_acc,
                    'user_input'=> auth()->user()->id,
                    'pd_code'=> $request->kode_pd,
                    'qty'=> $request->qty,
                    'description'=> $request->description
                ]);

            for($x=0; $x<count($array); $x++){
                $date = '01 '.$array[$x]['bulan'];
                $month = date('m', strtotime($date));
                $year = date('Y', strtotime($date));
                //
                DB::table('d_asset_conf')
                    ->insert([
                        'asset_conf_code' => $setCode,
                        'month' => $month,
                        'year'=> $year,
                        'periode'=> $date,
                        'periode_value'=> $request->nilai_depresiasi,
                        'periode_acc'=> $request->dep_acc,
                        'rec_periode_depretiation'=> $array[$x]['note'],
                        'periode_acum_acc'=> $request->accum_acc,
                        'description'=> '',
                        'seq'=> ($x+1),
                    ]);
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('/admin/asset/confirmation');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function show($pocode)
     {
       $header = DB::table('t_asset_conf')
           ->join('m_supplier','t_asset_conf.supplier','m_supplier.id')
           ->join('m_user','t_asset_conf.user_input','m_user.id')
           ->join('m_produk','m_produk.id','=','t_asset_conf.barang')
           ->join('m_coa as a','a.id','=','t_asset_conf.asset_acc')
           ->join('m_coa as b','b.id','=','t_asset_conf.accum_acc')
           ->join('m_coa as c','c.id','=','t_asset_conf.dep_acc')
           ->select('t_asset_conf.*','m_supplier.name as supplier','m_user.name as user_input','a.desc as desc1','b.desc as desc2','c.desc as desc3','m_produk.name')
           ->where('t_asset_conf.asset_conf_code',$pocode)
           ->first();

       $barang = DB::table('d_asset_conf')
            ->where('asset_conf_code',$pocode)
           ->get();

        $COAAsset = $this->getCoaInInterface('102');
       // dd($header);
       return view('admin.fixed-asset.confirmation.detail',compact('header','barang','COAAsset'));
     }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function edit($code)
     {
        // dd($pdcode);
        $dataAsset = DB::table('t_asset_conf')
            ->leftjoin('m_produk','m_produk.id','=','t_asset_conf.barang')
            ->leftjoin('m_supplier','m_supplier.id','=','t_asset_conf.supplier')
            ->select('t_asset_conf.*','m_produk.name as barang','m_supplier.name')
            ->where('t_asset_conf.asset_conf_code',$code)
            ->first();

        $detail = DB::table('d_asset_conf')
            ->select('d_asset_conf.*')
            ->where('asset_conf_code',$code)
            ->get();
       // dd($dataAsset);

       $COAAsset = $this->getCoaInInterface('102');
       $COAAccumDep = $this->getCoaInInterface('103');
       $COADep = $this->getCoaInInterface('706');

       return view('admin.fixed-asset.confirmation.update', compact('gudang','dataAsset','COAAsset','COAAccumDep','COADep','detail'));
     }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function update(Request $request)
     {
         // dd($request->all());
         // $rec_date = date('Y-m-d', strtotime($request->conf_date));

         $array = [];

         $i = 0;
         foreach($request->bulan as $rawBulan)
         {
             $array[$i]['bulan'] = $rawBulan;
             $i++;
         }

         $i = 0;
         foreach($request->asset_val_bulan as $rawAssetValBulan)
         {
             $array[$i]['asset_val_bulan'] = $rawAssetValBulan;
             $i++;
         }

         $i = 0;
         foreach($request->note as $rawNote)
         {
             $array[$i]['note'] = $rawNote;
             $i++;
         }

         DB::beginTransaction();
         try{
             DB::table('t_asset_conf')
                 ->where('asset_conf_code',$request->asset_code)
                 ->update([
                     'asset_acc'=> $request->asset_acc,
                     'accum_acc'=> $request->accum_acc,
                     'jml_bulan'=> $request->jml_bulan,
                     'dep_value'=> $request->nilai_depresiasi,
                     'dep_acc'=> $request->dep_acc,
                     'user_input'=> auth()->user()->id,
                     'description'=> $request->description
                 ]);

            DB::table('d_asset_conf')->where('asset_conf_code', '=', $request->asset_code)->delete();

             for($x=0; $x<count($array); $x++){
                 $date = '01 '.$array[$x]['bulan'];
                 $month = date('m', strtotime($date));
                 $year = date('Y', strtotime($date));
                 //
                 DB::table('d_asset_conf')
                     ->insert([
                         'asset_conf_code' => $request->asset_code,
                         'month' => $month,
                         'year'=> $year,
                         'periode'=> $date,
                         'periode_value'=> $request->nilai_depresiasi,
                         'periode_acc'=> $request->dep_acc,
                         'rec_periode_depretiation'=> $array[$x]['note'],
                         'periode_acum_acc'=> $request->accum_acc,
                         'description'=> '',
                         'seq'=> ($x+1),
                     ]);
             }

             DB::commit();
         }catch(\Exception $e){
             DB::rollback();
             dd($e);
         }
         return redirect('/admin/asset/confirmation');
     }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($pocode)
     {
         $deletePo = DB::table('t_asset_conf')->where('asset_conf_code',$pocode)->delete();

         return view('admin.fixed-asset.confirmation.index',compact('deletePo'));
     }

    public function getPDBySupplier($supplier)
    {
        $dataPo = DB::table('t_fixed_asset_pd')
            ->select('t_fixed_asset_pd.sj_masuk_code','t_fixed_asset_pd.id')
            ->where('t_fixed_asset_pd.supplier','=',$supplier)
            ->get();


        return Response::json($dataPo);
    }

    public function getPDByKode($pdcode)
    {
        $databarang = DB::table('d_fixed_asset_pd')
            ->join('m_produk','m_produk.id','=','d_fixed_asset_pd.produk_id')
            ->select('m_produk.name','d_fixed_asset_pd.id','d_fixed_asset_pd.qty')
            ->where('d_fixed_asset_pd.sj_masuk_code',$pdcode)
            ->get();


        return Response::json($databarang);
    }

    public function getPDByQty($id)
    {
        $databarang = DB::table('d_fixed_asset_pd')
            ->join('d_fixed_asset_po','d_fixed_asset_po.id','=','d_fixed_asset_pd.dpo_id')
            ->join('m_produk','m_produk.id','=','d_fixed_asset_pd.produk_id')
            ->select('d_fixed_asset_pd.qty','d_fixed_asset_po.total_neto','m_produk.id')
            ->where('d_fixed_asset_pd.id',$id)
            ->get();

        return Response::json($databarang);
    }

    public function getStatus($id)
    {
        $status = DB::table('t_fixed_asset_pd')
            ->select('status')
            ->where('t_fixed_asset_pd.id',$id)
            ->get();

        return Response::json($status);
    }

    public function inApprove($asset)
    {
        //POSTING
        DB::table('t_asset_conf')
            ->where('asset_conf_code',$asset)
            ->update(['status' => 'post']);

        $header = DB::table('t_asset_conf')
            ->where('asset_conf_code',$asset)
            ->first();

        $detail = DB::table('d_asset_conf')
            ->where('asset_conf_code',$asset)
            ->orderBy('seq')
            ->get();

        $getCode = substr($header->asset_conf_code,2,8);
        $asset_code = 'AT'.$getCode;

        DB::beginTransaction();
        try{
            DB::table('t_asset')
                ->insert([
                    'asset_code' => $asset_code,
                    'asset_conf_code' => $header->asset_conf_code,
                    'asset_conf_id' => $header->id,
                    'rec_date' => date('Y-m-d'),
                    'barang'=> $header->barang,
                    'supplier'=> $header->supplier,
                    'qty'=> $header->qty,
                    'asset_acc'=> $header->asset_acc,
                    'asset_value'=> $header->asset_value,
                    'accum_acc'=> $header->accum_acc,
                    'jml_bulan'=> $header->jml_bulan,
                    'dep_value'=> $header->dep_value,
                    'dep_acc'=> $header->dep_acc,
                    'user_input'=> auth()->user()->id,
                    'description'=> $header->description
                ]);

            foreach ($detail as $key => $raw_data) {
                DB::table('d_asset')
                    ->insert([
                        'asset_code' => $asset_code,
                        'month' => $raw_data->month,
                        'year'=> $raw_data->year,
                        'periode'=> $raw_data->periode,
                        'periode_value'=> $raw_data->periode_value,
                        'periode_acc'=> $raw_data->periode_acc,
                        'rec_periode_depretiation'=> $raw_data->rec_periode_depretiation,
                        'periode_acum_acc'=> $raw_data->periode_acum_acc,
                        'description'=> $raw_data->description,
                        'seq'=> $raw_data->seq,
                    ]);
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/asset/confirmation');
    }

    public function close($asset)
    {
        DB::table('t_asset_conf')
            ->where('asset_conf_code',$asset)
            ->update(['status' => 'close']);

        return redirect('admin/asset/confirmation');
    }

    public function getAccodeByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataSupplier = DB::table('t_asset_conf')
        ->join('d_asset_conf', 't_asset_conf.id', '=', 'd_asset_conf.id')
        ->select('t_asset_conf.asset_conf_code')
        ->where('t_asset_conf.rec_date','>=',date('Y-m-d', strtotime($tglmulai)))
        ->where('t_asset_conf.rec_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
        ->groupBy('t_asset_conf.id')
        ->get();
        return Response::json($dataSupplier);
    }

    public function laporanConfirmation()
    {
      $dataSupplier = DB::table('m_supplier')
      ->join('t_asset_conf', 'm_supplier.id', '=', 't_asset_conf.supplier')
      ->select('m_supplier.id as supplier_id','m_supplier.name')
      ->groupBy('m_supplier.id')
      ->get();

      $dataBarang = DB::table('m_produk')
      ->select('id as barang_id','name')
      ->groupBy('id')
      ->get();
      // dd($dataSupplier);

      return view('admin.fixed-asset.confirmation.laporan',compact('dataSupplier','dataBarang'));
    }

    public function laporanAsset()
    {
      $dataSupplier = DB::table('m_supplier')
      ->join('t_asset_conf', 'm_supplier.id', '=', 't_asset_conf.supplier')
      ->select('m_supplier.id as supplier_id','m_supplier.name')
      ->groupBy('m_supplier.id')
      ->get();

      $dataBarang = DB::table('m_produk')
      ->select('id as barang_id','name')
      ->groupBy('id')
      ->get();
      // dd($dataSupplier);

      return view('admin.fixed-asset.confirmation.laporanasset',compact('dataSupplier','dataBarang'));
    }

    public function laporanPeriodPost()
    {
      $dataSupplier = DB::table('m_supplier')
      ->join('t_asset_conf', 'm_supplier.id', '=', 't_asset_conf.supplier')
      ->select('m_supplier.id as supplier_id','m_supplier.name')
      ->groupBy('m_supplier.id')
      ->get();

      $dataBarang = DB::table('m_produk')
      ->select('id as barang_id','name')
      ->groupBy('id')
      ->get();
      // dd($dataSupplier);

      return view('admin.fixed-asset.confirmation.laporanperiodpost',compact('dataSupplier','dataBarang'));
    }
    protected function getCoaInInterface($headerCOA)
    {
        $codeCoa = explode(",", $headerCOA);

        if ($codeCoa[0]=='') {
            $codeCoa = [];
            $data = [];
        }else{
            $query = [];
            for ($i=0; $i < count($codeCoa); $i++) {
                $query[$i] = DB::table('m_coa');
                $query[$i]->select('id','code','desc');
                $query[$i]->where('code', 'like', $codeCoa[$i].'%');
                if ($i>0) {
                    $query[$i]->union($query[$i-1]);
                }
            }
            $query[count($codeCoa)-1]->orderBy('id');

            $data = $query[count($codeCoa)-1]->get();

            //show only child
            $data_pembanding = $query[count($codeCoa)-1]->get();

            foreach ($data as $key => $raw_data) {
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
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }

    protected function setCode()
    {
        $getLastCode = DB::table('t_asset_conf')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'AC'.$dataDate.$nol.$getLastCode;
    }

    public function apiAsset()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);

        $asset = DB::table('t_asset_conf')
            ->join('m_produk','m_produk.id','t_asset_conf.barang')
            ->select('t_asset_conf.id as id','asset_conf_code','asset_type','asset_value','dep_value','status','m_produk.name as produk_name')
            ->orderBy('t_asset_conf.id', 'desc')
            ->get();

        $roleSuperAdmin = DB::table('m_role')
            ->where('name','Super Admin')
            ->first();

        $i=0;
        // dd($asset);
        return Datatables::of($asset)
        ->addColumn('action', function ($asset) use ($i){
            if(  $asset->status == 'in process'){
                if(auth()->user()->role == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                    '<td>'.
                    // '<a href="'. url('admin/asset/report-asset/'.$asset->asset_conf_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                    '<a href="'. url('admin/asset/asset-edit/'.$asset->asset_conf_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $asset->asset_conf_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/asset/asset-delete/'.$asset->asset_conf_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->asset_conf_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    '<a href="'. url('admin/asset/asset-posting/'.$asset->asset_conf_code) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->asset_conf_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    '</td>'.
                    '</tr>'.
                    '</table>';
                }else {
                    // if($asset->print == false){
                    //     return '<table id="tabel-in-opsi">'.
                    //     '<tr>'.
                    //     '<td>'.
                    //     // '<a href="  '. url('admin/asset/report-asset/'.$asset->asset_conf_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                    //     '<a href="'. url('admin/asset/asset-edit/'.$asset->asset_conf_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $asset->asset_conf_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    //     '<a href="'. url('admin/asset/asset-delete/'.$asset->asset_conf_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->asset_conf_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    //     '<a href="'. url('admin/asset/asset-posting/'.$asset->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->asset_conf_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                    //     '</td>'.
                    //     '</tr>'.
                    //     '</table>';
                    // }
                    // else{
                        return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                        '<td>'.
                        // '<a href="'. url('admin/asset/asset-edit/'.$asset->asset_conf_code.'/edit') .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $asset->asset_conf_code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/asset-delete/'.$asset->asset_conf_code.'/delete') .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $asset->asset_conf_code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/asset-posting/'.$asset->id) .'" class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Posting '. $asset->asset_conf_code .'"><span class="fa fa-truck"></span></a>'.'&nbsp;'.
                        '</td>'.
                        '</tr>'.
                        '</table>';
                    // }
                }
            }else{
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                '<td>'.
                // '<a href="'. url('admin/asset/pd-cancel/'.$suratJalan->sj_masuk_code) .'" class="btn btn-sm btn-danger" data-toggle="tooltip"  title="Cancel '. $suratJalan->sj_masuk_code  .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
                '<a href="'. url('admin/asset/report-asset-confirmation/'.$asset->asset_conf_code) .'" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Cetak"  id="print_'.$i++.'"><span class="fa fa-file-pdf-o"></span> </a>'.'&nbsp;'.
                '</td>'.
                '</tr>'.
                '</table>';
            }
        })
        ->editColumn('asset_conf_code', function($asset){
            return '<a href="'. url('admin/asset/asset-detail/'.$asset->asset_conf_code) .'">'. $asset->asset_conf_code .'</a> ';
        })
        // ->editColumn('type_asset', function($asset){
        //       return ucfirst($asset->asset_type);
        // })
        ->editColumn('asset_value', function($asset){
                return 'Rp. '.number_format($asset->asset_value,2,'.','.');
            })
        ->editColumn('status', function($asset){
            if( $asset->status == 'in process' ){
                return '<span class="label label-default">in process</span>';
            }
            elseif ($asset->status == 'post'){
                return '<span class="label label-success">post</span>';
            }
            elseif ($asset->status == 'close'){
                return '<span class="label label-danger">close</span>';
            }
        })
        ->addIndexColumn()
        ->rawColumns(['asset_conf_code','action','status','asset_value'])
        ->make(true);
    }

    public function period()
    {
        $month = date('m');
        $year = date('Y');

        // dd($header);
        return view('admin.fixed-asset.confirmation.period',compact('month','year'));
    }

    public function getPeriodPosting($month,$year)
    {
        $data = DB::table('d_asset')
            ->join('t_asset','t_asset.asset_code','=','d_asset.asset_code')
            ->join('m_produk','m_produk.id','=','t_asset.barang')
            ->select('t_asset.asset_code','t_asset.asset_type','m_produk.name as asset_name','d_asset.periode_value','d_asset.rec_periode_depretiation','d_asset.description')
            ->where('d_asset.month',$month)
            ->where('d_asset.year',$year)
            ->where('d_asset.status',null)
            ->get();

        // $data = $year;
        return Response::json($data);
    }

    public function periodPosting(Request $request)
    {
        // dd($request->all());

        $data = DB::table('d_asset')
            ->where('d_asset.month',$request->month)
            ->where('d_asset.year',$request->year)
            ->get();

        foreach ($data as $raw_data) {
            DB::table('d_asset')
                 ->where('id',$raw_data->id)
                 ->update([
                     'status'=> 'complete',
                 ]);
        }

        return redirect()->back()->with('message-success', 'Data Berhasil Diposting');
    }
}
