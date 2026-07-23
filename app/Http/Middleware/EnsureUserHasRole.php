<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

        if (! $user || $roleNames->isEmpty()) {
            return redirect()->route('login');
        }

        if (! $user->roles()->whereIn('name', $roleNames->all())->exists()) {
            return $this->redirectToDashboard($user->roles()->pluck('name')->all());
        }

        return $next($request);
    }

    private function redirectToDashboard(array $roleNames): RedirectResponse
    {
        if (in_array('manager', $roleNames, true)) {
            return redirect()->route('manager.dashboard');
        }

        return redirect()->route('admin.dashboard');
    }
}
