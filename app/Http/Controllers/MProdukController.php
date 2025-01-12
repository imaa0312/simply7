<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;
use App\Models\MBahanProdukModel;
use App\Models\MGudangModel;
use App\Models\MHargaProdukModel;
use App\Models\MJenisProdukModel;
use App\Models\MKategoriModel;
use App\Models\MMerekProdukModel;
use App\Models\MProdukImage;
use App\Models\MProdukModel;
use App\Models\MSatuanKemasanProdukModel;
use App\Models\MSatuanUnitModel;
use App\Models\MStokProdukModel;
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
        $data = MProductModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size')
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
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-product" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-product" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-product" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-product" href="javascript:void(0);" data-id="'.$row->id.'">
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
            ->editColumn('status', function($row){
                return $row->kategori." > ". $row->sub_kategori." > ". $row->ssub_kategori." > ". $row->sssub_kategori." > ". $row->size;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function editProduct($id)
    {
        $data = MSizeModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size')
            ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
            ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
            ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_produk.ssub_kategori_id')
            ->join('m_sssub_kategori_produk', 'm_sssub_kategori_produk.id', '=', 'm_produk.sssub_kategori_id')
            ->join('m_brand', 'm_brand.id', '=', 'm_produk.brand_id')
            ->join('m_size', 'm_size.id', '=', 'm_produk.size_id')
            ->find($id);

        if($data){
            $return = array(
                "name" => $data->name,
                "sku" => $data->code,
                "sub_kategori_list" => $sub_kategori_list,
                "ssub_kategori_list" => $ssub_kategori_list,
                "sssub_kategori_list" => $sssub_kategori_list,
                "brand_list" => $brand_list,
                "size_list" => $size_list,
                "kategori_id" => $kategori_id,
                "sub_kategori_id" => $data->sub_kategori_id,
                "ssub_kategori_id" => $data->ssub_kategori_id,
                "sssub_kategori_id" => $data->sssub_kategori_id,
                "brand_id" => $data->brand,
                "size_id" => $data->size,
                "description" => $data->description,
                "price_purchase" => $data->price_purchase,
                "price_sale" => $data->price_sale,
                "profit_percent" => $data->profit_percent,
                "stok_minimal" => $data->stok_minimal,
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
                $data = new MProductModel;
                $data->status = 1;
            } else {
                $data = MProductModel::find($id);
            }

            $data->name = $request->input('product_name');
            $data->category = $request->input('category');
            $data->sub_category = $request->input('sub_category');
            $data->ssub_category = $request->input('ssub_category');
            $data->sssub_category = $request->input('sssub_category');
            $data->brand = $request->input('brand');
            $data->size = $request->input('size');
            $data->sku = $request->input('sku');
            $data->desc = $request->input('desc');
            $data->price_purchase = $request->input('p_price');
            $data->price_sale = $request->input('s_price');
            $data->profit_percent = $request->input('profit');
            $data->stok_minimal = $request->input('qty_alert');
            $data->save();

            $request->validate([
                'images' => 'required|array',
                'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            $images = [];

            foreach($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('product_images'), $imageName);
                $images[] = ['product_id' => $data->id, 'image' => $imageName];
            }

            foreach ($images as $imageData) {
                Image::create($imageData);
            }

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
        // $request->validate([
        //     'images' => 'required|array',
        //     'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        // ]);

        $images = [];

        foreach($request->file('images') as $image) {
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('product_images'), $imageName);
            $images[] = ['product_id' => $data->id, 'image' => $imageName];
        }

        foreach ($images as $imageData) {
            Image::create($imageData);
        }

        $return = array(
            "status" => true,
            "msg" => "Successfully saved"
        );
    }

    public function deleteSize($id)
    {
        $dataKategori = MProductModel::find($id);
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
        $dataKategori = MProductModel::find($id);
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
