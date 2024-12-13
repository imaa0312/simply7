<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;



class ApiKotaKabController extends Controller
{
	public function listKotaKab(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : list Kota kabupaten
	    * Tipe         : update
	    */
		$return = [];
		$data = DB::table('m_kota_kab')
			->select('m_kota_kab.code','m_kota_kab.name','m_kota_kab.type','m_provinsi.name as provinsi')
			->join("m_provinsi", "m_kota_kab.provinsi", "=" , "m_provinsi.id")
			->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Kota kabupaten not found';
		}

		return response($return);
	}

	public function searchKotaKab(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : search Kota kabupaten
	    * Tipe         : update
	    */
		$return = [];
		$name   = $request->input("name");

		$query = DB::table('m_kota_kab')
			->select('m_kota_kab.code','m_kota_kab.name','m_kota_kab.type','m_provinsi.name as provinsi')
			->join("m_provinsi", "m_kota_kab.provinsi", "=" , "m_provinsi.id");

		if ($name) {
            $query->where('m_kota_kab.name', 'ILIKE', '%' . $name . '%');
        }

        $data = $query->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Kota kabupaten not found';
		}

		return response($return);
	}

	public function detailKotaKab(request $request)
	{
		/**
	    * Programmer   : Kris
	    * Tanggal      : 26-10-2017
	    * Fungsi       : detail Kota kabupaten
	    * Tipe         : update
	    */
		$return = [];
		$id = $request->input("id");
		$data = DB::table('m_kota_kab')
			->select('m_kota_kab.code','m_kota_kab.name','m_kota_kab.type','m_provinsi.name as provinsi')
			->join("m_provinsi", "m_kota_kab.provinsi", "=" , "m_provinsi.id")
			->where('m_kota_kab.id', $id)
			->get();

		if (count($data) !== 0) {
			$return['success'] = true;
			$return['msgServer'] = $data;
		}else{
			$return['success'] = false;
			$return['msgServer'] = 'Kota kabupaten not found';
		}

		return response($return);
	}
}