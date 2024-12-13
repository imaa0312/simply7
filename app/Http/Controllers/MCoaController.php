<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;
use App\Models\MCoaModel;
use Yajra\Datatables\Datatables;

class MCoaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coa = DB::table('m_coa');
        // dd($coa);
        return view('admin.coa.index',compact('coa'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $coa = MCoaModel::orderBy('desc','ASC')->get();

        return view('admin.coa.create',compact('coa'));
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

        //code
        ( $request->parent_code == null )  ? $code = $request->code : $code = $request->parent_code.$request->code;

        //parent-id
        ( $request->parent_id == null) ? $parent_id = 0 : $parent_id =  $request->parent_id;

        //cek code
        $cekCode = MCoaModel::where('code',$code)->count();

        if($cekCode > 0 ){
            return redirect()
            ->back()
            ->with('message','Code Sudah Dipakai');
        }
        //replace-array-request
        $request->merge([
                'code' => $code,
                'parent_id' => $parent_id,
            ]);

        DB::beginTransaction();

        try{

            MCoaModel::create($request->except('_token','_method','parent_code'));

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect()->route('coa.index');
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
        $coa = MCoaModel::find($id);
        $coaParent = MCoaModel::orderBy('desc','ASC')->get();
        // dd($coa);
        return view('admin.coa.update',compact('coa','coaParent'));
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
        MCoaModel::where('id',$id)->update($request->except('_token','_method','code'));

        return redirect()->route('coa.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cekParentId = MCoaModel::where('parent_id',$id)->count();
        if($cekParentId > 0){
             return redirect()->back()->with('message','Data tidak Bisa dihapus karena punya cabang');
        }

        $cekTransaksi = DB::table('t_cash_bank')->where('id_coa',$id)->count();

        if($cekTransaksi > 0){
             return redirect()->back()->with('message','Data tidak Bisa dihapus karena Dipakai transaksi');
        }

        MCoaModel::where('id',$id)->delete();
        // var_dump($cekParentId);
        return redirect()->back()->with('message-success','Data berhasil dihapus');
    }

    public function getParent($id)
    {
        return Response::json(MCoaModel::find($id));
    }
    public function apiCoa()
    {
        $coa = MCoaModel::orderBy('code')->get();

        return Datatables::of($coa)
        ->addColumn('action', function ($coa) {
            return '<a href="'. route('coa.edit',$coa->id) .'" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ubah"><i class="fa fa-edit"></i></a>'.'&nbsp;'.
            '<a href="'. route('coa.delete',$coa->id) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Hapus">
                <i class="fa fa-trash"></i>
            </a>';
            })

        ->addIndexColumn()
        ->rawColumns(['action'])
        ->make(true);
    }
}
