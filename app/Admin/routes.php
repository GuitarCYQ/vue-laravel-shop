<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('users', 'UsersController@index');

    $router->get('categories', 'ProductCategoryController@index');
    $router->get('categories/create', 'ProductCategoryController@create');
    $router->post('categories', 'ProductCategoryController@store');
    $router->get('categories/{id}/edit', 'ProductCategoryController@edit');
    $router->put('categories/{id}', 'ProductCategoryController@update');

    $router->get('products', 'ProductsController@index');
    $router->get('products/create', 'ProductsController@create');
    $router->post('products', 'ProductsController@store');
    $router->get('products/{id}/edit', 'ProductsController@edit');
    $router->put('products/{id}', 'ProductsController@update');

    // 订单
    $router->get('orders', 'OrdersController@index')->name('orders.index');
    // 订单详情
    $router->get('orders/{order}', 'OrdersController@show')->name('orders.show');
    // 订单发货
    $router->post('orders/{order}/ship', 'OrdersController@ship')->name('orders.ship');

    // 优惠券码
    $router->get('coupon_codes', 'CouponCodesController@index');
});
