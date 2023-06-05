<?php

namespace App\RepositoryEloquent\Category;

use App\Define\CommonDefine;
use App\Helpers\FormatHelper;
use App\Models\Category;
use App\RepositoryEloquent\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Define\AuthDefine;

class CategoryRepository extends BaseRepository implements CategoryInterface
{
    public function model()
    {
        return Category::class;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getList($request)
    {
        $perPage = (int)$request->get('per_page') ?? CommonDefine::DEFAULT_LIMIT;

        $data = $this->model
            ->select(['id', 'name', 'author_id', 'parent_id'])
            ->with(['author:id,name', 'parent:id,name']);

        if ($request->has('authors') && $request->authors) {
            $data = $data->whereHas('author', function ($query) use ($request) {
                $query->whereIn('id', $request->authors);
            });
        }

        if ($request->has('name') && $request->name) {
            $data = $data->where('name', 'LIKE', '%' . $request->name . '%');
        }
        $data = $data->orderBy('created_at', "DESC")->paginate($perPage);

        return FormatHelper::paginate($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getDetail($id)
    {
        $userRole = auth()->user();

        if (!$userRole || $userRole->role_id !== AuthDefine::ROLE_ADMIN) {
            return [
                'success' => false,
                'message' => '管理者ではないユーザー'
            ];
        }

        $data = $this->model->find($id);

        if (!$data) {
            return [
                'success' => false,
                'message' => 'ユーザーが見つかりません'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    /**
     * @param $request
     * @return Category
     */
    public function createCategory($request): Category
    {
        $paramsCreate = [
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'author_id' => auth()->user()->id
        ];
        $data = $this->model->create($paramsCreate);

        return $data;
    }

    /**
     * @param $request
     * @param $id
     * @return boolean
     */
    public function updateCategory($request, $id): bool
    {
        $paramsUpdate = [
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            // 'author_id' => auth()->user()->id
        ];

        $update = $this->update($paramsUpdate, $id);

        return $update;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getListCategoryByUser($request)
    {
        $data = $this->model
            ->select(['id', 'name', 'author_id'])
            ->with(['author:id,name']);

        if ($request->has('authors') && $request->authors) {
            $data = $data->whereHas('author', function ($query) use ($request) {
                $query->whereIn('id', $request->authors);
            });
        }

        if ($request->has('name') && $request->name) {
            $data = $data->where('name', 'LIKE', '%' . $request->name . '%');
        }
        $data = $data->orderBy('created_at', "DESC")->get();

        return $data;
    }
}
