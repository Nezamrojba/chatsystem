<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * API routes use token-based auth, so CSRF is handled by Sanctum.
     */
    protected $except = [
        'api/*',
    ];
}

