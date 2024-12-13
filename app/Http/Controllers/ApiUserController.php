<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use App\Models\MUserModel;
use App\Models\MRoleModel;
//use Hash;
use Auth;
use DB;



class ApiUserController extends Controller
{
	public function listUser(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 08-12-2017
	    * Fungsi       : list user
	    * Tipe         : update
	    */
		$return = [];
		$data = DB::table('m_user')
			->select('m_user.id','m_user.name','m_user.email','m_user.address','m_user.birthdate','m_role.name as role')
			->join("m_role", "m_user.role", "=" , "m_role.id")
			->where('m_role.name','Sales')
			->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Sales not found';
		}

		return response($return);
	}

	public function detailUser(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 04-01-2018
	    * Fungsi       : detail user
	    * Tipe         : update
	    */
		$return = [];
		$id = $request->input("id");
		$data = DB::table('m_user')
			->select('m_user.id','m_user.name','m_user.email','m_user.address','m_user.birthdate','m_role.name as role')
			->join("m_role", "m_user.role", "=" , "m_role.id")
			->where('m_user.id', $id)
			->where('m_role.name','Sales')
			->first();

		if (count($data) !== 0) {
			$target_data = DB::table('m_target_sales')
				->select('*')
				->where('sales', $id)
				->whereYear('month', '=', date("Y"))
	            ->whereMonth('month', '=', date("m"))
				->first();

			$target = 0;
			if (count($target_data) !== 0) {
				$target = $target_data->monthly_target;
			}

			$target_achieved = DB::table('t_sales_order')
				->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
				->where('sales', $id)
				->where(function ($query) {
				    $query->where('status_aprove','approved')
				          ->orWhere('status_aprove','closed');
					})
				->whereYear('so_date', '=', date("Y"))
	            ->whereMonth('so_date', '=', date("m"))
				->sum('d_sales_order.total');

			$month_min_1 = date("Y-n-j", strtotime("first day of previous month"));
			$target_achieved_min_1 = DB::table('t_sales_order')
				->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
				->where('sales', $id)
				->where(function ($query) {
				    $query->where('status_aprove','approved')
				          ->orWhere('status_aprove','closed');
					})
				->whereYear('so_date', '=', date("Y", strtotime($month_min_1)))
	            ->whereMonth('so_date', '=', date("m", strtotime($month_min_1)))
				->sum('d_sales_order.total');

			$month_min_2 = date("Y-n-j", strtotime("first day of previous month"));
			$month_min_2 = date("Y-m-d",strtotime( $month_min_2."-1 month"));

			$target_achieved_min_2 = DB::table('t_sales_order')
				->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
				->where('sales', $id)
				->where(function ($query) {
				    $query->where('status_aprove','approved')
				          ->orWhere('status_aprove','closed');
					})
				->whereYear('so_date', '=', date("Y", strtotime($month_min_2)))
	            ->whereMonth('so_date', '=', date("m", strtotime($month_min_2)))
				->sum('d_sales_order.total');

			$month_min_3 = date("Y-n-j", strtotime("first day of previous month"));
			$month_min_3 = date("Y-m-d",strtotime( $month_min_3."-2 month"));

			$target_achieved_min_3 = DB::table('t_sales_order')
				->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
				->where('sales', $id)
				->where(function ($query) {
				    $query->where('status_aprove','approved')
				          ->orWhere('status_aprove','closed');
					})
				->whereYear('so_date', '=', date("Y", strtotime($month_min_3)))
	            ->whereMonth('so_date', '=', date("m", strtotime($month_min_3)))
				->sum('d_sales_order.total');

			$target_achieved_today = DB::table('t_sales_order')
				->join("d_sales_order", "d_sales_order.so_code", "=" , "t_sales_order.so_code")
				->where('sales', $id)
				->where(function ($query) {
				    $query->where('status_aprove','approved')
				          ->orWhere('status_aprove','closed');
					})
				->whereYear('so_date', '=', date("Y"))
	            ->whereMonth('so_date', '=', date("m"))
	            ->whereDay('so_date', '=', date("d"))
				->sum('d_sales_order.total');

			$point_sales = DB::table('m_point_sales')
				->where('sales', $id)
				->sum('point');

			// $target_achieved = 0;
			// if (count($achieved_data) !== 0) {
			// 	$target_achieved = $achieved_data->total;
			// }

			$data->target = $target;
			$data->daily_target = $target / 25;
			$data->target_achieved = $target_achieved;
			$data->target_achieved_min_1 = $target_achieved_min_1;
			$data->target_achieved_min_2 = $target_achieved_min_2;
			$data->target_achieved_min_3 = $target_achieved_min_3;
			$data->target_achieved_today = $target_achieved_today;
			$data->point = $point_sales;

			$senin = date('Y-m-d',strtotime('Monday this week', strtotime(date('Y-m-d'))));
			$data->senin_minggu_ini = $senin;
			$data->promo = [];

			$dataHeader = DB::table('t_header_promo')
				->join("t_promo", "t_promo.header", "=" , "t_header_promo.id")
				->join("t_distribusi_promo", "t_distribusi_promo.promo", "=" , "t_promo.id")
				->select('t_header_promo.id', 't_header_promo.name')
				->where('t_distribusi_promo.sales', $id)
				->get();

			if (count($dataHeader) > 0) {
				$data->promo = $dataHeader;
			}else{
				$data->promo = [];
			}

		}

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Sales not found';
		}

		return response($return);
	}

	public function listPromo(request $request)
	{
		$return = [];
		$id_header = $request->input("id_header");
		$sales = $request->input("sales");

		$data = DB::table('t_promo')
			->select('t_promo.id as id_promo','t_promo.code', 't_promo.judul', 't_promo.deskripsi','t_promo.start_date','t_promo.end_date')
			->join("t_distribusi_promo", "t_distribusi_promo.promo", "=" , "t_promo.id")
			->where('t_distribusi_promo.sales', $sales)
			->where('t_promo.header', $id_header)
			->where('start_date', '<=' , date('Y-m-d'))
			->where('end_date', '>=' , date('Y-m-d'))
			->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Promo not found';
		}

		return response($return);
	}

	public function updateUser(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : update user
	    * Tipe         : update
	    */

		$return = [];
		$id = $request->input("id");
		$name = $request->input("name");
		$email = $request->input("email");
		$address = $request->input("address");
		$birthdate = $request->input("birthdate");

		if ($name == "" || $name == null || $email == "" || $email == null || $address == "" || $address == null || $birthdate == "" || $birthdate == null ){
			$return["success"] = false;
            $return["msgServer"] = "Please fill the blank field.";
		}else{
			DB::table('m_user')
        	->where('id', $id)
        	->update(['name' => $name,
				    'email' => $email,
				    'address' => $address,
				    'birthdate' => $birthdate
					]);

        	$return['success'] = true;
			$return['msgServer'] = 'Update success';
		}

		return response($return);
	}

	public function changePassword(Request $request)
    {
    	/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : change password
	    * Tipe         : update
	    */
    	$return = [];
        $email = $request->input("email");
        $oldpassword = $request->input("oldpass");
        $newpassword = $request->input("newpass");
		$repassword = $request->input("repass");

		if ($email == "" || $email == null || $oldpassword == "" || $oldpassword == null || $newpassword == "" || $newpassword == null || $repassword == "" || $repassword == null ){
			$return["success"] = false;
            $return["msgServer"] = "Please fill the blank field.";
		}else if ($newpassword !== $repassword) {
            $return["success"] = false;
            $return["msgServer"] = "Your password doesn't match.";
        }else{
        	$data_user = DB::table('m_user')->where('email', $email)->first();
            if ($data_user) {
                if(Hash::check($oldpassword, $data_user->password)){
                	 DB::table('m_user')
			            ->where('email', $email)
			            ->update(array('password' => Hash::make($newpassword)));
			        $return["success"] = true;
			        $return["msgServer"] = "Update password success.";
                } else {
                    $return['success'] = false;
                    $return['msgServer'] = "Old password is wrong.";
                }
            } else {
                $return['success'] = false;
                $return['msgServer'] = "Email is wrong.";
            }
        }
        return response($return);
    }

    public function loginUser(Request $request)
    {
    	/**
	    * Programmer   : Kris
	    * Tanggal      : 08-12-2017
	    * Fungsi       : login user
	    * Tipe         : update
	    */

    	$return     = [];
        $username   = $request->input("username");
        $password   = $request->input("password");
        $data_user = '';

        try {
        	$cekusername = 0;
        	$get_by_email = DB::table('m_user')
	        	->select('m_user.id','m_user.name','m_user.username','m_user.email','m_user.address','m_user.birthdate','m_role.name as role','m_user.login','m_user.password')
				->join("m_role", "m_user.role", "=" , "m_role.id")
        		->where('m_user.email', $username)
        		->where('m_role.name', 'Sales')
        		->first();

        	if (count($get_by_email) !== 0) {
        		$cekusername = 1;
        		$data_user = $get_by_email;
        	}else{
        		$get_by_username = DB::table('m_user')
		        	->select('m_user.id','m_user.name','m_user.username','m_user.email','m_user.address','m_user.birthdate','m_role.name as role','m_user.login','m_user.password')
					->join("m_role", "m_user.role", "=" , "m_role.id")
	        		->where('m_user.username', $username)
	        		->where('m_role.name', 'Sales')
	        		->first();
	        	if (count($get_by_username) !== 0) {
	        		$cekusername = 1;
	        		$data_user = $get_by_username;
	        	}
        	}

            if ($cekusername == 1) {
            	if ($data_user->login == 0) {
            		if(Hash::check($password, $data_user->password)){
            			$point_sales = DB::table('m_point_sales')
							->where('sales', $data_user->id)
							->sum('point');

		               	$data = [
		                "id"            => $data_user->id,
		                "name"     		=> $data_user->name,
		                "email"         => $data_user->email,
		                "address"       => $data_user->address,
		                "role"         	=> $data_user->role,
		                "point"         => $point_sales,
		                ];

		                DB::table('m_user')
			            	->where('id', $data_user->id)
			            	->update(['login' => 1]);

	                    $return['success'] = true;
	                    $return['msgServer'] = $data;
	                } else {
	                    $return['success'] = false;
	                    $return['msgServer'] = "Password is wrong.";
	                }
            	} else{
            		$return['success'] = false;
                	$return['msgServer'] = "Already Login";
            	}
            } else {
                $return['success'] = false;
                $return['msgServer'] = "Username is wrong.";
            }
        } catch (Exception $e) {
            $return['success'] = false;
            $return['msgServer'] = 'Login Failed.';
        }
        return response($return);
    }

    public function logoutUser(Request $request)
    {
    	/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : login user
	    * Tipe         : update
	    */
    	$return     = [];
        $username   = $request->input("username");

        try {
            DB::table('m_user')
	        	->where('email', $username)
	        	->orwhere('username', $username)
	        	->update(['login' => 0]);

            $return['success'] = true;
            $return['msgServer'] = "Logout Success.";
        } catch (Exception $e) {
            $return['success'] = false;
            $return['msgServer'] = 'Logout Failed.';
        }
        return response($return);
    }

    public function redeemPoint(Request $request)
    {
    	/**
	    * Programmer   : Kris
	    * Tanggal      : 15-11-2017
	    * Fungsi       : use point
	    * Tipe         : update
	    */
    	$return     = [];
        $sales   = $request->input("sales");
        $point   = $request->input("point");

        $point_sales = DB::table('m_point_sales')
			->where('sales', $sales)
			->sum('point');


		if ($point_sales >= $point) {

			DB::table('t_redeem_point')
				->insert([
	                "sales"     => $sales,
	                "point"     => $point,
		        ]);

		    DB::table('m_point_sales')
				->insert([
	                "sales"    => $sales,
	                "type"     => 'use-point',
	                "point"    => -$point,
		        ]);

			$return['success'] = true;
            $return['msgServer'] = "Redeem Success";
		}else{
			$return['success'] = false;
            $return['msgServer'] = 'Point not enough';
		}
        return response($return);
    }

    public function createUserTrial(Request $request)
    {
    	/**
	    * Programmer   : Kris
	    * Tanggal      : 09-08-2018
	    * Fungsi       : create user
	    * Tipe         : update
	    */
    	$return     = [];
        $nama   = urldecode($request->input("nama"));
        // $username   = $request->input("username");
        $email   = $request->input("email");
        $password   = $request->input("password");
        $company_code   = $request->input("company_code");

        $roleSuperAdmin = MRoleModel::where('name', 'Super Admin')->first();

        $data_email = DB::table('m_user')
        	->where('email',$email)
        	->get();

        $data_cc = DB::table('m_user')
        	->where('company_code',$company_code)
        	->get();

		if (count($data_email) == 0) {
			if (count($data_cc) == 0) {
				$createAdmin = new MUserModel;
		        $createAdmin->name = $nama;
		        $createAdmin->username = $email;
		        $createAdmin->email = $email;
		        // $createAdmin->address = $request->alamat;
		        // $createAdmin->birthdate = date('Y-m-d', strtotime($request->birthdate));
		        $createAdmin->password =  bcrypt(str_replace(' ', '', $password));
		        $createAdmin->role = $roleSuperAdmin->id;
		        $createAdmin->company_code = $company_code;
		        $createAdmin->save();

		        $return['success'] = true;
            	$return['msgServer'] = "Create user berhasil";
			}else{
				$return['success'] = false;
            	$return['msgServer'] = 'Company code sudah dipakai';
			}
		}else{
			$return['success'] = false;
            $return['msgServer'] = 'Email sudah dipakai';
		}
        return response($return);
    }

    public function createUserDejozz(Request $request)
    {
    	ini_set('max_execution_time', 3000);
    	/**
	    * Programmer   : Kris
	    * Tanggal      : 30-01-2019
	    * Fungsi       : create user
	    * Tipe         : update
	    */
    	$return     = [];

    	// dejozz
    	$curlSession = curl_init();
        // curl_setopt($curlSession, CURLOPT_URL, "http://wsdejozz.azurewebsites.net/wsdejozz.asmx/APIBiztekTryFree?sOid=".$request->id);

        curl_setopt($curlSession, CURLOPT_URL, "http://wsdejozz.azurewebsites.net/wsdejozz.asmx/APIBiztekCheckUser?sOid=".$request->id);

        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);

        $jsonData = json_decode(curl_exec($curlSession));
        curl_close($curlSession);

        $count1 = count($jsonData);

        if ($count1 > 0) {
        	if ($jsonData[0]->returnvalue == 'OK') {
	        	// dd($jsonData[0]->returnvalue);

	        	$curlSession2 = curl_init();
		        curl_setopt($curlSession2, CURLOPT_URL, "http://wsdejozz.azurewebsites.net/wsdejozz.asmx/APIBiztekTryFree?sOid=".$request->id);

		        // curl_setopt($curlSession2, CURLOPT_URL, "http://wsdejozz.azurewebsites.net/wsdejozz.asmx/APIBiztekCheckUser?sOid=".$request->id);

		        curl_setopt($curlSession2, CURLOPT_BINARYTRANSFER, true);
		        curl_setopt($curlSession2, CURLOPT_RETURNTRANSFER, true);

		        $jsonData2 = json_decode(curl_exec($curlSession2));
		        curl_close($curlSession2);

		        $count2 = count($jsonData2);

		        if ($count2 > 0) {
		            //create user
		            foreach ($jsonData2 as $key => $data) {
		            	// dd($data);

		            	$nama   = urldecode($data->profilename);
				        $username   = $data->profileoid;
				        $email   = $data->email;
				        $password   = $data->profilepass;
				        $company_code   = $data->cmpcodebiztek;

				        $roleSuperAdmin = MRoleModel::where('name', 'Super Admin')->first();

				        $data_email = DB::table('m_user')
				        	->where('email',$email)
				        	->get();

				        $data_cc = DB::table('m_user')
				        	->where('company_code',$company_code)
				        	->get();

						if (count($data_email) == 0) {
							if (count($data_cc) == 0) {
								$createAdmin = new MUserModel;
						        $createAdmin->name = $nama;
						        $createAdmin->username = $username;
						        $createAdmin->email = $email;
						        $createAdmin->password =  bcrypt(str_replace(' ', '', $password));
						        $createAdmin->role = $roleSuperAdmin->id;
						        $createAdmin->company_code = $company_code;
						        $createAdmin->save();
							}
						}
		            }
		        }
	        }
        }
  //       $nama   = urldecode($request->input("nama"));
  //       // $username   = $request->input("username");
  //       $email   = $request->input("email");
  //       $password   = $request->input("password");
  //       $company_code   = $request->input("company_code");

  //       $roleSuperAdmin = MRoleModel::where('name', 'Super Admin')->first();

  //       $data_email = DB::table('m_user')
  //       	->where('email',$email)
  //       	->get();

  //       $data_cc = DB::table('m_user')
  //       	->where('company_code',$company_code)
  //       	->get();

		// if (count($data_email) == 0) {
		// 	if (count($data_cc) == 0) {
		// 		$createAdmin = new MUserModel;
		//         $createAdmin->name = $nama;
		//         $createAdmin->username = $email;
		//         $createAdmin->email = $email;
		//         // $createAdmin->address = $request->alamat;
		//         // $createAdmin->birthdate = date('Y-m-d', strtotime($request->birthdate));
		//         $createAdmin->password =  bcrypt(str_replace(' ', '', $password));
		//         $createAdmin->role = $roleSuperAdmin->id;
		//         $createAdmin->company_code = $company_code;
		//         $createAdmin->save();

		//         $return['success'] = true;
  //           	$return['msgServer'] = "Create user berhasil";
		// 	}else{
		// 		$return['success'] = false;
  //           	$return['msgServer'] = 'Company code sudah dipakai';
		// 	}
		// }else{
		// 	$return['success'] = false;
  //           $return['msgServer'] = 'Email sudah dipakai';
		// }

        return redirect()->back();
    }
}