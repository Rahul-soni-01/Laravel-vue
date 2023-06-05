<?php

namespace App\Http\Controllers;
use App\Helpers\ResponseHelper;
use App\RepositoryEloquent\ParentCategory\ParentCategoryInterface;
use Illuminate\Http\Response;

class ParentCategoryController extends Controller
{
    /**
     * @var ParentCategoryInterface
     */
    private ParentCategoryInterface $parentCategoryRepository;

    /**
     * @param ParentCategoryInterface $parentCategoryRepository
     */
    public function __construct(ParentCategoryInterface $parentCategoryRepository)
    {
        $this->parentCategoryRepository = $parentCategoryRepository;
    }

    /**
     * Get list parent category.
     * @return Response
     */
    public function index()
    {
        return ResponseHelper::ok($this->parentCategoryRepository->list());
    }
}
