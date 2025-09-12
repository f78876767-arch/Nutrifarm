<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Ensure HTTP Basic auth challenged if missing
        if (!$user) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Admin Area"',
            ]);
        }

        // Check role-based admin
        $isAdmin = false;
        if (method_exists($user, 'roles')) {
            $isAdmin = $user->roles()->where('name', 'Admin')->exists();
        }

        // Fallback allowlist for seeded accounts
        if (!$isAdmin && !in_array($user->email, ['superadmin@example.com', 'admin@example.com'])) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}
