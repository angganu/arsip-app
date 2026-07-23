<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $roleNames = collect($roles)
            ->flatMap(static fn (string $role) => explode(',', $role))
            ->map(static fn (string $role) => trim($role))
            ->filter();

        if (! $user || $roleNames->isEmpty() || ! $user->roles()->whereIn('name', $roleNames->all())->exists()) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
