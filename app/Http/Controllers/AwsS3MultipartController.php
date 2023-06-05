<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\File;

class AwsS3MultipartController extends Controller
{
    public function createMultipartUpload(Request $request)
    {
        $originalFilename = $request['filename'];
        $key = 'file_products/' . md5(uniqid()) . "." . File::extension($originalFilename);

        if (!is_string($key)) {
            return response()->json(['error' => 's3: filename returned from "getKey" must be a string'], 500);
        }

        $client = Storage::disk('s3')->getClient();
        $bucket = config('filesystems.disks.s3.bucket');
        $response = $client->createMultipartUpload([
            'Bucket'        => $bucket,
            'Key'           => $key,
            'Expires'       => 3000,
            'ContentType'    => $request["type"],
        ]);

        $mpuKey = !empty($response['Key']) ? $response['Key'] : null;
        $mpuUploadId = !empty($response['UploadId']) ? $response['UploadId'] : null;

        if (!$mpuKey || !$mpuUploadId) {
            return response()->json(['error' => 'Unable to process upload request.'], 400);
        }

        return ResponseHelper::ok([
            'key'       => $mpuKey,
            'uploadId'  => $mpuUploadId
        ]);
    }

    public function signPartUpload(Request $request)
    {
        $client = Storage::disk('s3')->getClient();

        $key = $request->has('key') ? $request->get('key') : null;
        $uploadId = $request->has('uploadId') ? $request->get('uploadId') : null;
        $partNumber = $request->has('partNumber') ? $request->get('partNumber') : null;

        if (!is_string($key)) {
            return response()->json(['error' => 's3: the object key must be passed as a query parameter. For example: "?key=abc.jpg"'], 400);
        }

        if (!intval($partNumber)) {
            return response()->json(['error' => 's3: the part number must be a number between 1 and 10000.'], 400);
        }

        // Creating a presigned URL. I don't think this is correct.
        $bucket = config('filesystems.disks.s3.bucket');
        $cmd = $client->getCommand('UploadPart', [
            'Bucket'        => $bucket,
            'Key'           => $key,
            'UploadId'      => $uploadId,
            'PartNumber'    => $partNumber,
        ]);

        $response = $client->createPresignedRequest($cmd, '+20 minutes');
        $presignedUrl = (string)$response->getUri();

        return ResponseHelper::ok(['url' => $presignedUrl]);
    }
    public function completeMultipartUpload(Request $request)
    {
        $key = $request->has('key') ? $request->get('key') : null;
        $uploadId = $request->has('uploadId') ? $request->get('uploadId') : null;
        $parts = $request->has('parts') ? ($request->get('parts')) : null;

        try {
            if (!is_string($key)) {
                return response()->json(['error' => 's3: the object key must be passed as a query parameter. For example: "?key=abc.jpg"'], 400);
            }

            if (!is_array($parts)) {
                return response()->json(['error' => 's3: "parts" must be an array of {ETag, PartNumber} objects.'], 400);
            }

            $client = Storage::disk('s3')->getClient();
            $bucket = config('filesystems.disks.s3.bucket');
            $completeUpload = $client->completeMultipartUpload([
                'Bucket'          => $bucket,
                'Key'             => $key,
                'UploadId'        => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
            ]);

            return response()->json([
                "location" => $completeUpload["Location"],
                "status" => "success"
            ]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return response()->json([
                "location" => $completeUpload["Location"],
                "status" => "error"
            ]);
        }
    }
}
