<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\api\v1\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Aws\S3\S3Client;

class S3UploadController extends BaseApiController
{
    public function generatePresignedUrl(Request $request): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'file_name' => 'required|string',
            'file_type' => 'required|string',
        ]);

        // Generate unique file key
        //$key = 'courses-media/' . time() . '_' . $validated['file_name'];
        $key = 'courses-media/' . $validated['file_name'];

        // Create S3 client
        $s3 = new S3Client([
            'region' => config('services.ses.region'),
            'version' => 'latest',
            'credentials' => [
                'key'    => config('services.ses.key'),
                'secret' => config('services.ses.secret'),
            ],
        ]);

        // Generate presigned URL
        $cmd = $s3->getCommand('PutObject', [
            'Bucket'      => env('AWS_BUCKET'),
            'Key'         => $key,
            'ContentType' => $validated['file_type'],
            'ACL'         => 'public-read', // or 'private' if needed
        ]);

        $request = $s3->createPresignedRequest($cmd, '+60 seconds');
        $uploadUrl = (string) $request->getUri();
        $fileUrl = explode('?', $uploadUrl)[0];
        $data = [
            'uploadUrl' => $uploadUrl,
            'fileUrl'   => $fileUrl, // public file URL
        ];
        return $this->sendResponse($data, 'S3 presigned URL generated successfully.');
    }
}
