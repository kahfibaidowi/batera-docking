<?php

namespace App\Http\Middleware;

use Closure;

class Role{

    /**
     * Handle an incoming request.
     * Must first execute Auth Middleware
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        if(in_array($request['fm__login_data']['role'], $roles)){
            return $next($request);
        }

        return response()->json([
            'error' =>"ACCESS_NOT_ALLOWED"
        ], 403);
    }
}
