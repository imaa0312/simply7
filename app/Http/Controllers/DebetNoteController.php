<?php

namespace App\Http\Controllers;

use App\Models\MCustomerModel;
use App\Models\MSupplierModel;
use App\Models\TDebetNoteModel;
use DB;
use Illuminate\Http\Request;
use Response;

class DebetNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $debetNote = TDebetNoteModel::join('m_user','m_user.id','t_debet_note.user_confirm')
                    ->select('*','t_debet_note.status as debet_status')
                    ->orderBy('t_debet_note.id','DESC')
                    ->get();
        foreach ($debetNote as $key => $value) {
            $person = null;
            $invoice = null;
            if( $value->type == 'HUTANG' ){

                $person = DB::table('m_supplier')->where('id',$value->id_person)->first();
                $invoice = DB::table('t_purchase_invoice')->where('id',$value->ref_id)->first();

            }else{

                $person = DB::table('m_customer')->where('id',$value->id_person)->first();
                $invoice = DB::table('t_faktur')->where('id',$value->ref_id)->first();
            }

            $value->person = $person;
            $value->invoice = $invoice;
        }
        // dd($debetNote);
        return view('admin.accounting.debet-note.index',compact('debetNote'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $code = $this->setCode();
        $customer =  DB::table('t_faktur')
                    ->join('m_customer','m_customer.id','t_faktur.customer')
                    ->select('m_customer.id','m_customer.name as customer_name','m_customer.main_address')
                    ->where('m_customer.status',true)
                    ->orderBy('name')
                    ->groupBy('m_customer.id','m_customer.main_address')
                    ->get();


        $supplier = DB::table('t_purchase_invoice')
                    ->join('m_supplier','m_supplier.id','t_purchase_invoice.supplier')
                    ->select('m_supplier.id as supplier','m_supplier.name as supplier_name','m_supplier.main_address')
                    ->where('m_supplier.status',true)
                    ->orderBy('name')
                    ->groupBy('m_supplier.id','m_supplier.name','m_supplier.main_address')
                    ->get();
        // dd($customer);
        return view('admin.accounting.debet-note.create',compact('customer','supplier','code'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $person = null;
        $type = null;

        ( $request->customer == null ) ? $person = $request->supllier : $person = $request->customer;
        ( $request->type == 'AP' ) ? $type = 'HUTANG' : $type = 'PIUTANG';

        $request->merge([
            'code'      => $this->setCode(),
            'type'      => $type,
            'id_person' => $person,
            'date'      => date('Y-m-d'),
            'user_confirm' => auth()->user()->id,
        ]);

        DB::beginTransaction();
        try{
            TDebetNoteModel::create($request->except('supllier','customer'));

            if( $type == 'PIUTANG' ){

                DB::table('t_faktur')->where('id',$request->ref_id)->update([
                    'debet_note' => $request->total,
                ]);

            }elseif( $type == 'HUTANG' ){
                DB::table('t_purchase_invoice')->where('id',$request->ref_id)->update([
                    'debet_note' => $request->total,
                ]);
            }

            DB::commit();
        }catch( \Exception $e){
            dd($e);
            DB::rollback();
        }

        return redirect()->route('debet-note.index');
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
    public function edit($code)
    {
        $customer = DB::table('t_faktur')
                    ->join('m_customer','m_customer.id','t_faktur.customer')
                    ->select('m_customer.id','m_customer.name as customer_name','m_customer.main_address')
                    ->where('m_customer.status',true)
                    ->orderBy('name')
                    ->groupBy('m_customer.id','m_customer.main_address')
                    ->get();

        $supplier =  DB::table('t_purchase_invoice')
                    ->join('m_supplier','m_supplier.id','t_purchase_invoice.supplier')
                    ->select('m_supplier.id as supplier','m_supplier.name as supplier_name','m_supplier.main_address')
                    ->where('m_supplier.status',true)
                    ->orderBy('name')
                    ->groupBy('m_supplier.id','m_supplier.name','m_supplier.main_address')
                    ->get();

        $debetNote = TDebetNoteModel::join('m_user','m_user.id','t_debet_note.user_confirm')
                    ->select('*','m_user.name as user_confirm')
                    ->orderBy('t_debet_note.id','DESC')
                    ->where('code',$code)
                    ->first();

            if( $debetNote->type == 'HUTANG' ){

                $person = DB::table('m_supplier')->where('id',$debetNote->id_person)->first();
                $invoice = DB::table('t_purchase_invoice')->where('id',$debetNote->ref_id)->first();
                $debetNote->all_invoice = DB::table('t_purchase_invoice')->where('supplier',$debetNote->id_person)->get();
            }else{

                $person = DB::table('m_customer')->where('id',$debetNote->id_person)->first();
                $invoice = DB::table('t_faktur')->where('id',$debetNote->ref_id)->first();
                $debetNote->all_invoice = DB::table('t_faktur')->where('customer',$debetNote->id_person)->get();
            }

            $debetNote->person = $person;
            $debetNote->invoice = $invoice;

            // dd($customer);
            return view('admin.accounting.debet-note.update',compact('debetNote','customer','supplier'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $code)
    {
        $person = null;
        $type = null;

        ( $request->customer == null ) ? $person = $request->supllier : $person = $request->customer;
        ( $request->type == 'AP' ) ? $type = 'HUTANG' : $type = 'PIUTANG';

        $request->merge([
            'type'      => $type,
            'id_person' => $person,
        ]);

        DB::beginTransaction();
        try{
            TDebetNoteModel::where('code',$code)->update($request->except('supllier','customer','_token','_method'));

            if( $type == 'PIUTANG' ){

                DB::table('t_faktur')->where('id',$request->ref_id)->update([
                    'debet_note' => $request->total,
                ]);

            }elseif( $type == 'HUTANG' ){
                DB::table('t_purchase_invoice')->where('id',$request->ref_id)->update([
                    'debet_note' => $request->total,
                ]);
            }


            DB::commit();
        }catch( \Exception $e){
            dd($e);
            DB::rollback();
        }

        return redirect()->route('debet-note.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($code)
    {
        // dd($id);
        TDebetNoteModel::where('code',$code)->delete();

        return redirect()->route('debet-note.index');
    }

    public function posting($code)
    {
        //get debet note data
        $debetNote = TDebetNoteModel::where('code',$code)->first();

        DB::beginTransaction();
        try{
            //calculate
            if( $debetNote->type == 'PIUTANG' ){

                $invoice = DB::table('t_faktur')->where('id',$debetNote->ref_id)->first();

                // dd($invoice);

                DB::table('t_faktur')->where('id',$debetNote->ref_id)->update([
                    'total' => $invoice->total + $debetNote->total,
                ]);

            }elseif( $debetNote->type == 'HUTANG' ){
                $faktur = DB::table('t_purchase_invoice')->where('id',$debetNote->ref_id)->first();

                DB::table('t_purchase_invoice')->where('id',$debetNote->ref_id)->update([
                    'jumlah_yg_dibayarkan' => $faktur->jumlah_yg_dibayarkan + $debetNote->total,
                ]);
            }

        //AUTOJURNAL
            $id_gl = DB::table('t_general_ledger')
                ->insertGetId([
                    'general_ledger_date' => date('Y-m-d'),
                    'general_ledger_periode' => date('Ym'),
                    'general_ledger_keterangan' => 'DN No.'.$debetNote->code,
                    'general_ledger_status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 1,
                    'id_coa' => $debetNote->coa_debet,
                    'debet_credit' => 'debet',
                    'total' => $debetNote->total,
                    'ref' => $debetNote->code,
                    'type_transaksi' => 'DN',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 2,
                    'id_coa' => $debetNote->coa_credit,
                    'debet_credit' => 'credit',
                    'total' => $debetNote->total,
                    'ref' => $debetNote->code,
                    'type_transaksi' => 'DN',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

        //update-status debet-note
            $debetNote->update(['status' => 'post']);
            DB::commit();
        }catch( \Exception $e){
            dd($e);
            DB::rollback();
        }
        return redirect()->route('debet-note.index');
    }

    protected function setCode($type = 'DN')
    {
        $getLastCode = DB::table('t_debet_note')->count();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return $type.$dataDate.$nol.$getLastCode;
    }

    public function showInvoiceAp($supplier)
    {
        $result = DB::table('t_purchase_invoice')
                ->where('supplier',$supplier)
                ->get();

        return Response::json($result);
    }

    public function showInvoiceAr($customer)
    {
        $result = DB::table('t_faktur')
                ->where('customer',$customer)
                ->get();

        return Response::json($result);
    }

    public function showCoaByNameInterface($name)
    {
        $data_interface = DB::table('m_interface')
            ->where('var',$name)
            ->first();

        $code_coa = explode(",", $data_interface->code_coa);

        if ($code_coa[0]=='' || $code_coa[0] == null) {
            $code_coa = [];
            $data = [];
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
            $data = $query[count($code_coa)-1]->get();
        }

        return Response::json($data);
    }
}
