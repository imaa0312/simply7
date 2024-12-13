<?php

use IlluminateHttpRequest;
use IlluminateSupportFacadesInput;

use AppProvinces;
use AppRegencies;
use AppDistricts;
use AppVillages;

class CountryController extends Controller
{
    public function provinces(){
      $provinces = Provinces::all();
      return view('indonesia', compact('provinces'));
    }

    public function regencies(){
      $provinces_id = Input::get('province_id');
      $regencies = Regencies::where('province_id', '=', $provinces_id)->get();
      return response()->json($regencies);
    }
}