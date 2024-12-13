<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MUserModel;
use App\Models\MRoleModel;
use Auth;

class SalesMiddleware
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
        
        $roleSales = MRoleModel::where('name', 'Sales')->first();
        
        if( Auth::user()->role == $roleSales->id ){
            return $next($request);
        }
        abort(404);
    }
}
