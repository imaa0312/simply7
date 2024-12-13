<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use Auth;

class AccountingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //$roleGudang = MRoleModel::whereIn('name', ['Gudang'])->first();
        $roleAdmin = MRoleModel::whereIn('name', ['Accounting','Super Admin','Admin'])->get();
        //dd($roleAdmin);
        $cek = 0;
        foreach ($roleAdmin as $key => $value) {
            if( Auth::user()->role == $value->id){
                $cek +=1;
            }
        }
        //dd($cek);
        if( $cek > 0){
            return $next($request);
        }
        abort(404);
        //alert("hai");
    }
}
