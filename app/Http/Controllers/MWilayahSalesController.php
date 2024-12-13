<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MWilayahSalesModel;
use App\Models\MWilayahPembagianSalesModel;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use DB;



class MWilayahSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataWilayah = DB::table('m_wilayah_sales')
                            ->select('m_wilayah_sales.id','m_wilayah_sales.name as wilayah_name','m_user.name as sales_name')
                            ->join('m_wilayah_pembagian_sales','m_wilayah_pembagian_sales.wilayah_sales','=','m_wilayah_sales.id')
                            ->join('m_user','m_user.id','=','m_wilayah_pembagian_sales.sales')
                            ->orderBy('m_wilayah_sales.name')
                            ->get() ;
        // dd($dataWilayah);
        return view('admin.wilayah.index',compact('dataWilayah'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.wilayah.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:30'
        ]);
        // dd($request->all());
        $cekData = MWilayahSalesModel::where('name',$request->name)->count();
        if($cekData > 0 ){
            return redirect()->back()->with('message','Data Sudah Ada');
        }

        MWilayahSalesModel::create([
            'name' => strtoupper($request->name),
        ]);

        return redirect('admin/wilayah-sales');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $roleSales = MRoleModel::where('name','Sales')->first();
        $dataWilayah = MWilayahSalesModel::find($id);
        $dataSales  = MUserModel::where('role','=',$roleSales->id)->get();
        $dataWilayahSales = DB::table('m_wilayah_pembagian_sales')
                            ->select('m_wilayah_pembagian_sales.id as wps_id','m_wilayah_pembagian_sales.wilayah_sales',
                            'm_user.id as sales_id','m_user.name as name_sales','m_user.email','m_user.address','m_wilayah_sales.id as wilayah_id','m_wilayah_sales.name as name_wilayah')
                            ->join('m_user','m_user.id','=','m_wilayah_pembagian_sales.sales')
                            ->join('m_wilayah_sales','m_wilayah_sales.id','=','m_wilayah_pembagian_sales.wilayah_sales')
                            ->where('m_wilayah_pembagian_sales.wilayah_sales',$id)
                            ->get();
        return view('admin.sales.detail-wilayah-sales',compact('dataWilayahSales','dataWilayah','dataSales'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dataWilayah = MWilayahSalesModel::find($id);
        return view('admin.wilayah.update',compact('dataWilayah'));
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
        $this->validate($request,[
            'name' => 'required|max:30'
        ]);

        MWilayahSalesModel::where('id',$id)->update([
            'name' => $request->name,
        ]);

        return redirect('admin/wilayah-sales');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cek = DB::table('m_wilayah_pembagian_sales')->where('wilayah_sales', '=', $id)->count();
        if ($cek > 0) {
            return redirect()->back()->with('message', 'Data Tidak Bisa Dihapus Karena Sudah Dipakai Untuk Pembagian Wilayah Sales');
        }
        DB::table('m_wilayah_sales')->where('id',$id)->delete();

        return redirect()->back()->with('message-success', 'Data Berhasil Dihapus');
    }

    public function tempatSales(Request $request)
    {
        // dd($request->all());
        $cekWilayahsales = MWilayahPembagianSalesModel::where('wilayah_sales',$request->wilayah)
                            ->where('sales',$request->sales)->get();
        if( count($cekWilayahsales) > 0 )
        {
            return redirect()->back()->with('message','Sales Sudah Masuk Ke Wilayah ini');
        }else{
            MWilayahPembagianSalesModel::create([
                'sales' => $request->sales,
                'wilayah_sales' => $request->wilayah,
            ]);

            return redirect()->back()->with('message-success','Berhasil Menempatan Sales');

        }
    }
}
