<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;



class MApprovalController extends Controller
{
        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data_approval = DB::table("m_approval")
            ->join("m_user", "m_user.id", "=" , "m_approval.user")
			->select('m_approval.id as approval_id', 'm_user.name')
			->get();

        return view('admin.approval.index', compact('data_approval'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $get_user = DB::table("m_user")
            ->where('role',1)
            ->get();
        return view('admin.approval.create',compact('get_user'));
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
            'user' => 'required',
        ]);

        $data = DB::table("m_approval")
            ->where('user',$request->user)
            ->get();

        if (count($data) == 0) {
            DB::table('m_approval')
            ->insert(['user' => $request->user]);

            return redirect('admin/approval/');
        }else{
            return redirect()->back()->with('message','User telah dimasukkan');
        }
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('m_approval')->where('id', $id)->delete();

        return redirect('admin/approval/');
    }
}
