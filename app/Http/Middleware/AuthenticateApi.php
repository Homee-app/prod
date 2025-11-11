<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthenticateApi
{
    public function handle(Request $request, Closure $next)
    {
       
        // Check for authentication (e.g., token in header)
        $token = $request->header('Authorization'); // Or however you send the token

        if (!$token) {  // No token provided
            if ($request->expectsJson() || $request->is('api/*')) {
                throw new HttpException(401, 'Unauthorized.');
            } else {
                // Handle non-API requests (if any) - perhaps redirect
                return redirect('/login'); // Or wherever your login is
            }
        }

        // Verify the token (e.g., against your database or auth provider)
        // Example (using Laravel Sanctum):
        if (!$request->user() && $token) { //If User is not found and token is provided
            $request->headers->set('Authorization', 'Bearer '.$token); //setting the token in header
            if($user = auth()->user()){ // checking the user using sanctum
                // Authentication successful
            }else{
                if ($request->expectsJson() || $request->is('api/*')) {
                    throw new HttpException(401, 'Unauthorized.');
                } else {
                    // Handle non-API requests (if any) - perhaps redirect
                    return redirect('/login'); // Or wherever your login is
                }
            }
        }

        if ($request->user()) { //If user is found
            return $next($request);  // Authentication successful
        } else {
            if ($request->expectsJson() || $request->is('api/*')) {
                throw new HttpException(401, 'Unauthorized.');
            } else {
                // Handle non-API requests (if any) - perhaps redirect
                return redirect('/login'); // Or wherever your login is
            }
        }
    }
}