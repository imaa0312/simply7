<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class MCompanyProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('m_company_profile')->get();

        return view('admin.company.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $company = DB::table('m_company_profile')
            ->where('company_code','TM')
            ->first();

        $namefile = null;
        if($request->hasFile('file')) {
            if ($company->photo != null || $company->photo != '') {
                unlink('img/'.$company->photo);
            }
            $namefile = $request->file->getClientOriginalName();
            $request->file->move('img/',$namefile);
        }
        else{
            $namefile = $company->photo;
        }

        DB::table('m_company_profile')
            ->where('company_code','TM')
            ->update([
                'name' => $request->name,
                'address' => $request->address,
                'postal_code' => $request->kode_pos,
                'email' => $request->email,
                'phone_1' => $request->telepon_1,
                'phone_2' => $request->telepon_2,
                'photo' => $namefile,
            ]);

        return redirect('admin/company-profile');
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
        $company = DB::table('m_company_profile')->where('id', $id)->first();
        return view('admin.company.edit', compact('company'));
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
        DB::table('m_company_profile')
        ->where('id',$id)
        ->update([
            'name' => $request->name,
            'address' => $request->address,
            'postal_code' => $request->kode_pos,
            'email' => $request->email,
            'phone_1' => $request->telepon_1,
            'phone_2' => $request->telepon_2,
            'npwp' => $request->npwp,
            'company_director' => $request->company_director,
        ]);

        return redirect('admin/company-profile');
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
