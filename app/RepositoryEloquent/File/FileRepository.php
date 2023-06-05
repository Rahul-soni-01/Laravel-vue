<?php
namespace App\RepositoryEloquent\File;

use App\Models\File;
use App\RepositoryEloquent\BaseRepository;

class FileRepository extends BaseRepository implements FileInterface
{
    public function model()
    {
        return File::class;
    }

    public function InsertFile($params)
    {
        $fileInsert = $this->model->create($params);

        return $fileInsert;
    }
}
