<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MRoleModel;
use App\Models\MUserModel;
use App\Models\MTargetSalesModel;
use DB;



class MSalesTargetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getData = MTargetSalesModel::with('salesTargetRelation')->orderBy('id','DESC')->get();
        // dd($getData);
        return view('admin.sales.target-sales.index', compact('getData'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $rolesales = MRoleModel::where('name', 'Sales')->first();
        $getMsales = MUserModel::where('role','=',$rolesales->id)->get();
        $dataTargetSales = MTargetSalesModel::get();

        return view('admin.sales.target-sales.create', compact('getMsales'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $this->validate($request,[
            'target' => 'required|max:10',
        ]);

        $month = date('m', strtotime($request->bulan_target));
        $year = date('Y', strtotime($request->bulan_target));

        $bulan_target = date('Y-m-d', strtotime($request->bulan_target));

        $validationBulan = MTargetSalesModel::where('sales',$request->sales)
                            ->whereMonth('month', '=', $month)
                            ->whereYear('month', '=', $year)
                            ->get();

        if(count($validationBulan) == 0){

            $storeTarget = new MTargetSalesModel;
            $storeTarget->sales = $request->sales;
            $storeTarget->month = $bulan_target;
            $storeTarget->monthly_target = $request->target;
            $storeTarget->save();

            return redirect('admin/target-sales');

        }else{

            return redirect()->back()->with('message','Target Bulan sudah Ada');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sales = MTargetSalesModel::with('salesTargetRelation')->where('id',$id)->first();

        // dd($sales);
        return view('admin.sales.target-sales.update', compact('sales'));
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
        // dd($request->all());

        $this->validate($request,[
            'monthly_target' => 'required|max:10',
        ]);
        $oldTargetSales = MTargetSalesModel::find($id);

        $month = date('m', strtotime($request->month));
        $year = date('Y', strtotime($request->month));
        $bulan_target = date('Y-m-d', strtotime($request->month));


        $oldMonth = date('m',strtotime($oldTargetSales->month));
        $oldYear = date('Y',strtotime($oldTargetSales->month));

        if( $month == $oldMonth && $year == $oldYear){
            $updateTarget = MTargetSalesModel::find($id);
            $updateTarget->month = $bulan_target;
            $updateTarget->monthly_target = $request->monthly_target;
            $updateTarget->save();

            return redirect('admin/target-sales');
        }else{

            $validationBulan = MTargetSalesModel::where('sales',$request->sales)
                                ->whereMonth('month', '=', $month)
                                ->whereYear('month', '=', $year)
                                ->get();

            if(count($validationBulan) == 0){

                $updateTarget = MTargetSalesModel::find($id);
                $updateTarget->month = $bulan_target;
                $updateTarget->monthly_target = $request->monthly_target;
                $updateTarget->save();

                return redirect('admin/target-sales');
            }else{
                return redirect()->back()->with('message','Target Bulan sudah Ada');
            }
        }
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
}
