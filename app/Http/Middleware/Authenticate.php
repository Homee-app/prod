<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // Detect path prefix or customize per guard
            if ($request->is('admin/*')) {
                return route('admin.login');
            }

            // return route('login'); // fallback for web users
        }

        return null;
    }
}
