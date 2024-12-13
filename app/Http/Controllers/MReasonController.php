<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\MReasonModel;

class MReasonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reason = MReasonModel::orderBy('id','DESC')->get();

        return view('admin.catatan.alasan.index',compact('reason'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.catatan.alasan.create');
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
            'reason' => 'required|max:50|unique:m_reason'
        ]);

        MReasonModel::create($request->all());

        return redirect('admin/tipe-alasan-cancel');

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
        $dataAlasan = MReasonModel::find($id);
        return view('admin.catatan.alasan.update',compact('dataAlasan'));
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
            'reason' => 'required|max:50|unique:m_reason'
        ]);

        MReasonModel::where('id',$id)->update($request->except('_token','_method'));

        return redirect('admin/tipe-alasan-cancel');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        MReasonModel::where('id',$id)->delete();

        return redirect('admin/tipe-alasan-cancel');
    }
}
