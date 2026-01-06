<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\System\Configuration;

class LockedUser
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
        $user = auth()->user();

        if ($user) {
            if ($user->is_locked) {
                $message = $user->msg_locked;
                return response()->view('errors.403_user', compact('message'), 403);
            }
        }

        return $next($request);
    }
}
