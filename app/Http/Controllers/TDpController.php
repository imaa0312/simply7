<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MCustomerModel;
use DB;
use Response;
use App\Models\MInterfaceModel;

class TDpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataDp = DB::table('t_down_payment')
                ->join('m_customer','m_customer.id','=','t_down_payment.customer')
                ->select('t_down_payment.*','m_customer.name as customer','m_customer.id as customer_id')
                ->get();
        return view('admin.transaksi.downpayment.index',compact('dataDp'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $getCustomer = MCustomerModel::orderBy('id','DESC')->where('status',true)->get();
        $codeDP = $this->setCodeDP();
        return view('admin.transaksi.downpayment.create',compact('getCustomer','codeDP'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'customer' => 'required',
            'dp_total' => 'required',
        ]);

        //dd($request->all());

        DB::beginTransaction();
        try {
            $code = $this->setCodeDP();
            $dp_total = str_replace(array('.', ','), '' , $request->dp_total);
            //header
            DB::table('t_down_payment')->insert([
                'dp_code' =>  $code,
                'customer' => $request->customer,
                'dp_total' => $dp_total,
                'user_input' => auth()->user()->id,
                'type' => $request->type,
                'akun_coa' => $request->cash_akun_bank,
                'description' => $request->description,
            ]);

            //detail
            DB::table('d_down_payment')->insert([
                'dp_code' =>  $code,
                'transaksi' => $code,
                'in' => $dp_total,
                'saldo_akhir' => $dp_total,
            ]);

            // dd($code);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi/dp');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $dataDp = DB::table('t_down_payment')
                ->join('m_customer','m_customer.id','=','t_down_payment.customer')
                ->join('d_down_payment','d_down_payment.dp_code','=','t_down_payment.dp_code')
                ->select('t_down_payment.*','m_customer.name as customer','m_customer.id as customer_id','d_down_payment.*','d_down_payment.created_at as tgl_detail_dp')
                ->where('t_down_payment.id',$id)
                ->get();
        // dd($dataDp);
        return view('admin.transaksi.downpayment.detail',compact('dataDp'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $getCustomer = MCustomerModel::orderBy('id','DESC')->where('status',true)->get();
        $dataDp = DB::table('t_down_payment')
                ->join('m_customer','m_customer.id','=','t_down_payment.customer')
                ->select('t_down_payment.*','m_customer.name as customer','m_customer.id as customer_id')
                ->where('t_down_payment.id',$id)
                ->first();

        $var = '';
        if ($dataDp->type == 'tunai') {
            $var = 'VAR_CASH';
        }else{
            $var = 'VAR_BANK';
        }

        $data_interface = MInterfaceModel::where('var',$var)->first();

        $codeCoa = explode(",", $data_interface->code_coa);

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

        $dataCoa = $data;

        //dd($var);
        return view('admin.transaksi.downpayment.update',compact('getCustomer','dataDp','dataCoa'));
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
        $this->validate($request,[
            'customer' => 'required',
            'dp_total' => 'required',
        ]);

        //dd($request->all());

        DB::beginTransaction();
        try {
            $dp_total = str_replace(array('.', ','), '' , $request->dp_total);

            //header
            DB::table('t_down_payment')->where('id',$id)->update([
                'customer' => $request->customer,
                'dp_total' => $dp_total,
                'user_input' => auth()->user()->id,
                'type' => $request->type,
                'akun_coa' => $request->cash_akun_bank,
                'description' => $request->description,
            ]);

            //detail
            DB::table('d_down_payment')->where('dp_code',$request->dp_code)->update([
                'in' => $dp_total,
                'saldo_akhir' => $dp_total,
            ]);


            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi/dp');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            //find-header
            $dpHeader = DB::table('t_down_payment')->where('id',$id)->first();

            //header-delete
            DB::table('t_down_payment')->where('id',$id)->delete();

            //detail-delete
            DB::table('d_down_payment')->where('dp_code',$dpHeader->dp_code)->delete();


            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi/dp');
    }


    public function posting($id)
    {
        $data_dp = DB::table('t_down_payment')->where('id',$id)->first();

        DB::beginTransaction();
        try {
            //header-delete
            DB::table('t_down_payment')->where('id',$id)->update([
                'status' => 'post'
            ]);

            //AUTO JURNAL DP IN
            $id_gl = DB::table('t_general_ledger')
                ->insertGetId([
                    'general_ledger_date' => date('Y-m-d'),
                    'general_ledger_periode' => date('Ym'),
                    'general_ledger_keterangan' => 'DP SI IN No.'.$data_dp->dp_code,
                    'general_ledger_status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','1010102')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 1,
                    'id_coa' => $data_dp->akun_coa,
                    'debet_credit' => 'debet',
                    'total' => $data_dp->dp_total,
                    'ref' => $data_dp->dp_code,
                    'type_transaksi' => 'DPSI',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','2010201')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 2,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'credit',
                    'total' => $data_dp->dp_total,
                    'ref' => $data_dp->dp_code,
                    'type_transaksi' => 'DPSI',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi/dp');
    }

    protected function setCodeDP()
    {
        $dataDate = date("ym");
        $getLastCode = DB::table('t_down_payment')->select('id')->orderBy('id', 'desc')->pluck('id')->first();
        $getLastCode = $getLastCode +1;
        $nol = null;
        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";
        }else{$nol = null;}

        return 'DPS'.$dataDate.$nol.$getLastCode;
    }

    public function laporanDp()
    {
        $dataCustomer = DB::table('m_customer')->select('id','name')->get();
        return view('admin.transaksi.downpayment.laporan',compact('dataCustomer'));
    }

    public function getCustomerByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataCustomer = DB::table('m_customer')
            ->join('t_down_payment', 'm_customer.id', '=', 't_down_payment.customer')
            ->select('m_customer.id as customer_id','name','main_address')
            ->where('t_down_payment.dp_date','>=',date('Y-m-d', strtotime($tglmulai)))
            ->where('t_down_payment.dp_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('m_customer.id')
            ->get();

        return Response::json($dataCustomer);
    }

    public function getDpByCustomer($customerId)
    {
        $dataDp = DB::table('t_down_payment')->select('id','dp_code','type')->where('customer',$customerId)->get();

        return Response::json($dataDp);

    }
}
