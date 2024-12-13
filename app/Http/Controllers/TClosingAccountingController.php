<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;

class TClosingAccountingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data_terakhir = DB::table('m_periode_closing')
            ->where('type','accounting')
            ->orderBy('periode','desc')
            ->first();

        if (!empty($data_terakhir)) {
            $closing_terakhir = date('m-Y', strtotime($data_terakhir->periode));
            $closing_next = date('m-Y', strtotime('+1 months',strtotime($data_terakhir->periode)));
        }else{
            $closing_terakhir = '-';
            $closing_next = '-';
        }

        //dd($closing_next);

        return view('admin.accounting.closing-accounting.index', compact('closing_terakhir','closing_next'));
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

    public function closing(Request $request)
    {
        ini_set('memory_limit', '512MB');
        ini_set('max_execution_time', 3000);
        $date = '01-'.$request->periode;

        $date_this_month = date('Y-m-d', strtotime($date));
        $date_next_month = date('Y-m-d', strtotime('+1 months',strtotime($date)));

        $first = date('Y-m-01', strtotime($date_next_month));

        // Last day of the month.
        $last = date('Y-m-t', strtotime($date_this_month));

        //dd($last);
        $date_now = date('Y-m-d');

        // if (($date_now == $first) ||($date_now == $last)) {
            $cek = DB::table('m_periode_closing')
                ->whereMonth('periode',date('m', strtotime($date)))
                ->whereYear('periode',date('Y', strtotime($date)))
                ->where('type','accounting')
                ->count();

            if ($cek > 0) {
                return redirect()->back()->with('message','Sudah melakukan closing pada periode ini');
            }else{
                DB::table('m_periode_closing')
                    ->insert([
                        'periode' => date('Y-m-d', strtotime($date)),
                        'type' => 'accounting',
                    ]);

                //insert coa bulan depan
                $query = DB::table('m_coa');
                $query->select('id as id_coa','code','desc');
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

                $status_closing = DB::table('t_closing_accounting')
                    ->first();

                if (!empty($status_closing)) {
                    //jika sudah pernah closing
                    //update bulan ini
                    foreach ($list_coa as $raw_data) {
                        //saldo awal sudah diisi bulan sebelumnya, tinggal diambil untuk hitung
                        $saldo_awal = DB::table('t_closing_accounting')
                            ->where('id_coa', '=', $raw_data->id_coa)
                            ->whereMonth('periode',date('m', strtotime($date_this_month)))
                            ->whereYear('periode',date('Y', strtotime($date_this_month)))
                            ->sum('total_open');

                        //ambil debet credit dari journal
                        $total_debet = DB::table('d_general_ledger')
                            ->where('id_coa', '=', $raw_data->id_coa)
                            ->where('debet_credit', '=', 'debet')
                            ->whereMonth('created_at',date('m', strtotime($date_this_month)))
                            ->whereYear('created_at',date('Y', strtotime($date_this_month)))
                            ->sum('total');

                        $total_credit = DB::table('d_general_ledger')
                            ->where('id_coa', '=', $raw_data->id_coa)
                            ->where('debet_credit', '=', 'credit')
                            ->whereMonth('created_at',date('m', strtotime($date_this_month)))
                            ->whereYear('created_at',date('Y', strtotime($date_this_month)))
                            ->sum('total');

                        $total_balance = $saldo_awal + $total_debet - $total_credit;

                        DB::table('t_closing_accounting')
                            ->where('id_coa', '=', $raw_data->id_coa)
                            ->whereMonth('periode',date('m', strtotime($date_this_month)))
                            ->whereYear('periode',date('Y', strtotime($date_this_month)))
                            ->update([
                                'closing_date' => date('Y-m-d'),
                                //'total_open' => 0,
                                'total_debet' => $total_debet,
                                'total_credit' => $total_credit,
                                'total_balance' => $total_balance,
                                'status' => 'close',
                            ]);
                    }
                }else{
                    //jika belum pernah closing sama sekali
                    //insert bulan ini
                    foreach ($list_coa as $raw_data) {
                        $saldo_awal = DB::table('m_saldo_awal_coa')
                            ->where('id_coa', '=', $raw_data->id_coa)
                            ->sum('total');

                        //ambil debet credit dari journal
                        $total_debet = DB::table('d_general_ledger')
                            ->where('id_coa', '=', $raw_data->id_coa)
                            ->where('debet_credit', '=', 'debet')
                            ->whereMonth('created_at',date('m', strtotime($date_this_month)))
                            ->whereYear('created_at',date('Y', strtotime($date_this_month)))
                            ->sum('total');

                        $total_credit = DB::table('d_general_ledger')
                            ->where('id_coa', '=', $raw_data->id_coa)
                            ->where('debet_credit', '=', 'credit')
                            ->whereMonth('created_at',date('m', strtotime($date_this_month)))
                            ->whereYear('created_at',date('Y', strtotime($date_this_month)))
                            ->sum('total');

                        $total_balance = $saldo_awal + $total_debet - $total_credit;

                        DB::table('t_closing_accounting')
                            ->insert([
                                'periode' => $date_this_month,
                                'id_coa' => $raw_data->id_coa,
                                'closing_date' => date('Y-m-d'),
                                'total_open' => $saldo_awal,
                                'total_debet' => $total_debet,
                                'total_credit' => $total_credit,
                                'total_balance' => $total_balance,
                                'group' => 'MONTHLY',
                                'keterangan' => 'MONTHLY CLOSING BALANCE '.date('m-Y', strtotime($date_this_month)),
                                'status' => 'close',
                            ]);
                    }
                }

                //insert untuk bulan depan
                foreach ($list_coa as $raw_data) {
                    //balance bulan ini buat saldo awal bulan depan
                    $saldo_awal = DB::table('t_closing_accounting')
                        ->where('id_coa', '=', $raw_data->id_coa)
                        ->whereMonth('periode',date('m', strtotime($date_this_month)))
                        ->whereYear('periode',date('Y', strtotime($date_this_month)))
                        ->sum('total_balance');

                    $total_debet = 0;
                    $total_credit = 0;
                    $total_balance = $saldo_awal + $total_debet - $total_credit;

                    DB::table('t_closing_accounting')
                        ->insert([
                            'periode' => $date_next_month,
                            'id_coa' => $raw_data->id_coa,
                            'total_open' => $saldo_awal,
                            'total_debet' => $total_debet,
                            'total_credit' => $total_credit,
                            'total_balance' => $total_balance,
                            'group' => 'MONTHLY',
                            'keterangan' => 'MONTHLY CLOSING BALANCE '.date('m-Y', strtotime($date_next_month)),
                        ]);
                }
                return redirect()->back()->with('message-success','Closing Berhasil Dilakukan');
            }
        // }else{
        //     return redirect()->back()->with('message','Belum Tanggal Closing');
        // }
    }

    public function getTransaksi($periode)
    {
        $month = '01-'.$periode;

        $pi = DB::table('t_surat_jalan_masuk')
            ->whereMonth('sj_masuk_date',date('m', strtotime($month)))
            ->whereYear('sj_masuk_date',date('Y', strtotime($month)))
            ->where('status','in process')
            ->count();

        $si = DB::table('t_surat_jalan')
            ->whereMonth('sj_date',date('m', strtotime($month)))
            ->whereYear('sj_date',date('Y', strtotime($month)))
            ->where('status','in process')
            ->count();

        $prt = DB::table('t_retur_sj')
            ->whereMonth('retur_dates',date('m', strtotime($month)))
            ->whereYear('retur_dates',date('Y', strtotime($month)))
            ->where('status','in process')
            ->count();

        $srt = DB::table('t_retur_sjm')
            ->whereMonth('retur_dates',date('m', strtotime($month)))
            ->whereYear('retur_dates',date('Y', strtotime($month)))
            ->where('status','in process')
            ->count();

        $ap = DB::table('t_pembayaran')
            ->whereMonth('payment_date',date('m', strtotime($month)))
            ->whereYear('payment_date',date('Y', strtotime($month)))
            ->where('status','in approval')
            ->count();

        $ar = DB::table('t_pi_pembayaran')
            ->whereMonth('payment_date',date('m', strtotime($month)))
            ->whereYear('payment_date',date('Y', strtotime($month)))
            ->where('status','in approval')
            ->count();

        $expense = DB::table('t_cash_bank')
            ->whereMonth('cash_bank_date',date('m', strtotime($month)))
            ->whereYear('cash_bank_date',date('Y', strtotime($month)))
            ->where('cash_bank_status','in process')
            ->where('cash_bank_group','EXPENSE')
            ->count();

        $receipt = DB::table('t_cash_bank')
            ->whereMonth('cash_bank_date',date('m', strtotime($month)))
            ->whereYear('cash_bank_date',date('Y', strtotime($month)))
            ->where('cash_bank_status','in process')
            ->where('cash_bank_group','RECEIPT')
            ->count();

        $mutation = DB::table('t_cash_bank')
            ->whereMonth('cash_bank_date',date('m', strtotime($month)))
            ->whereYear('cash_bank_date',date('Y', strtotime($month)))
            ->where('cash_bank_status','in process')
            ->where('cash_bank_group','MUTATION')
            ->count();

        $transaksi = array(
            array("transaksi" => "Purchase Invoice","jumlah" => $pi),
            array("transaksi" => "Sales Invoice","jumlah" => $si),
            array("transaksi" => "Purchase Retur","jumlah" => $prt),
            array("transaksi" => "Sales Retur","jumlah" => $srt),
            array("transaksi" => "A/P Payment","jumlah" => $ap),
            array("transaksi" => "A/R Payment","jumlah" => $ar),
            array("transaksi" => "Cash/Bank Expense","jumlah" => $expense),
            array("transaksi" => "Cash/Bank Receipt","jumlah" => $receipt),
            array("transaksi" => "Cash/Bank Mutation","jumlah" => $mutation),
        );

        return Response::json($transaksi);
    }

    public function getCoa($periode)
    {
        $query = DB::table('m_coa');
        $query->select('id as id_coa','code','desc');
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

        $month = '01-'.$periode;

        foreach ($list_coa as $key => $raw_data) {
            $saldo_awal = DB::table('m_saldo_awal_coa')
                ->where('id_coa', '=', $raw_data->id_coa)
                ->sum('total');

            //ambil debet credit dari journal
            $total_debet = DB::table('d_general_ledger')
                ->where('id_coa', '=', $raw_data->id_coa)
                ->where('debet_credit', '=', 'debet')
                ->whereMonth('created_at',date('m', strtotime($month)))
                ->whereYear('created_at',date('Y', strtotime($month)))
                ->sum('total');

            $total_credit = DB::table('d_general_ledger')
                ->where('id_coa', '=', $raw_data->id_coa)
                ->where('debet_credit', '=', 'credit')
                ->whereMonth('created_at',date('m', strtotime($month)))
                ->whereYear('created_at',date('Y', strtotime($month)))
                ->sum('total');

            $total_balance = $saldo_awal + $total_debet - $total_credit;

            if ($saldo_awal == 0 && $total_debet == 0 && $total_credit == 0 && $total_balance == 0) {
                //unset($list_coa[$key]);
                $raw_data->saldo_awal = $saldo_awal;
                $raw_data->debet = $total_debet;
                $raw_data->credit = $total_credit;
                $raw_data->saldo_akhir = $total_balance;
            }else{
                $raw_data->saldo_awal = $saldo_awal;
                $raw_data->debet = $total_debet;
                $raw_data->credit = $total_credit;
                $raw_data->saldo_akhir = $total_balance;
            }
        }

        return Response::json($list_coa);
    }
}
