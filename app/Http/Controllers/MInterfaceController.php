<?php

namespace App\Http\Controllers;

use App\Models\MCoaModel;
use App\Models\MInterfaceModel;
use Illuminate\Http\Request;
use DB;

class MInterfaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $interface = MInterfaceModel::orderBy('var','asc')->get();

        return view('admin.interface.index',compact('interface'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        abort(404);
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
        $data = MInterfaceModel::find($id);

        $code_coa = explode(",", $data->code_coa);

        $query = DB::table('m_coa');
        $query->orderBy('code','ASC');

        $count = $query->count();
        $take = ceil($count/3);

        $jmlRow = $take;

        $offset1 = $take*(1-1);
        $offset2 = $take*(2-1);
        $offset3 = $take*(3-1);

        //$dataCoa = $query->skip($offset1)->take($take)->get();

        $dataCoa = $query->get();

        foreach ($dataCoa as $key => $datacoavalue) {
            $checked = false;
            foreach ($code_coa as $oldcoa) {
                if( $oldcoa == $datacoavalue->code  ){
                    $checked = true;
                }
            }
            $datacoavalue->checked = $checked;
        }

        //$take = 20;
        //$offset = $take*($page-1);
        $dataCoa1 = array_slice($dataCoa->toArray(), $offset1, $take);
        $dataCoa2 = array_slice($dataCoa->toArray(), $offset2, $take);
        $dataCoa3 = array_slice($dataCoa->toArray(), $offset3, $take);

        // dd($dataCoa,$code_coa);
        return view('admin.interface.update',compact('data','dataCoa1','dataCoa2','dataCoa3','code_coa','jmlRow'));
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
        //decode-json-stringify
        $coaDecode = json_decode($request->code_coa);
        
        //implode-delimiter(,)
        $code_coa = implode(",", $coaDecode);

        //replace-array $request
        $request->merge(['code_coa' => $code_coa]);

        // dd($coaImplode);
        MInterfaceModel::where('id',$id)->update($request->except('_token','_method'));

        return redirect()->route('interface.index');
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

    public function interfaceDetail($id)
    {
        $data_interface = DB::table('m_interface')
            ->where('id',$id)
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

        //dd($data);

        return view('admin.interface.detail',compact('data','data_interface'));
    }
}
