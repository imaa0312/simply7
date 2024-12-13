<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use App\Models\TargetSalesModel;
use DB;
use carbon;
use Yajra\Datatables\Datatables;

class MUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getMUser = DB::table('m_user')
            ->select('m_user.*', 'm_user.email','m_user.address', 'm_user.role', 'm_role.name as role_name')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->get();

        // dd($getMUser);

        return view('admin.user.index', compact('getMUser'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $getMRole = DB::table('m_role')
            ->get();
        return view('admin.user.create', compact('getMRole'));
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
            'nama' => 'required|max:50',
            'username' => 'unique:m_user',
            'email' => 'required|email|unique:m_user',
            'role' => 'required',
            'password' => 'required',
        ]);

        //dd($request->all());
        //$roleSales  = MRoleModel::where('name', 'Sales')->first();
        $newSales = new MUserModel;
        $newSales->name = $request->nama;
        $newSales->username = $request->username;
        $newSales->email = $request->email;
        $newSales->address = $request->alamat;
        $newSales->birthdate = date('Y-m-d', strtotime($request->birthdate));
        $newSales->password =  bcrypt(str_replace(' ', '', $request->password));
        $newSales->role = $request->role;
        $newSales->save();

        return redirect('admin/user');
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
        $dataUser = MUserModel::where('id', '=', $id)->first();
        $getMRole = DB::table('m_role')
            ->get();

        return view('admin.user.update', compact('dataUser', 'getMRole'));
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
            'nama' => 'required|max:50',
            'email' => 'email|unique:m_user,email,'.$id,
            'role' => 'required',
            'username' => 'unique:m_user,username,'.$id,
            //'password' => 'required',
        ]);

        $updateSales = MUserModel::where('id', '=', $id)->update([
            'name' => $request->nama,
            'address' => $request->alamat,
            'birthdate' => date('Y-m-d', strtotime($request->birthdate)),
            'role' => $request->role
        ]);

        return redirect('admin/user');
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

    public function activeInActive($id)
    {
        $user = DB::table('m_user')->where('id', $id)->first();
        // dd($user);

        if($user->status == 'active'){
            DB::table('m_user')->where('id', $id)->update(["status" => "inactive"]);
        }else{
            DB::table('m_user')->where('id', $id)->update(["status" => "active"]);
        }

        return redirect()->back();
    }

    public function apiUser()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);
        $user = DB::table('m_user')
        ->leftjoin('m_role','m_role.id','m_user.role')
        ->select('m_user.id','m_user.name','m_user.username','m_user.email','m_user.status','m_user.address','m_role.name as role')

        ->get();

        return Datatables::of($user)
            ->addColumn('action', function ($user) {
                $buttonClass = ($user->status == 'active') ? "btn-danger" : "btn-success";
                return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'.url('/admin/user/'.$user->id.'/edit').'" class="btn btn-warning  btn-sm"><i class="fa fa-edit"></i></a>'.

                            '&nbsp'.

                            '<a href="'.url('/admin/user-status/'.$user->id).'" class="btn btn-sm '.$buttonClass.'"><i class="fa fa-eye"></i></a>'.

                        '</td>'.
                    '</tr>'.
                '</table>';

            })
            ->addIndexColumn()
            ->rawColumns(['action'])
            ->make(true);
    }
}
