<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Response;
use Illuminate\Http\Request;
use App\Models\MProdukModel;
use App\Models\MSupplierModel;
use App\Models\MJangkaWaktu;
use App\Models\MReasonModel;
use App\Models\TFixedAssetPoModel;
use Yajra\Datatables\Datatables;


class TFixedAssetPoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $po = DB::table('t_fixed_asset_po')
            ->join('m_supplier','t_fixed_asset_po.supplier','m_supplier.id')
            ->select('t_fixed_asset_po.*','m_supplier.name')
            ->orderBy('t_fixed_asset_po.id','DESC')
            ->get();

        // dd($po);

        // foreach ($po as $dataPO) {
        //     $sj = true;
        //     $cekSjm = DB::table('t_surat_jalan_masuk')
        //     ->where('po_code',$dataPO->po_code)
        //     ->first();
        //     // dd($cekSj);
        //     if (count($cekSjm) > 0 ) {
        //         $sj = false; // jika ada false
        //     }
        //     $dataPO->sj = $sj;
        // }
        //dd($po);
        return view('admin.fixed-asset.po.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $codePo = $this->setCode();

        $supllier = MSupplierModel::orderBy('name','ASC')->get();

        $barang = MProdukModel::where('type_asset','asset')->orderBy('name','ASC')->get();

        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();

        return view('admin.fixed-asset.po.create',compact('supllier','codePo','barang','jangkaWaktu'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     public function update(Request $request)
     {
         dd($request->all());

         $po_code = $request->po_code;

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
             DB::table('t_fixed_asset_po')
                 ->where('po_code',$po_code)
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
                     'type_asset' => $request->type_asset,
                 ]);

             //delete-detail-po-old
             DB::table('d_fixed_asset_po')->where('po_code',$po_code)->delete();
             // DPurchaseOrderModel::where('po_code',$pocode)->delete();

             //detail-po
             for($n=0; $n<count($array); $n++){
                 $totalTanpaDiskon = $array[$n]['hargaProduk'] * $array[$n]['qty'];
                 $totalAmmountDetail = $totalTanpaDiskon - $array[$n]['subtotal'];

                 DB::table('d_fixed_asset_po')
                     ->insert([
                         'po_code' => $po_code,
                         'produk' => $array[$n]['produk_id'],
                         'qty'=> $array[$n]['qty'],
                         'satuan_unit'=> $array[$n]['satuan'],
                         // 'free_qty'=> $array[$n]['free'],
                         'price'=> $array[$n]['hargaProduk'],
                         'total_neto'=> $array[$n]['subtotal'],
                         'diskon_amount'=> $totalAmmountDetail,
                         'diskon_potongan'=> $array[$n]['potongan'],
                         'diskon_persen'=> $array[$n]['persen']
                     ]);
             }

             DB::commit();
         }catch(\Exception $e){
             DB::rollback();
             dd($e);
         }

         return redirect('admin/asset/po');
     }

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
        foreach($request->subTotal as $rawsubTotal){
            $array[$i]['subtotal'] = str_replace(array('.', ','), '' , $rawsubTotal);
            $i++;
        }

        $i = 0;
        foreach($request->satuan as $rawsatuan){
            $array[$i]['satuan'] = $rawsatuan;
            $i++;
        }

        DB::beginTransaction();
        try{
            DB::table('t_fixed_asset_po')
                ->insert([
                    'po_code' => $setCode,
                    'supplier' => $request->suplier,
                    'po_date'=> $po_date,
                    'description'=> $request->description,
                    'jatuh_tempo'=> $request->top_hari,
                    'diskon_header_potongan'=> str_replace(array('.', ','), '' , $request->diskon_total_rp),
                    'diskon_header_persen'=> $request->diskon_total_persen,
                    'total_diskon_amount'=> $totalAmmount,
                    'type_asset'=> $request->type_asset,
                    'total_detail'=> str_replace(array('.', ','), '' , $request->total_detail),
                    'grand_total'=> (int)(str_replace(array('.', ','), '' ,$request->total)),
                    'user_input'=> auth()->user()->id,
                    'description'=> $request->description
                ]);

            //detail-po
            for($n=0; $n<count($array); $n++){
                $totalTanpaDiskon = $array[$n]['hargaProduk'] * $array[$n]['qty'];
                $totalAmmountDetail = $totalTanpaDiskon - $array[$n]['subtotal'];

                DB::table('d_fixed_asset_po')
                    ->insert([
                        'po_code' => $array[$n]['po_code'],
                        'produk' => $array[$n]['produk_id'],
                        'qty'=> $array[$n]['qty'],
                        'satuan_unit'=> $array[$n]['satuan'],
                        // 'free_qty'=> $array[$n]['free'],
                        'price'=> $array[$n]['hargaProduk'],
                        'total_neto'=> $array[$n]['subtotal'],
                        'diskon_amount'=> $totalAmmountDetail,
                        'diskon_potongan'=> $array[$n]['potongan'],
                        'diskon_persen'=> $array[$n]['persen']
                    ]);
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e);
        }

        return redirect('admin/asset/po');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function destroy($pocode)
     {
         $deletePo = DB::table('t_fixed_asset_po')->where('po_code',$pocode)->delete();

         return view('admin.fixed-asset.po.index',compact('deletePo'));
     }
    public function show($pocode)
    {
        $header = DB::table('t_fixed_asset_po')
            ->join('m_supplier','t_fixed_asset_po.supplier','m_supplier.id')
            ->join('m_user','t_fixed_asset_po.user_input','m_user.id')
            ->select('t_fixed_asset_po.*','m_supplier.name as supplier','m_user.name as user_input')
            ->where('t_fixed_asset_po.po_code',$pocode)
            ->first();

        $detail = DB::table('d_fixed_asset_po')
            ->join('m_produk','d_fixed_asset_po.produk','m_produk.id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->select('m_produk.*','d_fixed_asset_po.*','m_satuan_unit.code as code_unit')
            ->where('po_code',$pocode)
            ->get();
        // dd($header);
        return view('admin.fixed-asset.po.detail',compact('header','detail'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function cancelPOpost(Request $request)
     {
         // dd($request->all(),auth()->user()->id);
         DB::beginTransaction();
         try {
             DB::table('t_fixed_asset_po')->where('po_code',$request->po_code)->update([
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

         return redirect('admin/asset/po');
     }

     public function cancelPO($pocode)
     {
         $dataPO = DB::table('t_fixed_asset_po')->where('po_code',$pocode)->first();
         $reason = DB::table('m_reason')->orderBy('id','DESC')->get();

         return view('admin.fixed-asset.po.cancel',compact('dataPO','reason'));
     }

     public function getProduk(Request $request)
    {
        // return dd($request->all());

        // $result = MProdukModel::find($request->id);
        $result = DB::table('m_produk')
            ->select('m_produk.*','m_satuan_unit.code as code_unit')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
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


            $row .= "<td> <input type='text' class='form-control  input-sm' readonly value='".$result->name."' name='produk[".$request->length."]' data-toggle='tooltip' data-placement='top' title='".$result->name."' style='curpor:pointer;'></td>";

            $row .= "<td> <input type='text' class='form-control input-sm text-only-number' id='". $request->id."_persen'
            lass='form-control input-sm' onkeyup='hitungSubTotal(". $request->id.")' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' autocomplete='off' onchange='hitungSubTotal(". $request->id.")' value='0' name='persen[".$request->length."]'></td>";

            $row .= "<td> <input type='text' id='". $request->id."_potongan' class='form-control input-sm text-only-number' onkeyup='hitungSubTotal(". $request->id.")' onkeypress='return event.charCode >= 48 && event.charCode <= 57;' onchange='hitungSubTotal(". $request->id.")' value='0' name='potongan[".$request->length."]'></td>";

            $row .= "<td>
            <input type='number' min='1' class='form-control input-sm ". $request->id."_produkPrice' value='' name='hargaProduk[".$request->length."]' id='". $request->id."_harga' onkeyup='hitungSubTotal(".$request->id.");' onkeypress='hitungSubTotal(". $request->id.");' autocomplete='off' onchange='hitungSubTotal(". $request->id.");' required>
            </td>";

            $row .= "<td> <input type='number' min='1' max='' id='".$request->id."_jumlah' class='form-control input-sm' onkeyup='hitungSubTotal(".$request->id.");' onkeypress='hitungSubTotal(". $request->id.");' autocomplete='off' onchange='hitungSubTotal(". $request->id.");' name='jumlah[".$request->length."]' value='1'></td>";

            // $row .= "<td> <input type='number' min='0' max='' class='form-control input-sm' onkeyup='hitungSubTotal(".$request->id.");' name='free[".$request->length."]' value='0'></td>";

            $row .= "<td> <input type='text' class='form-control input-sm' readonly name='satuan[".$request->length."]' value='".$result->code_unit."' ></td>";

            $row .= "<td> <input type='text' readonly class='form-control input-sm ". $request->id."_subTotal' value='' name='subTotal[". $request->id."]' id='". $request->id."_subTotal'></td>";

            $row .= "<td> <button type='button' value='".$request->id."' class='btn btn-danger btn-sm btn-delete' title='Hapus' onclick='hapusBaris(". $request->id.")'><span class='fa fa-trash'></span></button></td>";

            $row .= "</tr>";
        }
        return $row;
    }

     public function copyFixedPo($pocode)
     {
       $header = DB::table('t_fixed_asset_po')
       ->join('m_supplier','m_supplier.id','=','t_fixed_asset_po.supplier')
       ->select('t_fixed_asset_po.*','m_supplier.name as supplier','m_supplier.code as sup_code','m_supplier.id as sup_id')
       ->where('t_fixed_asset_po.po_code','=',$pocode)
       ->first();

       $detail = DB::table('d_fixed_asset_po')
       ->join('m_produk','d_fixed_asset_po.produk','=','m_produk.id')
       ->where('po_code',$pocode)
       ->get();

       $barang = DB::table('m_produk')->where('type_asset','asset')->get();

       $pocode = $this->setCode();

       $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();
       // dd($header);
       return view('admin.fixed-asset.po.copy-Po',compact('header','detail','pocode','barang','jangkaWaktu'));
     }

    public function edit($pocode)
    {
        $header = DB::table('t_fixed_asset_po')
            ->join('m_supplier','t_fixed_asset_po.supplier','m_supplier.id')
            ->join('m_user','t_fixed_asset_po.user_input','m_user.id')
            ->select('t_fixed_asset_po.*','m_supplier.id as supplier_id','m_supplier.name as supplier','m_supplier.code','m_user.name as user_input')
            ->where('t_fixed_asset_po.po_code',$pocode)
            ->first();

        $detail = DB::table('d_fixed_asset_po')
            ->join('m_produk','d_fixed_asset_po.produk','m_produk.id')
            ->where('po_code',$pocode)
            ->get();
            // dd($detail);

        $supplier = MSupplierModel::orderBy('name','ASC')->get();

        $barang = MProdukModel::orderBy('name','ASC')->where('type_asset','asset')->get();

        $jangkaWaktu = MJangkaWaktu::orderBy('jangka_waktu')->get();

        // dd($header,$detail);

        return view('admin.fixed-asset.po.update',compact('header','detail','supplier','barang','jangkaWaktu'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function inApprove(Request $request,$pocode)
    {
        $asset = DB::table('t_fixed_asset_po')
            ->join('d_fixed_asset_po','d_fixed_asset_po.po_code','t_fixed_asset_po.po_code')
            ->select('d_fixed_asset_po.*')
            ->where('d_fixed_asset_po.po_code',$pocode)
            ->orderBy('t_fixed_asset_po.po_code', 'desc')
            ->get();
        // dd($asset);
        DB::beginTransaction();
        try{
            foreach($asset as $as){
            $qty_sebelum = DB::table('d_fixed_asset_po')
            ->where('po_code',$pocode)
            ->where('produk',$as->produk)
            ->first();
            // dd($qty_sebelum);
            $qty_setelah = $qty_sebelum->qty + $as->save_qty;

            $setelah = DB::table('d_fixed_asset_po')
            ->where('po_code',$pocode)
            ->where('produk',$as->produk)
            ->update([
                'save_qty' => $qty_setelah,
            ]);
            // dd($setelah);
        }
            DB::commit();
            $success = true;
        }catch(\Exception $e){
            dd($e);
            $success = false;
            DB::rollback();
        }

        DB::table('t_fixed_asset_po')
            ->where('po_code',$pocode)
            ->update(['status_aprove' => 'in approval']);

        return redirect('admin/asset/po');
    }

    public function approve($pocode)
    {
        DB::table('t_fixed_asset_po')
            ->where('po_code',$pocode)
            ->update(['status_aprove' => 'approved']);

        return redirect('admin/asset/po');
    }

    public function reject($pocode)
    {
        DB::table('t_fixed_asset_po')
            ->where('po_code',$pocode)
            ->update(['status_aprove' => 'reject']);

        return redirect('admin/asset/po');
    }

    protected function setCode()
    {
        $getLastCode = DB::table('t_fixed_asset_po')->select('id')->orderBy('id', 'desc')->pluck('id')->first();

        $dataDate = date('ym');

        $getLastCode = $getLastCode +1;

        $nol = null;

        if(strlen($getLastCode) == 1){$nol = "000";}elseif(strlen($getLastCode) == 2){$nol = "00";}elseif(strlen($getLastCode) == 3){$nol = "0";}else{$nol = null;}

        return 'AO'.$dataDate.$nol.$getLastCode;
    }

    public function apiPo()
    {
        $po = DB::table('t_fixed_asset_po')
            ->join('m_supplier','t_fixed_asset_po.supplier','m_supplier.id')
            ->select('t_fixed_asset_po.*','m_supplier.name')
            ->orderBy('t_fixed_asset_po.id','DESC')
            ->get();
        //dd($po);
        foreach ($po as $dataPO) {
            $sj = true;
            $cekSjm = DB::table('t_fixed_asset_pd')
                    ->where('po_code',$dataPO->po_code)
                    ->first();
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
                        '<a href="'. url('admin/asset/report-asset-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/po-edit/'.$po->po_code.'/edit') .'" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Ubah '. $po->po_code .'">
                        <span class="fa fa-edit"></span> </a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/po-delete/'.$po->po_code) .'" onclick="return confirm('."'Apakah Anda Yakin Untuk Menghapus ?'".')" class="btn btn-danger btn-sm"  data-toggle="tooltip" data-placement="top" title="Hapus '. $po->po_code .'">
                        <span class="fa fa-trash"></span>
                        </a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/po-send-approve/'.$po->po_code) .'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Kirim Persetujuan '. $po->po_code .'"> <i class="fa fa-paper-plane"></i> </a>'.'&nbsp;'.
                        '<a href="'. url('admin/asset/po-copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                    '</td>'.
                '</tr>'.
            '</table>';
            }elseif(  $po->status_aprove == 'in approval'){
                if(  $roleUser->status_approval == 1){
                    return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'. url('admin/asset/report-asset-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                            '<a href="'. url('admin/asset/po-approved/'.$po->po_code) .'" class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Setujui '. $po->po_code .'"> <i class="fa fa-check"></i> </a>'.'&nbsp;'.
                            '<a href="'. url('admin/asset/po-reject/'.$po->po_code) .'" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Tolak '. $po->po_code .'"> <i class="fa fa-minus-circle" aria-hidden="true"></i></a>'.'&nbsp;'.
                            '<a href="'. url('admin/asset/po-copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                        '</td>'.
                    '</tr>'.
                '</table>';
                }

            }elseif($po->status_aprove == 'approved' ){
                if( $po->sj == true){return '<table id="tabel-in-opsi">'.
                    '<tr>'.
                        '<td>'.
                            '<a href="'. url('admin/asset/report-asset-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                            '<a href="'. url('admin/asset/po-cancel/'.$po->po_code) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Cancel '. $po->po_code .'" ><span class="fa fa-times"></span></a>'.'&nbsp;'.
                            '<a href="'. url('admin/asset/po-copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                        '</td>'.
                    '</tr>'.
                '</table>';
                }

                if( $po->sj == false){
                    return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/asset/report-asset-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '. $po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/asset/close/'.$po->po_code) .'" class="btn btn-sm btn-danger"data-toggle="tooltip" title="Close '. $po->po_code .'" ><span class="fa fa-hand-paper-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/asset/po-copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'.
                            '</td>'.
                        '</tr>'.
                    '</table>';
                }
            }
                else{
                    return '<table id="tabel-in-opsi">'.
                        '<tr>'.
                            '<td>'.
                                '<a href="'. url('admin/asset/report-asset-po/'.$po->po_code) .'" class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Print Out '.$po->po_code .'"><span class="fa fa-file-pdf-o"></span></a>'.'&nbsp;'.
                                '<a href="'. url('admin/asset/po-copy/'.$po->po_code) .'" class="btn btn-default btn-sm" data-toggle="tooltip" data-placement="top" title="Salin"> <i class="fa fa-files-o"></i> </a>'.'&nbsp;'. '</tr>'.
                            '</td>'.
                        '</tr>'.
                    '</table>';
                }
            })
            ->editColumn('code', function($po){
                return '<a href="'. url('admin/asset/po-detail/'.$po->po_code) .'">'. $po->po_code .'</a> ';
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

    public function waitinglistPo()
    {
        $dataPurchase = DB::table('t_fixed_asset_po')
        ->select('m_supplier.name as supplier_name','t_fixed_asset_po.po_date','t_fixed_asset_po.status_aprove','t_fixed_asset_po.po_code')
        ->join('m_supplier','m_supplier.id','t_fixed_asset_po.supplier')
        ->where('status_aprove', 'in approval')
        ->orderBy('t_fixed_asset_po.po_code','desc')
        ->get();

        return view('admin.fixed-asset.po.waiting-approval',compact('dataPurchase'));
    }

    public function sendApprovalPo()
    {
        $dataPurchase = DB::table('t_fixed_asset_po')
        ->select('m_supplier.name as supplier_name','t_fixed_asset_po.po_date','t_fixed_asset_po.status_aprove','t_fixed_asset_po.po_code')
        ->join('m_supplier','m_supplier.id','t_fixed_asset_po.supplier')
        ->where('status_aprove', 'in process')
        ->orderBy('t_fixed_asset_po.po_code','desc')
        ->get();

        return view('admin.fixed-asset.po.send-approve',compact('dataPurchase'));
    }

    public function laporanPO()
    {
        $dataSupplier = DB::table('m_supplier')
        ->join('t_fixed_asset_po', 'm_supplier.id', '=', 't_fixed_asset_po.supplier')
        ->select('m_supplier.id as supplier_id','m_supplier.name')
        ->groupBy('m_supplier.id')
        ->get();

        $dataBarang = DB::table('m_produk')
        ->select('id as barang_id','name')
        ->groupBy('id')
        ->get();
        // dd($dataSupplier);

        return view('admin.fixed-asset.po.laporan',compact('dataSupplier','dataBarang'));
    }

    public function purchaseOrder($poCode)
    {
        $header = DB::table('t_fixed_asset_po')
        ->join('m_supplier','t_fixed_asset_po.supplier','m_supplier.id')
        ->join('m_user','t_fixed_asset_po.user_input','m_user.id')
        ->select('*','m_supplier.name as supplier','m_user.name as user_input','t_fixed_asset_po.status_aprove')
        ->where('t_fixed_asset_po.po_code',$poCode)
        ->first();

        $detail = DB::table('d_fixed_asset_po')
        ->join('m_produk','d_fixed_asset_po.produk','m_produk.id')
        ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
        ->select('m_produk.*','d_fixed_asset_po.*','m_satuan_unit.code as code_unit')
        ->where('po_code',$poCode)
        ->get();

        $company = DB::table('m_company_profile')->first();

        $pdf = PDF::loadview('admin.report.purchase-order',['company' => $company,'header'=>$header,'detail'=>$detail]);
        $customPaper = array(0,0,21.84,13.97);
        $pdf->setPaper($customPaper);
        // $pdf->setPaper('A4', 'landscape');
        return $pdf->stream();
    }
    public function getSupplierByPeriode($periode)
    {
        $tglmulai = substr($periode,0,10);
        $tglsampai = substr($periode,13,10);

        $dataSupplier = DB::table('m_supplier')
        ->join('t_fixed_asset_po', 'm_supplier.id', '=', 't_fixed_asset_po.supplier')
        ->select('m_supplier.id as supplier_id','name','main_address')
        ->where('t_fixed_asset_po.po_date','>=',date('Y-m-d', strtotime($tglmulai)))
        ->where('t_fixed_asset_po.po_date','<',date('Y-m-d', strtotime($tglsampai. ' + 1 days')))
        ->groupBy('m_supplier.id')
        ->get();
        return Response::json($dataSupplier);
    }
    public function getPOBySupplier($supplierID)
    {
        $dataPO = DB::table('t_fixed_asset_po')
        ->where('supplier',$supplierID)
        ->orderBy('po_code')
        ->get();
        return Response::json($dataPO);
    }

    public function getBarangByPo($poId)
    {

            $dataBarang = DB::table('m_produk')
            ->rightjoin('d_fixed_asset_po', 'd_fixed_asset_po.produk', '=', 'm_produk.id')
            ->select('m_produk.id as barang_id','m_produk.name')
            ->where('d_fixed_asset_po.po_code',$poId)
            ->groupBy('m_produk.id')
            ->get();


        return Response::json($dataBarang);
  }

  public function laporanPOPD()
  {
      $datasupplier = DB::table('m_supplier')
      ->join('t_fixed_asset_po', 'm_supplier.id', '=', 't_fixed_asset_po.supplier')
      ->select('m_supplier.id as supplier_id','name')
      //->where
      ->groupBy('m_supplier.id')
      ->get();

      $dataBarang = DB::table('m_produk')
      ->rightjoin('d_fixed_asset_po', 'd_fixed_asset_po.produk', '=', 'm_produk.id')
      ->join('t_fixed_asset_po', 'd_fixed_asset_po.po_code', '=', 't_fixed_asset_po.po_code')
      ->select('m_produk.id as barang_id','m_produk.name')
      //->where('t_fixed_asset_po.supplier',$supplier)
      ->groupBy('m_produk.id')
      ->get();

      return view('admin.fixed-asset.po.laporanpopd',compact('datasupplier','dataBarang'));
  }

  public function getBarangPOPDBySupplier($supplier)
  {

    $dataBarang = DB::table('m_produk')
    ->rightjoin('d_fixed_asset_po', 'd_fixed_asset_po.produk', '=', 'm_produk.id')
    ->select('m_produk.id as barang_id','m_produk.name')
    ->where('d_fixed_asset_po.po_code',$poId)
    ->groupBy('m_produk.id')
    ->get();


      return Response::json($dataBarang);
  }

  public function closeAO($pocode)
  {
      DB::table('t_fixed_asset_po')->where('po_code',$pocode)->update([
          'status_aprove' => 'closed',
      ]);

      return redirect('admin/asset/po');
  }

}
