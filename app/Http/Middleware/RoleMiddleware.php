<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $roles = collect($roles)
            ->flatMap(fn (string $role): array => preg_split('/[|,]/', $role) ?: [])
            ->map(fn (string $role): string => trim($role))
            ->filter()
            ->values()
            ->all();

        $user = Auth::user();

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'غير مصرح لك بالوصول إلى هذه الصفحة.');
    }
}
