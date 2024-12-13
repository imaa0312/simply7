<?php

namespace App\Http\Controllers;

use App\Models\MCoaModel;
use App\Models\MCustomerModel;
use App\Models\MInterfaceModel;
use App\Models\MSaldoAwalPiutang;
use DB;
use Illuminate\Http\Request;

class MSaldoAwalPiutangController extends Controller
{
    public function SAPiutang()
    {
    	$dataSP = MSaldoAwalPiutang::with('customerRelation')->orderBy('id','DESC')->get();

    	return view('admin.accounting.saldo-awal-piutang.index',compact('dataSP'));
    }

    public function createSAPiutang()
    {
    	$dataCustomer = MCustomerModel::orderBy('name','ASC')->where('status',1)->get();


        $interface = MInterfaceModel::where('var','VAR_PIUTANG')->first();

        $coa = $this->getSingleCoaInInterface($interface);
        // dd($coa);
    	return view('admin.accounting.saldo-awal-piutang.create',compact('dataCustomer','coa'));
    }

    public function storePost(Request $request)
    {
    	// dd($request->all());
        $request->merge(['date_nota' => date('Y-m-d',strtotime($request->date_nota)) ]);

        MSaldoAwalPiutang::create($request->all());

        return redirect('admin/accounting/saldo-awal-piutang');

    }

    public function edit($id)
    {
        $sap = MSaldoAwalPiutang::find($id);

        $customer = MCustomerModel::where('id',$sap->customer)->first();

        $coa = MCoaModel::where('id',$sap->akun_piutang)->first();

        return view('admin.accounting.saldo-awal-piutang.update',compact('coa','sap','customer'));
    }

    public function update(Request $request,$id)
    {
        MSaldoAwalPiutang::where('id',$id)->update($request->except('_token'));
        return redirect('admin/accounting/saldo-awal-piutang');

    }

    public function postingSAPHutang($id)
    {
        MSaldoAwalPiutang::where('id',$id)->update(['status' => 'post']);
        return redirect('admin/accounting/saldo-awal-piutang');
    }

    public function deleteSAPHutang($id)
    {
        MSaldoAwalPiutang::where('id',$id)->delete();
        return redirect('admin/accounting/saldo-awal-piutang');
    }

     protected function getSingleCoaInInterface($interface)
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

            $data = $query[count($codeCoa)-1]->first();
        }

        return $data;
    }
}
