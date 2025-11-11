<?php

namespace App\Http\Controllers\api\v1;

use App\Helpers\ApiResponse;
use App\Models\Image;
use App\Traits\Common_trait;

class ImageController extends BaseApiController
{
    use Common_trait;
    public function destroy($id)
    {
        $image = Image::find($id);
        if(!$image?->id){
            return ApiResponse::notFound(__('messages.not_found',['item' => 'Image']));
        }
        
        $this->deleteFile($image);
        
        $image->forceDelete();
        return ApiResponse::success([],__('messages.success_msg',['item' => 'Image deleted']));
    }
}
