<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        return route('login');
    }

    public function handle($request, Closure $next, ...$guards)
    {
        if (Auth::check() && Auth::user()->force_logout_at) {
            Auth::logout();
            return redirect('/login')->with('message', 'Tu sesión ha sido cerrada por un administrador.');
        }

        return parent::handle($request, $next, ...$guards);
    }
}
