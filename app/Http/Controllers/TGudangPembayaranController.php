<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\MCustomerModel;

class TGudangPembayaranController extends Controller
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

    public function pembayaran()
    {
        $dataCustomer = DB::table('t_faktur')
            ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
            ->select('t_faktur.customer','m_customer.name as customer','m_customer.id as id_customer')
            ->groupBy('t_faktur.customer','m_customer.name','m_customer.id')
            ->where('status_payment', '=', 'unpaid')
            ->get();

        foreach ($dataCustomer as $key => $raw_data) {
            $data =  DB::table('t_faktur')
                ->join('m_customer', 'm_customer.id', '=', 't_faktur.customer')
                ->select('t_faktur.*','m_customer.name as customer','m_customer.id as id_customer')
                ->where('m_customer.id',$raw_data->id_customer)
                ->where('status_payment', '=', 'unpaid')
                ->orderBy('t_faktur.faktur_code', 'DESC')
                ->get();

            foreach ($data as $key2 => $raw_data2) {
                $waiting = DB::table('d_pembayaran')
                    ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                    ->where('d_pembayaran.faktur_code',$raw_data2->faktur_code)
                    ->where('t_pembayaran.status','in approval')
                    ->sum('d_pembayaran.total');

                $jumlah_dibayar = $raw_data2->jumlah_yg_dibayarkan + $waiting;

                if ($jumlah_dibayar >= $raw_data2->total) {
                    unset($data[$key2]);
                }
            }

            if (count($data) < 1) {
                unset($dataCustomer[$key]);
            }
        }

        $dataBank = DB::table('m_bank')->get();

        $dataRekening = DB::table('m_rekening_tujuan')
            ->select('m_rekening_tujuan.id as id_rek','m_rekening_tujuan.no_rekening','m_rekening_tujuan.atas_nama','m_bank.name as bank_name')
            ->join('m_bank', 'm_bank.id', '=', 'm_rekening_tujuan.bank')
            ->get();

        $dataMetode = DB::table('m_metode_pembayaran')->get();

        return view('admin.gudang-aktivitas.pembayaran.create',compact('dataCustomer','dataBank','dataMetode','dataRekening'));
    }

    public function createPembayaran(request $request)
    {
        $this->validate($request, [
            'customer' => 'required',
            'jumlah_yang_dibayar' => 'required',
            'type' => 'required',
        ]);

        $status = "";

        $faktur = json_decode($request->array_faktur);

        if ($request->type == 2) {
            $this->validate($request, [
                'bank' => 'required',
                'rekening' => 'required',
            ]);
            $bank = $request->bank;
            $no_giro = null;
            $jatuh_tempo_giro = null;
            $rekening_tujuan = $request->rekening;
            $dp_code = null;
            //$status = "approved";
        }elseif ($request->type == 3){
             $this->validate($request, [
                'bank' => 'required',
                'no_giro' => 'required',
                'jatuh_tempo_giro' => 'required',
            ]);
            $bank = $request->bank;
            $no_giro = $request->no_giro;
            $jatuh_tempo_giro = date('Y-m-d', strtotime($request->jatuh_tempo_giro));
            $rekening_tujuan = null;
            $dp_code = null;
            //$status = "in approval";
        }elseif ($request->type == 4){
             $this->validate($request, [
                'dp' => 'required',
            ]);
            $bank = null;
            $no_giro = null;
            $jatuh_tempo_giro = null;
            $rekening_tujuan = null;
            $dp_code = $request->dp;
            //$status = "in approval";
        }else{
            $bank = null;
            $no_giro = null;
            $jatuh_tempo_giro = null;
            $rekening_tujuan = null;
            $dp_code = null;
            //$status = "approved";
        }

        //dd($request->all());

        $dataDate =date("ym");

        $getLastCode = DB::table('t_pembayaran')
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

        $pembayaran_code = 'NFWA'.$dataDate.$nol.$getLastCode;

        $sales = DB::table('m_customer')
            ->join('m_wilayah_pembagian_sales', 'm_wilayah_pembagian_sales.wilayah_sales', '=', 'm_customer.wilayah_sales')
            ->where('m_customer.id',$request->customer)
            ->pluck('m_wilayah_pembagian_sales.sales')
            ->first();

        //insert header
        $data =  DB::table('t_pembayaran')
            ->insert([
                'pembayaran_code' => $pembayaran_code,
                //'faktur_code' => $request->faktur_code,
                'customer' => $request->customer,
                'sales' => $sales,
                //'total' => $request->jumlah_yang_dibayar,
                'type' => $request->type,
                'dp_code' => $dp_code,
                'keterangan' => $request->keterangan,
                'user_receive' => $request->user,
                'user_confirm' => $request->user,
                'bank' => $bank,
                'no_giro' => $no_giro,
                'rekening_tujuan' => $rekening_tujuan,
                'jatuh_tempo_giro' => $jatuh_tempo_giro,
                //'usia_pembayaran' => $usia_pembayaran,
                'confirm_date' => date("Y-m-d"),
            ]);

        //insert detail
        //$faktur = $request->faktur;
        asort($faktur);
        //reindex
        $faktur = array_values($faktur);

        $saldo = $request->jumlah_yang_dibayar;

        for ($i=0; $i < count($faktur); $i++) {
            //dd($faktur[$i]);

            if ($saldo > 0) {
                $dataFaktur = DB::table('t_faktur')
                    ->where('faktur_code', $faktur[$i])
                    ->first();

                $start = strtotime(date('Y-m-d', strtotime($dataFaktur->created_at)));
                $end = strtotime(date("Y-m-d"));
                $usia_pembayaran = ceil(abs($end - $start) / 86400);

                $waiting = DB::table('d_pembayaran')
                    ->join('t_pembayaran', 't_pembayaran.pembayaran_code', '=', 'd_pembayaran.pembayaran_code')
                    ->where('d_pembayaran.faktur_code',$faktur[$i])
                    ->where('t_pembayaran.status','in approval')
                    ->sum('d_pembayaran.total');

                $belumdibayar = 0;
                $belumdibayar = $dataFaktur->total - $dataFaktur->jumlah_yg_dibayarkan - $waiting;

                $potong = 0;
                if ($saldo > $belumdibayar) {
                    $potong = $belumdibayar;
                }else{
                    $potong = $saldo;
                }

                $saldo = $saldo - $potong;

                $data =  DB::table('d_pembayaran')
                    ->insert([
                        'pembayaran_code' => $pembayaran_code,
                        'faktur_code' => $faktur[$i],
                        'total' => $potong,
                        'usia_pembayaran' => $usia_pembayaran,
                    ]);
            }
        }

        return redirect('admin/gudang-pembayaran-list');
    }

    public function pembayaranList()
    {
        $dataPembayaran = DB::table('t_pembayaran')
            ->join('m_metode_pembayaran', 'm_metode_pembayaran.id', '=', 't_pembayaran.type')
            ->leftjoin('m_bank', 'm_bank.id', '=', 't_pembayaran.bank')
            ->join('m_customer', 'm_customer.id', '=', 't_pembayaran.customer')
            ->select('*','t_pembayaran.type as type_payment','m_bank.name as bank_name','t_pembayaran.status as status_pembayaran')
            //->where('t_pembayaran.status','in approval')
            ->orderBy('t_pembayaran.id','desc')
            ->get();

        foreach ($dataPembayaran as $raw_data) {
            $total = DB::table('d_pembayaran')
                ->where('pembayaran_code',$raw_data->pembayaran_code)
                ->sum('total');

            $raw_data->total = $total;
        }

        return view('admin.gudang-aktivitas.pembayaran.index', compact('dataPembayaran'));
    }

    public function dpList()
    {
        $dataDp = DB::table('t_down_payment')
                ->join('m_customer','m_customer.id','=','t_down_payment.customer')
                ->select('t_down_payment.*','m_customer.name as customer','m_customer.id as customer_id')
                ->get();
        return view('admin.gudang-aktivitas.dp.index',compact('dataDp'));
    }

    public function createDP()
    {
        $getCustomer = MCustomerModel::orderBy('id','DESC')->where('status',true)->get();
        $codeDP = $this->setCodeDP();
        return view('admin.gudang-aktivitas.dp.create',compact('getCustomer','codeDP'));
    }

    protected function setCodeDP()
    {
        $dataDate = date("ym");
        $getLastCode = DB::table('t_down_payment')->select('id')->orderBy('id', 'desc')->pluck('id')->first();
        $getLastCode = $getLastCode +1;
        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";
        }else{$nol = null;}

        return 'DP'.$dataDate.$nol.$getLastCode;
    }

    public function storeDP(Request $request)
    {
        $this->validate($request,[
            'customer' => 'required',
            'dp_total' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $code = $this->setCodeDP();

            //header
            DB::table('t_down_payment')->insert([
                'dp_code' =>  $code,
                'customer' => $request->customer,
                'dp_total' => $request->dp_total,
                'user_input' => auth()->user()->id,
                'type' => $request->type,
                'description' => $request->description,
            ]);

            //detail
            DB::table('d_down_payment')->insert([
                'dp_code' =>  $code,
                'transaksi' => $code,
                'in' => $request->dp_total,
                'saldo_akhir' => $request->dp_total,
            ]);

            // dd($code);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('admin/gudang-dp-list');
    }
}
