<?php

namespace App\RepositoryEloquent\Tag;

use App\Models\Tag;
use Illuminate\Support\Collection;

interface TagInterface
{
    /**
     * @param $request
     * @return Tag|null
     */
    public function createTag($request) : Tag|null;


    public function findTagName($tagName);

    /**
     * @param $id
     * @return mixed
     */
    public function getTagRelatedItems($id);

    public function search($id);
}
