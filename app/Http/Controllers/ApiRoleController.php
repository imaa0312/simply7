<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiRoleController extends Controller
{
	public function listRole(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : read role
	    * Tipe         : update
	    */
		$return = [];
		$data = DB::table('m_role')->select('id','name')->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Role not found';
		}

		return response($return);
	}

	public function detailRole(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : detail role
	    * Tipe         : update
	    */
		$return = [];
		$id = $request->input("id");
		$data = DB::table('m_role')->select('id','name')->where('id', $id)->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Role not found';
		}

		return response($return);
	}
}