<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MCustomerNPWPModel;



class MCustomerNPWPController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        return view('admin.customer.npwp.create',compact('id'));
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
            'name' => 'required|max:20',
            'npwp' => 'required|max:15',
            // 'tax_object_zipcode' => 'required|max:6'
        ]);

        MCustomerNPWPModel::create($request->all());

        return redirect('admin/customer/'.$request->customer);
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
        $dataNpwp = MCustomerNPWPModel::find($id);

        return view('admin.customer.npwp.update',compact('dataNpwp'));
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
            'name' => 'required|max:20',
            'npwp' => 'required|max:15',
            'tax_object_zipcode' => 'required|max:6'
        ]);

        MCustomerNPWPModel::where('id',$id)->update($request->except('_token','_method'));

        return redirect('admin/customer/'.$request->customer);
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
