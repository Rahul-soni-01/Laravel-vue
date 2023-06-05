<?php

namespace App\RepositoryEloquent\Product;

use App\Models\Product;
use Illuminate\Support\Collection;

interface ProductInterface
{
    /**
     * @param $request
     * @return Product|null
     */
    public function createProduct($request): Product|null;

    /**
     * @param $request
     * @param $id
     * @return Product|null
     */
    public function updateProductPublic($request): bool|null;

    /**
     * @param $request
     * @return array
     */
    public function getList($request);

    /**
     * @param $request
     * @return array
     */
    public function getListProductPublic($request);

    /**
     * @param $request
     * @return array
     */
    public function listProductAdmin($request);

    /**
     * @param $request
     * @return bool
     */
    public function updateStatus($request): bool;

    /**
     * @param $id
     * @return bool|null
     */
    public function deleteProduct($id): bool|null;

    /**
     * @param $id
     * @return \App\Models\Product
     */
    public function getDetail($id): \App\Models\Product;

    /**
     * @param $id
     * @return \App\Models\Product
     */
    public function getDetailAdmin($id): \App\Models\Product;

    /**
     * @param $request
     * @return array
     */
    public function getListCreator($request);

    /**
     * @param $request
     * @return mixed
     */
    public function productView($request);

    /**
     * @param $request
     * @return array
     */
    public function getFavoriteProducts($request);

    /**
     * @param $productId
     * @return mixed
     */
    public function findProduct($productId);

    public function updateView($productId);
}
