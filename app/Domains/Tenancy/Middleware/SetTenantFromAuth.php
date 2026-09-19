<?php

namespace App\Domains\Tenancy\Middleware;

use App\Domains\Tenancy\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantFromAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $user->loadMissing(['tenant.organization']);

            if ($user->tenant) {
                TenantContext::set($user->tenant);
            } else {
                TenantContext::clear();
            }
        } else {
            TenantContext::clear();
        }

        return $next($request);
    }
}
