<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MKategoriModel;
use App\Models\MSubKategoriModel;
use App\Models\MSsubKategoriModel;
use App\Models\MSssubKategoriModel;
use App\Models\MSupplierModel;
use App\Models\MGudangModel;
use App\Models\MTokoModel;
use App\Models\MProvinsiModel;
use App\Models\MKotaKabModel;
use App\Models\MUserModel;
use App\Models\MExpenseCategoryModel;
use App\Models\MBrandModel;
use App\Models\MSizeModel;
use DataTables;

class MasterController extends Controller
{
    public function category(){
        return view('category-list');

        // echo "<pre>";
        // print_r($getDataKategori);
    }

    public function getProvince()
    {
        $data = MProvinsiModel::get();
        $prov = '<label class="form-label">Province</label>
                <select class="select" id="prov" name="prov">
                <option>Choose Province</option>';
        foreach($data as $dt){
            $prov .= '<option value="'.$dt->id.'">'.$dt->name.'</option>';
        }
        $prov .= '</select>';

        if($data){
            $return = array(
                "province" => $prov,
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

    public function getCity($id="")
    {
        if($id==""){
            $data = MKotaKabModel::select('m_kota_kab.*', 'm_provinsi.name as province')
                ->join('m_provinsi', 'm_provinsi.id', '=', 'm_kota_kab.provinsi')
                ->get();
        } else {
            $data = MKotaKabModel::select('m_kota_kab.*', 'm_provinsi.name as province')
                ->join('m_provinsi', 'm_provinsi.id', '=', 'm_kota_kab.provinsi')
                ->where('provinsi', $id)
                ->get();
        }
        
        $city = '<label class="form-label">City</label>
            <select class="select" id="city" name="city">
            <option>Choose City</option>';
        foreach($data as $dt){
            $city .= '<option value="'.$dt->id.'">'.$dt->name.'</option>';
        }
        $city .= '</select>';

        if($data){
            $return = array(
                "city" => $city,
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

    public function getKategori()
    {
        $dataKategori = MKategoriModel::where('status', '=', 1)->get();
        $cat = '<label class="form-label">Category</label>
                <select class="form-control" id="category" name="category">
                <option>Choose Category</option>';
        foreach($dataKategori as $kat){
            $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $cat .= '</select>';

        if($dataKategori){
            $return = array(
                "category" => $cat,
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

    public function getSubKategori()
    {
        $dataKategori = MSubKategoriModel::where('status', '=', 1)->get();
        
        $cat = '<label class="form-label">Sub Category</label>
            <select class="form-control" id="subcategory" name="subcategory">
            <option>Choose Sub Category</option>';
        foreach($dataKategori as $kat){
            $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $cat .= '</select>';

        if($dataKategori){
            $return = array(
                "category" => $cat,
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

    public function getSsubKategori()
    {
        $dataKategori = MSsubKategoriModel::where('status', '=', 1)->get();

        $cat = '<label class="form-label">Sub-Sub Category</label>
            <select class="form-control" id="ssubcategory" name="ssubcategory">
            <option>Choose Sub-Sub Category</option>';
        foreach($dataKategori as $kat){
            $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $cat .= '</select>';

        if($dataKategori){
            $return = array(
                "category" => $cat,
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

    public function getSssubKategori()
    {
        $dataKategori = MSssubKategoriModel::where('status', '=', 1)->get();

        $cat = '<label class="form-label">Sub-Sub-Sub Category</label>
            <select class="form-control" id="sssubcategory" name="sssubcategory">
            <option>Choose Sub-Sub-Sub Category</option>';
        foreach($dataKategori as $kat){
            $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $cat .= '</select>';

        if($dataKategori){
            $return = array(
                "category" => $cat,
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

    public function getBrand()
    {
        $dataKategori = MBrandModel::where('status', '=', 1)->get();

        $cat = '<label class="form-label">Brand</label>
            <select class="form-control" id="brand" name="brand">
            <option>Choose Brand</option>';
        foreach($dataKategori as $kat){
            $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $cat .= '</select>';

        if($dataKategori){
            $return = array(
                "category" => $cat,
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

    public function getSize()
    {
        $dataKategori = MSizeModel::where('status', '=', 1)->get();

        $cat = '<label class="form-label">Size</label>
            <select class="form-control" id="size" name="size">
            <option>Choose Size</option>';
        foreach($dataKategori as $kat){
            $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $cat .= '</select>';

        if($dataKategori){
            $return = array(
                "category" => $cat,
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

    public function getStoreManager()
    {
        $data = MUserModel::where('role', '=', 5)->get();
        $manager = '<label>Store Manager</label>
                <select class="form-control" id="manager" name="manager">
                <option>Choose Manager</option>';
        foreach($data as $dt){
            $manager .= '<option value="'.$dt->id.'">'.$dt->name.'</option>';
        }
        $manager .= '</select>';

        if($data){
            $return = array(
                "manager" => $manager,
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

    public function getExpenseCategory()
    {
        $data = MExpenseCategoryModel::where('status', '=', 1)->get();
        $category = '<label class="form-label">Expense Category</label>
                <select class="form-control" id="category" name="category">
                <option>Choose Expense Category</option>';
        foreach($data as $dt){
            $category .= '<option value="'.$dt->id.'">'.$dt->name.'</option>';
        }
        $category .= '</select>';

        if($data){
            $return = array(
                "category" => $category,
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

    public function categoryDatatables(){
        $data = MKategoriModel::orderBy('id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-cat" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-category" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-cat" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-cat" href="javascript:void(0);" data-id="'.$row->id.'">
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

    public function editKategori($id)
    {
        $dataKategori = MKategoriModel::find($id);

        if($dataKategori){
            $return = array(
                "name" => $dataKategori->name,
                "code" => $dataKategori->code,
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

    public function storeKategori(Request $request)
    {
        $id = $request->input('cat_id');

        if($id == ""){
            $dataKategori = new MKategoriModel;
            $dataKategori->status = 1;
        } else {
            $dataKategori = MKategoriModel::find($id);
        }

        $dataKategori->name = $request->input('cat_name');
        $dataKategori->code = $request->input('code');
        $dataKategori->save();

        if($dataKategori){
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

        echo json_encode($return);
    }

    public function deleteKategori($id)
    {
        $dataKategori = MKategoriModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreKategori($id)
    {
        $dataKategori = MKategoriModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }




    
    public function subcategory(){
        return view('sub-category-list');

        // echo "<pre>";
        // print_r($getDataKategori);
    }

    public function subcategoryDatatables(){
        $data = MSubKategoriModel::orderBy('m_sub_kategori_produk.id','DESC')->get();
        // select('m_kategori_produk.name as kategori', 'm_sub_kategori_produk.*')
            // ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_sub_kategori_produk.kategori_id')
            // ->orderBy('m_sub_kategori_produk.id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-cat" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-category" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-cat" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-cat" href="javascript:void(0);" data-id="'.$row->id.'">
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

    public function editSubKategori($id)
    {
        $dataKategori = MSubKategoriModel::find($id);
        // select('m_sub_kategori_produk.*', 'm_kategori_produk.name as kategori_name')
            // ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_sub_kategori_produk.kategori_id')
            // ->find($id);
        
        // $kategori = MKategoriModel::get();
        // $cat = '<label class="form-label">Category</label>
        //     <select class="form-control" id="category" name="category">';
        // foreach($kategori as $kat){
        //     $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        // }
        // $cat .= '</select>';

        if($dataKategori){
            $return = array(
                // "kategori_id" => $dataKategori->kategori_id,
                // "kategori_name" => $dataKategori->kategori_name,
                "code" => $dataKategori->code,
                "name" => $dataKategori->name,
                "status" => true
                // "category_list" => $cat
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data not found"
            );
        }

        echo json_encode($return);
    }

    public function storeSubKategori(Request $request)
    {
        $id = $request->input('subcat_id');

        if($id == ""){
            $dataKategori = new MSubKategoriModel;
            $dataKategori->status = 1;
        } else {
            $dataKategori = MSubKategoriModel::find($id);
        }

        $dataKategori->kategori_id = $request->input('category');
        $dataKategori->name = $request->input('cat_name');
        $dataKategori->code = $request->input('code');
        $dataKategori->save();

        if($dataKategori){
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

        echo json_encode($return);
    }

    public function deleteSubKategori($id)
    {
        $dataKategori = MSubKategoriModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreSubKategori($id)
    {
        $dataKategori = MSubKategoriModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }




    
    public function ssubcategory(){
        return view('ssub-category-list');

        // echo "<pre>";
        // print_r($getDataKategori);
    }

    public function ssubcategoryDatatables(){
        $data = MSsubKategoriModel::orderBy('m_ssub_kategori_produk.id','DESC')->get();
        // select('m_kategori_produk.name as kategori_name', 'm_sub_kategori_produk.name as sub_kategori_name', 'm_ssub_kategori_produk.*')
            // ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_ssub_kategori_produk.sub_kategori_id')
            // ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_sub_kategori_produk.kategori_id')
            // ->orderBy('m_ssub_kategori_produk.id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-cat" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-category" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-cat" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-cat" href="javascript:void(0);" data-id="'.$row->id.'">
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

    public function editSsubKategori($id)
    {
        $dataKategori = MSsubKategoriModel::find($id);
        // select('m_kategori_produk.id as kategori_id', 'm_sub_kategori_produk.name as sub_kategori_name', 'm_ssub_kategori_produk.*')
            // ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_ssub_kategori_produk.sub_kategori_id')
            // ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_sub_kategori_produk.kategori_id')
            // ->find($id);

        // $kategori = MKategoriModel::get();
        // $cat = '<label class="form-label">Category</label>
        //     <select class="form-control" id="category" name="category">';
        // foreach($kategori as $kat){
        //     $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        // }
        // $cat .= '</select>';

        // $subkategori = MSubKategoriModel::where('kategori_id', $dataKategori->kategori_id)->get();
        // $subcat = '<label class="form-label">Sub Category</label>
        //     <select class="form-control" id="subcategory" name="subcategory">';
        // foreach($subkategori as $kat){
        //     $subcat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        // }
        // $subcat .= '</select>';

        if($dataKategori){
            $return = array(
                // "kategori_id" => $dataKategori->kategori_id,
                // "sub_kategori_id" => $dataKategori->sub_kategori_id,
                // "sub_kategori_name" => $dataKategori->sub_kategori_name,
                "name" => $dataKategori->name,
                // "category_list" => $cat,
                // "sub_category_list" => $subcat,
                "code" => $dataKategori->code,
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

    public function storeSsubKategori(Request $request)
    {
        $id = $request->input('subcat_id');

        if($id == ""){
            $dataKategori = new MSsubKategoriModel;
            $dataKategori->status = 1;
        } else {
            $dataKategori = MSsubKategoriModel::find($id);
        }

        // $dataKategori->sub_kategori_id = $request->input('subcategory');
        $dataKategori->name = $request->input('cat_name');
        $dataKategori->code = $request->input('code');
        $dataKategori->save();

        if($dataKategori){
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

        echo json_encode($return);
    }

    public function deleteSsubKategori($id)
    {
        $dataKategori = MSsubKategoriModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreSsubKategori($id)
    {
        $dataKategori = MSsubKategoriModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }




    
    public function sssubcategory(){
        return view('sssub-category-list');

        // echo "<pre>";
        // print_r($getDataKategori);
    }

    public function sssubcategoryDatatables(){
        $data = MSssubKategoriModel::orderBy('id','DESC')->get();
            // select('m_kategori_produk.name as kategori_name', 'm_sub_kategori_produk.name as sub_kategori_name', 'm_ssub_kategori_produk.name as ssub_kategori_name', 'm_sssub_kategori_produk.*')
            // ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_sssub_kategori_produk.ssub_kategori_id')
            // ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_ssub_kategori_produk.sub_kategori_id')
            // ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_sub_kategori_produk.kategori_id')
            // ->orderBy('m_ssub_kategori_produk.id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-cat" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-category" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-cat" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-cat" href="javascript:void(0);" data-id="'.$row->id.'">
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

    public function editSssubKategori($id)
    {
        $dataKategori = MSssubKategoriModel::find($id);
        // select('m_kategori_produk.id as kategori_id', 'm_sub_kategori_produk.name as sub_kategori_name', 'm_ssub_kategori_produk.name as ssub_kategori_name', 'm_sssub_kategori_produk.*')
        //     ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_sssub_kategori_produk.ssub_kategori_id')
        //     ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_ssub_kategori_produk.sub_kategori_id')
        //     ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_sub_kategori_produk.kategori_id')
            // ->find($id);

        // $kategori = MKategoriModel::get();
        // $cat = '<label class="form-label">Category</label>
        //     <select class="form-control" id="category" name="category">';
        // foreach($kategori as $kat){
        //     $cat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        // }
        // $cat .= '</select>';

        // $subkategori = MSubKategoriModel::where('kategori_id', $dataKategori->kategori_id)->get();
        // $subcat = '<label class="form-label">Sub Category</label>
        //     <select class="form-control" id="subcategory" name="subcategory">';
        // foreach($subkategori as $kat){
        //     $subcat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        // }
        // $subcat .= '</select>';

        // $ssubkategori = MSsubKategoriModel::where('sub_kategori_id', $dataKategori->sub_kategori_id)->get();
        // $ssubcat = '<label class="form-label">Sub-Sub Category</label>
        //     <select class="form-control" id="ssubcategory" name="ssubcategory">';
        // foreach($ssubkategori as $kat){
        //     $ssubcat .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        // }
        // $ssubcat .= '</select>';

        if($dataKategori){
            $return = array(
                // "kategori_id" => $dataKategori->kategori_id,
                // "sub_kategori_id" => $dataKategori->sub_kategori_id,
                // "ssub_kategori_id" => $dataKategori->ssub_kategori_id,
                // "ssub_kategori_name" => $dataKategori->ssub_kategori_name,
                "name" => $dataKategori->name,
                // "category_list" => $cat,
                // "sub_category_list" => $subcat,
                // "ssub_category_list" => $ssubcat,
                "code" => $dataKategori->code,
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

    public function storeSssubKategori(Request $request)
    {
        $id = $request->input('subcat_id');

        if($id == ""){
            $dataKategori = new MSssubKategoriModel;
            $dataKategori->status = 1;
        } else {
            $dataKategori = MSssubKategoriModel::find($id);
        }

        // $dataKategori->ssub_kategori_id = $request->input('ssubcategory');
        $dataKategori->name = $request->input('cat_name');
        $dataKategori->code = $request->input('code');
        $dataKategori->save();

        if($dataKategori){
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

        echo json_encode($return);
    }

    public function deleteSssubKategori($id)
    {
        $dataKategori = MSssubKategoriModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreSssubKategori($id)
    {
        $dataKategori = MSssubKategoriModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }





    public function brand(){
        return view('brand-list');
    }

    public function brandDatatables(){
        $data = MBrandModel::orderBy('id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-brand" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-brand" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-brand" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-brand" href="javascript:void(0);" data-id="'.$row->id.'">
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
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function editBrand($id)
    {
        $data = MBrandModel::find($id);
        if($data){
            $return = array(
                "name" => $data->name,
                "code" => $data->code,
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

    public function storeBrand(Request $request)
    {
        $id = $request->input('brand_id');

        if($id == ""){
            $data = new MBrandModel;
            $data->status = 1;
        } else {
            $data = MBrandModel::find($id);
        }

        $data->name = $request->input('brand_name');
        $data->code = $request->input('code');
        $data->save();

        if($data){
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

        echo json_encode($return);
    }

    public function deleteBrand($id)
    {
        $dataKategori = MBrandModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreBrand($id)
    {
        $dataKategori = MBrandModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }





    public function size(){
        return view('size');
    }

    public function sizeDatatables(){
        $data = MSizeModel::orderBy('id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-size" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-size" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-size" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-size" href="javascript:void(0);" data-id="'.$row->id.'">
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
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function editSize($id)
    {
        $data = MSizeModel::find($id);
        if($data){
            $return = array(
                "name" => $data->name,
                "code" => $data->code,
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

    public function storeSize(Request $request)
    {
        $id = $request->input('size_id');

        if($id == ""){
            $data = new MSizeModel;
            $data->status = 1;
        } else {
            $data = MSizeModel::find($id);
        }

        $data->name = $request->input('size_name');
        $data->code = $request->input('code');
        $data->save();

        if($data){
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

        echo json_encode($return);
    }

    public function deleteSize($id)
    {
        $dataKategori = MSizeModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreSize($id)
    {
        $dataKategori = MSizeModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }





    public function supplier(){
        return view('suppliers');
    }

    public function supplierDatatables(){
        $data = MSupplierModel::select('m_kota_kab.name as city_name', 'm_supplier.*')
            ->join('m_provinsi', 'm_provinsi.id', '=', 'm_supplier.province')
            ->join('m_kota_kab', 'm_kota_kab.id', '=', 'm_supplier.city')
            ->orderBy('m_supplier.id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-sup" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-supplier" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-sup" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-sup" href="javascript:void(0);" data-id="'.$row->id.'">
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
            ->rawColumns(['checkbox', 'action', 'status'])
            ->make(true);
    }

    public function editSupplier($id)
    {
        $data = MSupplierModel::select('m_kota_kab.name as city_name', 'm_supplier.*')
            ->join('m_provinsi', 'm_provinsi.id', '=', 'm_supplier.province')
            ->join('m_kota_kab', 'm_kota_kab.id', '=', 'm_supplier.city')
            ->find($id);

        $province = MProvinsiModel::get();
        $prov = '<label>Province</label>
            <select class="form-control" id="prov" name="prov">';
        foreach($province as $kat){
            $prov .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $prov .= '</select>';

        $city_data = MKotaKabModel::where('provinsi', $data->province)->get();
        $city = '<label>City</label>
            <select class="form-control" id="city" name="city">';
        foreach($city_data as $kat){
            $city .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $city .= '</select>';

        if($data){
            $return = array(
                "province" => $data->province,
                "city" => $data->city,
                "name" => $data->name,
                "phone" => $data->phone,
                "address" => $data->address,
                "desc" => $data->description,
                "province_list" => $prov,
                "city_list" => $city,
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

    public function storeSupplier(Request $request)
    {
        $id = $request->input('sup_id');

        if($id == ""){
            $data = new MSupplierModel;
            $data->status = 1;
        } else {
            $data = MSupplierModel::find($id);
        }

        $data->province = $request->input('prov');
        $data->city = $request->input('city');
        $data->name = $request->input('sup_name');
        $data->phone = $request->input('phone');
        $data->address = $request->input('address');
        $data->description = $request->input('desc');
        $data->save();

        if($data){
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

        echo json_encode($return);
    }

    public function deleteSupplier($id)
    {
        $dataKategori = MSupplierModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreSupplier($id)
    {
        $dataKategori = MSupplierModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }




    public function warehouse(){
        return view('warehouse');
    }

    public function warehouseDatatables(){
        $data = MGudangModel::orderBy('id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-store" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-store" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                    </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function editWarehouse($id)
    {
        $data = MGudangModel::find($id);

        if($data){
            $return = array(
                "name" => $data->name,
                "phone" => $data->phone,
                "address" => $data->address,
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

    public function storeWarehouse(Request $request)
    {
        $id = $request->input('warehouse_id');

        if($id == ""){
            $data = new MGudangModel;
            $data->status = 1;
        } else {
            $data = MGudangModel::find($id);
        }

        $data->name = $request->input('warehouse_name');
        $data->phone = $request->input('phone');
        $data->address = $request->input('address');
        $data->save();

        if($data){
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

        echo json_encode($return);
    }




    public function store(){
        return view('store-list');
    }

    public function storeDatatables(){
        $data = MTokoModel::select('m_toko.*', 'm_user.name as manager')
            ->join('m_user', 'm_toko.store_manager', '=', 'm_user.id')
            ->orderBy('m_toko.id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-store" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-stores" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-store" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-store" href="javascript:void(0);" data-id="'.$row->id.'">
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

    public function editStore($id)
    {
        $store_manager = MUserModel::where('role', '=', 5)->get();

        $manager = '<label>Store Manager</label>
            <select class="form-control" id="manager" name="manager">';
        foreach($store_manager as $mng){
            $manager .= '<option value="'.$mng->id.'">'.$mng->name.'</option>';
        }
        $manager .= '</select>';
        
        $data = MTokoModel::find($id);
        if($data){
            $return = array(
                "name" => $data->name,
                "address" => $data->address,
                "manager" => $data->store_manager,
                "manager_list" => $manager,
                "phone" => $data->phone,
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

    public function storeStore(Request $request)
    {
        $id = $request->input('store_id');

        if($id == ""){
            $data = new MTokoModel;
            $data->status = 1;
        } else {
            $data = MTokoModel::find($id);
        }

        $data->name = $request->input('store_name');
        $data->address = $request->input('address');
        $data->phone = $request->input('telp');
        $data->store_manager = $request->input('manager');
        $data->save();

        if($data){
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

        echo json_encode($return);
    }

    public function deleteStore($id)
    {
        $dataKategori = MTokoModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreStore($id)
    {
        $dataKategori = MTokoModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }







    public function expenseCategory(){
        return view('expense-category');
    }

    public function expenseCategoryDatatables(){
        $data = MExpenseCategoryModel::orderBy('id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-expense-category" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-expense-category" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-expense-category" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-expense-category" href="javascript:void(0);" data-id="'.$row->id.'">
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
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function editExpenseCategory($id)
    {
        $data = MExpenseCategoryModel::find($id);
        if($data){
            $return = array(
                "name" => $data->name,
                "desc" => $data->description,
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

    public function storeExpenseCategory(Request $request)
    {
        $id = $request->input('exp_cat_id');

        if($id == ""){
            $data = new MExpenseCategoryModel;
            $data->status = 1;
        } else {
            $data = MExpenseCategoryModel::find($id);
        }

        $data->name = $request->input('nama');
        $data->description = $request->input('desc');
        $data->save();

        if($data){
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

        echo json_encode($return);
    }

    public function deleteExpenseCategory($id)
    {
        $dataKategori = MExpenseCategoryModel::find($id);
        $dataKategori->status = 0;
        $dataKategori->save();

        if($dataKategori){
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

    public function restoreExpenseCategory($id)
    {
        $dataKategori = MExpenseCategoryModel::find($id);
        $dataKategori->status = 1;
        $dataKategori->save();

        if($dataKategori){
            $return = array(
                "status" => true,
                "msg" => "Successfully restored"
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