<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.dashboard.index');
    }
    

    public function updateStatus(Request $request)
    {
        $input = $request->all();

        $id     = $input['id'];
        $model  = $input['model'];
        $status = $input['status'];
        try {
            $modelClass = "App\\Models\\" . $model;

            if (!class_exists($modelClass)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
            };
            $data = $modelClass::find($id);

            

            if (!$data) {
                return response()->json(['status' => 'error', 'message' => 'Data not found'], 404);
            }

            if ($status == 0) {
                $data->tokens()->delete(); 
            }

            $data->status = $status;
            if ($data->save()) {
                return response()->json(['status' => 'success', 'message' => __('messages.update_success', ['item' => 'Status']),]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => __('messages.something_went_wrong')], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|max:15|confirmed',
        ]);

        $user = auth()->user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            return redirect()->back()->with('flash-success', __('messages.update_success',['item' => 'Password']) );
        } else {
            return redirect()->back()->with('Oops! '.__('messages.something_went_wrong'));
        }
    }
}
