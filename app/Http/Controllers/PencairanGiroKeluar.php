<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;

class PencairanGiroKeluar extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataPencairan = DB::table('t_pencairan')
                ->select('*')
                ->where('type','keluar')
                ->orderBy('id','desc')
                ->get();
        return view('admin.accounting.pencairan-giro-keluar.index',compact('dataPencairan'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $noGiro = DB::table('t_pi_pembayaran')
                ->join('m_supplier','m_supplier.id','t_pi_pembayaran.supplier')
                ->select('t_pi_pembayaran.*','m_supplier.name as supplier')
                ->where('t_pi_pembayaran.payment_date','<=',date('Y-m-d'))
                ->where('t_pi_pembayaran.status_giro', false)
                ->whereNotNull('t_pi_pembayaran.no_giro')
                // ->groupBy('')
                ->get();

        // dd($noGiro);

        return view('admin.accounting.pencairan-giro-keluar.create',compact('noGiro'));
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
        $this->validate($request, [
            'use_giro' => 'required',
            'pencairan_date' => 'required',
        ]);

        $giro = json_decode($request->use_giro);

        $dataDate =date("ym");
        $getLastCode = DB::table('t_pencairan')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        $getLastCode = $getLastCode + 1;

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

        $pencairan_code = 'BGK'.$dataDate.$nol.$getLastCode;

        //insert detail
        asort($giro);
        //reindex
        $giro = array_values($giro);

        $grand_total = 0;
        //insert detail
        for ($i=0; $i < count($giro); $i++) {
            $dataFaktur = DB::table('t_pi_pembayaran')
                ->where('pembayaran_code', $giro[$i])
                ->first();

            $totalFaktur = DB::table('d_pi_pembayaran')
                ->where('pembayaran_code', $giro[$i])
                ->sum('total');

            $grand_total = $grand_total + $totalFaktur;

            $data =  DB::table('d_pencairan')
                ->insert([
                    'pencairan_code' => $pencairan_code,
                    'no_giro' => $dataFaktur->no_giro,
                    'pembayaran_code' => $giro[$i],
                    'bank' => $dataFaktur->bank,
                    'id_coa' => $dataFaktur->rekening_tujuan,
                    'total' => $totalFaktur,
                    'keterangan' => $request->catatan[$giro[$i]],
                ]);

            DB::table('t_pi_pembayaran')
                ->where('pembayaran_code', '=', $giro[$i])
                ->update([
                    'status_giro' => true,
                ]);
        }

        //insert header
        $data =  DB::table('t_pencairan')
            ->insert([
                'pencairan_code' => $pencairan_code,
                'pencairan_date' => date('Y-m-d', strtotime($request->pencairan_date)),
                'total' => $grand_total,
                'type' => 'keluar',
                'user_receive' => $request->user,
                'user_input' => $request->user,
                'user_confirm' => $request->user,
                'confirm_date' => date("Y-m-d"),
        ]);

        return redirect('admin/pencairan/giro-keluar');
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

    public function posting($code)
    {
        // dd($code);
        $dataPencairan = DB::table('t_pencairan')
            ->where('pencairan_code',$code)
            ->first();

        $detailPencairan = DB::table('d_pencairan')
            ->where('pencairan_code',$code)
            ->get();

        DB::table('t_pencairan')
            ->where('pencairan_code',$code)
            ->update(['status' => 'post']);

        try{
            foreach ($detailPencairan as $raw_data) {
                //cash bank
                $type = 'BBK';
                $data_code = $this->setCode($type);
                $cash_bank_code = $data_code['code'];

                DB::table('t_cash_bank')
                    ->insert([
                        'cash_bank_code' => $cash_bank_code,
                        'seq_code' => $data_code['sequence'],
                        'cash_bank_date' => date('Y-m-d'),
                        'cash_bank_type' => $type,
                        'cash_bank_group' => 'PENCAIRANOUT',
                        'cash_bank_ref' => $code,
                        'id_coa' => $raw_data->id_coa,
                        'cash_bank_total' => $raw_data->total,
                        'cash_bank_status' => 'post',
                        'cash_bank_keterangan' => $raw_data->keterangan,
                        'user_confirm' => auth()->user()->id,
                        'confirm_date' => date("Y-m-d"),
                ]);

                //insert-detail
                $id_coa = DB::table('m_coa')
                    ->where('code','20103')
                    ->first();

                DB::table('d_cb_expense_receipt')
                    ->insert([
                        'cash_bank_code' => $cash_bank_code,
                        'id_coa' => $id_coa->id,
                        'total' => $raw_data->total,
                        'ref' => $raw_data->no_giro,
                        'keterangan' => '',
                ]);
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        //AUTO GIRO MASUK
        $id_gl = DB::table('t_general_ledger')
            ->insertGetId([
                'general_ledger_date' => date('Y-m-d'),
                'general_ledger_periode' => date('Ym'),
                'general_ledger_keterangan' => 'Pencairan Giro Keluar No.'.$code,
                'general_ledger_status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        $seq = 1;
        $id_coa = DB::table('m_coa')
            ->where('code','20103')
            ->first();

        DB::table('d_general_ledger')
            ->insert([
                't_gl_id' => $id_gl,
                'sequence' => $seq,
                'id_coa' => $id_coa->id,
                'debet_credit' => 'debet',
                'total' => $dataPencairan->total,
                'ref' => $code,
                'type_transaksi' => 'BGK',
                'status' => 'post',
                'user_confirm' => auth()->user()->id,
                'confirm_date' => date('Y-m-d'),
        ]);

        $seq++;
        
        foreach ($detailPencairan as $raw_data) {
            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => $seq,
                    'id_coa' => $raw_data->id_coa,
                    'debet_credit' => 'credit',
                    'total' => $raw_data->total,
                    'ref' => $code,
                    'type_transaksi' => 'BGK',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);
            $seq++;
        }

        return redirect('admin/pencairan/giro-keluar');
    }

    public function delete($code)
    {
        //dd($code);
        $dataPencairan = DB::table('d_pencairan')
            ->where('pencairan_code', '=', $code)
            ->get();

        foreach ($dataPencairan as $raw_data) {
            DB::table('t_pi_pembayaran')
                ->where('pembayaran_code', '=', $raw_data->pembayaran_code)
                ->update([
                    'status_giro' => false,
                ]);
        }

        DB::table('t_pencairan')->where('pencairan_code',$code)->delete();
        DB::table('d_pencairan')->where('pencairan_code',$code)->delete();

        return redirect('admin/pencairan/giro-keluar');
    }

    public function showNoGiro(Request $request)
    {
        $row = '';
        foreach ($request->giro as $key => $value) {

            $result = DB::table('t_pi_pembayaran')
                    ->join('m_supplier','m_supplier.id','t_pi_pembayaran.supplier')
                    ->join('m_user','m_user.id','t_pi_pembayaran.user_receive')
                    ->join('m_bank','m_bank.id','t_pi_pembayaran.bank')
                    ->join('m_coa','m_coa.id','t_pi_pembayaran.rekening_tujuan')
                    ->select('t_pi_pembayaran.*','m_supplier.name as supplier','m_bank.name as bank','m_user.name as user_receive','m_coa.desc')
                    ->where('t_pi_pembayaran.id',$value)
                    ->first();

            $total = DB::table('d_pi_pembayaran')
                    ->where('d_pi_pembayaran.pembayaran_code',$result->pembayaran_code)
                    ->sum('total');

            $row .= '<tr id="tr_'.$result->pembayaran_code.'">';
                $row .= '<td>'
                        .'<div style="text-align:center;" class="checkbox"><label><input type="checkbox" class="ck" value="'.$result->pembayaran_code.'" id="ck_'.$result->pembayaran_code.'"></label></div>'
                        .'</td>';

                $row .= '<td>'.$result->bank.'</td>';

                $row .= '<td>'.$result->desc.'</td>';

                $row .= '<td>'.$result->no_giro.'</td>';

                $row .= '<td>'.$result->supplier.'</td>';

                $row .= '<td>'.date('d-m-Y',strtotime($result->payment_date)).'</td>';

                $row .= '<td>'.date('d-m-Y',strtotime($result->tgl_ambil_giro)).'</td>';

                $row .= '<td>'.date('d-m-Y',strtotime($result->jatuh_tempo_giro)).'</td>';

                $row .= '<td>Rp. '.number_format($total,0,'.','.').'</td>';

                //$row .= '<td>'.$result->user_receive.'</td>';

                $row .= '<td><textarea class="form-control input-sm" name="catatan['.$result->pembayaran_code.']"></textarea> </td>';

            $row .= '</tr>';
        }
        return $row;
    }

    public function detail($code)
    {
        $dataPencairan = DB::table('t_pencairan')
            ->select('*')
            ->where('pencairan_code',$code)
            ->first();

        $detailPencairan = DB::table('d_pencairan')
            ->select('d_pencairan.*','m_coa.desc','t_pi_pembayaran.pembayaran_code','m_supplier.name','t_pi_pembayaran.tgl_ambil_giro','t_pi_pembayaran.jatuh_tempo_giro')
            ->join('m_coa','m_coa.id','d_pencairan.id_coa','m_supplier.name as customer_name')
            ->join('t_pi_pembayaran','t_pi_pembayaran.pembayaran_code','d_pencairan.pembayaran_code')
            ->join('m_supplier','m_supplier.id','t_pi_pembayaran.supplier')
            ->where('pencairan_code',$code)
            ->get();

        //dd($dataDetailExpense);

        return view('admin.accounting.pencairan-giro-keluar.detail', compact('dataPencairan','detailPencairan'));
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

    public function laporan()
    {
        $dataCB = DB::table('d_cb_expense_receipt')
            ->select('t_cash_bank.id as cash_bank_id','t_cash_bank.cash_bank_code')
            ->where('t_cash_bank.cash_bank_group', 'PENCAIRANOUT')
            ->join('t_cash_bank','t_cash_bank.cash_bank_code','=','d_cb_expense_receipt.cash_bank_code')
            ->join('m_coa','m_coa.id','=','t_cash_bank.id_coa')
            ->get();

        $dataAkun = DB::table('d_cb_expense_receipt')
            ->select('t_cash_bank.id_coa','m_coa.code','m_coa.desc')
            ->where('t_cash_bank.cash_bank_group', 'PENCAIRANOUT')
            ->join('t_cash_bank','t_cash_bank.cash_bank_code','=','d_cb_expense_receipt.cash_bank_code')
            ->join('m_coa','m_coa.id','=','t_cash_bank.id_coa')
            ->groupBy('t_cash_bank.id_coa','m_coa.code','m_coa.desc')
            ->get();

        return view('admin.accounting.pencairan-giro-keluar.laporan', compact('dataCB','dataAkun'));
    }

    public function getCashBank($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataCB = DB::table('d_cb_expense_receipt')
            ->select('t_cash_bank.id as cash_bank_id','t_cash_bank.cash_bank_code')
            ->where('t_cash_bank.cash_bank_group', 'PENCAIRANOUT')
            ->where('t_cash_bank.cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_cash_bank.cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->join('t_cash_bank','t_cash_bank.cash_bank_code','=','d_cb_expense_receipt.cash_bank_code')
            ->join('m_coa','m_coa.id','=','t_cash_bank.id_coa')
            ->get();

        return Response::json($dataCB);
    }

    public function getAkun($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataAkun = DB::table('d_cb_expense_receipt')
            ->select('t_cash_bank.id_coa','m_coa.code','m_coa.desc')
            ->where('t_cash_bank.cash_bank_group', 'PENCAIRANOUT')
            ->where('t_cash_bank.cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_cash_bank.cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->join('t_cash_bank','t_cash_bank.cash_bank_code','=','d_cb_expense_receipt.cash_bank_code')
            ->join('m_coa','m_coa.id','=','t_cash_bank.id_coa')
            ->groupBy('t_cash_bank.id_coa','m_coa.code','m_coa.desc')
            ->get();

        return Response::json($dataAkun);
    }
}
