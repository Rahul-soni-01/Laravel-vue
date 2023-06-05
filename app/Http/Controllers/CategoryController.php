<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\Category\UpdateRequest;
use App\RepositoryEloquent\ParentCategory\ParentCategoryInterface;
use Illuminate\Http\Request;
use App\RepositoryEloquent\Category\CategoryInterface;
use App\Http\Requests\Category\CreateRequest;
use App\Http\Requests\Category\ListCategoryRequest;

class CategoryController extends Controller
{
    /**
     *@var CategoryInterface $categoryRepository
     */
    private CategoryInterface $categoryRepository;

    /**
     * @var ParentCategoryInterface
     */
    private ParentCategoryInterface $parentCategoryRepository;

    /**
     *@param  CategoryInterface $categoryRepository
     * @param ParentCategoryInterface $parentCategoryRepository
     */
    public function __construct(
        CategoryInterface $categoryRepository,
        ParentCategoryInterface $parentCategoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->parentCategoryRepository = $parentCategoryRepository;
    }

    /**
     * Get data Category.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(ListCategoryRequest $request)
    {
        $dataCategory = $this->categoryRepository->getList($request);

        return ResponseHelper::ok($dataCategory);
    }

    /**
     * Get data detail Category.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function detail(Request $request)
    {
        $categoryDetail = $this->categoryRepository->getDetail($request->route('id'));
        if (!$categoryDetail['success']) {
            return  ResponseHelper::bad([], $categoryDetail['message']);
        }

        return  ResponseHelper::ok($categoryDetail['data']);
    }

    /**
     * Create a new category
     * @param CreateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRequest $request)
    {
        $create = $this->categoryRepository->createCategory($request);

        if (!$create) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }

    /**
     * Update a category.
     *
     * @param UpdateRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, $id)
    {
        $update = $this->categoryRepository->updateCategory($request, $id);

        if (!$update) {
            return ResponseHelper::bad();
        }

        return ResponseHelper::ok();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->categoryRepository->destroy($id);

        return ResponseHelper::ok();
    }

    /**
     * Get data Category.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function listCategoryByUser(ListCategoryRequest $request)
    {
        $dataCategory = $this->categoryRepository->getListCategoryByUser($request);

        return ResponseHelper::ok($dataCategory);
    }

    /**
     * Get data Category.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function userListCategoryByParent()
    {
        $dataCategory = $this->parentCategoryRepository->list();

        return ResponseHelper::ok($dataCategory);
    }

    /**
     * Get data Category.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function listCategoryByCreator(ListCategoryRequest $request)
    {
        $dataCategory = $this->categoryRepository->getListCategoryByUser($request);

        return ResponseHelper::ok($dataCategory);
    }
}
