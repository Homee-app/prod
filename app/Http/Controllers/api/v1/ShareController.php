<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\api\v1\BaseApiController;
use App\Models\Room;
use App\Models\User;
use App\Traits\Common_trait;
use Illuminate\Http\Request;
// use App\Services\BranchService;
use App\Services\DeepLinkService;

class ShareController extends BaseApiController
{
    use Common_trait;

    // public function __construct(protected BranchService $branchService) {}
    public function __construct(protected DeepLinkService $deepLinkService) {}

    public function room(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        $linkData = $this->deepLinkService->createDeepLink(
            'room',
            $room->id
        );

        return ApiResponse::success($linkData);
    }

    public function tetant(Request $request, $id)
    {
        $tetant = User::findOrFail($id);
        $linkData = $this->deepLinkService->createDeepLink(
            'tenant-details',
            $tetant->id
        );

        return ApiResponse::success($linkData);
    }
}
