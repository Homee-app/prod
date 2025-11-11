<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Suburb;

class SuburbController extends Controller
{
    public function getAll()
    {
        $suburbs = Suburb::all(['id', 'name', 'state', 'postcode']); // Adjust fields as needed

        return response()->json([
            'status' => true,
            'message' => 'Suburb list fetched successfully.',
            'data' => $suburbs
        ]);   
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $suburbs = \App\Models\Suburb::where(function ($q) use ($query) {
        $q->where('name', 'LIKE', $query . '%')
            ->orWhere('postcode', 'LIKE', $query . '%');
        })
            ->orderBy('name')
            // ->limit(15)
            ->get(['id', 'name', 'postcode', 'state']);

        return response()->json([
            'status' => true,
            'data' => $suburbs,
            'message' => 'Suburb list fetched successfully.'
        ]);
    }

    public function getByIds(Request $request)
    {
        // 1. Validate input
        $request->validate([
            // For POST request with 'ids' as an array in JSON body:
            'ids' => 'required|array',
            'ids.*' => 'integer|min:1', // Each ID must be an integer and at least 1

            // If you choose a GET request with comma-separated IDs in a query string:
            // 'ids' => 'required|string', // e.g., ?ids=1,5,10
        ]);

        // 2. Extract IDs
        $suburbIds = $request->input('ids');

        // If using GET with comma-separated IDs:
        // $suburbIds = array_map('intval', explode(',', $request->input('ids')));


        // Ensure IDs are unique and filter out any non-numeric or zero values if not strictly validated
        // Although validation above covers this, it's good for robustness.
        $suburbIds = array_unique(array_filter($suburbIds, function($id) {
            return is_numeric($id) && $id > 0;
        }));

        if (empty($suburbIds)) {
            return response()->json([
                'status' => false,
                'data' => [],
                'message' => 'No valid suburb IDs provided.'
            ], 400); // Bad request
        }

        // 3. Fetch suburbs
        // Using `whereIn` to fetch multiple records by their IDs
        $suburbs = Suburb::whereIn('id', $suburbIds)
                         ->get(['id', 'name', 'postcode', 'state']);

        // 4. Return response
        return response()->json([
            'status' => true,
            'data' => $suburbs,
            'message' => 'Suburbs fetched by IDs successfully.'
        ]);
    }
}
