<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$params): Response
    {
        $allowed = array_values(array_unique(array_filter(array_map(
            'trim',
            (array) preg_split('/[|,]/', implode(',', $params)),
        ))));

        $user = $request->user();
        $role = $user?->role;
        $value = $role instanceof UserRole ? $role->value : (string) $role;

        abort_if(! $user || ! in_array($value, $allowed, true), 403);

        return $next($request);
    }
}
