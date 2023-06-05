<?php


namespace App\RepositoryEloquent\ParentCategory;

use Illuminate\Support\Collection;

interface ParentCategoryInterface
{
    /**
     * @return Collection
     */
    public function list() : Collection;
}
