<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecondaryAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if(!$user->is_secondary){
            return $next($request);
        }
        $route = $request->path();
    
        $paths_to_check = ['dashboard','plans','accounting','configuration','403','auto-update','information','reports/list'];
        if(in_array($route, $paths_to_check)){
            $permissions = $user->permissions->pluck('permission')->toArray();
            if(in_array($route, $permissions)){
                return $next($request);
            }else{
                if(count($permissions) > 0){
                    $first_permission = $permissions[0];
                    return redirect($first_permission);
                }else{
                    return redirect('logs');
                }
            }
        }
        return redirect()->route('system.dashboard');
        // if(in_array($route, $permissions)){
        //     return $next($request);
        // }
    
    }
}
