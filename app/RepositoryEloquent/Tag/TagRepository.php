<?php

namespace App\RepositoryEloquent\Tag;

use App\Define\CommonDefine;
use App\Models\Tag;
use App\RepositoryEloquent\BaseRepository;

class TagRepository extends BaseRepository implements TagInterface
{
    public function model()
    {
        return Tag::class;
    }

    /**
     * @param $request
     * @return Tag|null
     */
    public function createTag($request): Tag|null
    {
        $create = $this->updateOrCreate(
            [
                'name' => $request['name'],
                'author_id' => $request['author_id']
            ],
            $request
        );

        return $create;
    }


    /**
     * @param $tagName
     * @return mixed
     */
    public function findTagName($tagName)
    {
        return $this->model->where('name',  $tagName)->first();
    }

    /**
     * Get products and posts having the tag
     * @param $id
     * @return mixed
     */
    public function getTagRelatedItems($id)
    {
        $relatedItems = $this->model
            ->select('id')
            ->where('id', $id)
            ->with('posts', function ($q) {
                $q->select('posts.id', 'posts.title', 'posts.content', 'posts.url_file')
                    ->where('posts.status', CommonDefine::POST_STATUS_ENABLE)
                    ->where('posts.is_public', CommonDefine::POST_IS_PUBLIC)
                    ->with('users', 'category');
            })
            ->with('products', function ($q) {
                $q->select('products.id', 'products.title', 'products.content', 'products.price')
                    ->where('products.status', CommonDefine::POST_STATUS_ENABLE)
                    ->where('products.is_public', CommonDefine::PRODUCT_IS_PUBLIC)
                    ->with('user', 'category', 'files');
            })
            ->first();

        unset($relatedItems->id);

        return $relatedItems;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function search($id)
    {
        $data = $this->model->with(['posts', 'posts.users:id', 'posts.users.ownerFan:id,author_id,nickname'])->findOrFail($id);
        return $data;
    }
}
