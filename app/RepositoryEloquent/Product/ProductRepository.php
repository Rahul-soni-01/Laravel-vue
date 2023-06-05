<?php

namespace App\RepositoryEloquent\Product;

use App\Define\CommonDefine;
use App\Helpers\FormatHelper;
use App\Models\Product;
use App\RepositoryEloquent\BaseRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ProductRepository extends BaseRepository implements ProductInterface
{
    public function model()
    {
        return Product::class;
    }

    /**
     * @param $request
     * @return Product|null
     */
    public function createProduct($request): Product|null
    {
        $create = $this->model->create($request);

        return $create;
    }

    /**
     * @param $request
     * @param $id
     * @return Product|null
     */
    public function updateProduct($request, $id): Product|null
    {
        $update = $this->updateOrCreate(
            [
                'id' => $id
            ],
            $request
        );

        return $update;
    }

    /**
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        //        $perPage = (int)$request->get('per_page') ?? CommonDefine::DEFAULT_LIMIT;
        $data = $this->model
            ->select(
                'id',
                'title',
                'category_id',
                'author_id',
                'price',
                'is_public',
                'thumbnail_url',
                'type',
                'status',
                'view',
                'created_at',
                'updated_at',
                'brand_id',
                'plan_id'
            )
            ->where('status', CommonDefine::ACTIVE)
            ->with([
                'files:id,url',
                'category:id,name',
                'user:id,name',
                'tags:id,name'
            ])
            ->withCount('usersFavorite');

        if ($request->has('title') && $request->title) {
            $data = $data->where('title', 'LIKE', '%' . $request->title . '%');
        }

        if ($request->has('author_id') && $request->author_id) {
            $data = $data->where('author_id', $request->author_id);
        }

        if ($request->has('category_ids') && $request->category_ids) {
            $data = $data->whereIn('category_id', $request->category_ids);
        }

        if ($request->has('is_month') && $request->is_month) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subMonth(1))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_week') && $request->is_week) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subDay(7))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_hour') && $request->is_hour) {
            $data = $data->whereDate('created_at', Carbon::now());
        }

        if ($request->has('status') && $request->status) {
            $data = $data->where('status', $request->status);
        }

        if ($request->has('is_public') && $request->is_public) {
            $data = $data->where('is_public', $request->is_public);
        }

        if ($request->has('type') && $request->type) {
            $data = $data->where('type', $request->type);
        }

        if ($request->has('sort_by') && $request->sort_by) {
            $data = $data->orderBy($request->sort_by, $request->sort_type ?? 'desc');
        }

        $data = $data->orderBy('created_at', "DESC");
        //            ->paginate($perPage);

        //        return FormatHelper::paginate($data);
        return $data->get();
    }

    /**
     * @param $request
     * @return array
     */
    public function getListProductPublic($request)
    {
        $perPage = (int)$request->get('per_page') ?? 30;
        $data = $this->model
            ->select(
                'id',
                'title',
                'category_id',
                'author_id',
                'price',
                'is_public',
                'type',
                'status',
                'view',
                'created_at',
                'updated_at',
                'brand_id',
                'thumbnail_url',
                'plan_id'
            )
            ->where('is_public', CommonDefine::ACTIVE)
            ->where('status', CommonDefine::ACTIVE)
            ->with([
                'category:id,name',
                'user:id,name',
                'tags:id,name',
                'user.ownerFan',
            ])
            ->withCount('usersFavorite');

        if ($request->has('title') && $request->title) {
            $data = $data->where(function ($query) use ($request) {
                $query->where('title', 'LIKE', '%' . $request->title . '%')->orWhere(function ($query) use ($request) {
                    $query->whereHas('tags', function ($q) use ($request) {
                        $q->where('tags.name', 'LIKE', '%' . $request->title . '%');
                    });
                });
            });
        }

        if ($request->has('author_id') && $request->author_id) {
            $data = $data->where('author_id', $request->author_id);
        }

        if ($request->has('category_ids') && $request->category_ids) {
            $data = $data->whereIn('category_id', $request->category_ids);
        }

        if ($request->has('is_month') && $request->is_month) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subMonth(1))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_week') && $request->is_week) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subDay(7))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_hour') && $request->is_hour) {
            $data = $data->whereDate('created_at', Carbon::now());
        }

        if ($request->has('status') && $request->status) {
            $data = $data->where('status', $request->status);
        }

        if ($request->has('type') && $request->type) {
            $data = $data->where('type', $request->type);
        }

        if ($request->has('sort_by') && $request->sort_by) {
            $data = $data->orderBy($request->sort_by, $request->sort_type ?? 'desc');
        }

        if ($request->has('is_top') && $request->is_top) {
            $data = $data->orderBy('view', 'desc');
        }

        if ($request->has('top_favorite') && $request->top_favorite) {
            $data = $data->orderBy('users_favorite_count', 'desc');
        }

        $data = $data->paginate($perPage);

        return FormatHelper::paginate($data);
    }

    /**
     * @param $request
     * @return array
     */
    public function listProductAdmin($request)
    {
        $perPage = (int)$request->get('per_page') ?? CommonDefine::DEFAULT_LIMIT;
        $data = $this->model
            ->select(
                'id',
                'title',
                'category_id',
                'author_id',
                'price',
                'is_public',
                'type',
                'status',
                'view',
                'created_at',
                'updated_at',
                'brand_id',
                'thumbnail_url',
                'plan_id'
            )
            ->with([
                'files:id,url',
                'category:id,name',
                'user:id,name',
                'tags:id,name'
            ])
            ->withCount('usersFavorite');

        if ($request->has('title') && $request->title) {
            $data = $data->where('title', 'LIKE', '%' . $request->title . '%');
        }

        if ($request->has('author_id') && $request->author_id) {
            $data = $data->where('author_id', $request->author_id);
        }

        if ($request->has('category_ids') && $request->category_ids) {
            $data = $data->whereIn('category_id', $request->category_ids);
        }

        if ($request->has('is_month') && $request->is_month) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subMonth(1))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_week') && $request->is_week) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subDay(7))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_hour') && $request->is_hour) {
            $data = $data->whereDate('created_at', Carbon::now());
        }

        if ($request->has('status') && $request->status) {
            $data = $data->where('status', $request->status);
        }

        if ($request->has('is_public') && $request->is_public) {
            $data = $data->where('is_public', $request->is_public);
        }

        if ($request->has('type') && $request->type) {
            $data = $data->where('type', $request->type);
        }

        if ($request->has('sort_by') && $request->sort_by) {
            $data = $data->orderBy($request->sort_by, $request->sort_type ?? 'desc');
        }

        $data = $data->orderBy('created_at', "DESC")
            ->paginate($perPage);

        return FormatHelper::paginate($data);
    }

    /**
     * @param $request
     * @return array
     */
    public function getListCreator($request)
    {
        $perPage = (int)$request->get('per_page') ?? CommonDefine::DEFAULT_LIMIT;
        $data = $this->model
            ->select(
                'id',
                'title',
                'category_id',
                'author_id',
                'price',
                'is_public',
                'type',
                'status',
                'view',
                'created_at',
                'updated_at',
                'brand_id',
                'thumbnail_url',
                'plan_id'
            )
            ->where('author_id', auth()->user()->id)
            ->where('status', CommonDefine::ACTIVE)
            ->with([
                'files:id,url',
                'category:id,name',
                'user:id,name',
                'tags:id,name'
            ])
            ->withCount('usersFavorite');

        if ($request->has('title') && $request->title) {
            $data = $data->where('title', 'LIKE', '%' . $request->title . '%');
        }

        if ($request->has('author_id') && $request->author_id) {
            $data = $data->where('author_id', $request->author_id);
        }

        if ($request->has('category_ids') && $request->category_ids) {
            $data = $data->whereIn('category_id', $request->category_ids);
        }

        if ($request->has('is_month') && $request->is_month) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subMonth(1))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_week') && $request->is_week) {
            $data = $data->whereDate('created_at', '>=', Carbon::now()->subDay(7))
                ->whereDate('created_at', '<=', Carbon::now());
        }

        if ($request->has('is_hour') && $request->is_hour) {
            $data = $data->whereDate('created_at', Carbon::now());
        }

        if ($request->has('status') && $request->status) {
            $data = $data->where('status', $request->status);
        }

        if ($request->has('is_public') && $request->is_public) {
            $data = $data->where('is_public', $request->is_public);
        }

        if ($request->has('type') && $request->type) {
            $data = $data->where('type', $request->type);
        }

        if ($request->has('sort_by') && $request->sort_by) {
            $data = $data->orderBy($request->sort_by, $request->sort_type ?? 'desc');
        }

        if ($request->has('is_top') && $request->is_top) {
            $data = $data->orderBy('view', 'desc');
        }

        if ($request->has('top_favorite') && $request->top_favorite) {
            $data = $data->orderBy('users_favorite_count', 'desc');
        }

        $data = $data->orderBy('created_at', "DESC")->paginate($perPage);

        return FormatHelper::paginate($data);
    }

    /**
     * @param $request
     * @return bool
     */
    public function updateStatus($request): bool
    {
        $productIds = Arr::get($request, 'ids');
        $status = Arr::get($request, 'status');

        $update = $this->model
            ->whereIn('id', $productIds)
            ->update([
                'status' => $status
            ]);

        return $update;
    }

    /**
     * @param $request
     * @return bool
     */
    public function updateProductPublic($request): bool
    {
        $params = [
            'is_public' => $request['public_status']
        ];

        if ($request['public_status'] == 1) {
            $params['date_public'] = Carbon::now();
        }

        $update = $this->update($params, $request['id']);

        return $update;
    }

    /**
     * @param $id
     * @return bool|null
     */
    public function deleteProduct($id): bool|null
    {
        $delete = $this->destroy($id);
        return $delete;
    }

    /**
     * @param $id
     * @return \App\Models\Product
     */
    public function getDetail($id): \App\Models\Product
    {
        $data = $this->model
            ->select(
                'id',
                'title',
                'content',
                'category_id',
                'author_id',
                'price',
                'is_public',
                'auto_public',
                'date_public',
                'type',
                'status',
                'view',
                'created_at',
                'updated_at',
                'brand_id',
                'pro_stripe_id',
                'price_stripe_id',
                'product_stripe',
                'price_stripe',
                'thumbnail_url',
                'plan_id'
            )
            ->with([
                'files:id,url,name',
                'category',
                'tags:id,name',
                'userPayments',
                'plan.fan'
            ])
            ->with('usersFavorite')
            ->findOrFail($id);

        return $data;
    }

    /**
     * @param $id
     * @return \App\Models\Product
     */
    public function getDetailAdmin($id): \App\Models\Product
    {
        $data = $this->model
            ->select(
                'id',
                'title',
                'category_id',
                'content',
                'author_id',
                'price',
                'is_public',
                'type',
                'status',
                'view',
                'created_at',
                'updated_at',
                'brand_id',
                'thumbnail_url',
                'plan_id'
            )
            ->with([
                'files:id,url',
                'category',
                'tags:id,name'
            ])
            ->withCount('usersFavorite')
            ->findOrFail($id);

        return $data;
    }

    public function productView($request)
    {
        $product = $this->findOrFail($request->get('product_id'));
        if (!$product) {
            return false;
        }
        $data = [
            'view' => $product->view + 1
        ];

        $this->update($data, $product->id);
        return true;
    }

    /**
     * Get list of favorite product or video
     * @param $request
     * @return array
     */
    public function getFavoriteProducts($request)
    {
        $type = Arr::get($request, 'type', CommonDefine::PRODUCT_VIDEO);
        $productPerPage = (int)$request->get('per_page') ?? CommonDefine::DEFAULT_LIMIT_FAVORITE_PAGE;

        $data = $this->model->select('products.*')->with('files:url')
            ->join('product_favorite', 'products.id', '=', 'product_favorite.product_id')
            ->where('product_favorite.user_id', auth()->user()->id)
            ->where('type', $type)
            ->where('products.is_public', CommonDefine::PRODUCT_IS_PUBLIC)
            ->with(['user.userInfo', 'usersFavorite'])
            ->orderBy('product_favorite.created_at', 'DESC')
            ->paginate($productPerPage);

        return FormatHelper::paginate($data);
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function findProduct($productId)
    {
        return $this->model->findOrFail($productId);
    }

    public function updateView($productId)
    {
        $product = $this->findProduct($productId);
        $product->view = $product->view + 1;
        $product->save();
    }
}
