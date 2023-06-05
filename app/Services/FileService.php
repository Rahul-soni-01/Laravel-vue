<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileService extends BaseService
{
    /**
     * get URL file to S3
     *
     * @param $files
     * @param $path
     * @return bool
     * @throws \Exception
     */
    public function storeFileToS3($files, $path)
    {
        try {
            $fileName = $files->getClientOriginalName();
            $extension = $files->getClientOriginalExtension();
            $fileName = trim($fileName, '.' . $extension) . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $urlPath = Storage::disk('s3')->put($path, $files, $fileName);

            return $urlPath;
        }catch (\Exception $exception)
        {
            throw $exception;
        }

    }
}

