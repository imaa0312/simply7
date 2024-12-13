<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MKategoriModel;

class MasterController extends Controller
{
    public function category(){
        $getDataKategori = MKategoriModel::orderBy('id','DESC')->get();
        return view('category-list',compact('getDataKategori'));

        // echo "<pre>";
        // print_r($getDataKategori);
    }

    public function edit($id)
    {
        $dataKategori = MKategoriModel::find($id);

        if($dataKategori){
            $return = array(
                "name" => $dataKategori->name,
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
}
