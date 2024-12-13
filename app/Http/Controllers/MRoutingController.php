<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class MRoutingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coa = DB::table('m_routing');

        return view('admin.routing.index',compact('coa'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $coa = DB::table('m_routing')
            ->get();

        $setSj = $this->setCode();

        return view('admin.routing.create',compact('coa','setSj'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $setCode = $this->setCode();

            DB::table('m_routing')
                ->insert([
                    'code' => $setCode,
                    'name'=> $request->name,
                    'sdlc'=> $request->sdlc,
                    'soc'=> $request->soc,
                ]);
        return redirect('accounting/routing');
    }

    protected function setCode()
    {
        $getLastCode = DB::table('m_routing')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'RTG'.$dataDate.$nol.$getLastCode;
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
        $data = DB::table('m_routing')
        ->select('m_routing.*')
        ->where('code',$code)
        ->first();
        // dd($coa);
        return view('admin.routing.update',compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // dd($request->all());
            DB::table('m_routing')
            ->where('code',$request->code)
                ->update([
                    'name'=> $request->name,
                    'sdlc'=> $request->sdlc,
                    'soc'=> $request->soc,
                ]);
        return redirect('accounting/routing');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($code)
    {
      $deletePo = DB::table('m_routing')
                    ->where('code',$code)
                    ->delete();

      return view('admin.routing.index',compact('deletePo'));
    }

    public function apiRouting()
    {
        $code = DB::table('m_routing')
            ->orderBy('m_routing.code', 'desc')
            ->get();
        foreach ($code as $data) {
            $wo = true;
            $cekCode = DB::table('t_work_order')
                ->where('routing_code',$data->code)
                ->get();
            // dd($cekCode);
            if (count($cekCode) > 0 ) {
                $wo = false; // jika ada false
            }
            $data->routing = $wo;
        }

        return Datatables::of($code)
        ->addColumn('action', function ($code) {
            if( $code->routing == true){
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                    '<td>'.
                    '<a href="'. url('accounting/routing/delete/'.$code->code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ? '".')" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Hapus '. $code->code .'"><span class="fa fa-trash"></span></a>'.'&nbsp;'.
                    '<a href="'. url('accounting/routing/edit/'.$code->code) .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $code->code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '</td>'.
                '</tr>'.
            '</table>';
        }
        else{
            return '<table id="tabel-in-opsi">'.
                '<tr>'.
                    '<td>'.
                    '<a href="'. url('accounting/routing/edit/'.$code->code) .'" class="btn btn-sm btn-primary"data-toggle="tooltip" title="Ubah '. $code->code .'"><span class="fa fa-edit"></span></a>'.'&nbsp;'.
                    '</td>'.
                '</tr>'.
            '</table>';
            }
        })
            ->editColumn('sdlc', function($code){
                    return 'Rp. '.number_format($code->sdlc,2,'.','.');
                      })
            ->editColumn('soc', function($code){
                    return 'Rp. '.number_format($code->soc,2,'.','.');
                      })
            ->editColumn('name', function($code){
                    return ucfirst($code->name);
                    })

        ->addIndexColumn()
        ->rawColumns(['action'])
        ->make(true);
    }

    public function getRouting($id)
    {
        $dataPo = DB::table('m_routing')
            ->select('m_routing.sdlc','m_routing.soc')
            ->where('m_routing.id',$id)
            ->first();

        return Response::json($dataPo);
    }

    public function getRow(Request $request)
    {
        $result = DB::table('m_produk')
            ->select('m_produk.*','m_satuan_unit.code as code_unit')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->where('m_produk.id', $request->id)
            ->first();

            return Response::json($result);
    }


}
