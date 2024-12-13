<?php

namespace App\Http\Controllers;

use App\Models\MCoaModel;
use App\Models\MInterfaceModel;
use App\Models\MSaldoAwalHutang;
use App\Models\MSupplierModel;
use DB;
use Illuminate\Http\Request;

class MSaldoAwalController extends Controller
{
    public function SAHutang()
    {
        $dataSH = MSaldoAwalHutang::with('supplierRelation')->orderBy('id','desc')->get();

        return view('admin.accounting.saldo-awal-hutang.index',compact('dataSH'));
    }

    public function createSAHutang()
    {
        $dataSupplier = DB::table('m_supplier')
            ->select('*')
            ->get();

        $interface = MInterfaceModel::where('var','VAR_HUTANG')->first();

        $coa = $this->getSingleCoaInInterface($interface);

        return view('admin.accounting.saldo-awal-hutang.create',compact('dataSupplier','coa'));
    }

    public function storeSAHutang(Request $request)
    {
        // dd($request->all());
        $request->merge(['date_nota' => date('Y-m-d',strtotime($request->date_nota)) ]);

        MSaldoAwalHutang::create($request->all());

        return redirect('admin/accounting/saldo-awal-hutang');
    }

    public function edit($id)
    {
        $sap = MSaldoAwalHutang::find($id);

        $supplier = MSupplierModel::where('id',$sap->supplier)->first();

        $coa = MCoaModel::where('id',$sap->akun_hutang)->first();

        return view('admin.accounting.saldo-awal-hutang.update',compact('coa','sap','supplier'));
    }

    public function update(Request $request,$id)
    {
        MSaldoAwalHutang::where('id',$id)->update($request->except('_token'));
        return redirect('admin/accounting/saldo-awal-hutang');

    }

    public function postingSAHutang($id)
    {
        MSaldoAwalHutang::where('id',$id)->update(['status' => 'post']);
        return redirect('admin/accounting/saldo-awal-hutang');
    }

    public function deleteSAHutang($id)
    {
        MSaldoAwalHutang::where('id',$id)->delete();
        return redirect('admin/accounting/saldo-awal-hutang');
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
