<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait ResolvesTenant
{
    /**
     * Resolve the current tenant ID from the authenticated user,
     * falling back to the first available tenant for dev environments.
     */
    protected function resolveTenantId(): int
    {
        if (Auth::check() && Auth::user()->tenant_id) {
            return Auth::user()->tenant_id;
        }

        return \App\Models\Tenant::first()->id ?? 1;
    }
}
