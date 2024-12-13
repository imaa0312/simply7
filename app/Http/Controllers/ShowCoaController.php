<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Illuminate\Http\Request;

class ShowCoaController extends Controller
{
    public function show($noref)
    {
    	$result = DB::table('d_general_ledger')
	    		->join('m_coa','m_coa.id','d_general_ledger.id_coa')
	    		->select('*','d_general_ledger.debet_credit')
	    		->where('ref',$noref)
	    		->orderBy('sequence')
	    		->get()
	    		->toArray();
	    $row = '';
    	foreach ($result as $key => $value) {
    		$i = $key++;
    		$row .= '<tr id="tr_'.$i.'">';
    			$row .= "<td>".$value->code."</td>";
    			$row .= "<td>".$value->desc."</td>";

    			if( $value->debet_credit == 'debet' ){
	    			$row .= "<td>Rp. ".number_format($value->total,0,'.','.')."</td>";
	    			$row .= "<td>Rp. 0</td>";
    			}else if( $value->debet_credit == 'credit' ){
	    			$row .= "<td>Rp. 0</td>";
    				$row .= "<td>Rp. ".number_format($value->total,0,'.','.')."</td>";
    			}
    		$row .= '</tr>';
    	}
    	return $row;
	    // return Response::json($result);
    }
}
