<?php

namespace App\Http\Middleware;

use App\User;
use Illuminate\Support\Facades\Auth;
use Closure;

class AuthAdminUser
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

        if($user->role->permission_level < 1) {
          return response(view('errors', ['message' => 'Access Denied: 403']), 403);
        }

        // Super user
        if($user->role->permission_level === 2 || !isset($request['userId'])) {
          return $next($request);
        }

        $targetUser = User::find($request['userId']);

        $groups = $user->groups;
        $targetGroups = $targetUser->groups;
        $inGroup = false;
        foreach($groups as $group) {
          foreach($targetGroups as $targetGroup) {
            if($group['id'] === $targetGroup['id']) {
              $inGroup = true;
            }
          }
        }

        if(!$inGroup) {
          return response(view('errors', ['message' => 'Access Denied: 403']), 403);
        }

        return $next($request);
    }
}
