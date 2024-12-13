<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use App\Models\MCustomerModel;
use App\Models\TPlanningModel;



class TPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataPlan = DB::table('t_planning')
            ->leftjoin('m_customer', 'm_customer.id', '=', 't_planning.customer')
            ->join('m_user', 'm_user.id', '=', 't_planning.sales')
            ->select('t_planning.id','m_user.name as sales','t_planning.plan','t_planning.date','t_planning.start_hour','t_planning.status','m_customer.name as customer')
            ->orderBy('t_planning.date')
            ->orderBy('t_planning.start_hour')
            ->get();

        // dd($dataPenagihan);
        return view('admin.plan.index', compact('dataPlan'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $roleSales = MRoleModel::where('name', 'Sales')->first();
        // $getMsales = MUserModel::where('role','=',$roleSales->id)->get();
        // $getCustomer = MCustomerModel::get();
        // return view('admin.plan.create', compact('getMsales','getCustomer'));

        $getSales = DB::table('m_user')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->join('m_wilayah_pembagian_sales', 'm_wilayah_pembagian_sales.sales', '=', 'm_user.id')
            ->join('m_wilayah_sales', 'm_wilayah_sales.id', '=', 'm_wilayah_pembagian_sales.wilayah_sales')
            ->select('m_user.id as sales_id','m_user.name as sales','m_wilayah_sales.name as wilayah_name')
            ->where('m_role.name','Sales')
            ->get();

        // dd($getSales);

        return view('admin.plan.create', compact('getSales'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'sales' => 'required',
            'customer' => 'required',
            'plan' => 'required',
            'tgl' => 'required',
            'start_hour' => 'required',
            'finish_hour' => 'required'
        ]);
        //dd($request->all());

        $tgl = date('Y-m-d', strtotime($request->tgl));

        $plan = new TPlanningModel;
        $plan->customer = $request->customer;
        $plan->sales = $request->sales;
        $plan->plan = $request->plan;
        $plan->date = $tgl;
        $plan->start_hour = $request->start_hour;
        $plan->finish_hour = $request->finish_hour;
        $plan->save();

        return redirect('admin/plan');
    }

    public function createPlanMatrix(Request $request)
    {
        $this->validate($request, [
            'sales' => 'required',
            'periode' => 'required',
        ]);

        //dd($request->all());

        $tglmulai = substr($request->periode,0,10);
        $tglsampai = substr($request->periode,13,10);

        $date = $tglmulai;
        $x = 'a';
        while (strtotime($date) <= strtotime($tglsampai)) {
            for ($i=1; $i <= 12; $i++) {
                //dd($request->{"customer".$i.$x});
                if ($request->{"customer".$i.$x} != null) {
                    if ($request->{"planAO".$i.$x} != null) {
                        DB::table('t_planning')
                            ->insert([
                                'customer' => $request->{"customer".$i.$x},
                                'sales' => $request->sales,
                                'plan' => 'Ambil Order',
                                'date' => date('Y-m-d', strtotime($date)),
                                'start_hour' => $request->{"jam".$i.$x},
                                'finish_hour' => $request->{"selesai".$i.$x},
                            ]);
                    }
                    if ($request->{"planT".$i.$x} != null) {
                        DB::table('t_planning')
                            ->insert([
                                'customer' => $request->{"customer".$i.$x},
                                'sales' => $request->sales,
                                'plan' => 'Tagihan',
                                'date' => date('Y-m-d', strtotime($date)),
                                'start_hour' => $request->{"jam".$i.$x},
                                'finish_hour' => $request->{"selesai".$i.$x},
                            ]);
                    }
                }
            }

            $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
            $x++;
        }

        return redirect('admin/plan');
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
        $getPlan = TPlanningModel::where('id', '=', $id)->first();

        $dataPlan = DB::table('t_planning')
            ->join('m_customer', 'm_customer.id', '=', 't_planning.customer')
            ->join('m_user', 'm_user.id', '=', 't_planning.sales')
            ->select('t_planning.id','m_user.id as sales_id','m_user.name as sales','t_planning.plan','t_planning.date','t_planning.start_hour','t_planning.status','m_customer.id as customer_id','m_customer.name as customer')
            ->where('t_planning.id','=',$id)
            ->first();

        $roleAdmin = MRoleModel::where('name', 'Admin')->first();
        $getSales = MUserModel::where('role','!=',$roleAdmin->id)->get();
        $getCustomer = MCustomerModel::get();

        return view('admin.plan.update', compact('getPlan','getSales','getCustomer','dataPlan'));
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
        $this->validate($request, [
            'sales' => 'required',
            'customer' => 'required',
            'plan' => 'required',
            'tgl' => 'required',
            'start_hour' => 'required',
            'finish_hour' => 'required',
        ]);

        $tgl = date('Y-m-d', strtotime($request->tgl));

        $updatePPenagihan = TPlanningModel::where('id', '=', $id)->update([
            'sales' => $request->sales,
            'customer' => $request->customer,
            'plan' => $request->plan,
            'date' => $tgl,
            'start_hour' => $request->start_hour,
            'finish_hour' => $request->finish_hour,
        ]);

        return redirect('admin/plan');
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

    //

    public function showCustomerBySales(Request $request,$id)
    {
        $dataCustomerBySales =  DB::table('m_customer')
            ->join('m_wilayah_sales','m_wilayah_sales.id','=','m_customer.wilayah_sales')
            ->join('m_wilayah_pembagian_sales','m_wilayah_pembagian_sales.wilayah_sales','=','m_wilayah_sales.id')
            ->select('m_customer.id','m_customer.name','m_customer.main_address')
            ->where('m_wilayah_pembagian_sales.sales',$id)
            ->get();

        return Response::json($dataCustomerBySales);
    }

    public function laporanSales()
    {
        $dataSales = DB::table('m_user')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->select('m_user.id as sales_id','m_user.name')
            ->where('m_role.name','Sales')
            ->get();

        return view('admin.plan.laporansales', compact('dataSales'));
    }
}
