<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
ini_set('max_execution_time', '300');

class TClosingStokController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data_terakhir = DB::table('m_periode_closing')
            ->where('type','stok')
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
        return view('admin.accounting.closing-stok.index', compact('closing_terakhir','closing_next'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
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
        DB::beginTransaction();
            try {
        // if (($date_now == $first) ||($date_now == $last)) {
                $cek = DB::table('m_periode_closing')
                    ->whereMonth('periode',date('m', strtotime($date)))
                    ->whereYear('periode',date('Y', strtotime($date)))
                    ->where('type','stok')
                    ->count();

                //dd($cek);

                if ($cek > 0) {
                    //dd($cek);
                    return redirect()->back()->with('message','Sudah melakukan closing pada periode ini');
                }else{
                    
                        $data =  DB::table('m_periode_closing')
                            ->insert([
                                'periode' => date('Y-m-d', strtotime($date)),
                                'type' => 'stok',
                            ]);
                        $data_gudang = DB::table('m_gudang')->get();
                        $data_barang = DB::table('m_produk')->get();
                        //dd($cek);

                        $status_closing = DB::table('m_stok_produk')
                            ->where('type','closing')
                            ->first();

                        //dd($status_closing);

                        if (!empty($status_closing)) {
                            //jika sudah pernah closing
                            $array = [];
                            $i = 0;
                            foreach ($data_gudang as $raw_data) {
                                foreach ($data_barang as $raw_data2) {
                                    //insert balance bulan lalu sebagai stok awal bulan ini
                                    $stok_awal = DB::table('m_stok_produk')
                                        ->where('produk_code', '=', $raw_data2->code)
                                        ->where('gudang', '=', $raw_data->id)
                                        ->where('type', '=', 'closing')
                                        ->whereMonth('periode',date('m', strtotime($date_last_month)))
                                        ->whereYear('periode',date('Y', strtotime($date_last_month)))
                                        ->sum('balance');

                                    $stok = DB::table('m_stok_produk')
                                        ->where('produk_code', '=', $raw_data2->code)
                                        ->where('gudang', '=', $raw_data->id)
                                        ->whereMonth('created_at',date('m', strtotime($date_last_month)))
                                        ->whereYear('created_at',date('Y', strtotime($date_last_month)))
                                        ->sum('stok');

                                    $balance = $stok_awal + $stok;

                                    // $array[$i]['produk_code'] = $raw_data2->code; 
                                    // $array[$i]['gudang'] = $raw_data->id; 
                                    // $array[$i]['stok_awal'] = $stok_awal; 
                                    // $array[$i]['stok'] = $stok; 
                                    // $array[$i]['balance'] = $balance; 
                                    // $array[$i]['type'] = $closing; 
                                    
                                    // $i++;

                                    DB::table('m_stok_produk')->insert([
                                        'produk_code' => $raw_data2->code,
                                        'produk_id'   => $raw_data2->id,
                                        'gudang'      => $raw_data->id,
                                        'periode'     => $date_this_month,
                                        'stok_awal'   => $stok_awal,
                                        'stok'        => 0,
                                        'balance'     => $balance,
                                        'type'        => 'closing',
                                    ]);
                                }
                            }
                            // dd($array);
                            // DB::table('m_stok_produk')->insert($array);
                        }else{
                            foreach ($data_gudang as $raw_data) {
                                foreach ($data_barang as $raw_data2) {
                                    //saldo awal 0 karena belum pernah closing
                                    $stok_awal = 0;

                                    $stok = DB::table('m_stok_produk')
                                        ->where('produk_code', '=', $raw_data2->code)
                                        ->where('gudang', '=', $raw_data->id)
                                        ->whereMonth('created_at',date('m', strtotime($date_this_month)))
                                        ->whereYear('created_at',date('Y', strtotime($date_this_month)))
                                        ->sum('stok');

                                    $balance = $stok_awal + $stok;


                                    // $array[$i]['produk_code'] = $raw_data2->code; 
                                    // $array[$i]['gudang'] = $raw_data->id; 
                                    // $array[$i]['stok_awal'] = $stok_awal; 
                                    // $array[$i]['stok'] = $stok; 
                                    // $array[$i]['balance'] = $balance; 
                                    // $array[$i]['type'] = $closing; 
                                    
                                    // $i++;

                                    DB::table('m_stok_produk')->insert([
                                        'produk_code' => $raw_data2->code,
                                        'produk_id'   => $raw_data2->id,
                                        'gudang'      => $raw_data->id,
                                        'periode'     => $date_this_month,
                                        'stok_awal'   => $stok_awal,
                                        'stok'        => 0,
                                        'balance'     => $balance,
                                        'type'        => 'closing',
                                    ]);
                                }
                            }
                            // dd($array);
                            // DB::table('m_stok_produk')->insert($array);
                        }
                        DB::commit();
                        return redirect()->back()->with('message-success','Closing Berhasil Dilakukan');
                    
                }
            }catch(\Exception $e){
                DB::rollback();
                dd($e);
            }
        // }else{
        //     return redirect()->back()->with('message','Belum Tanggal Closing');
        // }
    }
}
