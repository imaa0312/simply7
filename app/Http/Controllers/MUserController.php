<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use App\Models\TargetSalesModel;
use DB;
use Hash;
use Datatables;

class MUserController extends Controller
{
    public function index()
    {
        $getMUser = MUserModel::select('m_user.*', 'm_role.name as role_name')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->get();

        return view('users', compact('getMUser'));
    }

    public function getRole()
    {
        $data = MRoleModel::get();
        $prov = '<label>Role</label>
                <select class="form-control"  id="roles" name="roles">
                <option>Choose Roles Permission</option>';
        foreach($data as $dt){
            $prov .= '<option value="'.$dt->id.'">'.$dt->name.'</option>';
        }
        $prov .= '</select>';

        if($data){
            $return = array(
                "roles" => $prov,
                "status" => true
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data not found"
            );
        }

        echo json_encode($return);
    }

    public function usersDatatables(){
        $data = MUserModel::select('m_user.*', 'm_role.name as role_name')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-users" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-users" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-users" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-users" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-square-check"></i>
                        </a>
                    </div>';
            })
            ->editColumn('status', function($row){
                if($row->status == 0)
                    return '<span class="badge rounded-pill bg-danger">Deleted</span>';
                else
                    return '<span class="badge rounded-pill bg-success">Active</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
    
    public function storeUsers(Request $request)
    {
        $id = $request->input('users_id');
        $date_arr = explode("/", $request->birthdate);
        $date = date('Y-m-d', strtotime($date_arr[2]."-".$date_arr[1]."-".$date_arr[0]));

        if($id == ""){
            $val = \Validator::make($request->all(), [
                'password' => 'required|confirmed|min:4',
            ]);

            if ($val->fails()) {
                $return = array(
                    "status" => false,
                    "msg" => "Password is required and must be confirmed"
                );
            } else {
                $dataUser = new MUserModel;
                $dataUser->name = $request->user_name;
                $dataUser->username = $request->uname;
                $dataUser->phone = $request->phone;
                $dataUser->address = $request->address;
                $dataUser->birthdate = $date;
                $dataUser->password =  bcrypt(str_replace(' ', '', $request->password));
                $dataUser->role = $request->roles;
                $dataUser->save();
                $dataUser->status = 1;

                if($dataUser){
                    $return = array(
                        "status" => true,
                        "msg" => "Successfully saved"
                    );
                } else {
                    $return = array(
                        "status" => false,
                        "msg" => "Oops! Something wen't wrong"
                    );
                }
            }
        } else {
            $dataUser = MUserModel::find($id);
            // $hash = bcrypt(str_replace(' ', '', $request->password));

            if(Hash::check($request->password, $dataUser->password)){
                $dataUser->name = $request->user_name;
                $dataUser->username = $request->uname;
                $dataUser->phone = $request->phone;
                $dataUser->address = $request->address;
                $dataUser->birthdate = $date;
                $dataUser->role = $request->roles;
                $dataUser->save();

                if($dataUser){
                    $return = array(
                        "status" => true,
                        "msg" => "Successfully saved"
                    );
                } else {
                    $return = array(
                        "status" => false,
                        "msg" => "Oops! Something wen't wrong"
                    );
                }
            } else {
                $return = array(
                    "status" => false,
                    "msg" => "Password didn't match"
                );
            }
        }

        echo json_encode($return);
    }
    
    public function editUsers($id)
    {
        $dataUser = MUserModel::select('m_user.*', 'm_role.name as role_name')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->find($id);

        $roles = MRoleModel::get();
        $cat = '<label>Role</label>
            <select class="form-control" id="roles" name="roles">';
        foreach($roles as $kat){
            $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $cat .= '</select>';

        if($dataUser){
            $return = array(
                "role_id" => $dataUser->role,
                "name" => $dataUser->name,
                "username" => $dataUser->username,
                "phone" => $dataUser->phone,
                "address" => $dataUser->address,
                "birthdate" => date('d/m/Y', strtotime($dataUser->birthdate)),
                "role" => $cat,
                "status" => true
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data not found"
            );
        }

        echo json_encode($return);
    }
    
    public function deleteUsers($id)
    {
        $user = MUserModel::find($id);
        $user->status = 0;
        $user->save();

        if($user){
            $return = array(
                "status" => true,
                "msg" => "Successfully deleted"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }
    
    public function restoreUsers($id)
    {
        $user = MUserModel::find($id);
        $user->status = 1;
        $user->save();

        if($user){
            $return = array(
                "status" => true,
                "msg" => "Successfully deleted"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }





    public function index()
    {
        $getMUser = MUserModel::select('m_user.*', 'm_role.name as role_name')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->get();

        return view('users', compact('getMUser'));
    }

    public function getRole()
    {
        $data = MRoleModel::get();
        $prov = '<label>Role</label>
                <select class="form-control"  id="roles" name="roles">
                <option>Choose Roles Permission</option>';
        foreach($data as $dt){
            $prov .= '<option value="'.$dt->id.'">'.$dt->name.'</option>';
        }
        $prov .= '</select>';

        if($data){
            $return = array(
                "roles" => $prov,
                "status" => true
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data not found"
            );
        }

        echo json_encode($return);
    }

    public function usersDatatables(){
        $data = MUserModel::select('m_user.*', 'm_role.name as role_name')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-users" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-users" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-users" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-users" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-square-check"></i>
                        </a>
                    </div>';
            })
            ->editColumn('status', function($row){
                if($row->status == 0)
                    return '<span class="badge rounded-pill bg-danger">Deleted</span>';
                else
                    return '<span class="badge rounded-pill bg-success">Active</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
    
    public function storeUsers(Request $request)
    {
        $id = $request->input('users_id');
        $date_arr = explode("/", $request->birthdate);
        $date = date('Y-m-d', strtotime($date_arr[2]."-".$date_arr[1]."-".$date_arr[0]));

        if($id == ""){
            $val = \Validator::make($request->all(), [
                'password' => 'required|confirmed|min:4',
            ]);

            if ($val->fails()) {
                $return = array(
                    "status" => false,
                    "msg" => "Password is required and must be confirmed"
                );
            } else {
                $dataUser = new MUserModel;
                $dataUser->name = $request->user_name;
                $dataUser->username = $request->uname;
                $dataUser->phone = $request->phone;
                $dataUser->address = $request->address;
                $dataUser->birthdate = $date;
                $dataUser->password =  bcrypt(str_replace(' ', '', $request->password));
                $dataUser->role = $request->roles;
                $dataUser->save();
                $dataUser->status = 1;

                if($dataUser){
                    $return = array(
                        "status" => true,
                        "msg" => "Successfully saved"
                    );
                } else {
                    $return = array(
                        "status" => false,
                        "msg" => "Oops! Something wen't wrong"
                    );
                }
            }
        } else {
            $dataUser = MUserModel::find($id);
            // $hash = bcrypt(str_replace(' ', '', $request->password));

            if(Hash::check($request->password, $dataUser->password)){
                $dataUser->name = $request->user_name;
                $dataUser->username = $request->uname;
                $dataUser->phone = $request->phone;
                $dataUser->address = $request->address;
                $dataUser->birthdate = $date;
                $dataUser->role = $request->roles;
                $dataUser->save();

                if($dataUser){
                    $return = array(
                        "status" => true,
                        "msg" => "Successfully saved"
                    );
                } else {
                    $return = array(
                        "status" => false,
                        "msg" => "Oops! Something wen't wrong"
                    );
                }
            } else {
                $return = array(
                    "status" => false,
                    "msg" => "Password didn't match"
                );
            }
        }

        echo json_encode($return);
    }
    
    public function editUsers($id)
    {
        $dataUser = MUserModel::select('m_user.*', 'm_role.name as role_name')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->find($id);

        $roles = MRoleModel::get();
        $cat = '<label>Role</label>
            <select class="form-control" id="roles" name="roles">';
        foreach($roles as $kat){
            $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $cat .= '</select>';

        if($dataUser){
            $return = array(
                "role_id" => $dataUser->role,
                "name" => $dataUser->name,
                "username" => $dataUser->username,
                "phone" => $dataUser->phone,
                "address" => $dataUser->address,
                "birthdate" => date('d/m/Y', strtotime($dataUser->birthdate)),
                "role" => $cat,
                "status" => true
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data not found"
            );
        }

        echo json_encode($return);
    }
    
    public function deleteUsers($id)
    {
        $user = MUserModel::find($id);
        $user->status = 0;
        $user->save();

        if($user){
            $return = array(
                "status" => true,
                "msg" => "Successfully deleted"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }
    
    public function restoreUsers($id)
    {
        $user = MUserModel::find($id);
        $user->status = 1;
        $user->save();

        if($user){
            $return = array(
                "status" => true,
                "msg" => "Successfully deleted"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }
}
