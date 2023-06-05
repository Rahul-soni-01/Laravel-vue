<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Client\Response;
use App\Define\CommonDefine;

class BrandController extends Controller
{
    /**
     * @return mixed
     */
    public function creatorListBrand() : mixed
    {
        $listBrand = CommonDefine::BRAND;
        $listBrandResponse = [];
        foreach ($listBrand as $key => $item) {
            $listBrandResponse[] = [
                'id' => $key + 1,
                'name' => $item
            ];
        }

        return ResponseHelper::ok($listBrandResponse);
    }
}
