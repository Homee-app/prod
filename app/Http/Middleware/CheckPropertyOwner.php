<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Models\PropertyOwner;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPropertyOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $propertyId = $request->route('propertyId') ?? $request->input('property_id');
        $owner = PropertyOwner::whereUserId($user->id)
            ->with('property')
            ->first();

        if (!$owner) {
            return ApiResponse::error(__('messages.not_found', ['item' => 'Property']));
        }

        // If propertyId is given, filter down to that property
        if ($propertyId) {
            $property = $owner->property->firstWhere('id', $propertyId);

            if (!$property) {
                return ApiResponse::error(__('messages.not_found', ['item' => 'Property']));
            }
        } else {
            // Otherwise return all properties
            $property = $owner->property;
        }

        // Attach to request
        $request->attributes->set('property', $property);

        return $next($request);
    }
}
