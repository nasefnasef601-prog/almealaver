<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolScopeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $routeSchoolId = $request->route('school_id')
            ?? $request->input('school_id')
            ?? $request->query('school_id');

        if ($routeSchoolId && $user->role === 'supervisor') {
            $userSchoolId = $user->school_id;
            if ((string) $routeSchoolId !== (string) $userSchoolId) {
                abort(403, 'ليس لديك صلاحية للوصول إلى بيانات هذه المدرسة.');
            }
        }

        return $next($request);
    }
}
