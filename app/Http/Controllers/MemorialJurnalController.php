<?php

namespace App\Http\Controllers;

use DB;
use Response;
use App\Models\MCoaModel;
use Illuminate\Http\Request;

class MemorialJurnalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $memorial = DB::table('t_general_ledger')
                    ->join('d_general_ledger','d_general_ledger.ref','t_general_ledger.general_ledger_ref')
                    ->select('t_general_ledger.*')
                    ->groupBy('t_general_ledger.id')
                    ->orderBy('t_general_ledger.id','DESC')
                    ->where('d_general_ledger.type_transaksi','MEMORIAL')
                    ->get();
                    
        return view('admin.accounting.memorial.index',compact('memorial'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $code = $this->setCode();
        return view('admin.accounting.memorial.create',compact('code'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $noref = $this->setCode();
        $array = [];

        $i=0;
        foreach($request->coa_id as $raw1){
            $array[$i]['ref'] = $noref;
            $array[$i]['id_coa'] = $raw1;
            $array[$i]['sequence'] = $i+1;
            $array[$i]['type_transaksi'] = 'MEMORIAL';
            $i++;
        }

        //reindex
        $debet = array_values($request->debit);
        $credit = array_values($request->credit);

        $i=0;
        foreach ($request->type  as $rawtype) {

            if( $rawtype == 'debit'){
                $array[$i]['debet_credit'] = 'debet';
                $array[$i]['total'] = $debet[$i];
            }else{
                $array[$i]['debet_credit'] = 'credit';
                $array[$i]['total'] = $credit[$i];
            }
            $i++;
        }
        // echo "<pre>";
        // print_r($array);
        // dd($request->all());
        DB::beginTransaction();

        try {
            $header = DB::table('t_general_ledger')->insert([
                'general_ledger_date' => date('Y-m-d'),
                'general_ledger_periode' => date('Ym'),
                'general_ledger_ref' => $noref,
                'general_ledger_keterangan' => $request->desckripsi,
                'user_confirm' => auth()->user()->id,
            ]);

            for($x=0; $x<count($array); $x++){
                $array[$x]['t_gl_id'] = DB::getPdo()->lastInsertId();
            }

            DB::table('d_general_ledger')->insert($array);
            // echo "<pre>";
            // print_r($array);
            // die();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect()->route('memorial.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($noref)
    {
        $header = DB::table('t_general_ledger')
                    ->join('m_user','m_user.id','t_general_ledger.user_confirm')
                    ->select('t_general_ledger.*','m_user.name as user_confirm')
                    ->where('general_ledger_ref',$noref)
                    ->first();

        $detail = DB::table('d_general_ledger')
                    ->join('m_coa','m_coa.id','d_general_ledger.id_coa')
                    ->select('*','d_general_ledger.debet_credit')
                    ->where('ref',$noref)
                    ->where('t_gl_id',$header->id)
                    ->get();
        // dd($detail);

        return view('admin.accounting.memorial.detail',compact('header','detail'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($noref)
    {
         $header = DB::table('t_general_ledger')
                    ->join('m_user','m_user.id','t_general_ledger.user_confirm')
                    ->select('t_general_ledger.*','m_user.name as user_confirm')
                    ->where('general_ledger_ref',$noref)
                    ->first();

        $detail = DB::table('d_general_ledger')
                    ->join('m_coa','m_coa.id','d_general_ledger.id_coa')
                    ->select('*','d_general_ledger.debet_credit')
                    ->orderBy('sequence','asc')
                    ->where('ref',$noref)
                    ->where('t_gl_id',$header->id)
                    ->get();
        // dd($detail);
        return view('admin.accounting.memorial.update',compact('header','detail'));
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
        $noref = $request->ref_code;
        $array = [];

        $i=0;
        foreach($request->coa_id as $raw1){
            $array[$i]['ref'] = $noref;
            $array[$i]['id_coa'] = $raw1;
            $array[$i]['sequence'] = $i+1;
            $array[$i]['type_transaksi'] = 'MEMORIAL';
            $array[$i]['t_gl_id'] = $id;
            $i++;
        }

        //reindex
        $debet = array_values($request->debit);
        $credit = array_values($request->credit);

        $i=0;
        foreach ($request->type  as $rawtype) {
            //debet-credit
            if( $rawtype == 'debet'){
                $array[$i]['debet_credit'] = 'debet';
                $array[$i]['total'] = $debet[$i];
            }else{
                $array[$i]['debet_credit'] = 'credit';
                $array[$i]['total'] =  $credit[$i];
            }
            $i++;
        }
        // echo "<pre>";
        // print_r($array);
        // dd($request->all());
        DB::beginTransaction();

        try {
            $header = DB::table('t_general_ledger')->where('id',$id)->update([
                'general_ledger_date' => date('Y-m-d'),
                'general_ledger_periode' => date('Ym'),
                'general_ledger_keterangan' => $request->desckripsi,
            ]);


            DB::table('d_general_ledger')->where('t_gl_id',$id)->where('ref',$noref)->delete();

            DB::table('d_general_ledger')->insert($array);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect()->route('memorial.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($noref)
    {
        DB::table('d_general_ledger')->where('ref',$noref)->delete();

        DB::table('t_general_ledger')->where('general_ledger_ref',$noref)->delete();
        return redirect()->route('memorial.index');
    }

    public function posting($noref)
    {
        DB::table('t_general_ledger')->where('general_ledger_ref',$noref)->update([
            'general_ledger_status' => 'post'
        ]);
        
        return redirect()->route('memorial.index');
    }

    protected function setCode()
    {
        $getLastCode = DB::table('t_general_ledger')->select('id')->orderBy('id', 'desc')->pluck('id')->first();
        $getLastCode = $getLastCode +1;
        $date = date("ym");
        $nol = null;
       if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'MEMO'.$date.$nol.$getLastCode;
    }

    public function getAccountByGrup($grup)
    {
        $result = MCoaModel::where('grup',$grup)->get();

        $data_pembanding = $result;

        foreach ($result as $key => $raw_data) {
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
                unset($result[$key]);
            }
        }

        return Response::json($result);
    }
}
