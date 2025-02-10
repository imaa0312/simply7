<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

use App\Models\MGudangModel;
use App\Models\MTokoModel;
use App\Models\MProdukModel;
use App\Models\TransferStockModel;
use App\Models\DTransferStockModel;
use App\Models\MStokProdukModel;

use DataTables;



class MStokProdukController extends Controller
{
    public function stockTransfer()
    {
        $data['store'] = MTokoModel::where('status', 1)->get();
        return view('stock-transfer', compact('data'));
    }

    public function stockTransferDatatables()
    {
        $data = TransferStockModel::select('t_transfer_stock.*', 'm_user.name as user')
            ->join('m_user', 't_transfer_stock.user_id', '=', 'm_user.id')
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                return '<div class="edit-delete-action">
                    <a class="me-2 p-2 btn btn-success btn-sm view-ts" href="javascript:void(0);" data-bs-toggle="modal"
                        data-bs-target="#add-units" data-id="'.$row->id.'" data-toggle="tooltip" title="View Transfer Stock">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>';
            })
            ->addColumn('tempat_asal', function($row){
                if($row->asal == 0) return "Warehouse";
                else {
                    return MTokoModel::find($row->asal)->name;
                }
            })
            ->addColumn('tempat_tujuan', function($row){
                if($row->tujuan == 0) return "Warehouse";
                else {
                    return MTokoModel::find($row->tujuan)->name;
                }
            })
            ->rawColumns(['action', 'tempat_asal', 'tempat_tujuan'])
            ->make(true);
    }

    public function getDest($id)
    {
        if($id == 0){
            $data = MTokoModel::where('status', '=', 1)->get();
            $option = "";
        } else {
            $data = MTokoModel::whereNot('id',$id)->where('status', '=', 1)->get();
            $option = "<option value='0'>Warehouse</option>";
        }

        foreach($data as $dt){
            $option .= "<option value='".$dt->id."'>".$dt->name."</option>";
        }
        
        echo json_encode($option);
    }

    public function getProduct(Request $request, $id)
    {
        $data = MStokProdukModel::select('m_produk.*', 'm_kategori_produk.name as kategori', 'm_sub_kategori_produk.name as sub_kategori', 'm_ssub_kategori_produk.name as ssub_kategori', 'm_sssub_kategori_produk.name as sssub_kategori', 'm_brand.name as brand', 'm_size.name as size', 'm_stok_produk.balance')
            ->join('m_produk', 'm_produk.id', '=', 'm_stok_produk.produk_id')
            ->join('m_kategori_produk', 'm_kategori_produk.id', '=', 'm_produk.kategori_id')
            ->join('m_sub_kategori_produk', 'm_sub_kategori_produk.id', '=', 'm_produk.sub_kategori_id')
            ->join('m_ssub_kategori_produk', 'm_ssub_kategori_produk.id', '=', 'm_produk.ssub_kategori_id')
            ->join('m_sssub_kategori_produk', 'm_sssub_kategori_produk.id', '=', 'm_produk.sssub_kategori_id')
            ->join('m_brand', 'm_brand.id', '=', 'm_produk.brand_id')
            ->join('m_size', 'm_size.id', '=', 'm_produk.size_id')
            ->where('m_produk.name', 'like', '%'.$request->input('q').'%')
            ->orWhere('m_brand.name', 'like', '%'.$request->input('q').'%')
            ->orWhere('m_sssub_kategori_produk.name', 'like', '%'.$request->input('q').'%')
            ->orWhere('m_size.name', 'like', '%'.$request->input('q').'%')
            ->orWhere('m_produk.sku', 'like', '%'.$request->input('q').'%')
            ->where('m_stok_produk.place', '=', $id)
            ->where('m_stok_produk.balance', '>', 0)
            ->get();
            
        echo json_encode($data);
    }

    public function getMax($id, $asal)
    {
        $data = MStokProdukModel::select( 'balance')
            ->where('place', '=', $asal)
            ->where('produk_id', '=', $id)
            ->orderBy('id', 'DESC')->first();
            
        if($data){
            $return = array(
                "status" => true,
                "balance" => $data->balance
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Failed"
            );
        }

        echo json_encode($return);
    }

    public function transferStore(Request $request)
    {
        $date = date('Ymd');
        $latestTS = DB::table('t_transfer_stock')
            ->where('ts_code', 'like', "TS-".$date."%")
            ->orderBy('ts_code', 'DESC')
            ->first();

        if ($latestTS) {
            $lastNumber = (int) substr($latestTS->ts_code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $ts_code = 'TS-' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $ts = new TransferStockModel;
        $ts->ts_code = $ts_code;
        $ts->ts_date = date("Y-m-d H:i", strtotime($request->input('ts_date')));
        $ts->asal = $request->input('asal');
        $ts->tujuan = $request->input('tujuan');
        $ts->user_id = 1;
        $ts->description = $request->input('desc');
        $ts->save();

        $dts = new DTransferStockModel;
        $dts->ts_id = $ts->id;
        $dts->produk = $request->input('produk');
        $dts->qty = $request->input('qty');
        $dts->save();

        $cek_asal = MStokProdukModel::where('place', '=', $request->input('asal'))
            ->where('produk_id', '=', $request->input('produk'))
            ->orderBy('id', 'DESC')->first();
        if($cek_asal)  $stok_awal_asal = $cek_asal->balance;
        else $stok_awal_asal = 0;

        $balance_asal = $stok_awal_asal - $request->input('qty');

        $mutasi = new MStokProdukModel;
        $mutasi->refno = $ts_code;
        $mutasi->produk_id = $request->input('produk');
        $mutasi->person = 1;
        $mutasi->stok_awal = $stok_awal_asal;
        $mutasi->qty = $request->input('qty');
        $mutasi->balance = $balance_asal;
        $mutasi->place = $request->input('asal');
        $mutasi->trx = 'transfer';
        $mutasi->save();

        $cek_tujuan = MStokProdukModel::where('place', '=', $request->input('tujuan'))
            ->where('produk_id', '=', $request->input('produk'))
            ->orderBy('id', 'DESC')->first();
        if($cek_tujuan)  $stok_awal_tujuan = $cek_tujuan->balance;
        else $stok_awal_tujuan = 0;

        $balance_tujuan = $stok_awal_tujuan + $request->input('qty');

        $mutasi = new MStokProdukModel;
        $mutasi->refno = $ts_code;
        $mutasi->produk_id = $request->input('produk');
        $mutasi->person = 1;
        $mutasi->stok_awal = $stok_awal_tujuan;
        $mutasi->qty = $request->input('qty');
        $mutasi->balance = $balance_tujuan;
        $mutasi->place = $request->input('tujuan');
        $mutasi->trx = 'transfer';
        $mutasi->save();

        if($mutasi){
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

    public function editTransfer($id)
    {
        $data = TransferStockModel::select('t_transfer_stock.*', 'd_transfer_stock.produk', 'd_transfer_stock.qty', 'm_produk.name as product_name')
            ->join('d_transfer_stock', 'd_transfer_stock.ts_id', '=', 't_transfer_stock.id')
            ->join('m_produk', 'd_transfer_stock.produk', '=', 'm_produk.id')
            ->where('t_transfer_stock.id', '=', $id)->first();
        
        if($data){
            if($data->tujuan == 0) $tujuan = "Warehouse";
            else $tujuan = MTokoModel::find($data->tujuan)->name;

            $return = array(
                "status"        => true,
                "id"            => $id,
                "ts_code"       => $data->ts_code,
                "ts_date"       => $data->ts_date,
                "asal"          => $data->asal,
                "tujuan"        => $data->tujuan,
                "tujuan_name"   => $tujuan,
                "produk_id"     => $data->produk,
                "product_name"  => $data->product_name,
                "qty"           => $data->qty,
                "description"   => $data->description
            );
        } else {
            $return = array(
                "status" => false,
                "msg" => "Data Not Found"
            );
        }

        echo json_encode($return);
    }

    public function laporanStok()
    {
        $dataGudang = DB::table('m_gudang')
                ->select('id as gudang_id','name')
                ->groupBy('id','name')
                ->get();

        // $dataBarang = DB::table('m_produk')
        //         ->select('code as barang_code','name')
        //         ->groupBy('id')
        //         ->get();

        $dataBarang = DB::table('m_stok_produk')
            ->join('m_produk','m_produk.code','=','m_stok_produk.produk_code')
            ->select('m_produk.id','m_produk.code as barang_code','m_produk.name',DB::raw('SUM(m_stok_produk.stok) as stok'))
            ->groupBy('m_produk.id','m_produk.code','name')
            // ->where('m_stok_produk.stok','!=', 0)
            ->where(function ($query) {
                $query->where('m_stok_produk.stok','!=', 0)
                ->orWhere('m_stok_produk.balance','!=',0);
            })
            ->get();

        return view('admin.transaksi.stok.laporan',compact('dataGudang','dataBarang'));
    }
}
