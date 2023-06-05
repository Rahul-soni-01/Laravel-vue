<?php
namespace App\RepositoryEloquent\ParentCategory;

use App\Models\CategoryParent;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Support\Collection;


class ParentCategoryRepository extends BaseRepository implements ParentCategoryInterface
{
    public function model()
    {
        return CategoryParent::class;
    }

    /**
     * @return Collection
     */
    public function list() : Collection
    {
        $data = $this->model->select(
            'id',
            'name'
        )->with(['children'])->get();

        return $data;
    }
}
