<?php

namespace App\Http\Controllers;

use DB;
use Response;
use App\Models\MInterfaceModel;
use Illuminate\Http\Request;

class TExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('t_cash_bank')
            ->where('cash_bank_group','EXPENSE')
            ->orderBy('id','desc')
            ->get();
        foreach ($data as $raw_data) {
            $coa = DB::table('m_coa')
                ->where('id',$raw_data->id_coa)
                ->first();

            $raw_data->name_coa = $coa->desc;
        }

        return view('admin.accounting.expense.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $validasi = $this->validasiInterface();
        if($validasi['cek'] == true){
            return redirect('admin/accounting/expense-list')->with('message',''.$validasi['nameInterface'].' Kosong');
        }

        $Interface = MInterfaceModel::where('var','VAR_EXPENSE')->first();

        $dataCoaExpense = $this->getCoaInInterface($Interface);
        // dd($dataCoaExpense);

        return view('admin.accounting.expense.create',compact('dataCoaExpense'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data_code = $this->setCode($request->pembayaran);
        $cash_bank_code = $data_code['code'];

        //dd($cash_bank_code);

        $array = [];

        $i = 0;
        foreach($request->coa_id as $raw1){
            $array[$i]['cash_bank_code'] = $cash_bank_code;
            $array[$i]['id_coa'] = $raw1;
            $i++;
        }

        $i = 0;
        foreach($request->subTotal as $raw2){
            $array[$i]['total'] = $raw2;
            $i++;
        }

        $i = 0;
        foreach($request->catatan as $raw3){
            $array[$i]['keterangan'] = $raw3;
            $i++;
        }

        DB::beginTransaction();
        try{
            DB::table('t_cash_bank')
                ->insert([
                    'cash_bank_code' => $cash_bank_code,
                    'seq_code' => $data_code['sequence'],
                    'cash_bank_date' => date('Y-m-d', strtotime($request->expense_date)),
                    'cash_bank_type' => $request->pembayaran,
                    'cash_bank_group' => 'EXPENSE',
                    'id_coa' => $request->cash_akun_bank,
                    'cash_bank_total' => $request->total,
                    'cash_bank_keterangan' => $request->description,
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date("Y-m-d"),
                ]);

                //insert-detail
                DB::table('d_cb_expense_receipt')->insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/accounting/expense-list');
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
    public function edit($expense_code)
    {
        $Interface = MInterfaceModel::where('var','VAR_EXPENSE')->first();

        $dataCoaExpense = $this->getCoaInInterface($Interface);

         $dataExpense = DB::table('t_cash_bank')
            ->join('m_coa', 'm_coa.id', '=', 't_cash_bank.id_coa')
            ->join('m_user', 'm_user.id', '=', 't_cash_bank.user_confirm')
            ->select('t_cash_bank.*','m_coa.desc as desc_coa','m_coa.code as code_coa','m_user.name as user_confirm')
            ->where('cash_bank_code',$expense_code)
            ->first();

        $dataDetailExpense = DB::table('d_cb_expense_receipt')
            ->join('m_coa', 'm_coa.id', '=', 'd_cb_expense_receipt.id_coa')
            ->select('d_cb_expense_receipt.*','m_coa.desc as desc_coa','m_coa.code as code_coa')
            ->where('d_cb_expense_receipt.cash_bank_code',$expense_code)
            ->get();

        if($dataExpense->cash_bank_type == 'BKK'){
            $var = 'VAR_CASH';
        }elseif($dataExpense->cash_bank_type == 'BBK'){
            $var = 'VAR_BANK';
        }
        $data_interface = MInterfaceModel::where('var',$var)->first();

        $coa = $this->getCoaInInterface($data_interface);
        // dd($dataExpense,$dataDetailExpense);
        return view('admin.accounting.expense.update',compact('dataExpense','dataDetailExpense','dataCoaExpense','coa'));
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
        $array = [];

        $i = 0;
        foreach($request->coa_id as $raw1){
            $array[$i]['cash_bank_code'] = $request->cb_code;
            $array[$i]['id_coa'] = $raw1;
            $i++;
        }

        $i = 0;
        foreach($request->subTotal as $raw2){
            $array[$i]['total'] = $raw2;
            $i++;
        }

        $i = 0;
        foreach($request->catatan as $raw3){
            $array[$i]['keterangan'] = $raw3;
            $i++;
        }
        // echo "<pre>";
        //     print_r($array);
        //     dd($request->all());
        // die();
        DB::beginTransaction();
        try{
            DB::table('t_cash_bank')->where('cash_bank_code',$request->cb_code)
                ->update([
                    'cash_bank_type' => $request->pembayaran,
                    'id_coa' => $request->cash_akun_bank,
                    'cash_bank_total' => $request->total,
                    'cash_bank_keterangan' => $request->description,
                ]);

                DB::table('d_cb_expense_receipt')->where('cash_bank_code',$request->cb_code)->delete();

                //insert-detail
                DB::table('d_cb_expense_receipt')->insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/accounting/expense-list');
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

    public function posting($id)
    {
        DB::table('t_cash_bank')
            ->where('id',$id)
            ->update(['cash_bank_status' => 'post']);

        $data_expense = DB::table('t_cash_bank')
            ->where('id',$id)
            ->first();

        $detail_expense = DB::table('d_cb_expense_receipt')
            ->where('cash_bank_code',$data_expense->cash_bank_code)
            ->get();

        $id_gl = DB::table('t_general_ledger')
            ->insertGetId([
                'general_ledger_date' => date('Y-m-d'),
                'general_ledger_periode' => date('Ym'),
                'general_ledger_keterangan' => 'EXPENSE No.'.$data_expense->cash_bank_code,
                'general_ledger_status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        $urutan = 1;
        foreach ($detail_expense as $raw_data) {
            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => $urutan,
                    'id_coa' => $raw_data->id_coa,
                    'debet_credit' => 'debet',
                    'total' => $raw_data->total,
                    'ref' => $data_expense->cash_bank_code,
                    'type_transaksi' => 'EXPENSE',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $urutan++;
        }

        DB::table('d_general_ledger')
            ->insert([
                't_gl_id' => $id_gl,
                'sequence' => $urutan,
                'id_coa' => $data_expense->id_coa,
                'debet_credit' => 'credit',
                'total' => $data_expense->cash_bank_total,
                'ref' => $data_expense->cash_bank_code,
                'type_transaksi' => 'EXPENSE',
                'status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        return redirect('admin/accounting/expense-list');
    }

    public function delete($code)
    {
        DB::table('t_cash_bank')->where('cash_bank_code',$code)->delete();
        DB::table('d_cb_expense_receipt')->where('cash_bank_code',$code)->delete();

        return redirect('admin/accounting/expense-list');
    }

    public function tipePembayaran($var)
    {
        $data_interface = MInterfaceModel::where('var',$var)->first();

        $result = $this->getCoaInInterface($data_interface);

        return Response::json($result);
    }

    protected function getCoaInInterface($interface)
    {
        $codeCoa = explode(",", $interface->code_coa);

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

        //dd($data);
        return $data;
    }

    protected function setCode($type)
    {
        $getLastCode = DB::table('t_cash_bank')
            ->select('seq_code')
            ->where('cash_bank_type',$type)
            ->orderBy('id', 'desc')
            ->pluck('seq_code')
            ->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        $result = ['code' => $type.$dataDate.$nol.$getLastCode, 'sequence' => $getLastCode];

        return $result;
    }

    protected function validasiInterface()
    {
        $allInterface = MInterfaceModel::all();

        $cek = false;
        foreach ($allInterface as $key => $value) {
            $codeCoa = explode(',', $value->code_coa);

            if( $value->code_coa =='' || $value->code_coa == null ){
                $cek = true;
            }else{
                if( count($codeCoa) > 0 ){
                    unset($allInterface[$key]);
                }else{
                    $cek = true;
                }
            }
        }

        $name = null;
        foreach ($allInterface as $result){
            $name .= 'Master Interface '.$result->var.",";
        }

        $nameInterface = rtrim($name,",");

        $result= [
            'cek' => $cek,
            'nameInterface' => $nameInterface,
        ];
        // if($cek == true ){
        //     return redirect('admin/accounting/expense-list')->with('message',''.$nameInterface.' Kosong');
        // }

        return $result;
    }

    public function detail($expense_code)
    {
        $dataExpense = DB::table('t_cash_bank')
            ->join('m_coa', 'm_coa.id', '=', 't_cash_bank.id_coa')
            ->select('t_cash_bank.*','m_coa.desc as desc_coa','m_coa.code as code_coa')
            ->where('cash_bank_code',$expense_code)
            ->first();

        $dataDetailExpense = DB::table('d_cb_expense_receipt')
            ->join('m_coa', 'm_coa.id', '=', 'd_cb_expense_receipt.id_coa')
            ->select('d_cb_expense_receipt.*','m_coa.desc as desc_coa','m_coa.code as code_coa')
            ->where('d_cb_expense_receipt.cash_bank_code',$expense_code)
            ->get();

        //dd($dataDetailExpense);

        return view('admin.accounting.expense.detail', compact('dataExpense','dataDetailExpense'));
    }
}
