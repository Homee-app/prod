<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\api\v1\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Report;
use App\Models\User; // Assuming you're using User for type 1
use App\Models\Posts; // Assuming you're using Post for type 2
use App\Models\PostComments; // Assuming you're using Comment for type 3
use App\Models\Courses;
use App\Models\ReportCategory;
// use App\Models\Course; // Assuming you're using Course for type 4

class ReportController extends BaseApiController
{
    public function store(Request $request)
    {

        // Fetch allowed reasons from the master table
        $allowedReasons = ReportCategory::pluck('reason')->toArray();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'type'      => 'required|in:1,2,3,4', // 1: User, 2: Post, 3: Comment, 4: Course
            'type_id'   => 'required|integer', // ID of the reported entity
            'reason'    => 'required|string|in:' . implode(',', $allowedReasons),
            'description' => 'nullable|required_if:reason,Other|string' // Required only if reason = 'other'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        // Get logged-in user ID
        $authUser = $request->user(); // This assumes the user is authenticated

        $user_id = auth()->id();
        $type    = (int) $request->type;
        $type_id = (int) $request->type_id;

        if ($authUser->role != 2) {
            return $this->sendError('Invalid Report', ['message' => 'You are not allowed to report comments,posts,courses and other users'], 403);
        }

        // Check if the user has already reported this entity
        $existingReport = Report::where('reported_by', $user_id)
            ->where('type', $type)
            ->where('type_id', $type_id)
            ->exists();

        if ($existingReport && $type == 1) {
            return $this->sendError('Duplicate Report', ['message' => 'You have already reported this user.'], 409);
        } elseif ($existingReport && $type == 2) {
            return $this->sendError('Duplicate Report', ['message' => 'You have already reported this post.'], 409);
        } elseif ($existingReport && $type == 3) {
            return $this->sendError('Duplicate Report', ['message' => 'You have already reported this comment.'], 409);
        } elseif ($existingReport && $type == 4) {
            return $this->sendError('Duplicate Report', ['message' => 'You have already reported this course.'], 409);
        }

        // Fetch the entity
        $entity = match ($type) {
            1 => User::find($type_id),
            2 => Posts::find($type_id),
            3 => PostComments::find($type_id),
            4 => Courses::find($type_id),
            default => null,
        };

        if (!$entity) {
            return $this->sendError('Invalid type_id', ['message' => 'The specified type_id does not exist.'], 404);
        }

        // Prevent reporting own account
        if ($type == 1 && $entity->id == $user_id) {
            return $this->sendError('Invalid Report', ['message' => 'You cannot report yourself.'], 403);
        }

        // Check if parent_id is not null (user cannot report this entity)      

        if ($type == 1 && !empty($entity->parent_id)) {
            return $this->sendError('Invalid Report', ['message' => 'You cannot report this item as it has a parent_id.'], 403);
        }

        // Role child check
        if ($type == 1 && isset($entity->role, $authUser->role) && $authUser->role == 2 && $entity->role != 2) {
            return $this->sendError('Invalid Report', ['message' => 'You can only report users from the same role.'], 403);
        }

        if (
            $type == 1 && isset($entity->role, $authUser->role)
            && $authUser->role == 2 && $entity->role == 2
            && $entity->is_above_sixteen != $authUser->is_above_sixteen
        ) {

            return $this->sendError('Invalid Report', ['message' => 'You can only report users from the same age group.'], 403);
        }

        // Prevent users from reporting their own posts
        if ($type == 2 && isset($entity->user_id) && $entity->user_id == $user_id) {
            return $this->sendError('Invalid Report', ['message' => 'You cannot report your own post.'], 403);
        }

        // Prevent users from reporting their own comments
        if ($type == 3 && isset($entity->user_id) && $entity->user_id == $user_id) {
            return $this->sendError('Invalid Report', ['message' => 'You cannot report your own comment.'], 403);
        }


        if ($type == 4 && isset($entity->user_id) && $entity->user_id == $user_id) {
            return $this->sendError('Invalid Report', ['message' => 'You cannot report your own course.'], 403);
        }

        // Create a new report
        $report = Report::create([
            'reported_by' => $user_id,
            'type'        => $type,
            'type_id'     => $type_id,
            'reason'      => $request->reason,
            'description' => $request->description,
        ]);

        // Return success response
        return $this->sendResponse($report, 'Report submitted successfully.');
    }
}
