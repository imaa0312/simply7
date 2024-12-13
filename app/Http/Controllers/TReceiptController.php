<?php

namespace App\Http\Controllers;

use DB;
use Response;
use App\Models\MInterfaceModel;
use Illuminate\Http\Request;

class TReceiptController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('t_cash_bank')
            ->where('cash_bank_group','RECEIPT')
            ->orderBy('id','desc')
            ->get();
        foreach ($data as $raw_data) {
            $coa = DB::table('m_coa')
                ->where('id',$raw_data->id_coa)
                ->first();

            $raw_data->name_coa = $coa->desc;
        }

        return view('admin.accounting.receipt.index',compact('data'));
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

        $interface = MInterfaceModel::where('var','VAR_RECEIPT')->first();
        $dataCoaReceipt = $this->getCoaByInterFace($interface);

        return view('admin.accounting.receipt.create',compact('dataCoaReceipt'));
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

        try{
            DB::table('t_cash_bank')
                ->insert([
                    'cash_bank_code' => $cash_bank_code,
                    'seq_code' => $data_code['sequence'],
                    'cash_bank_date' => date('Y-m-d', strtotime($request->expense_date)),
                    'cash_bank_type' => $request->pembayaran,
                    'cash_bank_group' => 'RECEIPT',
                    'id_coa' => $request->cash_akun_bank,
                    'cash_bank_total' => $request->total,
                    'cash_bank_keterangan' => $request->description,
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date("Y-m-d"),
                ]);

                DB::table('d_cb_expense_receipt')->insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/accounting/receipt-list');
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
    public function edit($receipt_code)
    {
        $dataReceipt = DB::table('t_cash_bank')
            ->join('m_coa', 'm_coa.id', '=', 't_cash_bank.id_coa')
            ->join('m_user', 'm_user.id', '=', 't_cash_bank.user_confirm')
            ->select('t_cash_bank.*','m_coa.desc as desc_coa','m_coa.code as code_coa','m_user.name as user_confirm')
            ->where('cash_bank_code',$receipt_code)
            ->first();

        $dataDetailReceipt = DB::table('d_cb_expense_receipt')
            ->join('m_coa', 'm_coa.id', '=', 'd_cb_expense_receipt.id_coa')
            ->select('d_cb_expense_receipt.*','m_coa.desc as desc_coa','m_coa.code as code_coa')
            ->where('d_cb_expense_receipt.cash_bank_code',$receipt_code)
            ->get();

        $Interface = MInterfaceModel::where('var','VAR_RECEIPT')->first();

        $dataCoaReceipt = $this->getCoaByInterFace($Interface);

        if($dataReceipt->cash_bank_type == 'BKM'){
            $var = 'VAR_CASH';
        }elseif($dataReceipt->cash_bank_type == 'BBM'){
            $var = 'VAR_BANK';
        }
        $data_interface = MInterfaceModel::where('var',$var)->first();

        $coa = $this->getCoaByInterFace($data_interface);
        // dd($coa);

        return view('admin.accounting.receipt.update',compact('dataReceipt','dataDetailReceipt','dataCoaReceipt','coa'));


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
        // dd($request->all());
        // die();

        try{
            DB::table('t_cash_bank')->where('id',$id)
                ->update([
                    'cash_bank_date' => date('Y-m-d', strtotime($request->expense_date)),
                    'cash_bank_type' => $request->pembayaran,
                    'id_coa' => $request->cash_akun_bank,
                    'cash_bank_total' => $request->total,
                    'cash_bank_keterangan' => $request->description,
                ]);


                DB::table('d_cb_expense_receipt')->where('cash_bank_code',$request->cb_code)->delete();

                DB::table('d_cb_expense_receipt')->insert($array);

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/accounting/receipt-list');

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

        $data_receipt = DB::table('t_cash_bank')
            ->where('id',$id)
            ->first();

        $detail_receipt = DB::table('d_cb_expense_receipt')
            ->where('cash_bank_code',$data_receipt->cash_bank_code)
            ->get();

        $id_gl = DB::table('t_general_ledger')
            ->insertGetId([
                'general_ledger_date' => date('Y-m-d'),
                'general_ledger_periode' => date('Ym'),
                'general_ledger_keterangan' => 'RECEIPT No.'.$data_receipt->cash_bank_code,
                'general_ledger_status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        $urutan = 1;
        DB::table('d_general_ledger')
            ->insert([
                't_gl_id' => $id_gl,
                'sequence' => $urutan,
                'id_coa' => $data_receipt->id_coa,
                'debet_credit' => 'debet',
                'total' => $data_receipt->cash_bank_total,
                'ref' => $data_receipt->cash_bank_code,
                'type_transaksi' => 'RECEIPT',
                'status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        $urutan++;
        foreach ($detail_receipt as $raw_data) {
            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => $urutan,
                    'id_coa' => $raw_data->id_coa,
                    'debet_credit' => 'credit',
                    'total' => $raw_data->total,
                    'ref' => $data_receipt->cash_bank_code,
                    'type_transaksi' => 'RECEIPT',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            $urutan++;
        }

        return redirect('admin/accounting/receipt-list');
    }

    public function delete($code)
    {
        DB::table('t_cash_bank')->where('cash_bank_code',$code)->delete();
        DB::table('d_cb_expense_receipt')->where('cash_bank_code',$code)->delete();

        return redirect('admin/accounting/receipt-list');
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

        return $data;
    }

    protected function getCoaByInterFace($interface)
    {
        $codeCoa = explode(",", $interface->code_coa);
        if ($codeCoa[0]==''){
            $codeCoa = [];
            $data = [];
        }else{
            $compileAQuery = DB::table('m_coa')->select("id", "code", "desc")->where("code","LIKE", $codeCoa[0]."%");

            for ($i=0; $i <count($codeCoa) ; $i++) {
                if( $i>0 ){
                    $compileAQuery->union(DB::table('m_coa')->select("id", "code", "desc")->where("code","LIKE", $codeCoa[$i]."%"));
                }
            }

            $data = $compileAQuery->get();
            $data_pembanding = $compileAQuery->get();

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

    public function detail($receipt_code)
    {
        $dataReceipt = DB::table('t_cash_bank')
            ->join('m_coa', 'm_coa.id', '=', 't_cash_bank.id_coa')
            ->select('t_cash_bank.*','m_coa.desc as desc_coa','m_coa.code as code_coa')
            ->where('cash_bank_code',$receipt_code)
            ->first();

        $dataDetailReceipt = DB::table('d_cb_expense_receipt')
            ->join('m_coa', 'm_coa.id', '=', 'd_cb_expense_receipt.id_coa')
            ->select('d_cb_expense_receipt.*','m_coa.desc as desc_coa','m_coa.code as code_coa')
            ->where('d_cb_expense_receipt.cash_bank_code',$receipt_code)
            ->get();

        //dd($dataDetailReceipt);

        return view('admin.accounting.receipt.detail', compact('dataReceipt','dataDetailReceipt'));
    }
}
