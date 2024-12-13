<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class TMutationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('t_cash_bank')
            ->where('cash_bank_group','MUTATION')
            ->orderBy('id','desc')
            ->get();

        foreach ($data as $raw_data) {
            $coa = DB::table('m_coa')
                ->where('id',$raw_data->id_coa)
                ->first();

            $raw_data->name_coa = $coa->desc;
        }

        return view('admin.accounting.mutation.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $interfaceCash = DB::table('m_interface')
            ->where('var','VAR_CASH')
            ->first();

        $interfaceBank = DB::table('m_interface')
            ->where('var','VAR_BANK')
            ->first();

        $codeCoaCash = explode(",", $interfaceCash->code_coa);

        if ($codeCoaCash[0]=='') {
            $codeCoaCash = [];
            $dataCash = [];
        }else{
            $query = [];
            for ($i=0; $i < count($codeCoaCash); $i++) {
                $query[$i] = DB::table('m_coa');
                $query[$i]->select('id','code','desc');
                $query[$i]->where('code', 'like', $codeCoaCash[$i].'%');
                if ($i>0) {
                    $query[$i]->union($query[$i-1]);
                }
            }
            $query[count($codeCoaCash)-1]->orderBy('id');

            $dataCash = $query[count($codeCoaCash)-1]->get();

            //show only child
            $data_pembanding = $query[count($codeCoaCash)-1]->get();

            foreach ($dataCash as $key => $raw_data) {
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
                    unset($dataCash[$key]);
                }
                $raw_data->type = 'KAS';
            }
        }

        $codeCoaBank = explode(",", $interfaceBank->code_coa);

        if ($codeCoaBank[0]=='') {
            $codeCoaBank = [];
            $dataBank = [];
        }else{
            $query = [];
            for ($i=0; $i < count($codeCoaBank); $i++) {
                $query[$i] = DB::table('m_coa');
                $query[$i]->select('id','code','desc');
                $query[$i]->where('code', 'like', $codeCoaBank[$i].'%');
                if ($i>0) {
                    $query[$i]->union($query[$i-1]);
                }
            }
            $query[count($codeCoaBank)-1]->orderBy('id');

            $dataBank = $query[count($codeCoaBank)-1]->get();

            //show only child
            $data_pembanding = $query[count($codeCoaBank)-1]->get();

            foreach ($dataBank as $key => $raw_data) {
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
                    unset($dataBank[$key]);
                }
                $raw_data->type = 'BANK';
            }
        }

        $dataCoa = array_merge($dataCash->toArray(),$dataBank->toArray());

        //dd($dataCoa);

        return view('admin.accounting.mutation.create',compact('dataCoa'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());

        if ($request->type_akun == 'KAS') {
            $type = 'BKK';
        }else{
            $type = 'BBK';
        }

        //$cash_bank_code = $this->setCode($type);
        $data_code = $this->setCode($type);
        $cash_bank_code = $data_code['code'];

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

        $i = 0;
        foreach($request->type as $raw4){
            $array[$i]['type_akun'] = $raw4;
            $i++;
        }
        
        try{
            DB::table('t_cash_bank')
                ->insert([
                    'cash_bank_code' => $cash_bank_code,
                    'seq_code' => $data_code['sequence'],
                    'cash_bank_date' => date('Y-m-d', strtotime($request->mutation_date)),
                    'cash_bank_type' => $type,
                    'cash_bank_group' => 'MUTATION',
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

        return redirect('admin/accounting/mutation-list');
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
    public function edit($mutation_code)
    {
        // dd('aa');
        $dataMutation = DB::table('t_cash_bank')
            ->join('m_coa', 'm_coa.id', '=', 't_cash_bank.id_coa')
            ->join('m_user', 'm_user.id', '=', 't_cash_bank.user_confirm')
            ->select('t_cash_bank.*','m_coa.desc as desc_coa','m_coa.code as code_coa','m_user.name as user_confirm')
            ->where('cash_bank_code',$mutation_code)
            ->first();

        $dataDetailMutation = DB::table('d_cb_expense_receipt')
            ->join('m_coa', 'm_coa.id', '=', 'd_cb_expense_receipt.id_coa')
            ->select('d_cb_expense_receipt.*','m_coa.desc as desc_coa','m_coa.code as code_coa','m_coa.id as coa_id')
            ->where('d_cb_expense_receipt.cash_bank_code',$mutation_code)
            ->get();

            $interfaceCash = DB::table('m_interface')
            ->where('var','VAR_CASH')
            ->first();

        // dd($dataMutation,$dataDetailMutation);

        $interfaceBank = DB::table('m_interface')
            ->where('var','VAR_BANK')
            ->first();

        $codeCoaCash = explode(",", $interfaceCash->code_coa);

        if ($codeCoaCash[0]=='') {
            $codeCoaCash = [];
            $dataCash = [];
        }else{
            $query = [];
            for ($i=0; $i < count($codeCoaCash); $i++) {
                $query[$i] = DB::table('m_coa');
                $query[$i]->select('id','code','desc');
                $query[$i]->where('code', 'like', $codeCoaCash[$i].'%');
                if ($i>0) {
                    $query[$i]->union($query[$i-1]);
                }
            }
            $query[count($codeCoaCash)-1]->orderBy('id');

            $dataCash = $query[count($codeCoaCash)-1]->get();

            //cek code coa paling bawah
            $length = 0;
            foreach($dataCash as $raw_data) {
                $lengthCode = strlen($raw_data->code);
                if ($lengthCode > $length) {
                    $length =$lengthCode;
                }
                //$raw_data->test = $lengthCode;
                $raw_data->type = 'KAS';
            }

            //remove coa parent
            foreach ($dataCash as $key => $raw_data) {
                $lengthCode = strlen($raw_data->code);
                if ($lengthCode < $length) {
                    unset($dataCash[$key]);
                }
            }
        }

        $codeCoaBank = explode(",", $interfaceBank->code_coa);

        if ($codeCoaBank[0]=='') {
            $codeCoaBank = [];
            $dataBank = [];
        }else{
            $query = [];
            for ($i=0; $i < count($codeCoaBank); $i++) {
                $query[$i] = DB::table('m_coa');
                $query[$i]->select('id','code','desc');
                $query[$i]->where('code', 'like', $codeCoaBank[$i].'%');
                if ($i>0) {
                    $query[$i]->union($query[$i-1]);
                }
            }
            $query[count($codeCoaBank)-1]->orderBy('id');

            $dataBank = $query[count($codeCoaBank)-1]->get();

            //cek code coa paling bawah
            $length = 0;
            foreach($dataBank as $raw_data) {
                $lengthCode = strlen($raw_data->code);
                if ($lengthCode > $length) {
                    $length =$lengthCode;
                }
                //$raw_data->test = $lengthCode;
                $raw_data->type = 'BANK';
            }

            //remove coa parent
            foreach ($dataBank as $key => $raw_data) {
                $lengthCode = strlen($raw_data->code);
                if ($lengthCode < $length) {
                    unset($dataBank[$key]);
                }
            }
        }

        $dataCoa = array_merge($dataCash->toArray(),$dataBank->toArray());

        // dd($dataDetailMutation);

        return view('admin.accounting.mutation.update',compact('dataCoa','dataDetailMutation','dataMutation'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $mutationCode)
    {
        if ($request->type_akun == 'KAS') {
            $type = 'BKK';
        }else{
            $type = 'BBK';
        }

        // $cash_bank_code = $this->setCode($type);

        // dd($request->all());

        $array = [];

        $i = 0;
        foreach($request->coa_id as $raw1){
            $array[$i]['cash_bank_code'] = $mutationCode;
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

        $i = 0;
        foreach($request->type as $raw4){
            $array[$i]['type_akun'] = $raw4;
            $i++;
        }
        
        try{
            DB::table('t_cash_bank')->where('cash_bank_code',$mutationCode)
                ->update([
                    'cash_bank_date' => date('Y-m-d', strtotime($request->mutation_date)),
                    'cash_bank_type' => $type,
                    'cash_bank_group' => 'MUTATION',
                    // 'id_coa' => $request->cash_akun_bank,
                    'cash_bank_total' => $request->total,
                    'cash_bank_keterangan' => $request->description,
                ]);

                DB::table('d_cb_expense_receipt')->where('cash_bank_code',$mutationCode)->delete();

                //insert-detail
                DB::table('d_cb_expense_receipt')->insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/accounting/mutation-list');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($mutationCode)
    {
        // dd($mutationCode);
        DB::table('d_cb_expense_receipt')->where('cash_bank_code',$mutationCode)->delete();        
        DB::table('t_cash_bank')->where('cash_bank_code',$mutationCode)->delete();

        return redirect()->back();
    }

    public function posting($id)
    {
        DB::table('t_cash_bank')
            ->where('id',$id)
            ->update(['cash_bank_status' => 'post']);

        $dataHeader = DB::table('t_cash_bank')
            ->where('id',$id)
            ->first();

        $dataDetail = DB::table('d_cb_expense_receipt')
            ->where('cash_bank_code',$dataHeader->cash_bank_code)
            ->get();

        //AUTO JOURNAL OUT
        $id_gl = DB::table('t_general_ledger')
            ->insertGetId([
                'general_ledger_date' => date('Y-m-d'),
                'general_ledger_periode' => date('Ym'),
                'general_ledger_keterangan' => 'MUTATION OUT No.'.$dataHeader->cash_bank_code,
                'general_ledger_status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        $id_coa = DB::table('m_coa')
            ->where('code','10111')
            ->first();

        DB::table('d_general_ledger')
            ->insert([
                't_gl_id' => $id_gl,
                'sequence' => 1,
                'id_coa' => $id_coa->id,
                'debet_credit' => 'debet',
                'total' => $dataHeader->cash_bank_total,
                'ref' => $dataHeader->cash_bank_code,
                'type_transaksi' => 'MUTATION OUT',
                'status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        DB::table('d_general_ledger')
            ->insert([
                't_gl_id' => $id_gl,
                'sequence' => 2,
                'id_coa' => $dataHeader->id_coa,
                'debet_credit' => 'credit',
                'total' => $dataHeader->cash_bank_total,
                'ref' => $dataHeader->cash_bank_code,
                'type_transaksi' => 'MUTATION OUT',
                'status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        foreach ($dataDetail as $raw_data) {
            if ($raw_data->type_akun == 'KAS') {
                $type = 'BKM';
            }else{
                $type = 'BBM';
            }

            //$cash_bank_code = $this->setCode($type);
            $data_code = $this->setCode($type);
            $cash_bank_code = $data_code['code'];

            DB::table('d_cb_expense_receipt')
                ->where('id',$raw_data->id)
                ->update(['ref' => $cash_bank_code]);

            DB::table('t_cash_bank')
                ->insert([
                    'cash_bank_code' => $cash_bank_code,
                    'seq_code' => $data_code['sequence'],
                    'cash_bank_date' => date("Y-m-d"),
                    'cash_bank_type' => 'BKM',
                    'cash_bank_group' => 'MUTATIONTO',
                    'id_coa' => $raw_data->id_coa,
                    'cash_bank_total' => $raw_data->total,
                    'cash_bank_keterangan' => $raw_data->keterangan,
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date("Y-m-d"),
                    'cash_bank_status' => 'post',
                ]);

            //insert-detail
            DB::table('d_cb_expense_receipt')
                ->insert([
                    'cash_bank_code' => $cash_bank_code,
                    'id_coa' => $raw_data->id_coa,
                    'total' => $raw_data->total,
                    //'ref' => $cash_bank_code,
                    'keterangan' => $raw_data->keterangan
                ]);

            //AUTO JOURNAL IN
            $id_gl = DB::table('t_general_ledger')
                ->insertGetId([
                    'general_ledger_date' => date('Y-m-d'),
                    'general_ledger_periode' => date('Ym'),
                    'general_ledger_keterangan' => 'MUTATION IN No.'.$cash_bank_code,
                    'general_ledger_status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 1,
                    'id_coa' => $raw_data->id_coa,
                    'debet_credit' => 'debet',
                    'total' => $raw_data->total,
                    'ref' => $cash_bank_code,
                    'type_transaksi' => 'MUTATION IN',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $id_coa = DB::table('m_coa')
                ->where('code','10111')
                ->first();

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 2,
                    'id_coa' => $id_coa->id,
                    'debet_credit' => 'credit',
                    'total' => $raw_data->total,
                    'ref' => $cash_bank_code,
                    'type_transaksi' => 'MUTATION IN',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);
        }

        return redirect('admin/accounting/mutation-list');
    }

    public function detail($mutation_code)
    {
        $dataMutation = DB::table('t_cash_bank')
            ->join('m_coa', 'm_coa.id', '=', 't_cash_bank.id_coa')
            ->select('t_cash_bank.*','m_coa.desc as desc_coa','m_coa.code as code_coa')
            ->where('cash_bank_code',$mutation_code)
            ->first();

        $dataDetailMutation = DB::table('d_cb_expense_receipt')
            ->join('m_coa', 'm_coa.id', '=', 'd_cb_expense_receipt.id_coa')
            ->select('d_cb_expense_receipt.*','m_coa.desc as desc_coa','m_coa.code as code_coa')
            ->where('d_cb_expense_receipt.cash_bank_code',$mutation_code)
            ->get();

        //dd($dataDetailExpense);

        return view('admin.accounting.mutation.detail', compact('dataMutation','dataDetailMutation'));
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
}
