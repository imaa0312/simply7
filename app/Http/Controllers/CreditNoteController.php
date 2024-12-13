<?php

namespace App\Http\Controllers;

use DB;
use Response;
use App\Models\TCreditNoteModel;
use Illuminate\Http\Request;

class CreditNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $creditNote = TCreditNoteModel::join('m_user','m_user.id','t_credit_note.user_confirm')
                    ->select('*','t_credit_note.status as credit_status')
                    ->orderBy('t_credit_note.id','DESC')
                    ->get();
        foreach ($creditNote as $key => $value) {
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

        return view('admin.accounting.credit-note.index',compact('creditNote'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $code = $this->setCode();

        $customer = DB::table('t_faktur')->join('m_customer','m_customer.id','t_faktur.customer')
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
        return view('admin.accounting.credit-note.create',compact('customer','supplier','code'));
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
            TCreditNoteModel::create($request->except('customer','supllier'));

            if( $type == 'PIUTANG' ){

                DB::table('t_faktur')->where('id',$request->ref_id)->update([
                    'credit_note' => $request->total,
                ]);

            }elseif( $type == 'HUTANG' ){
                DB::table('t_purchase_invoice')->where('id',$request->ref_id)->update([
                    'credit_note' => $request->total,
                ]);
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
        }

        return redirect()->route('credit-note.index');
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
        $customer = DB::table('t_faktur')->join('m_customer','m_customer.id','t_faktur.customer')
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

        $creditNote = TCreditNoteModel::join('m_user','m_user.id','t_credit_note.user_confirm')
                    ->select('*','m_user.name as user_confirm')
                    ->orderBy('t_credit_note.id','DESC')
                    ->where('code',$code)
                    ->first();

            if( $creditNote->type == 'HUTANG' ){

                $person = DB::table('m_supplier')->where('id',$creditNote->id_person)->first();
                $invoice = DB::table('t_purchase_invoice')->where('id',$creditNote->ref_id)->first();
                $creditNote->all_invoice = DB::table('t_purchase_invoice')->where('supplier',$creditNote->id_person)->get();
            }else{

                $person = DB::table('m_customer')->where('id',$creditNote->id_person)->first();
                $invoice = DB::table('t_faktur')->where('id',$creditNote->ref_id)->first();
                $creditNote->all_invoice = DB::table('t_faktur')->where('customer',$creditNote->id_person)->get();
            }

            $creditNote->person = $person;
            $creditNote->invoice = $invoice;

            // dd($creditNote);
            return view('admin.accounting.credit-note.update',compact('creditNote','customer','supplier'));
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
            $creditNote = TCreditNoteModel::where('code',$code)->update($request->except('customer','supllier','_token','_method'));
                if( $type == 'PIUTANG' ){

                    DB::table('t_faktur')->where('id',$request->ref_id)->update([
                        'credit_note' => $request->total,
                    ]);

                }elseif( $type == 'HUTANG' ){
                    DB::table('t_purchase_invoice')->where('id',$request->ref_id)->update([
                        'credit_note' => $request->total,
                    ]);
                }

            $id_gl = DB::table('t_general_ledger')
                ->insertGetId([
                    'general_ledger_date' => date('Y-m-d'),
                    'general_ledger_periode' => date('Ym'),
                    'general_ledger_keterangan' => 'CN No.'.$creditNote->code,
                    'general_ledger_status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 1,
                    'id_coa' => $creditNote->coa_debet,
                    'debet_credit' => 'debet',
                    'total' => $creditNote->total,
                    'ref' => $creditNote->code,
                    'type_transaksi' => 'CN',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::table('d_general_ledger')
                ->insert([
                    't_gl_id' => $id_gl,
                    'sequence' => 2,
                    'id_coa' => $creditNote->coa_credit,
                    'debet_credit' => 'credit',
                    'total' => $creditNote->total,
                    'ref' => $creditNote->code,
                    'type_transaksi' => 'CN',
                    'status' => 'post',
                    'user_confirm' => auth()->user()->id,
                    'confirm_date' => date('Y-m-d'),
            ]);

            DB::commit();
        }catch(\Exception $e){

            DB::rollback();
        }

        return redirect()->route('credit-note.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($code)
    {
        TCreditNoteModel::where('code',$code)->delete();

        return redirect()->route('credit-note.index');
    }

    public function posting($code)
    {
         $creditNote = TCreditNoteModel::where('code',$code)->first();

        //calculate
        if( $creditNote->type == 'PIUTANG' ){

            $invoice = DB::table('t_faktur')->where('id',$creditNote->ref_id)->first();

            // dd($invoice);

            DB::table('t_faktur')->where('id',$creditNote->ref_id)->update([
                'jumlah_yg_dibayarkan' => $invoice->jumlah_yg_dibayarkan + $creditNote->total,
            ]);

        }elseif( $creditNote->type == 'HUTANG' ){
            $faktur = DB::table('t_purchase_invoice')->where('id',$creditNote->ref_id)->first();

            DB::table('t_purchase_invoice')->where('id',$creditNote->ref_id)->update([
                'total' => $faktur->total + $creditNote->total,
            ]);
        }

        //autojurnal

        //update-status debet-note
        $creditNote->update(['status' => 'post']);

        return redirect()->route('credit-note.index');
    }
    protected function setCode($type = 'CN')
    {
        $getLastCode = DB::table('t_credit_note')
            ->orderBy('id', 'desc')
            ->pluck('id')
            ->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return $type.$dataDate.$nol.$getLastCode;
    }
}
