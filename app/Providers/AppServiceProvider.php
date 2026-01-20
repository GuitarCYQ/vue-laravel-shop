<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // if (app()->isLocal()) {
        //     $this->app->register(\VIACreative\SudoSu\ServiceProvider::class);
        // }

        $config = config('pay');
        //判断当前项目运行环境是否为线上环境
        if (app()->environment() !== 'production') {
            $config['alipay']['default']['mode'] = $config['wechat']['default']['mode'] = Pay::MODE_SANDBOX;
            $config['logger']['level'] = 'debug';
        } else {
            $config['logger']['level'] = 'info';
        }

        // 往服务容器中注入一个名为 alipay 的单例对象
        $this->app->singleton('alipay', function() use ($config) {
            //调用Yansongda\Pay来创建一个支付宝支付对象
            $config['alipay']['default']['notify_url']   = ngrok_url('api.v1.payment.alipay.notify');
            $config['alipay']['default']['return_url']   = 'http://localhost:5173/payment/alipay/return';
            // 禁用SSL证书验证
            $config['alipay']['default']['ssl_verify'] = false;
            $config['alipay']['default']['gateway'] = 'https://openapi-sandbox.dl.alipaydev.com/gateway.do'; // 手动指定沙盒网关
            return Pay::alipay($config);
        });

        $this->app->singleton('wechat_pay', function() use ($config) {
            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
	{
		 \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Reply::observe(\App\Observers\ReplyObserver::class);
        \App\Models\Topic::observe(\App\Observers\TopicObserver::class);
        \App\Models\Link::observe(\App\Observers\LinkObserver::class);

        \Illuminate\Pagination\Paginator::useBootstrap();
        JsonResource::withoutWrapping();
    }
}
