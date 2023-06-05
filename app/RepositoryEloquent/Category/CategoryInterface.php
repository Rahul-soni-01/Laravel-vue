<?php

namespace App\RepositoryEloquent\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryInterface
{
    /**
     * @param $request
     * @return Collection
     */
    public function getList($request);

    /**
     * @param id
     * @return mixed
     */
    public function getDetail($id);

    /**
     * @param $request
     * @return Category
     */
    public function createCategory($request): Category;

    /**
     * @param $request
     * @param $id
     * @return bool
     */
    public function updateCategory($request, $id): bool;

    /**
     * @param $request
     * @return Collection
     */
    public function getListCategoryByUser($request);
}
