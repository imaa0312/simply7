<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use Auth;

class AdminMiddleware
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
        $roleAdmin = MRoleModel::whereIn('name', ['Super Admin','Admin'])->get();

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
