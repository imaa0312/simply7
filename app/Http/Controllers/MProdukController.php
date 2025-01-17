<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;
use App\Models\MSsubKategoriModel;
use App\Models\MSssubKategoriModel;
use App\Models\MBrandModel;
use App\Models\MSizeModel;
use App\Models\MKategoriModel;
use App\Models\MProdukImage;
use App\Models\MProdukModel;

use App\Models\MSubKategoriModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Yajra\Datatables\Datatables;




class MProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function product()
    {
        return view('product-list');
    }

    public function productDatatables(){
        $data = MProdukModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size')
            ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
            ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
            ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_produk.ssub_kategori_id')
            ->join('m_sssub_kategori_produk', 'm_sssub_kategori_produk.id', '=', 'm_produk.sssub_kategori_id')
            ->join('m_brand', 'm_brand.id', '=', 'm_produk.brand_id')
            ->join('m_size', 'm_size.id', '=', 'm_produk.size_id')
            ->orderBy('m_produk.id','DESC')->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                // if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-product" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-products" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-product" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                // else
                //     return '<div class="edit-delete-action">
                //         <a class="btn btn-success btn-sm p-2 restore-product" href="javascript:void(0);" data-id="'.$row->id.'">
                //             <i class="fas fa-square-check"></i>
                //         </a>
                //     </div>';
            })
            ->editColumn('status', function($row){
                if($row->status == 0)
                    return '<span class="badge rounded-pill bg-danger">Deleted</span>';
                else
                    return '<span class="badge rounded-pill bg-success">Active</span>';
            })
            ->editColumn('category', function($row){
                return $row->kategori." > ". $row->sub_kategori." > ". $row->ssub_kategori." > ". $row->sssub_kategori." > ". $row->size;
            })
            ->rawColumns(['status', 'action', 'category'])
            ->make(true);
    }

    public function editProduct($id)
    {
        $data = MProdukModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size')
            ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
            ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
            ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_produk.ssub_kategori_id')
            ->join('m_sssub_kategori_produk', 'm_sssub_kategori_produk.id', '=', 'm_produk.sssub_kategori_id')
            ->join('m_brand', 'm_brand.id', '=', 'm_produk.brand_id')
            ->join('m_size', 'm_size.id', '=', 'm_produk.size_id')
            ->find($id);
        $images = MProdukImage::where('produk_id', '=', $id)->first();
        $images_id = $images->id;
        $photos = $images->image;
        
        $dataKategori = MKategoriModel::where('status', '=', 1)->get();
        $kategori_list = '<label class="form-label">Category</label>
                <select class="form-control" id="category" name="category">
                <option>Choose Category</option>';
        foreach($dataKategori as $kat){
            $kategori_list .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $kategori_list .= '</select>';

        $dataKategori = MSubKategoriModel::where('status', '=', 1)->get();
        
        $sub_kategori_list = '<label class="form-label">Sub Category</label>
            <select class="form-control" id="subcategory" name="subcategory">
            <option>Choose Sub Category</option>';
        foreach($dataKategori as $kat){
            $sub_kategori_list .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $sub_kategori_list .= '</select>';

        $dataKategori = MSsubKategoriModel::where('status', '=', 1)->get();

        $ssub_kategori_list = '<label class="form-label">Sub-Sub Category</label>
            <select class="form-control" id="ssubcategory" name="ssubcategory">
            <option>Choose Sub-Sub Category</option>';
        foreach($dataKategori as $kat){
            $ssub_kategori_list .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $ssub_kategori_list .= '</select>';

        $dataKategori = MSssubKategoriModel::where('status', '=', 1)->get();

        $sssub_kategori_list = '<label class="form-label">Sub-Sub-Sub Category</label>
            <select class="form-control" id="sssubcategory" name="sssubcategory">
            <option>Choose Sub-Sub-Sub Category</option>';
        foreach($dataKategori as $kat){
            $sssub_kategori_list .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $sssub_kategori_list .= '</select>';

        $dataKategori = MBrandModel::where('status', '=', 1)->get();

        $brand_list = '<label class="form-label">Brand</label>
            <select class="form-control" id="brand" name="brand">
            <option>Choose Brand</option>';
        foreach($dataKategori as $kat){
            $brand_list .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $brand_list .= '</select>';

        $dataKategori = MSizeModel::where('status', '=', 1)->get();

        $size_list = '<label class="form-label">Size</label>
            <select class="form-control" id="size" name="size">
            <option>Choose Size</option>';
        foreach($dataKategori as $kat){
            $size_list .= '<option value="'.$kat->id.'">'.$kat->name.'</option>';
        }
        $size_list .= '</select>';

        if($data){
            $return = array(
                "id" => $data->id,
                "name" => $data->name,
                "sku" => $data->code,
                "kategori_list" => $kategori_list,
                "sub_kategori_list" => $sub_kategori_list,
                "ssub_kategori_list" => $ssub_kategori_list,
                "sssub_kategori_list" => $sssub_kategori_list,
                "brand_list" => $brand_list,
                "size_list" => $size_list,
                "kategori_id" => $data->kategori_id,
                "sub_kategori_id" => $data->sub_kategori_id,
                "ssub_kategori_id" => $data->ssub_kategori_id,
                "sssub_kategori_id" => $data->sssub_kategori_id,
                "brand_id" => $data->brand_id,
                "size_id" => $data->size_id,
                "description" => $data->description,
                "price_purchase" => $data->price_purchase,
                "price_sale" => $data->price_sale,
                "profit_percent" => $data->profit_percent,
                "stok_minimal" => $data->stok_minimal,
                "images_id" => $images_id,
                "images" => 'product_images/'.$photos,
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

    public function storeProduct(Request $request)
    {
        DB::beginTransaction();

        try{
            $id = $request->input('product_id');

            if($id == ""){
                $data = new MProdukModel;
            } else {
                $data = MProdukModel::find($id);
            }

            $data->name = $request->input('product_name');
            $data->kategori_id = $request->input('category');
            $data->sub_kategori_id = $request->input('subcategory');
            $data->ssub_kategori_id = $request->input('ssubcategory');
            $data->sssub_kategori_id = $request->input('sssubcategory');
            $data->brand_id = $request->input('brand');
            $data->size_id = $request->input('size');
            $data->sku = $request->input('sku');
            $data->description = $request->input('desc');
            $data->price_purchase = $request->input('p_price');
            $data->price_sale = $request->input('s_price');
            $data->profit_percent = $request->input('profit');
            $data->stok_minimal = $request->input('qty_alert');
            $data->save();

            $images = MProdukImage::find($request->input('images_id'));
            $images->produk_id = $data->id;
            $images->save();

            $return = array(
                "status" => true,
                "msg" => "Successfully saved"
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }

    public function uploadImages(Request $request){
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $imageName = time().'.'.$request->image->extension();
        $request->image->move(public_path('product_images'), $imageName);

        $data = new MProdukImage;
        $data->image = $imageName;
        $data->save();

        if($data){
            $return = array(
                "status" => true,
                "id" => $data->id,
                "images" => 'product_images/'.$data->image,
                "msg" => "Successfully saved"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data not found"
            );
        }

        echo json_encode($return);
    }

    public function deleteProduct($id)
    {
        $dataKategori = MProdukModel::find($id);
        $dataKategori->delete();

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

    public function delImages($id){
        $data = MProdukImage::find($id);
        $filename = $data->image;

        $file_path = public_path('product_images/').$filename;
        if (File::exists($file_path)) {
            File::delete($file_path);
        }
        $data->delete();

        if($data){
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
