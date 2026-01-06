<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VerificationCodesController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\CaptchasController;
use App\Http\Controllers\Api\AuthorizationsController;
use App\Http\Controllers\Api\ImagesController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\TopicsController;
use App\Http\Controllers\Api\UserAddressesController;
use App\Http\Controllers\Api\ProductCategoriesController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\PaymentController;

Route::prefix('v1')->name('api.v1.')->group(function () {
    # 短信验证码
    // Route::post('verificationCodes', [VerificationCodesController::class, 'store'])->name('verificationCodes.store');
    # 用户注册
    Route::post('users', [UsersController::class, 'store'])->name('users.store');
    # 获取第三方登录授权URL
    Route::get('socials/{social_type}/authorization-url', [AuthController::class, 'getAuthorizationUrl'])->name('socials');
    # 第三方登录
    Route::post('socials/{social_type}/authorizations', [AuthorizationsController::class, 'socialStore'])
    ->where('social_type', 'wechat')
    ->name('socials.authorizations.store');
    # 密码登录
    Route::post('authorizations', [AuthorizationsController::class, 'store'])->name('authorizations.store');
    # 刷新token
    Route::put('authorizations/current', [AuthController::class, 'update'])->name('authorizations.update');
    // # 删除token
    // Route::delete('authorizations/current', [AuthController::class, 'destroy'])->name('authorizations.destroy');


    # 限制 一分钟只能调用60次
    Route::middleware('throttle:' . config('api.rate_limits.access'))->group(function () {
        # 游客可以访问的接口

        // 某个用户的详情
        Route::get('users/{user}', [UsersController::class, 'show'])->name('users.show');
        // 分类列表
        Route::apiResource('categories', CategoriesController::class)->only('index');
        // 某个用户发布的话题
        Route::get('users/{user}/topics', [TopicsController::class, 'userIndex'])->name('users.topics.index');
        // 话题列表、详情
        Route::apiResource('topics', TopicsController::class)->only([
            'index', 'show'
        ]);


        # 登录后才能访问的接口
        Route::middleware('auth:api')->group(function() {
            // 当前登录用户信息
            Route::get('user', [UsersController::class, 'me'])->name('user.show');
            // 编辑登录用户信息
            Route::patch('user', [UsersController::class, 'update'])->name('user.update');
            // 上传图片
            Route::post('images', [ImagesController::class, 'store'])->name('images.store');

            // 发布、修改、删除话题
            Route::apiResource('topics', TopicsController::class)->only([
                'store', 'update', 'destroy'
            ]);


            // 用户收货地址
            Route::apiResource('addresses', UserAddressesController::class)->only([
                'index','store','update','destroy'
            ]);
            Route::patch('addresses/{id}/default',[UserAddressesController::class, 'isDefault'])->name('addresses.isDefault');

            // 商品分类
            Route::apiResource('productcategories', ProductCategoriesController::class)->only([
                'index','show','store','update','destroy'
            ]);

            // 商品
            Route::apiResource('products', ProductsController::class)->only([
                'index','show',
            ]);

            // 购物车
            Route::apiResource('carts', CartController::class)->only([
                'index','show','store','update',
            ]);
            Route::delete('carts', [CartController::class, 'destroy']);

            // 订单
            Route::apiResource('orders', OrdersController::class)->only([
                'index','show','store','update','destroy',
            ]);
            // 取消订单
            Route::post('orders/{order}/cancel', [OrdersController::class, 'cancelOrder']);

            // 确认收货
            Route::post('orders/{order}/received', [OrdersController::class, 'received']);
            // 评论
            Route::post('orders/{order}/sendReview', [OrdersController::class, 'sendReview']);
            





        });

    });

    # 限制 一分钟只能调用10次
    Route::middleware('throttle:' . config('api.rate_limits.sign'))->group(function () {

    });


    // 图片验证码
    Route::post('captchas', [CaptchasController::class, 'store'])->name('captchas.store');
    # 发送邮箱验证码
    Route::post('verification-codes',[VerificationCodesController::class, 'store'])->name('verification-codes.store');
    # 使用邮箱验证码登录
    Route::post('login/email', [AuthController::class, 'loginWithEmailCode'])->name('login.email');
    Route::post('login/password', [AuthController::class, 'loginWithPassword'])->name('login.password');
    # 退出登录
    Route::delete('logout', [AuthController::class, 'logout'])->name('logout');


    Route::get('alipay', function() {
        return app('alipay')->web([
            'out_trade_no' => time(),
            'total_amount' => '1',
            'subject' => 'test subject - 测试',
        ]);
    });

    ## 支付接口
    Route::get('payment/{order}/alipay', [PaymentController::class, 'payByAlipay'])->name('payment.alipay');
    Route::get('payment/alipay/return',  [PaymentController::class, 'alipayReturn'])->name('payment.alipay.return');
    Route::post('payment/alipay/notify', [PaymentController::class, 'alipayNotify'])->name('payment.alipay.notify');
    # 支付宝支付验证
    Route::post('payment/alipay/verify', [PaymentController::class, 'verifyPayStatus'])->name('payment.alipay.verify');
    


});


