<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MRoleModel;
use DataTables;

class MRoleController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
		return view('roles-permissions');
	}
	
	public function rolesDatatables(){
		$data = MRoleModel::orderBy('id','DESC')->get();
		return Datatables::of($data)
			->addIndexColumn()
			->rawColumns(['action'])
			->make(true);
	}
}
