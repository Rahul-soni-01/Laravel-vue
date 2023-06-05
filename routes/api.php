<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentKeyController;
use App\Http\Controllers\ProductPaymentController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserInfoController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FanController;
use App\Http\Controllers\LivestreamController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\NotificationHistoryController;
use App\Http\Controllers\ParentCategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AwsS3MultipartController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/test', [ProductController::class, 'test']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('/register-temp', [AuthController::class, 'registerTemp'])->name('register-temp');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/forgot/password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/password/update', [AuthController::class, 'updatePassword'])->name('password-update');
    Route::post('/fake-sendmail', [AuthController::class, 'fakeSendmail'])->name('fake-sendmail');
    Route::post('/user-support', [AuthController::class, 'userSupport'])->name('user-support');
    Route::post('/user-support2', [AuthController::class, 'userSupport2'])->name('user-support2');
});

Route::group(['prefix' => 's3'], function () {
    Route::post('/create-multipart-upload', [AwsS3MultipartController::class, 'createMultipartUpload']);
    Route::post('/sign-part-upload', [AwsS3MultipartController::class, 'signPartUpload']);
    Route::post('/complete-multipart-upload', [AwsS3MultipartController::class, 'completeMultipartUpload']);
});

Route::get('/posts/list/{fanId}', [PostController::class, 'listPostOnFan']);
Route::get('/products/list', [ProductController::class, 'listProductUser']);
Route::get('/products/detail/{id}', [ProductController::class, 'detailProductUserWithoutAuth']);
Route::get('/fan/list', [FanController::class, 'userListFanClub']);
Route::get('/fan/detail/{id}', [FanController::class, 'userDetailFan']);
Route::get('/fan/nickname/{nickname}', [FanController::class, 'getFromNickname']);
Route::get('/plan/list', [PlanController::class, 'userListPlan']);
Route::get('/category/list-by-parent', [CategoryController::class, 'userListCategoryByParent']);
Route::get('/category/list', [CategoryController::class, 'index']);
Route::get('/payment', [ProductController::class, 'payment']);
Route::get('/comment/list', [CommentController::class, 'index']);
Route::get('/tags/search/{id}', [TagController::class, 'searchTag']);

Route::group(['middleware' => 'api.auth'], function () {
    Route::get('/current-user', [UserController::class, 'getCurrentUser'])->name('current-user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::group(['prefix' => 'account'], function () {
        Route::post('/switch-role', [UserController::class, 'switchRole'])->name('switch-role');
        Route::post('/update-user-info', [UserInfoController::class, 'userUpdateInfo']);
        Route::post('/update-user-notify', [UserInfoController::class, 'userUpdateNotification']);
        Route::delete('/delete/{id}', [UserController::class, 'deleteAccount']);
    });
    Route::get('/product/get-favorite', [ProductController::class, 'getFavoriteProduct']);
    Route::post('/product/favorite', [ProductController::class, 'favoriteProduct']);
    Route::post('/product/view', [ProductController::class, 'productView']);
    Route::post('/products/create-payment', [ProductController::class, 'createPayment']);

    Route::group(['prefix' => 'post'], function () {
        Route::get('get-favorite', [PostController::class, 'getFavoritePost']);
        Route::post('favorite', [PostController::class, 'favoritePost']);
    });

    Route::group(['prefix' => 'notification'], function () {
        Route::get('/list', [NotificationHistoryController::class, 'index']);
        Route::post('/read/{id}', [NotificationHistoryController::class, 'readNotification']);
        Route::post('/create', [NotificationHistoryController::class, 'create']);
    });

    Route::group(['prefix' => 'message'], function () {
        Route::get('/index', [MessageController::class, 'getMessagesUser']);
        Route::get('/get-message-creator', [MessageController::class, 'getMessagesCreator']);
        Route::get('/unread', [MessageController::class, 'getMessagesUnread']);
        Route::get('/detail', [MessageController::class, 'getDetailMessage']);
        Route::post('/create', [MessageController::class, 'createMessage']);
        Route::post('/read/{id}', [MessageController::class, 'readMessage']);
        Route::delete('/delete/{id}', [MessageController::class, 'deleteMessage']);
        Route::post('/create-message-detail', [MessageController::class, 'createMessageDetail']);
    });

    Route::group(['prefix' => 'comment'], function () {
        Route::post('/create', [CommentController::class, 'create']);
        Route::post('/delete-by-user', [CommentController::class, 'deleteByUser']);
        Route::delete('/delete/{id}', [CommentController::class, 'destroy']);
    });

    Route::group(['prefix' => 'plans'], function () {
        Route::post('/create-plan-user', [PlanController::class, 'createPlanUser']);
        Route::post('/cancel-plan-user/{id}', [PlanController::class, 'cancelPlanUser']);
    });

    Route::group(['prefix' => 'admin', 'middleware' => 'admin'], function () {
        Route::get('/category-parent/list', [ParentCategoryController::class, 'index']);

        Route::group(['prefix' => 'users'], function () {
            Route::get('/index', [UserController::class, 'getDataUser']);
            Route::post('/change-user-status', [UserController::class, 'changeStatusUser']);
            Route::post('/change-user-confirm-status', [UserController::class, 'changeConfirmStatus']);
            Route::delete('/delete', [UserController::class, 'deleteUser']);
            Route::get('/detail', [UserController::class, 'getDetail']);
        });

        Route::group(['prefix' => 'posts'], function () {
            Route::get('/index', [PostController::class, 'index'])->name('admin-posts-index');
            Route::get('/detail/{id}', [PostController::class, 'detailAdmin'])->name('admin-posts-detail');
            Route::post('/update-status', [PostController::class, 'updateStatus'])->name('admin-posts-update-status');
            Route::delete('/delete/{id}', [PostController::class, 'adminDeletePost']);
        });

        Route::group(['prefix' => 'products'], function () {
            Route::get('/index', [ProductController::class, 'listProductAdmin'])->name('admin-products-list');
            Route::get('/detail/{id}', [ProductController::class, 'detailProductAdmin']);
            Route::post('/update-status', [ProductController::class, 'updateStatus'])->name('products-update-status');
            Route::delete('/delete/{id}', [ProductController::class, 'destroy'])->name('products-delete');
        });

        Route::group(['prefix' => 'category'], function () {
            Route::get('/index', [CategoryController::class, 'index'])->name('admin-category-index');
            Route::get('/detail/{id}', [CategoryController::class, 'detail'])->name('admin-category-detail');
            Route::post('/create', [CategoryController::class, 'create'])->name('admin-category-create');
            Route::post('/update/{id}', [CategoryController::class, 'update'])->name('admin-category-update');
            Route::delete('/delete/{id}', [CategoryController::class, 'destroy'])->name('admin-category-delete');
        });
    });

    Route::group(['prefix' => 'creator', 'middleware' => 'creator'], function () {
        Route::group(['prefix' => 'users'], function () {
            Route::delete('/delete', [UserController::class, 'creatorDeleteUser'])->name('admin-user-delete');
        });

        Route::group(['prefix' => 'posts'], function () {
            Route::get('/get-pre-signed', [PostController::class, 'getPreSigned'])->name('get-pre-signed-post');
            Route::get('/detail/{id}', [PostController::class, 'detail']);
            Route::get('/', [PostController::class, 'creatorListPost']);
            Route::post('/create', [PostController::class, 'create'])->name('posts-create');
            Route::post('/update/{id}', [PostController::class, 'update'])->name('posts-update');
            Route::post('/update-public', [PostController::class, 'updatePostPublic']);
            Route::delete('/delete/{id}', [PostController::class, 'creatorDeletePost']);
        });

        Route::group(['prefix' => 'products'], function () {
            Route::get('/get-pre-signed', [ProductController::class, 'getPreSigned'])->name('get-pre-signed-product');
            Route::get('/list', [ProductController::class, 'index'])->name('products-list');
            Route::get('/detail/{id}', [ProductController::class, 'detailProductCreator']);
            Route::post('/create2', [ProductController::class, 'create'])->name('products-create');
            Route::post('/update-public', [ProductController::class, 'updateProductPublic']);
            Route::post('/update/{id}', [ProductController::class, 'update'])->name('products-update');
            Route::delete('/delete/{id}', [ProductController::class, 'creatorDestroy']);
        });

        Route::group(['prefix' => 'fan'], function () {
            Route::get('/list', [FanController::class, 'creatorListFanClub']);
            Route::get('/detail/{id}', [FanController::class, 'creatorDetailFan']);
            Route::get('/get-by-author', [FanController::class, 'getByAuthor']);
            Route::post('/create', [FanController::class, 'create']);
            Route::delete('/delete/{id}', [FanController::class, 'destroy']);
            Route::post('/delete-user-follow', [FanController::class, 'deleteUserFollow']);
            Route::get('get-user-follow', [FanController::class, 'getUserFollowFan']);
        });

        Route::group(['prefix' => 'livestream'], function () {
            Route::get('/detail', [LivestreamController::class, 'detail']);
            Route::post('/create', [LivestreamController::class, 'create']);
            Route::post('/update', [LivestreamController::class, 'update']);
        });

        Route::group(['prefix' => 'plan'], function () {
            Route::get('/list', [PlanController::class, 'creatorListPlan']);
            Route::post('/create', [PlanController::class, 'create']);
            Route::post('/update/{id}', [PlanController::class, 'update']);
            Route::delete('/delete/{id}', [PlanController::class, 'destroy']);
            Route::get('/detail/{id}', [PlanController::class, 'detail']);
        });

        Route::group(['prefix' => 'category'], function () {
            Route::get('/index', [CategoryController::class, 'listCategoryByCreator']);
        });

        Route::group(['prefix' => 'brand'], function () {
            Route::get('/index', [BrandController::class, 'creatorListBrand']);
        });

        Route::group(['prefix' => 'comment'], function () {
            Route::delete('/delete/{id}', [CommentController::class, 'destroy']);
            Route::post('/insert', [CommentController::class, 'insert']);
        });
    });

    Route::group(['prefix' => 'user', 'middleware' => 'user'], function () {
        Route::post('/request-creator', [UserInfoController::class, 'request'])->name('request-creator');
        Route::get('/list-user', [UserController::class, 'UserGetDataUser']);
        Route::get('/tags/{id}', [TagController::class, 'getTagRelatedItems']);

        Route::group(['prefix' => 'products'], function () {
            Route::get('/list', [ProductController::class, 'listProductUser'])->name('user-products-list');
            Route::get('/detail/{id}', [ProductController::class, 'detailProductUser']);
            Route::get('/favorites', [ProductController::class, 'getFavoriteProducts']);
            Route::post('/register', [ProductController::class, 'userRegisterProduct']);
        });

        Route::group(['prefix' => 'fan'], function () {
            Route::get('/list', [FanController::class, 'userListFanClub']);
            Route::get('/detail/{id}', [FanController::class, 'userDetailFan']);
            Route::post('/register-fan', [FanController::class, 'userRegisterFanClub']);
            Route::post('/favorite', [FanController::class, 'favoriteFan']);
            Route::get('/favorites', [FanController::class, 'getFavoriteFans']);
        });

        Route::group(['prefix' => 'livestream'], function () {
            Route::get('/list', [LivestreamController::class, 'index']);
            Route::get('/detail/{id}', [LivestreamController::class, 'detailViewUser']);
        });

        Route::group(['prefix' => 'category'], function () {
            Route::get('/index', [CategoryController::class, 'listCategoryByUser']);
            Route::get('/list-by-parent', [CategoryController::class, 'userListCategoryByParent']);
        });

        Route::group(['prefix' => 'plan'], function () {
            Route::get('/list', [PlanController::class, 'userListPlan']);
            Route::post('/register', [PlanController::class, 'userRegisterPlan']);
            Route::post('check-user-in-plan', [PlanController::class, 'checkUserInPlan']);
            Route::post('check-user-in-plan-streaming', [PlanController::class, 'checkUserInPlanStreaming']);
        });

        Route::group(['prefix' => 'posts'], function () {
            Route::get('/index', [PostController::class, 'listPostUser']);
            Route::get('/list/{fanId}', [PostController::class, 'listPostOnFan']);
            Route::get('/favorites', [PostController::class, 'getFavoritePosts']);
            Route::get('/all', [PostController::class, 'getListByKeyword']);
        });

        Route::group(['prefix' => 'payment-key'], function () {
            Route::get('/get-by-user', [PaymentKeyController::class, 'getByUser']);
            Route::post('/create-key', [PaymentKeyController::class, 'createRandomKey']);
            Route::post('/update-key', [PaymentKeyController::class, 'updateStatusKey']);
        });
    });
});
