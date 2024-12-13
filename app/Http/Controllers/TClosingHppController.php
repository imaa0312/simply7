<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class TClosingHppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data_terakhir = DB::table('m_periode_closing')
            ->where('type','hpp')
            ->orderBy('periode','desc')
            ->first();

        if (!empty($data_terakhir)) {
            $closing_terakhir = date('m-Y', strtotime($data_terakhir->periode));
            $closing_next = date('m-Y', strtotime('+1 months',strtotime($data_terakhir->periode)));
        }else{
            $closing_terakhir = '-';
            $closing_next = '-';
        }

        //dd('ada');
        return view('admin.accounting.closing-hpp.index', compact('closing_terakhir','closing_next'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function closing(Request $request)
    {
        ini_set('memory_limit', '512MB');
        ini_set('max_execution_time', 3000);
        $date = '01-'.$request->periode;

        $date_this_month = date('Y-m-d', strtotime($date));
        $date_next_month = date('Y-m-d', strtotime('+1 months',strtotime($date)));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

        $first = date('Y-m-01', strtotime($date_next_month));

        // Last day of the month.
        $last = date('Y-m-t', strtotime($date_this_month));

        //dd($last);
        $date_now = date('Y-m-d');

        // if (($date_now == $first) ||($date_now == $last)) {
            $cek = DB::table('m_periode_closing')
                ->whereMonth('periode',date('m', strtotime($date)))
                ->whereYear('periode',date('Y', strtotime($date)))
                ->where('type','hpp')
                ->count();

            //dd($cek);

            if ($cek > 0) {
                return redirect()->back()->with('message','Sudah melakukan closing pada periode ini');
            }else{
                DB::table('m_periode_closing')
                    ->insert([
                        'periode' => date('Y-m-d', strtotime($date)),
                        'type' => 'hpp',
                    ]);

                $barang = DB::table('m_produk')
                    ->get();

                $status_closing = DB::table('t_closing_hpp')
                    ->first();

                foreach ($barang as $raw_data) {
                    //get amount pd
                    $get_pd_qty = DB::table('d_surat_jalan_masuk')
                        ->select('d_surat_jalan_masuk.produk_id','d_surat_jalan_masuk.qty','d_purchase_order.total_neto',DB::raw('(d_surat_jalan_masuk.qty * d_purchase_order.total_neto) as amount'))
                        ->join('d_purchase_order','d_purchase_order.id','=','d_surat_jalan_masuk.dpo_id')
                        ->where('d_surat_jalan_masuk.produk_id',$raw_data->id)
                        ->sum('d_surat_jalan_masuk.qty');

                    $get_pd_harga = DB::table('d_surat_jalan_masuk')
                        ->select('d_surat_jalan_masuk.produk_id','d_surat_jalan_masuk.qty','d_purchase_order.total_neto',DB::raw('(d_surat_jalan_masuk.qty * d_purchase_order.total_neto) as amount'))
                        ->join('d_purchase_order','d_purchase_order.id','=','d_surat_jalan_masuk.dpo_id')
                        ->where('d_surat_jalan_masuk.produk_id',$raw_data->id)
                        ->sum('d_purchase_order.total_neto');

                    $old_hpp = 0;
                    $old_stok = 0;
                    $new_hpp = 0;
                    $qty_masuk = 0;

                    if (!empty($status_closing)) {
                        //update jika sudah pernah
                        $hpp = DB::table('t_closing_hpp')
                            ->whereMonth('periode',date('m', strtotime($date)))
                            ->whereYear('periode',date('Y', strtotime($date)))
                            ->where('id_barang',$raw_data->id)
                            ->first();

                        if ($hpp) {
                            //barang ada
                            $old_hpp = $hpp->old_hpp;
                            $old_stok = $hpp->old_stok;

                            $amount_awal = $old_hpp * $old_stok; 
                            $amount_masuk = $get_pd_qty * $get_pd_harga;

                            if(($old_stok + $get_pd_qty) >0){
                                $new_hpp = (int)round(($amount_awal + $amount_masuk) / ($old_stok + $get_pd_qty));
                            }else{
                                $new_hpp = 0;
                            }

                            $qty_masuk = $get_pd_qty;

                            $balance = $old_stok + $qty_masuk;

                            DB::table('t_closing_hpp')
                                ->where('id_barang', '=', $raw_data->id)
                                ->whereMonth('periode',date('m', strtotime($date_this_month)))
                                ->whereYear('periode',date('Y', strtotime($date_this_month)))
                                ->update([
                                    'closing_date' => date('Y-m-d'),
                                    'new_hpp' => $new_hpp,
                                    'qty_masuk' => $qty_masuk,
                                    'balance' => $balance,
                                    'status' => 'close',
                            ]);
                        }else{
                            //barang tidak ada -> insert
                            $old_hpp = 0;
                            $old_stok = 0;

                            $amount_awal = $old_hpp * $old_stok; 
                            $amount_masuk = $get_pd_qty * $get_pd_harga;

                            if(($old_stok + $get_pd_qty) >0){
                                $new_hpp = (int)round(($amount_awal + $amount_masuk) / ($old_stok + $get_pd_qty));
                            }else{
                                $new_hpp = 0;
                            }

                            $qty_masuk = $get_pd_qty;

                            $balance = $old_stok + $qty_masuk;

                            DB::table('t_closing_hpp')
                                ->insert([
                                    'periode' => $date_this_month,
                                    'id_barang' => $raw_data->id,
                                    'code_barang' => $raw_data->code,
                                    'closing_date' => date('Y-m-d'),
                                    'old_hpp' => $old_hpp,
                                    'old_stok' => $old_stok,
                                    'new_hpp' => $new_hpp,
                                    'qty_masuk' => $qty_masuk,
                                    'balance' => $balance,
                                    // 'group' => 'MONTHLY',
                                    'keterangan' => 'MONTHLY CLOSING HPP '.date('m-Y', strtotime($date_this_month)),
                                    'status' => 'close',
                            ]);
                        }
                    }else{
                        //insert jika sudah pernah
                        $old_hpp = 0;
                        $old_stok = 0;

                        $amount_awal = $old_hpp * $old_stok; 
                        $amount_masuk = $get_pd_qty * $get_pd_harga;
                        if(($old_stok + $get_pd_qty) >0){
                            $new_hpp = (int)round(($amount_awal + $amount_masuk) / ($old_stok + $get_pd_qty));
                        }else{
                            $new_hpp = 0;
                        }

                        $qty_masuk = $get_pd_qty;

                        $balance = $old_stok + $qty_masuk;

                        DB::table('t_closing_hpp')
                            ->insert([
                                'periode' => $date_this_month,
                                'id_barang' => $raw_data->id,
                                'code_barang' => $raw_data->code,
                                'closing_date' => date('Y-m-d'),
                                'old_hpp' => $old_hpp,
                                'old_stok' => $old_stok,
                                'new_hpp' => $new_hpp,
                                'qty_masuk' => $qty_masuk,
                                'balance' => $balance,
                                // 'group' => 'MONTHLY',
                                'keterangan' => 'MONTHLY CLOSING HPP '.date('m-Y', strtotime($date_this_month)),
                                'status' => 'close',
                        ]);
                    }

                    //isi closing untuk bulan depan
                    $old_hpp = $new_hpp;
                    $old_stok = $old_stok + $qty_masuk;

                    $new_hpp = 0;
                    $qty_masuk = 0;
                    $balance = $old_stok;

                    DB::table('t_closing_hpp')
                        ->insert([
                            'periode' => $date_next_month,
                            'id_barang' => $raw_data->id,
                            'code_barang' => $raw_data->code,
                            'closing_date' => date('Y-m-d'),
                            'old_hpp' => $old_hpp,
                            'old_stok' => $old_stok,
                            'new_hpp' => $new_hpp,
                            'qty_masuk' => $qty_masuk,
                            'balance' => $balance,
                            // 'group' => 'MONTHLY',
                            'keterangan' => 'MONTHLY CLOSING HPP '.date('m-Y', strtotime($date_next_month)),
                    ]);

                }

                return redirect()->back()->with('message-success','Closing Berhasil Dilakukan');
            }
        // }else{
        //     return redirect()->back()->with('message','Belum Tanggal Closing');
        // }
    }
}
