<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use App\Models\TargetSalesModel;
use DB;
use carbon;



class MSalesController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $roleSales = MRoleModel::where('name', 'Sales')->first();

        //$getMsales = MUserModel::where('role', '=', $roleSales->id)->get();

        $getMsales = DB::table('m_user')
            ->select('m_user.id as user_id','m_user.name as name', 'm_user.username','m_user.email','m_user.address', 'm_wilayah_sales.name as wilayah_name')
            ->join('m_wilayah_pembagian_sales','m_user.id','m_wilayah_pembagian_sales.sales')
            ->join('m_wilayah_sales','m_wilayah_sales.id','m_wilayah_pembagian_sales.wilayah_sales')
            ->where('role', '=', $roleSales->id)
            ->get();

        foreach ($getMsales as $raw_data) {
            $point_sales = DB::table('m_point_sales')
                ->where('sales', $raw_data->user_id)
                ->sum('point');

            $raw_data->point = $point_sales;
        }

        // dd($getMsales);

        return view('admin.sales.index', compact('getMsales'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.sales.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $nama = $request->old('nama');
        $email = $request->old('email');
        $alamat = $request->old('alamat');
        $birthdate = $request->old('birthdate');

        $this->validate($request, [
            'nama' => 'required|max:50',
            'email' => 'required|email|unique:m_user',
        ]);

        $roleSales  = MRoleModel::where('name', 'Sales')->first();
        $newSales = new MUserModel;
        $newSales->name = $request->nama;
        $newSales->email = $request->email;
        $newSales->address = $request->alamat;
        $newSales->birthdate = $request->birthdate;
        $newSales->password =  bcrypt(str_replace(' ', '', $request->nama));
        $newSales->role = $roleSales->id;
        $newSales->save();

        return redirect('admin/sales');
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
        $getMsales = MUserModel::where('id', '=', $id)->first();

        return view('admin.sales.update', compact('getMsales'));
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
            'nama' => 'required|max:50'
        ]);

        $updateSales = MUserModel::where('id', '=', $id)->update([
            'name' => $request->nama,
            'address' => $request->alamat,
            'birthdate' => $request->birthdate
        ]);

        return redirect('admin/sales');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleteSales = MUserModel::where('id', '=', $id)->delete();

        return redirect()->back();
    }

    public function trash()
    {
        $userTrash = MUserModel::onlyTrashed()->get();
        // dd($userTrash);
        return view('admin.sales.trash', compact('userTrash'));
    }

    public function restore($id)
    {
        $userRestore = MUserModel::withTrashed()->where('id', '=', $id)->restore();

        return redirect()->back();
    }

    public function permanentDelete($id)
    {
        $cekDataSales = DB::table('t_sales_order')->where('sales',$id)->count();
        $cekDataWilyahSales = DB::table('m_wilayah_pembagian_sales')->where('sales',$id)->count();

        if($cekDataSales > 0 ){
            return redirect()->back()->with('message','Data tidak Bisa dihapus');
        }elseif($cekDataWilyahSales > 0){
            return redirect()->back()->with('message','Data tidak Bisa dihapus');
        }

        $permanentDelete = MUserModel::withTrashed()->where('id', '=', $id)->first();
        $permanentDelete->forceDelete();

        return redirect()->back();
    }

    public function salesTarget()
    {
        $roleAdmin = MRoleModel::where('name', 'admin')->first();
        $getMsales = MUserModel::where('role','!=',$roleAdmin->id)->get();
        $dataTargetSales = DB::table('t_target_sales')->get();

        return view('admin.sales.target_create', compact('getMsales'));
    }

    public function indexTarget()
    {
        $getData = TargetSalesModel::get();
        // dd($getData);
        // $ceka = DB::table('t_target_sales')->select('bulan_target')->get();
        // dd($ceka);S

        return view('admin.sales.target_index', compact('getData'));
    }

    public function storeTarget(Request $request)
    {
        $cekBulanSales = TargetSalesModel::get();

        $month = date('m', strtotime($request->bulan_target));
        $year = date('Y', strtotime($request->bulan_target));

        $validationBulan = DB::table('t_target_sales')
        ->where('sales_id','=',$request->sales)
        ->whereMonth('bulan_target', '=', $month)
        ->whereYear('bulan_target', '=', $year)
        ->get();

        // dd($validationBulan);
        foreach($validationBulan as $bulan)
        {
            echo $bulan->bulan_target."<br>";
            echo $month.$year;
        }
        var_dump(count($validationBulan));
        if(count($validationBulan) == 0)
        {
            $storeTarget = new TargetSalesModel;
            $storeTarget->sales_id = $request->sales;
            $storeTarget->bulan_target = $request->bulan_target;
            $storeTarget->target = $request->target;
            $storeTarget->save();
            return redirect('admin/target/sales/index');
        }else{
            return redirect()->back()->with('message','Target Bulan sudah Ada');
        }
    }

    public function salesTargetDelete($id)
    {
        TargetSalesModel::where('id', '=', $id)->delete();

        return redirect()->back();
    }


}
