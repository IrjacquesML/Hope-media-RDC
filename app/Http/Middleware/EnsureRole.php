<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Limite une route aux comptes ayant au moins le rôle indiqué.
 * Usage : ->middleware('role:admin') ou ->middleware('role:super_admin').
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole($role)) {
            if ($request->expectsJson()) {
                abort(403, 'Accès réservé.');
            }

            return redirect()->route('admin.dashboard')
                ->with('error', 'Cette section est réservée aux comptes « ' . (\App\Models\User::ROLES[$role]['label'] ?? $role) . ' ».');
        }

        return $next($request);
    }
}
