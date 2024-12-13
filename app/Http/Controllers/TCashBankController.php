<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MProdukModel;
use Response;
use DB;

class TCashBankController extends Controller
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

    public function laporanKas()
    {
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

        //dd($dataKas);

        return view('admin.accounting.cash-bank.laporan-kas', compact('dataKas'));
    }

    public function laporanBank()
    {
        $data_interface = DB::table('m_interface')
            ->where('var','VAR_BANK')
            ->first();

        $code_coa = explode(",", $data_interface->code_coa);

        if ($code_coa[0]=='') {
            $code_coa = [];
            $dataBank = [];
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
            $dataBank = $query[count($code_coa)-1]->get();

            //cek code coa paling bawah
            $length = 0;
            foreach($dataBank as $raw_data) {
                $lengthCode = strlen($raw_data->code);
                if ($lengthCode > $length) {
                    $length =$lengthCode;
                }
                $raw_data->test = $lengthCode;
            }

            //remove coa parent
            foreach ($dataBank as $key => $raw_data) {
                $lengthCode = strlen($raw_data->code);
                if ($lengthCode < $length) {
                    unset($dataBank[$key]);
                }
            }
        }

        return view('admin.accounting.cash-bank.laporan-bank', compact('dataBank'));
    }

    public function laporanGeneralJournal()
    {
        $akun = DB::table('d_general_ledger')
            ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
            ->join('m_coa','m_coa.id','=','d_general_ledger.id_coa')
            ->select('d_general_ledger.id_coa','m_coa.desc','m_coa.code')
            ->groupBy('id_coa','desc','code')
            ->orderBy('id_coa')
            ->get();
            
        return view('admin.accounting.cash-bank.laporan-general-journal', compact('akun'));
    }

    public function laporanGeneralLedger()
    {
        // $akun = DB::table('d_general_ledger')
        //     ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
        //     ->join('m_coa','m_coa.id','=','d_general_ledger.id_coa')
        //     ->select('d_general_ledger.id_coa','m_coa.desc','m_coa.code')
        //     ->groupBy('id_coa','desc','code')
        //     ->orderBy('id_coa')
        //     ->get();

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

        $akun = $list_coa;

        return view('admin.accounting.cash-bank.laporan-general-ledger', compact('akun'));
    }

    public function laporanTrialBalance()
    {
        return view('admin.accounting.cash-bank.laporan-trial-balance');
    }

    public function laporanNeraca()
    {
        return view('admin.accounting.cash-bank.laporan-neraca');
    }

    public function laporanLabaRugi()
    {
        return view('admin.accounting.cash-bank.laporan-laba-rugi');
    }

    public function laporanHpp()
    {
        $products = MProdukModel::orderBy('name')->get();

        return view('admin/accounting/cash-bank/laporan-hpp',compact('products'));
    }

    public function getAkunGj($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $akun = DB::table('d_general_ledger')
            ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
            ->join('m_coa','m_coa.id','=','d_general_ledger.id_coa')
            ->select('d_general_ledger.id_coa','m_coa.desc','m_coa.code')
            ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->groupBy('id_coa','desc','code')
            ->orderBy('id_coa')
            ->get();

        return Response::json($akun);
    }

    public function getAkunGl($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        // $akun = DB::table('d_general_ledger')
        //     ->join('t_general_ledger','t_general_ledger.id','=','d_general_ledger.t_gl_id')
        //     ->join('m_coa','m_coa.id','=','d_general_ledger.id_coa')
        //     ->select('d_general_ledger.id_coa','m_coa.desc','m_coa.code')
        //     ->where('t_general_ledger.general_ledger_date','>=', date('Y-m-d', strtotime($tglmulai)))
        //     ->where('t_general_ledger.general_ledger_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
        //     ->groupBy('id_coa','desc','code')
        //     ->orderBy('id_coa')
        //     ->get();

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

        $akun = $list_coa;

        return Response::json($akun);
    }

    public function getAkunBank($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $akun = DB::table('t_cash_bank')
            ->join('m_coa','m_coa.id','=','t_cash_bank.id_coa')
            ->select('t_cash_bank.id_coa','m_coa.desc','m_coa.code')
            ->where('t_cash_bank.cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_cash_bank.cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->where(function ($query) {
                $query->where('cash_bank_type','BBK')
                    ->orwhere('cash_bank_type','BBM');
            })
            ->groupBy('id_coa','desc','code')
            ->get();

        return Response::json($akun);
    }

    public function getAkunKas($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $akun = DB::table('t_cash_bank')
            ->join('m_coa','m_coa.id','=','t_cash_bank.id_coa')
            ->select('t_cash_bank.id_coa','m_coa.desc','m_coa.code')
            ->where('t_cash_bank.cash_bank_date','>=', date('Y-m-d', strtotime($tglmulai)))
            ->where('t_cash_bank.cash_bank_date','<', date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
            ->where(function ($query) {
                $query->where('cash_bank_type','BKK')
                    ->orwhere('cash_bank_type','BKM');
            })
            ->groupBy('id_coa','desc','code')
            ->get();

        return Response::json($akun);
    }
}
