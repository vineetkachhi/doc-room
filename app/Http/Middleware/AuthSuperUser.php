<?php

namespace App\Http\Middleware;

use App\User;
use Illuminate\Support\Facades\Auth;
use Closure;

class AuthSuperUser
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
        $user = Auth::user();

        if($user->role->permission_level < 2) {
          return response(view('errors', ['message' => 'Access Denied: 403']), 403);
        }
        
        return $next($request);
    }
}
