<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\RepositoryEloquent\Tag\TagInterface;

class TagController extends Controller
{
    /**
     * @var TagInterface $tagRepository
     */
    private TagInterface $tagRepository;

    /**
     * @param TagInterface $tagRepository
     */
    public function __construct(TagInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getTagRelatedItems($id)
    {
        return ResponseHelper::ok($this->tagRepository->getTagRelatedItems($id));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function searchTag($id)
    {
        $data = [];
        $tagPost = $this->tagRepository->search($id);
        $data = [
            "posts" => $tagPost->posts,
            "products" => $tagPost->products
        ];
        return ResponseHelper::ok($data);
    }
}
