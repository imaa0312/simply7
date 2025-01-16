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
use App\Models\MUser;
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
        $data = TPurchaseOrderModel::select('m_user.*', 'm_role.name as role_name')
            ->join('m_role', 'm_role.id', '=', 'm_user.role')
            ->get();
        return Datatables::of($data)
            ->addIndexColumn()
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

    //dropdown_selection

    public function asset(){
    $assets = AssetModel::all();
    return view('admin.purchasing.po.create', compact('assets'));
  }

  public function barang(){
    $assets = AssetModel::get('type_asset');
    $barang_name = MProdukModel::where('name', '=', $barang_name)->get();
    return response()->json($barang_name);
  }

    /**
    * Store a newly created repource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $this->validate($request, [
            'suplier' => 'required',
        ]);
        // dd($request->all());

        $setCode = $this->setCode();
        $sending_date = date('Y-m-d', strtotime($request->sending_date));
        $po_date = date('Y-m-d', strtotime($request->po_date));

        if( str_replace(array('.', ','), '' , $request->total_detail) ==  str_replace(array('.', ','), '' ,$request->total)){
            $totalAmmount = str_replace(array('.', ','), '' ,$request->total);
        }else{
            $totalAmmount = (int)(str_replace(array('.', ','), '' , $request->total_detail)) - (int)(str_replace(array('.', ','), '' ,$request->total));
        }
        $array = [];

        $i = 0;
        foreach($request->id_produk as $rowProduk)
        {
            $array[$i]['po_code'] = $setCode;
            $array[$i]['produk_id'] = $rowProduk;
            $i++;
        }

        $i = 0;
        foreach($request->persen as $rawpersen){
            $array[$i]['persen'] = $rawpersen;
            $i++;
        }

        $i = 0;
        foreach($request->potongan as $rawpotongan){
            $array[$i]['potongan'] = $rawpotongan;
            $i++;
        }

        $i = 0;
        foreach($request->hargaProduk as $rawhargaProduk){
            $array[$i]['hargaProduk'] = $rawhargaProduk;
            $i++;
        }

        $i = 0;
        foreach($request->jumlah as $rawjumlah){
            $array[$i]['qty'] = $rawjumlah;
            $i++;
        }
        $i = 0;
        foreach($request->free as $rawfree){
            $array[$i]['free'] = $rawfree;
            $i++;
        }

        $i = 0;
        foreach($request->subTotal as $rawsubTotal){
            $array[$i]['subtotal'] = str_replace(array('.', ','), '' , $rawsubTotal);
            $i++;
        }

        $i = 0;
        foreach($request->satuan as $rawsatuan){
            $array[$i]['satuan'] = $rawsatuan;
            $i++;
        }

        // echo "<pre>";
        //     print_r($array);
        //     dd($request->all(),$totalAmmount);
        //     die();
        // DB::beginTransaction();
        try{
            $insert = new TPurchaseOrderModel;
            $insert->po_code = $setCode;
            $insert->supplier = $request->suplier;
            $insert->po_date = $po_date;
            $insert->description = $request->description;
            $insert->jatuh_tempo = $request->top_hari;
            $insert->diskon_header_potongan = str_replace(array('.', ','), '' , $request->diskon_total_rp);
            $insert->diskon_header_persen = $request->diskon_total_persen;
            $insert->total_diskon_amount = $totalAmmount;
            $insert->type_asset = $request->type_asset;
            $insert->total_detail = str_replace(array('.', ','), '' , $request->total_detail);
            $insert->grand_total = (int)(str_replace(array('.', ','), '' ,$request->total));
            $insert->user_input = auth()->user()->id;
            $insert->description = $request->description;
            $insert->save();

            //detail-po
            for($n=0; $n<count($array); $n++){
                $totalTanpaDiskon = $array[$n]['hargaProduk'] * $array[$n]['qty'];
                $totalAmmountDetail = $totalTanpaDiskon - $array[$n]['subtotal'];

                $insertDetailTransaksi = new DPurchaseOrderModel;
                $insertDetailTransaksi->po_code = $array[$n]['po_code'];
                $insertDetailTransaksi->produk = $array[$n]['produk_id'];
                $insertDetailTransaksi->qty = $array[$n]['qty'];
                $insertDetailTransaksi->satuan_unit = $array[$n]['satuan'];
                $insertDetailTransaksi->free_qty = $array[$n]['free'];
                $insertDetailTransaksi->price = $array[$n]['hargaProduk'];
                $insertDetailTransaksi->total_neto = $array[$n]['subtotal'];
                $insertDetailTransaksi->diskon_amount = $totalAmmountDetail;
                $insertDetailTransaksi->diskon_potongan = $array[$n]['potongan'];
                $insertDetailTransaksi->diskon_persen = $array[$n]['persen'];
                $insertDetailTransaksi->price = $array[$n]['hargaProduk'];
                $insertDetailTransaksi->save();
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect()->route('po.index');
    }

    /**
    * Display the specified repource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function show($pocode)
    {
        $header = DB::table('t_purchase_order')
        ->join('m_supplier','t_purchase_order.supplier','m_supplier.id')
        ->join('m_user','t_purchase_order.user_input','m_user.id')
        ->select('t_purchase_order.*','m_supplier.name as supplier','m_user.name as user_input')
        ->where('t_purchase_order.po_code',$pocode)
        ->first();

        $detail = DB::table('d_purchase_order')
        ->join('m_produk','d_purchase_order.produk','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
        ->select('m_produk.*','d_purchase_order.*','m_satuan_unit.code as code_unit')
        ->where('po_code',$pocode)
        ->get();
        // dd($header);
        return view('admin.purchasing.po.detail',compact('header','detail'));
    }

    /**
    * Show the form for editing the specified repource.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function edit($pocode)
    {
        $header = DB::table('t_purchase_order')
        ->join('m_supplier','t_purchase_order.supplier','m_supplier.id')
        ->join('m_user','t_purchase_order.user_input','m_user.id')
        ->select('t_purchase_order.*','m_supplier.id as supplier_id','m_supplier.name as supplier','m_supplier.code','m_user.name as user_input')
        ->where('t_purchase_order.po_code',$pocode)
        ->first();

        $detail = DB::table('d_purchase_order')
        ->join('m_produk','d_purchase_order.produk','m_produk.id')
        ->where('po_code',$pocode)
        ->get();

        $supplier = MSupplierModel::orderBy('name','ASC')->get();

        $barang = MProdukModel::orderBy('id','DESC')->get();

        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();

        // dd($header,$detail);

        return view('admin.purchasing.po.update',compact('header','detail','supplier','barang','jangkaWaktu'));
    }

    public function waitinglist()
    {
        $dataPurchase = DB::table('t_purchase_order')
        ->select('m_supplier.name as supplier_name','t_purchase_order.po_date','t_purchase_order.status_aprove','t_purchase_order.po_code')
        ->join('m_supplier','m_supplier.id','t_purchase_order.supplier')
        ->where('status_aprove', 'in approval')
        ->orderBy('t_purchase_order.po_code','desc')
        ->get();

        return view('admin.purchasing.po.waiting-approval',compact('dataPurchase'));
    }

    public function sendApproval()
    {
        $dataPurchase = DB::table('t_purchase_order')
        ->select('m_supplier.name as supplier_name','t_purchase_order.po_date','t_purchase_order.status_aprove','t_purchase_order.po_code')
        ->join('m_supplier','m_supplier.id','t_purchase_order.supplier')
        ->where('status_aprove', 'in process')
        ->orderBy('t_purchase_order.po_code','desc')
        ->get();

        return view('admin.purchasing.po.send-approve',compact('dataPurchase'));
    }


    /**
    * Update the specified repource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, $pocode)
    {
        // dd($request->all());

        $sending_date = date('Y-m-d', strtotime($request->sending_date));
        $po_date = date('Y-m-d', strtotime($request->po_date));

        //( $request->total_detail ==  $request->total) ? $totalAmmount = $request->total  :  $totalAmmount = $request->total_detail - $request->total;
        if( str_replace(array('.', ','), '' , $request->total_detail) ==  str_replace(array('.', ','), '' ,$request->total)){
            $totalAmmount = str_replace(array('.', ','), '' ,$request->total);
        }else{
            $totalAmmount = (int)(str_replace(array('.', ','), '' , $request->total_detail)) - (int)(str_replace(array('.', ','), '' ,$request->total));
        }
        $array = [];

        $i = 0;
        foreach($request->id_produk as $rowProduk)
        {
            $array[$i]['produk_id'] = $rowProduk;
            $i++;
        }

        $i = 0;
        foreach($request->persen as $rawpersen){
            $array[$i]['persen'] = $rawpersen;
            $i++;
        }

        $i = 0;
        foreach($request->potongan as $rawpotongan){
            $array[$i]['potongan'] = $rawpotongan;
            $i++;
        }

        $i = 0;
        foreach($request->hargaProduk as $rawhargaProduk){
            $array[$i]['hargaProduk'] = $rawhargaProduk;
            $i++;
        }

        $i = 0;
        foreach($request->jumlah as $rawjumlah){
            $array[$i]['qty'] = $rawjumlah;
            $i++;
        }
        $i = 0;
        foreach($request->free as $rawfree){
            $array[$i]['free'] = $rawfree;
            $i++;
        }

        $i = 0;
        foreach($request->subTotal as $rawsubTotal){
            $array[$i]['subtotal'] = str_replace(array('.', ','), '' ,$rawsubTotal);
            $i++;
        }

        $i = 0;
        foreach($request->satuan as $rawsatuan){
            $array[$i]['satuan'] = $rawsatuan;
            $i++;
        }

        DB::beginTransaction();
        try{
            $update = TPurchaseOrderModel::where('po_code',$pocode)
            ->update([
                'po_date' => $po_date,
                'description' => $request->description,
                'jatuh_tempo' => $request->top_hari,
                'diskon_header_potongan' => (int)str_replace(array('.', ','), '' ,$request->diskon_total_rp),
                'diskon_header_persen' => $request->diskon_total_persen,
                'total_diskon_amount' => $totalAmmount,
                'total_detail' => (int)str_replace(array('.', ','), '' ,$request->total_detail),
                'grand_total' => (int)str_replace(array('.', ','), '' ,$request->total),
                'description' => $request->description,
                // 'type_asset' => $request->type_asset,

            ]);

            // delete-detail-po-old
            DPurchaseOrderModel::where('po_code',$pocode)->delete();

            //detail-po
            for($n=0; $n<count($array); $n++){
                // ( $array[$n]['hargaProduk'] == $array[$n]['subtotal']) ? $totalAmmountDetail = $array[$n]['subtotal']  :  $totalAmmountDetail =  $array[$n]['subtotal'] - $array[$n]['hargaProduk'];
                $totalTanpaDiskon = $array[$n]['hargaProduk'] * $array[$n]['qty'];
                $totalAmmountDetail = $totalTanpaDiskon - $array[$n]['subtotal'];

                $insertDetailTransaksi = new DPurchaseOrderModel;
                $insertDetailTransaksi->po_code = $pocode;
                $insertDetailTransaksi->produk = $array[$n]['produk_id'];
                $insertDetailTransaksi->qty = $array[$n]['qty'];
                $insertDetailTransaksi->satuan_unit = $array[$n]['satuan'];
                $insertDetailTransaksi->free_qty = $array[$n]['free'];
                $insertDetailTransaksi->price = $array[$n]['hargaProduk'];
                $insertDetailTransaksi->total_neto = $array[$n]['subtotal'];
                $insertDetailTransaksi->diskon_amount = $totalAmmountDetail;
                $insertDetailTransaksi->diskon_potongan = $array[$n]['potongan'];
                $insertDetailTransaksi->diskon_persen = $array[$n]['persen'];
                $insertDetailTransaksi->price = $array[$n]['hargaProduk'];
                $insertDetailTransaksi->save();
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect()->route('po.index');
    }

    /**
    * Remove the specified repource from storage.
    *
    * @param  int  $id
    * @return \Illuminate\Http\Response
    */
    public function destroy($pocode)
    {
        TPurchaseOrderModel::where('po_code',$pocode)->delete();
        DPurchaseOrderModel::where('po_code',$pocode)->delete();

        return redirect()->route('po.index');
    }

    public function inApprove($pocode)
    {
        TPurchaseOrderModel::where('po_code',$pocode)->update(['status_aprove' => 'in approval']);

        return redirect()->route('po.index');
    }

    public function approve($pocode)
    {
        TPurchaseOrderModel::where('po_code',$pocode)->update(['status_aprove' => 'approved']);

        return redirect()->route('po.index');
    }

    public function reject($pocode)
    {
        TPurchaseOrderModel::where('po_code',$pocode)->update(['status_aprove' => 'reject']);

        return redirect()->route('po.index');
    }

    protected function setCode()
    {
        $getLastCode = DB::table('t_purchase_order')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'POTK'.$dataDate.$nol.$getLastCode;
    }

    public function getProduk(Request $request)
    {
        // return dd($request->all());
        $result = DB::table('m_produk')
            ->select('m_produk.*','m_satuan_unit.code as code_unit')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
            ->where('m_produk.id', $request->id)
            ->first();

        $produk = $request->produk;


        $cekproduk = 0;
        if ($produk != null || $produk != '') {
            foreach ($produk as $i => $raw_produk) {
                if ($request->id == $produk[$i]) {
                    $cekproduk = 1;
                }
            }
        }

        if( $cekproduk == 0 ){

            $row = "<tr id='tr_".$request->id."'>";

            $row .= "<input type='hidden' value='". $request->id ."'name='id_produk[".$request->length."]' id='produk_id_". $request->id."'>";


            $row .= "<td> <input type='text' class='form-control input-sm' readonly value='".$result->name."' name='produk[".$request->length."]' data-toggle='tooltip' data-placement='top' title='".$result->name."' style='curpor:pointer;'></td>";

            $row .= "<td> <input type='text' class='form-control input-sm text-only-number' id='". $request->id."_persen'
            lass='form-control input-sm' onkeyup='hitungSubTotal(". $request->id.")' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' autocomplete='off' onchange='hitungSubTotal(". $request->id.")' value='0' name='persen[".$request->length."]'></td>";

            $row .= "<td> <input type='text' id='". $request->id."_potongan' class='form-control input-sm text-only-number' onkeyup='hitungSubTotal(". $request->id.")' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' onchange='hitungSubTotal(". $request->id.")' value='0' name='potongan[".$request->length."]'></td>";

            $row .= "<td>
            <input type='number' min='1' class='form-control input-sm ". $request->id."_produkPrice' value='' name='hargaProduk[".$request->length."]' id='". $request->id."_harga' onkeyup='hitungSubTotal(".$request->id.");' onkeypress='hitungSubTotal(". $request->id.");' autocomplete='off' onchange='hitungSubTotal(". $request->id.");' required>
            </td>";

            $row .= "<td> <input type='number' min='1' max='' id='".$request->id."_jumlah' class='form-control input-sm' onkeyup='hitungSubTotal(".$request->id.");' onkeypress='hitungSubTotal(". $request->id.");' autocomplete='off' onchange='hitungSubTotal(". $request->id.");' name='jumlah[".$request->length."]' value='1'></td>";

            $row .= "<td> <input type='number' min='0' max='' class='form-control input-sm' onkeyup='hitungSubTotal(".$request->id.");' name='free[".$request->length."]' value='0'></td>";

            $row .= "<td> <input type='text' class='form-control input-sm' readonly name='satuan[".$request->length."]' value='".$result->code_unit."' ></td>";

            $row .= "<td> <input type='text' readonly class='form-control input-sm ". $request->id."_subTotal' value='' name='subTotal[". $request->id."]' id='". $request->id."_subTotal'></td>";

            $row .= "<td> <button type='button' value='".$request->id."' class='btn btn-danger btn-sm btn-delete' title='Hapus' onclick='hapusBaris(". $request->id.")'><span class='fa fa-trash'></span></button></td>";

            $row .= "</tr>";
        }
        return $row;
    }

    public function getProdukByBarcode(Request $request)
    {
        // return dd($request->all());
        $result = DB::table('m_produk')
            ->select('m_produk.*','m_satuan_unit.code as code_unit')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil_id')
            ->where('m_produk.barcode', $request->barcode)
            ->first();

        $produk = $request->produk;


        $cekproduk = 0;
        if ($produk != null || $produk != '') {
            foreach ($produk as $i => $raw_produk) {
                if ($result->id == $produk[$i]) {
                    $cekproduk = 1;
                }
            }
        }

        if( $cekproduk == 0 ){

            $row = "<tr id='tr_".$result->id."'>";

            $row .= "<input type='hidden' value='". $result->id ."'name='id_produk[".$request->length."]' id='produk_id_". $result->id."'>";


            $row .= "<td> <input type='text' class='form-control input-sm' readonly value='".$result->name."' name='produk[".$request->length."]' data-toggle='tooltip' data-placement='top' title='".$result->name."' style='curpor:pointer;'></td>";

            $row .= "<td> <input type='text' class='form-control input-sm text-only-number' id='". $result->id."_persen'
            lass='form-control input-sm' onkeyup='hitungSubTotal(". $result->id.")' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' autocomplete='off' onchange='hitungSubTotal(". $result->id.")' value='0' name='persen[".$request->length."]'></td>";

            $row .= "<td> <input type='text' id='". $result->id."_potongan' class='form-control input-sm text-only-number' onkeyup='hitungSubTotal(". $result->id.")' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' onchange='hitungSubTotal(". $result->id.")' value='0' name='potongan[".$request->length."]'></td>";

            $row .= "<td>
            <input type='number' min='1' class='form-control input-sm ". $result->id."_produkPrice' value='' name='hargaProduk[".$request->length."]' id='". $result->id."_harga' onkeyup='hitungSubTotal(".$result->id.");' onkeypress='hitungSubTotal(". $result->id.");' autocomplete='off' onchange='hitungSubTotal(". $result->id.");' required>
            </td>";

            $row .= "<td> <input type='number' min='1' max='' id='".$result->id."_jumlah' class='form-control input-sm' onkeyup='hitungSubTotal(".$result->id.");' onkeypress='hitungSubTotal(". $result->id.");' autocomplete='off' onchange='hitungSubTotal(". $result->id.");' name='jumlah[".$request->length."]' value='1'></td>";

            $row .= "<td> <input type='number' min='0' max='' class='form-control input-sm' onkeyup='hitungSubTotal(".$result->id.");' name='free[".$request->length."]' value='0'></td>";

            $row .= "<td> <input type='text' class='form-control input-sm' readonly name='satuan[".$request->length."]' value='".$result->code_unit."' ></td>";

            $row .= "<td> <input type='text' readonly class='form-control input-sm ". $result->id."_subTotal' value='' name='subTotal[". $result->id."]' id='". $result->id."_subTotal'></td>";

            $row .= "<td> <button type='button' value='".$result->id."' class='btn btn-danger btn-sm btn-delete' title='Hapus' onclick='hapusBaris(". $result->id.")'><span class='fa fa-trash'></span></button></td>";

            $row .= "</tr>";
        }

        return [
            "row" => $row,
            "produk_id" => $result->id
        ];
    }

    public function detailSupplier($id)
    {
        return Response::json(MSupplierModel::find($id));
    }

    public function assetBarang($type)
    {
        $data = DB::table('m_produk')->where('type_asset','=',$type)->get();

        return Response::json($data);
    }

    public function laporanPO()
    {
        $dataSupplier = DB::table('m_supplier')
        ->join('t_purchase_order', 'm_supplier.id', '=', 't_purchase_order.supplier')
        ->select('m_supplier.id as supplier_id','name')
        ->groupBy('m_supplier.id', 'name')
        ->get();

        $dataBarang = DB::table('m_produk')
        ->select('id as barang_id','name')
        ->groupBy('id','name')
        ->get();

        return view('admin.purchasing.po.laporan',compact('dataSupplier','dataBarang'));
    }

    public function getSupplierByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataSupplier = DB::table('m_supplier')
        ->join('t_purchase_order', 'm_supplier.id', '=', 't_purchase_order.supplier')
        ->select('m_supplier.id as supplier_id','name','main_address')
        ->where('t_purchase_order.po_date','>=',date('Y-m-d', strtotime($tglmulai)))
        ->where('t_purchase_order.po_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
        ->groupBy('m_supplier.id')
        ->get();
        return Response::json($dataSupplier);
    }

    public function getPOBySupplier($supplierID)
    {
        $dataPO = DB::table('t_purchase_order')
        ->where('supplier',$supplierID)
        ->orderBy('po_code')
        ->get();
        return Response::json($dataPO);
    }

    public function getBarangByPo($poId)
    {
        if ($poId == '0') {
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_purchase_order', 'd_purchase_order.produk', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->groupBy('m_produk.id')
            ->get();
        }else{
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_purchase_order', 'd_purchase_order.produk', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('d_purchase_order.po_code',$poId)
            ->groupBy('m_produk.id')
            ->get();
        }

        return Response::json($dataBarang);
    }

    public function getBarangPOBySupplier($supplier)
    {
        if ($supplier == '0') {
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_purchase_order', 'd_purchase_order.produk', '=', 'm_produk.id')
            ->join('t_purchase_order', 'd_purchase_order.po_code', '=', 't_purchase_order.po_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            //->where('t_purchase_order.supplier',$supplier)
            ->groupBy('m_produk.id')
            ->get();
        }else{
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_purchase_order', 'd_purchase_order.produk', '=', 'm_produk.id')
            ->join('t_purchase_order', 'd_purchase_order.po_code', '=', 't_purchase_order.po_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('t_purchase_order.supplier',$supplier)
            ->groupBy('m_produk.id')
            ->get();
        }

        return Response::json($dataBarang);
    }

    public function laporanPOPD()
    {
        $datasupplier = DB::table('m_supplier')
        ->join('t_purchase_order', 'm_supplier.id', '=', 't_purchase_order.supplier')
        ->select('m_supplier.id as supplier_id','name')
        //->where
        ->groupBy('m_supplier.id','name')
        ->get();

        $dataBarang = DB::table('m_produk')
        ->rightjoin('d_purchase_order', 'd_purchase_order.produk', '=', 'm_produk.id')
        ->join('t_purchase_order', 'd_purchase_order.po_code', '=', 't_purchase_order.po_code')
        ->select('m_produk.id as barang_id','m_produk.name')
        //->where('t_purchase_order.supplier',$supplier)
        ->groupBy('m_produk.id','name')
        ->get();

        return view('admin.purchasing.po.laporanpopd',compact('datasupplier','dataBarang'));
    }
    public function getBarangPOPDBySupplier($supplier)
    {
        if ($supplier == '0') {
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_purchase_order', 'd_purchase_order.produk', '=', 'm_produk.id')
            ->join('t_purchase_order', 'd_purchase_order.po_code', '=', 't_purchase_order.po_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            //->where('t_purchase_order.supplier',$supplier)
            ->groupBy('m_produk.id')
            ->get();
        }else{
            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_purchase_order', 'd_purchase_order.produk', '=', 'm_produk.id')
            ->join('t_purchase_order', 'd_purchase_order.po_code', '=', 't_purchase_order.po_code')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('t_purchase_order.supplier',$supplier)
            ->groupBy('m_produk.id')
            ->get();
        }

        return Response::json($dataBarang);
    }

    public function cancelPO($pocode)
    {
        $dataPO = TPurchaseOrderModel::where('po_code',$pocode)
        ->first();
        $reason = MReasonModel::orderBy('id','DESC')->get();

        return view('admin.purchasing.po.cancel',compact('dataPO','reason'));
    }

    public function cancelPOpost(Request $request)
    {
        // dd($request->all(),auth()->user()->id);
        DB::beginTransaction();
        try {
            DB::table('t_purchase_order')->where('po_code',$request->po_code)->update([
                'cancel_reason' => $request->cancel_reason,
                'cancel_description' => $request->cancel_description,
                'user_cancel' => auth()->user()->id,
                'status_aprove' => 'cancel',
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
        }

        return redirect('admin/transaksi/purchase-order');
    }

    public function closePO($pocode)
    {
        DB::table('t_purchase_order')->where('po_code',$pocode)->update([
            'status_aprove' => 'closed',
        ]);

        return redirect('admin/transaksi/purchase-order');
    }

    public function copyPurchaseOrder($pocode)
    {
        $header = DB::table('t_purchase_order')
        ->join('m_supplier','m_supplier.id','=','t_purchase_order.supplier')
        ->select('t_purchase_order.*','m_supplier.name as supplier','m_supplier.code as sup_code','m_supplier.id as sup_id')
        ->where('t_purchase_order.po_code','=',$pocode)
        ->first();

        $detail = DB::table('d_purchase_order')
        ->join('m_produk','d_purchase_order.produk','=','m_produk.id')
        ->where('po_code',$pocode)
        ->get();

        $barang = DB::table('m_produk')->get();

        $pocode = $this->setCode();
        // dd($header);
        return view('admin.purchasing.po.copy-po',compact('header','detail','pocode','barang'));

    }

    public function apiPo()
    {
        // $users = User::select(['id', 'name', 'email', 'password', 'created_at', 'updated_at']);
        $po = DB::table('t_purchase_order')
            ->join('m_supplier','t_purchase_order.supplier','m_supplier.id')
            ->select('t_purchase_order.*','m_supplier.name')
            ->orderBy('t_purchase_order.id','DESC')
            ->get();
        //dd($po);
        foreach ($po as $dataPO) {
            $sj = true;
            $cekSjm = DB::table('t_surat_jalan_masuk')
                    ->where('po_code',$dataPO->po_code)
                    ->get();
            // dd($cekSj);
            if (count($cekSjm) > 0 ) {
                $sj = false; // jika ada false
            }
            $dataPO->sj = $sj;
        }

        $roleUser = \DB::table('m_role')
                ->where('id',Auth::user()->role)
                ->first();
        return Datatables::of($po)
        ->addColumn('action', function ($po) use ($roleUser) {
            if(  $po->status_aprove == 'in process'){
                return '<table id="tabel-in-opsi">'.
                '<tr>'.
                    '<td>'.
                        '<a href="'. url('admin/report-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/transaksi/purchase-order/'.$po->po_code.'/edit') .'" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ubah '. $po->po_code .'">
                        <span class="fa fa-edit"></span> </a>'.'&nbsp;'.
                        '<a href="'. url('purchasing/po-delete/'.$po->po_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm"  data-toggle="tooltip" data-placement="top" title="Hapus '. $po->po_code .'">
                        <span class="fa fa-trash"></span>
                        </a>'.'&nbsp;'.
                        '<a href="'. route('po.in-approve',$po->po_code) .'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Kirim Persetujuan '. $po->po_code .'"> <i class="fa fa-paper-plane"></i> </a>'.'&nbsp;'.
                        '<a href="'. url('admin/transaksi-purchasing-order/copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                    '</td>'.
                '</tr>'.
            '</table>';
            }elseif(  $po->status_aprove == 'in approval'){
                if(  $roleUser->status_approval == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'. url('admin/report-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                            '<a href="'. route('po.approved',$po->po_code) .'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Setujui '. $po->po_code .'"> <i class="fa fa-check"></i> </a>'.'&nbsp;'.
                            '<a href="'. route('po.reject',$po->po_code) .'" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Tolak '. $po->po_code .'"> <i class="fa fa-minus-circle" aria-hidden="true"></i></a>'.'&nbsp;'.
                            '<a href="'. url('admin/transaksi-purchasing-order/copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                        '</td>'.
                    '</tr>'.
                '</table>';
                }

            }elseif($po->status_aprove == 'approved' ){
                if( $po->sj == true){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'. url('admin/report-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                            '<a href="'. url('admin/puchase-order/cancel/'.$po->po_code) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $po->po_code .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
                            '<a href="'. url('admin/transaksi-purchasing-order/copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                        '</td>'.
                    '</tr>'.
                '</table>';
            }

                if( $po->sj == false){
                    return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/puchase-order/close/'.$po->po_code) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Close '. $po->po_code .'" ><span class="fa fa-hand-paper-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/transaksi-purchasing-order/copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                            '</td>'.
                        '</tr>'.
                    '</table>';
                }
            }
                else{
                    return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/report-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/transaksi-purchasing-order/copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'. '</tr>'.
                            '</td>'.
                        '</tr>'.
                    '</table>';
                }
            })
            ->editColumn('code', function($po){
                return '<a href="'. route('po.show',$po->po_code) .'">'. $po->po_code .'</a> ';
            })
            ->editColumn('po_date', function($po){
                return date('d-m-Y',strtotime($po->po_date));
            })
            ->editColumn('type_asset', function($produk){
              return ucfirst($produk->type_asset);
            })

            ->editColumn('status_aprove', function($po){
                if( $po->status_aprove == 'in process' ){
                    return '<span class="label label-default">in process</span>';}
                elseif(  $po->status_aprove == 'in approval'){
                    return '<span class="label label-info">in approval</span>';}
                elseif ($po->status_aprove == 'approved'){
                    return '<span class="label label-success">approved</span>';}
                elseif ($po->status_aprove == 'reject'){
                    return '<span class="label label-warning">reject</span>';}
                elseif ($po->status_aprove == 'cancel'){
                    return '<span class="label label-warning">cancel</span>';}
                else{
                    return '<span class="label label-danger">close</span>';}
                })
                ->addIndexColumn()
                ->rawColumns(['code','action','status_aprove','po_from','po_date'])
                ->make(true);
    }
}
