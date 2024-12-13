<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MCustomerModel;
use App\Models\MCPCustomerModel;
use App\Models\MCustomerOtherAddressModel;



class MCPCustomerController extends Controller
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
        $dataCustomer = MCustomerModel::find($id);
        return view('admin.customer.contact-person.create',compact('dataCustomer'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        MCPCustomerModel::create($request->all());

        return redirect('/admin/customer/'.$request->customer);
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
        $dataCustomer = MCPCustomerModel::join('m_customer','m_customer.id','=','m_cp_customer.customer')
                        ->select('m_customer.name as customer_name','m_customer.id as customer_id','m_cp_customer.*')
                        ->where('customer',$id)
                        ->first();

        return view('admin.customer.contact-person.update',compact('dataCustomer'));
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
            'cp_title' => 'required|max:10',
            'cp_name' => 'required|max:30',
            'cp_jabatan' => 'required',
            'cp_phone' => 'required|max:20',
        ]);

        MCPCustomerModel::where('id',$id)->update($request->except('_token'));

        return redirect('/admin/customer/'.$request->customer);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort(404);
    }
}
