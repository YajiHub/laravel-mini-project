<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $userRole = auth()->user()->role->name ?? null;

        // Split roles by | in case they are passed as "role1|role2"
        $allowedRoles = [];
        foreach ($roles as $roleString) {
            $allowedRoles = array_merge($allowedRoles, explode('|', $roleString));
        }

        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Unauthorized: You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
