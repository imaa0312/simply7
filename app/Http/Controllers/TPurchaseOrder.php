<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Response;
use Illuminate\Http\Request;
use App\Models\MProdukModel;
use App\Models\MSupplierModel;
use App\Models\TPurchaseOrderModel;
use App\Models\DPurchaseOrderModel;
use App\Models\TPurchaseOrderTempModel;
use App\Models\DPurchaseOrderTempModel;
use App\Models\TPurchaseReceiveModel;
use App\Models\DPurchaseReceiveModel;
use App\Models\MStokProdukModel;
use DataTables;

class TPurchaseOrder extends Controller
{
    /**
    * Display a listing of the repource.
    *
    * @return \Illuminate\Http\Response
    */
    public function purchaseOrder()
    {
        return view('purchase-list');
    }

    public function poDatatables(){
        $data = TPurchaseOrderModel::select('t_purchase_order.*', 'm_supplier.name as supplier', 'm_user.name as user')
            ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_order.supplier')
            ->join('m_user', 'm_user.id', '=', 't_purchase_order.user_id')
            ->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 'in process')
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-po" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-units" data-id="'.$row->id.'" data-toggle="tooltip" title="Edit PO">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-po" href="javascript:void(0);" data-id="'.$row->id.'" data-toggle="tooltip" title="Cancel PO">
                            <i class="fas fa-ban"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 view-po" href="javascript:void(0);" data-id="'.$row->id.'" data-toggle="tooltip" title="View PO" data-bs-toggle="modal"
                            data-bs-target="#add-units">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>';
            })
            ->editColumn('status', function($row){
                if($row->status == "received")
                    return '<span class="badge rounded-pill bg-success">Received</span>';
                else if($row->status == "in process")
                    return '<span class="badge rounded-pill bg-primary">In Process</span>';
                else
                    return '<span class="badge rounded-pill br-danger">Canceled</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function poProductTempDatatable($id =  0){
        // $data = DPurchaseOrderTempModel::select('d_purchase_order_temp.*', 'm_produk.name as name')
        //     ->join('m_produk', 'd_purchase_order_temp.prod_id', '=', 'm_produk.id')
        //     ->where('d_purchase_order_temp.po_id', '=', $id)
        //     ->get();
        
        $data = DPurchaseOrderTempModel::select(
            'm_produk.name',
            DB::raw('SUM(d_purchase_order_temp.qty) as total_qty'),
            DB::raw('SUM(d_purchase_order_temp.purchase_discount) as total_discount'),
            DB::raw('SUM(d_purchase_order_temp.purchase_tax_amount) as total_tax'),
            DB::raw('SUM(d_purchase_order_temp.total_cost) as total_cost')
        )
            ->join('m_produk', 'd_purchase_order_temp.prod_id', '=', 'm_produk.id')
            ->where('d_purchase_order_temp.po_id', '=', $id)
            ->groupBy('d_purchase_order_temp.po_id', 'd_purchase_order_temp.prod_id', 'm_produk.name')
            ->get();
            
        return Datatables::of($data)
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-users" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-users" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-users" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-users" href="javascript:void(0);" data-id="'.$row->id.'">
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

    public function poProductDatatable($id =  0){
        $data = DPurchaseOrderModel::select(
            'm_produk.name',
            DB::raw('SUM(d_purchase_order.qty) as total_qty'),
            DB::raw('SUM(d_purchase_order.purchase_discount) as total_discount'),
            DB::raw('SUM(d_purchase_order.purchase_tax_amount) as total_tax'),
            DB::raw('SUM(d_purchase_order.total_cost) as total_cost')
        )
            ->join('m_produk', 'd_purchase_order.produk', '=', 'm_produk.id')
            ->where('d_purchase_order.po_id', '=', $id)
            ->groupBy('d_purchase_order.po_id', 'd_purchase_order.produk', 'm_produk.name')
            ->get();
            
        return Datatables::of($data)
            ->addColumn('action', function($row){
                if($row->status == 1)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-users" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-users" data-id="'.$row->id.'">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-users" href="javascript:void(0);" data-id="'.$row->id.'">
                            <i class="fas fa-trash-can"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 restore-users" href="javascript:void(0);" data-id="'.$row->id.'">
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

    public function poTemp(Request $request){
        $po = new TPurchaseOrderTempModel;
        $po->user_id = 1;
        $po->save();

        $id = $po->id;

        if($po){
            $return = array(
                "status" => true,
                "id" => $id,
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

    public function poProductTemp(Request $request){
        $id = $request->input('po_id');
        $product = $request->input('product');
        $dt_product = MProdukModel::find($product);
        // $tax = (double)0.1*(double)$dt_product->price_purchase;
        $total = $request->input('qty') * $dt_product->price_purchase;
        $tax = 0;
        $total_cost = $dt_product->price_purchase+$tax;

        $temp = new DPurchaseOrderTempModel;
        $temp->po_id = $id;
        $temp->prod_id = $product;
        $temp->qty = $request->input('qty');
        $temp->purchase_price = $dt_product->price_purchase;
        $temp->purchase_discount = 0;
        $temp->purchase_tax_percent = 10;
        $temp->purchase_tax_amount = $tax;
        $temp->total_cost = $total_cost;
        $temp->save();

        if($temp){
            $return = array(
                "status" => true,
                "id" => $id,
                "total" => $total,
                "total_cost" => $total_cost,
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

    public function poDestroyTemp($id){
        $detail = DPurchaseOrderTempModel::where('po_id', '=', $id)->delete();
        $trans = TPurchaseOrderTempModel::find($id)->delete();

        if($trans){
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

    public function poStore(Request $request){
        $id = $request->input('po_id');
        $total = 0;

        if($request->input('mode') == "add"){
            $data_det_trx = DPurchaseOrderTempModel::where('po_id', '=', $id)->get();
            $trx = new TPurchaseOrderModel;
        } else {
            $trx = TPurchaseOrderModel::find($id);
        }

        $po_date = explode("/", $request->input('po_date'));

        $date = date('Ymd');
        $latestPO = DB::table('t_purchase_order')
            ->where('refno', 'like', "PO-".$date."%")
            ->orderBy('refno', 'desc')
            ->first();

        if ($latestPO) {
            $lastNumber = (int) substr($latestPO->po_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $refno = 'PO-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        
        $trx->supplier = $request->input('supplier');
        $trx->refno = $refno;
        $trx->po_date = date("Y-m-d H:i:s", strtotime($po_date[2]."-".$po_date[1]."-".$po_date[0]));
        $trx->order_discount = $request->input('order_discount');
        $trx->order_tax_percent = $request->input('order_tax');
        $trx->shipping_cost = $request->input('shipping_cost');
        $trx->status = 'in process';
        $trx->description = $request->input('desc');
        $trx->user_id = 1;
        $trx->save();

        if($request->input('mode') == "add"){
            foreach($data_det_trx as $det){
                $det_trx = new DPurchaseOrderModel;
                $det_trx->po_id = $trx->id;
                $det_trx->produk = $det->prod_id;
                $det_trx->qty = $det->qty;
                $det_trx->purchase_price = $det->purchase_price;
                $det_trx->purchase_discount = $det->purchase_discount;
                $det_trx->purchase_tax_percent = $det->purchase_tax_percent;
                $det_trx->purchase_tax_amount = $det->purchase_tax_amount;
                $det_trx->total_cost = $det->total_cost;
                $det_trx->save();

                $total = $total + $det->total_cost;
            }
        }

        $tax_amount = (double)$request->input('order_tax') / 100 * (double)$total;

        $upd_trx = TPurchaseOrderModel::find($trx->id);
        $upd_trx->order_tax_amount = $tax_amount;
        $upd_trx->grand_total = $total + $tax_amount - $request->input('order_discount');
        $upd_trx->save();

        if($trx){
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

    public function poEdit($id){
        $data_trx = TPurchaseOrderModel::find($id);

        if($data_trx){
            $nama_supp = MSupplierModel::find($data_trx->supplier)->name;
            $return = array(
                "id"                => $data_trx->id,
                "supplier"          => $data_trx->supplier,
                "refno"             => $data_trx->refno,
                "po_date"           => date("d/m/Y", strtotime($data_trx->po_date)),
                "order_discount"    => $data_trx->order_discount,
                "order_tax_percent" => $data_trx->order_tax_percent,
                "shipping_cost"     => $data_trx->shipping_cost,
                "grand_total"       => $data_trx->grand_total,
                "description"       => $data_trx->description,
                "nama_supp"         => $nama_supp,
                "status"            => true
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }

    public function poDel($id){
        $trans = TPurchaseOrderModel::find($id);
        $trans->status = "canceled";
        $trans->cancel_by = 1;
        $trans->save();

        if($trans){
            $return = array(
                "status" => true,
                "msg" => "Successfully canceled"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Oops! Something wen't wrong"
            );
        }

        echo json_encode($return);
    }






    public function getRef()
    {
        $data = TPurchaseOrderModel::where('status', '=', 'in process')->get();
        echo json_encode($data);
    }

    public function purchaseReceived()
    {
        return view('purchase-received');
    }

    public function prDatatables(){
        $data = TPurchaseReceiveModel::select('t_purchase_receive.*', 'm_supplier.name as supp_name', 'm_user.name as received_by')
            ->join('t_purchase_order', 't_purchase_order.id', '=', 't_purchase_receive.po_id')
            ->join('m_supplier', 'm_supplier.id', '=', 't_purchase_order.supplier')
            ->join('m_user', 'm_user.id', '=', 't_purchase_receive.user_id')
            ->get();
        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                if($row->status == 0)
                    return '<div class="edit-delete-action">
                        <a class="me-2 p-2 btn btn-success btn-sm edit-pr" href="javascript:void(0);" data-bs-toggle="modal"
                            data-bs-target="#add-received" data-id="'.$row->id.'" data-toggle="tooltip" title="Edit Purchase Received">
                            <i class="fas fa-pencil"></i>
                        </a>
                        <a class="btn btn-danger btn-sm p-2 del-pr" href="javascript:void(0);" data-id="'.$row->id.'" data-toggle="tooltip" title="Cancel Purchase Received">
                            <i class="fas fa-ban"></i>
                        </a>
                    </div>';
                else
                    return '<div class="edit-delete-action">
                        <a class="btn btn-success btn-sm p-2 view-pr" href="javascript:void(0);" data-id="'.$row->id.'" data-toggle="tooltip" title="View Purchase Received" data-bs-toggle="modal"
                            data-bs-target="#add-received">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>';
            })
            ->editColumn('status', function($row){
                if($row->status == 1)
                    return '<span class="badge rounded-pill bg-success">Received</span>';
                else
                    return '<span class="badge rounded-pill bg-primary">In Process</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function prBarcode($po, $barcode){
        $product = MProdukModel::where('barcode', '=', $barcode)->first();

        if($product){
            $id = $product->id;
            $data = TPurchaseOrderModel::select('t_purchase_order.refno')
                ->join('d_purchase_order', 'd_purchase_order.po_id', '=', 't_purchase_order.id')
                ->where('t_purchase_order.id', '=', $po)
                ->where('d_purchase_order.produk', '=', $id)
                ->first();

            if($data){
                $cek_pr = TPurchaseReceiveModel::where('po_id', '=', $po)
                    ->where('status', '=', 0)->first();
                
                if($cek_pr){
                    $pr_id = $cek_pr->id;
                    $cek_dpr =  DPurchaseReceiveModel::where('pr_id', '=', $pr_id)
                        ->where('produk', '=', $id)->first();
                } else {
                    $pr = new TPurchaseReceiveModel;
                    $pr->po_id = $po;
                    $pr->refno_po = $data->refno;
                    $pr->user_id = 1;
                    $pr->save();

                    $pr_id = $pr->id;
                    $cek_dpr =  DPurchaseReceiveModel::where('pr_id', '=', $pr_id)
                        ->where('produk', '=', $id)->first();
                }

                if($cek_dpr){
                    $qty = (int)$cek_dpr->qty;
                    $dpr = DPurchaseReceiveModel::find($cek_dpr->id);
                    $dpr->qty = $qty + 1;
                    $dpr->save();
                } else {
                    $dpr = new DPurchaseReceiveModel;
                    $dpr->pr_id = $pr_id;
                    $dpr->produk = $id;
                    $dpr->qty = 1;
                    $dpr->status_produk = 'good';
                    $dpr->save();
                }

                $return = array(
                    "status" => true,
                    "pr_id" => $pr_id
                );
            } else {
                $return = array(
                    "status" => false,
                    "msg" => "Product Not Found on this PO Reference"
                );
            }
        } else {
            $return = array(
                "status" => false,
                "msg" => "Barcode Not Found"
            );
        }

        echo json_encode($return);
    }

    public function prProduct($id){
        $detail = DPurchaseReceiveModel::select('d_purchase_receive.*', 'm_produk.name as product')
            ->join('m_produk', 'm_produk.id', '=', 'd_purchase_receive.produk')
            ->where('d_purchase_receive.pr_id', '=', $id)->get();
        
            return Datatables::of($detail)
            ->make(true);
    }

    public function prStore(Request $request){
        $id = $request->input('pr_id');

        $date = date('Ymd');
        $latestPR = DB::table('t_purchase_receive')
            ->where('pr_code', 'like', "PR-".$date."%")
            ->orderBy('pr_code', 'desc')
            ->first();

        if ($latestPR) {
            $lastNumber = (int) substr($latestPR->pr_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $pr_code = 'PR-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $data = TPurchaseReceiveModel::find($id);
        $data->pr_code = $pr_code;
        $data->pr_date = date("Y-m-d H:i", strtotime($request->input('pr_date')));
        $data->status = 1;
        $data->user_id = 1;
        $data->save();

        $det = DPurchaseReceiveModel::where('po_id', '=', $id)->get();
        foreach($det as $dt){
            $cek = MStokProdukModel::where('place', '=', 0)
                ->where('produk', '=', $dt->produk)
                ->order_by('id', 'DESC')->first();
            if($cek)  $stok_awal = $cek->balance;
            else $stok_awal = 0;

            $balance = $stok_awal + $dt->qty;

            $mutasi = new MStokProdukModel;
            $mutasi->refno = $pr_code;
            $mutasi->produk_id = $dt->produk;
            $mutasi->person = 1;
            $mutasi->stok_awal = $stok_awal;
            $mutasi->qty = $dt->qty;
            $mutasi->balance = $balance;
            $mutasi->place = 0;
            $mutasi->trx = 'PO received';
            $mutasi->save();
        }

        if($data){
            $return = array(
                "status" => true,
                "msg" =>"Data has been saved"
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Failed"
            );
        }

        echo json_encode($return);
    }

    public function prAdd(){
        $data = TPurchaseReceiveModel::where('user_id', '=', 1)
            ->where('status', '=', 0)->first();
        $prid = $data->id;

        $data = TPurchaseReceiveModel::where('user_id', '=', 1)
            ->where('status', '=', 0)->delete();
        $data = DPurchaseReceiveModel::where('pr_id', '=', $prid)
            ->delete();
    }

    public function prEdit($id){
        $data = TPurchaseReceiveModel::find($id);

        if($data){
            $return = array(
                "status" => true,
                "refno_po" => $data->refno_po,
                "pr_code" => $data->pr_code,
                "pr_date" => $data->pr_date
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data Not Found"
            );
        }

        echo json_encode($return);
    }
}
