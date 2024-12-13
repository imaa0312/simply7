<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DB;
use Mail;
use Auth;
use App\Http\Controllers\Controller;
use App\Models\MKecamatanModel;
use App\Models\MKelurahanDesaModel;
use App\Models\MKotaKabModel;
use App\Models\MProvinsiModel;
use Response;




class FrontCtrl extends Controller
{
	public function index()
    {
        $date_now = date('Y-m-d');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));
      //   $homepage = DB::table('m_home')->where('status', 'active')->first();
      //   if($homepage->nama_home == "promo"){
            // $data = [];
      //       return view('frontend.home_search',compact('homepage'));
      //   }
        $cat = DB::table('m_kategori_produk')->orderBy('id','desc')->limit(3)->get();
        $getDataNew = DB::table('m_produk')
            ->select('m_produk.id','m_produk.code','m_produk.name','m_produk.lebar','m_produk.panjang','m_produk.tinggi','m_produk.berat','m_harga_produk.price as harga','m_harga_produk.price_coret as harga_coret','m_satuan_unit.unit','m_merek_produk.name as nama_merek','m_produk.image','m_produk.merek_id','m_produk.merek','m_produk.name','m_produk.kategori_id')
            ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek_id')
            ->leftjoin('m_harga_produk','m_harga_produk.produk','m_produk.id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->where('m_harga_produk.gh_code','OL')
            ->where('m_harga_produk.price','!=', 0)
            ->where('m_harga_produk.date_start','<=', $date_now)
            ->where('m_harga_produk.date_end','>=', $date_now)
            ->orderBy('m_produk.created_at', 'DESC')
            ->limit(5)
            ->get();
            //dd($getDataNew);

            $prod_stok =[];

            if(count($getDataNew) > 0){
                foreach ($getDataNew as $key => $value) {

                    $balance = DB::table('m_stok_produk')
                            ->where('m_stok_produk.produk_code', $value->code)
                            ->where('m_stok_produk.gudang', 2)
                            ->where('type', 'closing')
                            ->whereMonth('periode',date('m', strtotime($date_last_month)))
                            ->whereYear('periode',date('Y', strtotime($date_last_month)))
                            ->sum('balance');

                    $stok = DB::table('m_stok_produk')
                            ->where('m_stok_produk.produk_code','=', $value->code)
                            ->where('m_stok_produk.gudang', 2)
                            ->whereMonth('created_at',date('m', strtotime($date_now)))
                            ->whereYear('created_at',date('Y', strtotime($date_now)))
                            ->groupBy('m_stok_produk.produk_code')
                            ->sum('stok');
                    // if($stok <= 0){
                    //     unset($getDataNew[$key]);
                    // }else{
                        $star = DB::table('m_review')->where('id_barang', $value->id)->get();

                        if(count($star) > 0){
                            $i = 0;
                            $rating = 0

                            ;
                            foreach ($star as $key1 => $value1) {
                                $rating = $rating + $value1->rating;
                                $i++;
                            }
                            $all_star = (int)round($rating / $i);
                            $value->star = $all_star;
                        }else{
                            $all_star = 0;
                            $value->star = $all_star;
                        }

                        $stok = $stok + $balance;
                        if($stok <= 0){
                            $stok = 0;
                        }else{
                            $stok = $stok;
                        }

                        $value->stok = $stok;
                        // $prod_stok = $getDataNew[$key];
                    // }

                }
            }



            //dd($getDataNew);

        return view('frontend.index', compact('cat','getDataNew'));
    }


    public function slider()
    {
        $slider = DB::table('m_slider')->get();

        return $slider;
    }

	public function flashSale()
	{
        // $date_now = date('d-m-Y');
        $date_start = date('Y-m-d 00:00:00');
		$date_now = date('Y-m-d 23:59:59');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

        $getDataFlash = DB::table('m_produk')
            ->select('m_produk.id','m_produk.code','m_produk.name','m_produk.lebar','m_produk.panjang','m_produk.tinggi','m_produk.berat','m_produk.harga_regular','m_produk.harga_sale','m_produk.ukuran','m_produk.satuan_ukuran','m_produk.class','m_merek_produk.name as nama_merek','m_produk.image','m_produk.link','m_produk.name_tampil')
            ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek_id')
            ->orderBy('m_produk.code', 'DESC')
            ->where('m_produk.date_start_sale','<=', $date_start)
            ->where('m_produk.date_end_sale','>=', $date_now)
            ->limit(3)
            ->get();

            foreach ($getDataFlash as $key => $value) {

                $stok = DB::table('m_stok_produk')
                        ->where('m_stok_produk.produk_code', $value->code)
                        ->where('m_stok_produk.gudang', 5)
                        ->groupBy('m_stok_produk.produk_code')
                        ->sum('stok');

                $star = DB::table('m_review')->where('id_barang', $value->id)->get();

                if(count($star) > 0){
                    $i = 0;
                    $rating = 0;
                    foreach ($star as $key1 => $value1) {
                        $rating = $rating + $value1->rating;
                        $i++;
                    }
                    $all_star = (int)round($rating / $i);
                    $value->star = $all_star;
                }else{
                    $all_star = 0;
                    $value->star = $all_star;
                }


                $value->stok = $stok;
            }
            //dd($getDataFlash);

        return $getDataFlash;

	}

	public function newProduct()
	{
		$date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

		$getDataNew = DB::table('m_produk')
            ->select('m_produk.id','m_produk.code','m_produk.name','m_produk.lebar','m_produk.panjang','m_produk.tinggi','m_produk.berat','m_produk.harga_regular as harga', 'm_produk.harga_sale as sale','m_produk.ukuran','m_produk.satuan_ukuran','m_produk.class','m_merek_produk.name as nama_merek','m_produk.image','m_produk.merek','m_produk.link','m_produk.name_tampil','m_produk.date_start_sale as date_start','m_produk.date_end_sale as date_end')
            ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek')
            ->orderBy('m_produk.created_at', 'DESC')
            ->limit(3)
            ->get();

            foreach ($getDataNew as $key => $value) {

                $stok = DB::table('m_stok_produk')
                        ->where('m_stok_produk.produk_code','=', $value->code)
                        ->where('m_stok_produk.gudang', 5)
                        ->groupBy('m_stok_produk.produk_code')
                        ->sum('stok');

                $star = DB::table('m_review')->where('id_barang', $value->id)->get();

                if(count($star) > 0){
                    $i = 0;
                    $rating = 0;
                    foreach ($star as $key1 => $value1) {
                        $rating = $rating + $value1->rating;
                        $i++;
                    }
                    $all_star = (int)round($rating / $i);
                    $value->star = $all_star;
                }else{
                    $all_star = 0;
                    $value->star = $all_star;
                }



                $value->stok = $stok;
            }
        return $getDataNew;

	}

	public function topProduct()
	{
		$date_now = date('d-m-Y');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));

		$getDataTop = DB::table('m_produk')
            ->select('m_produk.id','m_produk.code','m_produk.name','m_produk.lebar','m_produk.panjang','m_produk.tinggi','m_produk.berat','m_produk.harga_regular as harga','m_produk.harga_sale as sale','m_produk.ukuran','m_produk.satuan_ukuran','m_produk.class','m_merek_produk.name as nama_merek','m_produk.image','m_produk.merek','m_produk.link','m_produk.name_tampil','m_produk.date_start_sale as date_start','m_produk.date_end_sale as date_end')
            ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek')
            ->get();

            foreach ($getDataTop as $key => $value) {

                $stok = DB::table('m_stok_produk')
                        ->where('m_stok_produk.produk_code', $value->code)
                        ->where('m_stok_produk.gudang', 5)
                        ->groupBy('m_stok_produk.produk_code')
                        ->sum('stok');

                $value->stok = $stok;

                $star = DB::table('m_review')->where('id_barang', $value->id)->get();

                //dd($star);
                if(count($star) > 0){
                    $i = 0;
                    $rating = 0;
                    foreach ($star as $key1 => $value1) {
                        $rating = $rating + $value1->rating;
                        $i++;
                    }
                    $all_star = (int)round($rating / $i);
                    $value->star = $all_star;
                }else{
                    $all_star = 0;
                    $value->star = $all_star;
                }

            }

            $sorted = collect($getDataTop);

            $getDataTop = $sorted->sortByDesc('star');

            $getDataTop = $getDataTop->values()->toArray();

            $getDataTop = array_slice($getDataTop, 0, 3);

        //dd($getDataTop);
        return $getDataTop;
    }

    public function detailProduct($id)
    {
        $date_now = date('Y-m-d');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));
        $getDataNew = DB::table('m_produk')
            ->select('m_produk.id','m_produk.code','m_produk.name','m_produk.lebar','m_produk.panjang','m_produk.tinggi','m_produk.berat','m_harga_produk.price as harga','m_harga_produk.price_coret as harga_coret','m_satuan_unit.unit','m_merek_produk.name as nama_merek','m_produk.image','m_produk.merek','m_produk.merek_id','m_produk.name','m_produk.kategori_id','m_produk.sub_kategori_id','m_kategori_produk.name as kategori','m_sub_kategori_produk.name as sub_kategori','m_produk.deskripsi')
            ->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek_id')
            ->leftjoin('m_kategori_produk','m_kategori_produk.id','m_produk.kategori_id')
            ->leftjoin('m_sub_kategori_produk','m_sub_kategori_produk.id','m_produk.sub_kategori_id')
            ->leftjoin('m_harga_produk','m_harga_produk.produk','m_produk.id')
            ->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil')
            ->where('m_harga_produk.gh_code','OL')
            ->where('m_harga_produk.price','!=', 0)
            ->where('m_harga_produk.date_start','<=', $date_now)
            ->where('m_harga_produk.date_end','>=', $date_now)
            ->where("m_produk.id", $id)
            ->first();
        $produk_image_other = DB::table("m_produk_image")
                            ->where("produk_id", $id)
                            ->get();
        $balance = DB::table('m_stok_produk')
                        ->where('m_stok_produk.produk_code', $getDataNew->code)
                        ->where('m_stok_produk.gudang', 2)
                        ->where('type', 'closing')
                        ->whereMonth('periode',date('m', strtotime($date_last_month)))
                        ->whereYear('periode',date('Y', strtotime($date_last_month)))
                        ->sum('balance');
        //dd($getDataNew);
        $stok = DB::table('m_stok_produk')
                ->where('m_stok_produk.produk_code','=', $getDataNew->code)
                ->where('m_stok_produk.gudang', 2)
                ->whereMonth('created_at',date('m', strtotime($date_now)))
                ->whereYear('created_at',date('Y', strtotime($date_now)))
                ->groupBy('m_stok_produk.produk_code')
                ->sum('stok');

        $star = DB::table('m_review')->select('m_user.name as name_user','m_review.*')->join('m_user','m_user.id','m_review.id_user')->where('id_barang', $getDataNew->id)->paginate(3);
        $star_all = DB::table('m_review')->select('m_user.name as name_user','m_review.*')->join('m_user','m_user.id','m_review.id_user')->where('id_barang', $getDataNew->id)->get();

        $star1 = DB::table('m_review')->where('id_barang', $getDataNew->id)->where('rating',1)->get();
        $star2 = DB::table('m_review')->where('id_barang', $getDataNew->id)->where('rating',2)->get();
        $star3 = DB::table('m_review')->where('id_barang', $getDataNew->id)->where('rating',3)->get();
        $star4 = DB::table('m_review')->where('id_barang', $getDataNew->id)->where('rating',4)->get();
        $star5 = DB::table('m_review')->where('id_barang', $getDataNew->id)->where('rating',5)->get();

        if(count($star1) > 0){
            $total_star1 = count($star1);
        }else{
            $total_star1 = 0;
        }

        if(count($star2) > 0){
            $total_star2 = count($star2);
        }else{
            $total_star2 = 0;
        }

        if(count($star3) > 0){
            $total_star3 = count($star3);
        }else{
            $total_star3 = 0;
        }

        if(count($star4) > 0){
            $total_star4 = count($star4);
        }else{
            $total_star4 = 0;
        }

        if(count($star5) > 0){
            $total_star5 = count($star5);
        }else{
            $total_star5 = 0;
        }

        $getDataNew->total_star1 = $total_star1;
        $getDataNew->total_star2 = $total_star2;
        $getDataNew->total_star3 = $total_star3;
        $getDataNew->total_star4 = $total_star4;
        $getDataNew->total_star5 = $total_star5;

        $getDataNew->review = $star;

        if(count($star_all) > 0){
            $i = 0;
            $rating = 0;
            foreach ($star_all as $key1 => $value1) {
                $rating = $rating + $value1->rating;
                $i++;
            }
            $all_star = (int)round($rating / $i);
            $getDataNew->star = $all_star;
            $getDataNew->jumlah_ulasan = $i;
        }else{
            $all_star = 0;
            $getDataNew->star = $all_star;
            $getDataNew->jumlah_ulasan = $all_star;
        }

        $stok = $stok + $balance;
        if($stok <= 0){
            $stok = 0;
        }else{
            $stok = $stok;
        }


        $getDataNew->stok = $stok;
        //dd($getDataNew);
        return view('frontend.detail-product', compact('getDataNew','star','produk_image_other'));
    }

    public function kategori(Request $request)
    {
        $date_now = date('Y-m-d');
        $date = '01-'.date('m-Y', strtotime($date_now));
        $date_last_month = date('Y-m-d', strtotime('-1 months',strtotime($date)));
        $merk_awal =  $request->merk;
        $search =  $request->search;
        $merk_id=[];

        if(!empty($merk_awal)){
            $merk_id = explode(",", $merk_awal);
        }

        if(!empty($request->search)){
            $search =  $request->search;
        }else{
            $search = "";
        }
      //   $homepage = DB::table('m_home')->where('status', 'active')->first();
      //   if($homepage->nama_home == "promo"){
            // $data = [];
      //       return view('frontend.home_search',compact('homepage'));
      //   }
        if($request->kategori != null){
            $cat = DB::table('m_kategori_produk')->where('id',$request->kategori)->first();
        }else{
            $cat = 0;
        }

        if($request->subkategori != null){
            $subcat = DB::table('m_sub_kategori_produk')->where('id',$request->subkategori)->first();
        }else{
            $subcat = 0;
        }

        $kategori = DB::table('m_kategori_produk')->get();

        $merk = DB::table('m_merek_produk')
            ->select('m_merek_produk.name', 'm_merek_produk.id')
            ->orderBy('m_merek_produk.name',"asc")
            ->get();

        $searchValues = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);


        $query = DB::table('m_produk');
        $query->select('m_produk.id','m_produk.code','m_produk.name','m_produk.lebar','m_produk.panjang','m_produk.tinggi','m_produk.berat','m_harga_produk.price as harga','m_harga_produk.price_coret as harga_coret','m_satuan_unit.unit','m_merek_produk.name as nama_merek','m_produk.image','m_produk.merek','m_produk.name','m_produk.kategori_id','m_kategori_produk.name as nama_kategori');
        $query->leftjoin('m_merek_produk','m_merek_produk.id','m_produk.merek');
        $query->leftjoin('m_harga_produk','m_harga_produk.produk','m_produk.id');
        $query->leftjoin('m_satuan_unit','m_satuan_unit.id','m_produk.satuan_terkecil');
        $query->leftjoin('m_kategori_produk','m_kategori_produk.id','m_produk.kategori_id');
        $query->where('m_harga_produk.gh_code','OL');
        $query->where('m_harga_produk.price','!=', 0);
        $query->where('m_harga_produk.date_start','<=', $date_now);
        $query->where('m_harga_produk.date_end','>=', $date_now);
        if($search != ""){
            $query->where(function($q) use ($searchValues){
                foreach ($searchValues as $value) {
                    $q->where('m_produk.name','like', '%'.$this->escape_like($value).'%');
                }
            });
        }

        if($request->subkategori != null){
            //$query->where('m_produk.kategori_id','=', $request->kategori);
            $query->where('m_produk.sub_kategori_id','=', $request->subkategori);
        }else if($request->kategori != null){
           $query->where('m_produk.kategori_id','=', $request->kategori);
        }

        if($merk_id){
            $query->whereIn('m_produk.merek_id', $merk_id);
        }
        $query->orderBy('m_produk.created_at', 'DESC');
        $getDataNew = $query->paginate(6)->appends(request()->query());
            //dd($getDataNew);

            if(count($getDataNew) > 0){
                foreach ($getDataNew as $key => $value) {

                    $balance = DB::table('m_stok_produk')
                            ->where('m_stok_produk.produk_code', $value->code)
                            ->where('m_stok_produk.gudang', 2)
                            ->where('type', 'closing')
                            ->whereMonth('periode',date('m', strtotime($date_last_month)))
                            ->whereYear('periode',date('Y', strtotime($date_last_month)))
                            ->sum('balance');

                    $stok = DB::table('m_stok_produk')
                            ->where('m_stok_produk.produk_code','=', $value->code)
                            ->where('m_stok_produk.gudang', 2)
                            ->groupBy('m_stok_produk.produk_code')
                            ->sum('stok');
                    // if($stok <= 0){
                    //     unset($getDataNew[$key]);
                    // }else{
                    $star = DB::table('m_review')->where('id_barang', $value->id)->get();

                    if(count($star) > 0){
                        $i = 0;
                        $rating = 0;
                        foreach ($star as $key1 => $value1) {
                            $rating = $rating + $value1->rating;
                            $i++;
                        }
                        $all_star = (int)round($rating / $i);
                        $value->star = $all_star;
                    }else{
                        $all_star = 0;
                        $value->star = $all_star;
                    }

                    $stok = $stok + $balance;
                    if($stok <= 0){
                        $stok = 0;
                    }else{
                        $stok = $stok;
                    }

                    $value->stok = $stok;
                    // }

                }
            }

            if(count($kategori) > 0){
                foreach ($kategori as $key1 => $value1) {
                    $query1 = DB::table('m_produk');
                    $query1->select('m_produk.id','m_produk.kategori_id');
                    $query1->leftjoin('m_harga_produk','m_harga_produk.produk','m_produk.id');
                    $query1->where('m_harga_produk.gh_code','OL');
                    $query1->where('m_harga_produk.date_start','<=', $date_now);
                    $query1->where('m_harga_produk.date_end','>=', $date_now);
                    $query1->where('m_produk.kategori_id','=', $value1->id);
                    $produk = $query1->get();
                    if(count($produk) == 0){
                        unset($kategori[$key1]);
                    }else{
                        $value1->produk = count($produk);
                    }
                }
            }

            if(count($merk) > 0){
                foreach ($merk as $key2 => $value2) {
                    $query2 = DB::table('m_produk');
                    $query2->select('m_produk.id','m_produk.mereK_id');
                    $query2->leftjoin('m_harga_produk','m_harga_produk.produk','m_produk.id');
                    $query2->where('m_harga_produk.gh_code','OL');
                    $query2->where('m_harga_produk.date_start','<=', $date_now);
                    $query2->where('m_harga_produk.date_end','>=', $date_now);
                    $query2->where('m_produk.merek_id','=', $value2->id);
                    $merk_produk = $query2->get();
                    if(count($merk_produk) == 0){
                        unset($merk[$key2]);
                    }else{
                        $value2->merek = count($merk_produk);
                    }
                }
            }


        //dd($getDataNew);
        //dd($cat);
        //dd($merk_awal);

        return view('frontend.kategori-product', compact('cat','getDataNew',"kategori",'merk_awal','merk','search','subcat','merk_id'));
    }

    public function verifyUser($token)
    {
        $email = base64_decode($token);
        $id = DB::table('m_user')->where('email',$email)->first();
        if($id->status == "inactive"){
            DB::table('m_user')->where('email', $email)->update(['status'=>'active']);
            Auth::loginUsingId($id->id);
            //return redirect('/profile/'.$id->id)->with('success_front','Akun Berhasil di verifikasi');
            return redirect('/')->with('success_front','Akun Berhasil di verifikasi');;
        }else{
            return redirect('/');
        }

    }

    public function getKotaByProvinsi($provinsiId)
    {
        $data = MKotaKabModel::where('provinsi',$provinsiId)->orderBy('name')->get();

        return Response::json($data);
    }

    public function getKecamatanByKota($id_kota)
    {
        $data = MKecamatanModel::where('kota_kab',$id_kota)->orderBy('name')->get();

        return Response::json($data);
    }

    public function getKelurahanByKota($id_kecamatan)
    {
        $data = MKelurahanDesaModel::where('kecamatan',$id_kecamatan)->orderBy('name')->get();

        return Response::json($data);
    }

    public function escape_like($string)
    {
        $search = array('%', '_','"');
        $replace   = array('\%', '\_','\"');
        return str_replace($search, $replace, $string);
    }

    public function build_sorter($a, $b)
    {
        $c = $b->star - $a->name;
        return $c;
    }

    public function getReviews(Request $request){
        if(!empty($request->last)){
            $star = DB::table('m_review')
                    ->select('m_user.name as name_user','m_review.*')
                    ->join('m_user','m_user.id','m_review.id_user')
                    ->where('id_barang', $request->id)
                    ->where('m_review.id','<', $request->last)
                    ->limit(3)
                    ->orderBy("id","desc")
                    ->get();
        }else{
            $star = DB::table('m_review')
                    ->select('m_user.name as name_user','m_review.*')
                    ->join('m_user','m_user.id','m_review.id_user')
                    ->where('id_barang', $request->id)
                    ->limit(3)
                    ->orderBy("id","desc")
                    ->get();
        }
        $output = '';
        $last_id = '';
        if(count($star) > 0){
            // $output .= '<ul class="reviews" id="post_reviews">';
            foreach($star as $review){
                $output .= '<li>';
                $output .= '<div class="review-heading">';
                $output .= '<h5 class="name">'.$review->name_user.'</h5>';
                $output .= '<p class="date">'.date("d M Y H:i", strtotime($review->created_at)).'</p>';
                $output .= '<div class="review-rating">';
                $rev=5; 
                $output_star = "";
                for ($i=1; $i <= $rev ; $i++) { 
                    if($i <= $review->rating){
                        $output_star .='<i class="fa fa-star"></i>';
                    }else{
                        $output_star .= '<i class="fa fa-star-o empty"></i>';
                    }
                }
                $output .= $output_star;
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<div class="review-body">';
                $output .= '<p>'.$review->comment.'</p>';
                $output .= '</li>';
                $last_id = $review->id;
            }
            $output .= '<div class="reviews-pagination">';
            $output .= '<button type="button" name="load_more" data-id="'.$last_id.'" id="load_more" class="primary-btn">Load More</button>';
            $output .= '</div>';

            // $output .= '</ul>';
        }else{
            $output .= '<div class="reviews-pagination">';
            $output .= '<button type="button" name="load_more" id="load_more" class="primary-btn">Load More</button>';
            $output .= '</div>';
        }
        echo $output;
    }
}