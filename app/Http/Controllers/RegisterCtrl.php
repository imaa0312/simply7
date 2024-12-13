<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MCustomerModel;
use App\Models\MRoleModel;
use App\Models\MUserModel;
use Illuminate\Http\Request;
use Mail;
use DB;
use App\Mail\RegisterMail;


class RegisterCtrl extends Controller
{
    public function create(Request $request)
    {

        // dd($request->all());
        $this->validate($request, [
            'name' => 'required|max:50',
            'username' => 'unique:m_user',
            'email' => 'required|email|unique:m_user',
            'password' => 'required',
        ]);

        // dd($request->all());
        DB::beginTransaction();
        try{
            //insert-to-table-user
            $roleCustomer  = MRoleModel::where('name', 'Customer')->first();

            $newUser = new MUserModel();
            $newUser->name = $request->name;
            $newUser->username = $request->email;
            $newUser->email = $request->email;
            $newUser->password =  bcrypt(trim($request->password));
            $newUser->role = $roleCustomer->id;
            $newUser->status = "inactive";
            $newUser->created_at = date('Y-m-d H:i:s'); 
            $newUser->save();

            $newCustomer = new MCustomerModel();
            $newCustomer->code = $this->setCodeCustomer();
            $newCustomer->id_user = $newUser->id;
            $newCustomer->name = $request->name;
            $newCustomer->main_address = $request->address;
            $newCustomer->main_kelurahan = $request->kelurahan;
            $newCustomer->main_email = $request->email;
            $newCustomer->gudang = 2;
            $newCustomer->credit_limit_days = 0;
            $newCustomer->save();

            //insert-to-table-customer
            Mail::to($request->email)->send(new RegisterMail($newUser));
            DB::commit();
            //Auth::loginUsingId($newUser->id);
            $type_message= "success_message";
            $message = "Register berhasil";
            return redirect('masuk')->with("$type_message", "$message");
        }catch(\Exception $e){
            dd($e);
            DB::rollback();
            $type_message= "error_message";
            $message = "Register gagal, silahkan coba lagi";
            return redirect()->back()->with("$type_message", "$message");
        }
    }

    protected function setCodeCustomer()
    {
        $getLastCode = DB::table('m_customer')
                ->select('id')
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->first();
        $getLastCode = $getLastCode +1;

        $nol = null;
        if(strlen($getLastCode) == 1){
            $nol = "000000";
        }elseif(strlen($getLastCode) == 2){
            $nol = "00000";
        }elseif(strlen($getLastCode) == 3){
            $nol = "0000";
        }elseif(strlen($getLastCode) == 4){
            $nol = "000";
        }elseif(strlen($getLastCode) == 5){
            $nol = "00";
        }elseif(strlen($getLastCode) == 6){
            $nol = "0";
        }else{
            $nol = null;
        }

        return 'CST'.$nol.$getLastCode;
    }
}
