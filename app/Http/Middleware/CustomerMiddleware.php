<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use Auth;

class CustomerMiddleware
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
        
        $roleCustomer   = MRoleModel::where('name', 'Customer')->first();
        $roleAdmin      = MRoleModel::where('name', 'Admin')->first();
        $roleSuperAdmin = MRoleModel::where('name', 'Super Admin')->first();
        
        if(Auth::check()){
            if( Auth::user()->role == $roleCustomer->id ){
                return $next($request);
            }else if( Auth::user()->role == $roleSuperAdmin->id ){
                return redirect('/home');
            }else if( Auth::user()->role == $roleAdmin->id ){
                return redirect('/home');
            }else{
                return redirect('/');
            }
        }else{
             return redirect('/masuk');
        }
    }
}
