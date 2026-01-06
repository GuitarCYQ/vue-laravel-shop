<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Overtrue\EasySms\EasySms;
use App\Http\Requests\Api\VerificationCodeRequest;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendVerificationCode;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        /*$captchaCacheKey =  'captcha_'.$request->captcha_key;
        $captchaData = \Cache::get($captchaCacheKey);

        if (!$captchaData) {
            abort(403, '图片验证码已失效');
        }

        if (!hash_equals($captchaData['code'], $request->captcha_code)) {
            // 验证错误就清除缓存
            \Cache::forget($captchaCacheKey);
            throw new AuthenticationException('验证码错误');
        }

        $phone = $captchaData['phone'];

        if (!app()->environment('production')) {
            $code = '1234';
        } else {
            // 生成4位随机数，左侧补0
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);

            try {
                $result = $easySms->send($phone, [
                    'template' => config('easysms.gateways.aliyun.templates.register'),
                    'data' => [
                        'code' => $code
                    ],
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('aliyun')->getMessage();
                abort(500, $message ?: '短信发送异常');
            }
        }

        $smsKey = 'verificationCode_'.Str::random(15);
        $smsCacheKey = 'verificationCode_'.$smsKey;
        $expiredAt = now()->addMinutes(5);
        // 缓存验证码 5分钟过期。
        \Cache::put($smsCacheKey, ['phone' => $phone, 'code' => $code], $expiredAt);
        // 清除图片验证码缓存
        \Cache::forget($captchaCacheKey);

        return response()->json([
            'key' => $smsKey,
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(201);*/

        $captchaCacheKey = 'captcha_'.$request->captcha_key;
        $captchaData = \Cache::get($captchaCacheKey);

        if (!$captchaData) {
            abort(403, '图片验证码已失效');
        }

        // \Log::info('图片验证码',[
        //     '存储的验证'=>$captchaData['code'],
        //     '提交的验证'=>$request->captcha_code
        // ]);

        // 判断验证码 验证码正确就继续执行，不正确就结束,strtolower验证码转换成小写进行对比
        if (!hash_equals(strtolower($captchaData['code']), strtolower($request->captcha_code))) {
            // 验证码错误就删除缓存
            \Cache::forget($captchaCacheKey);
            // throw new AuthenticationException('验证码错误');
             // 返回带错误信息的 401 响应
            return response()->json([
                'message' => '图片验证码错误，请重新输入'
            ], 401);
        }


        $email = $request->email;
        if(!app()->environment('production')) {
            $code = '123456';
        } else {
            // 生成 6位数字验证码
            $code = rand(100000, 999999);

            // 发送验证码到邮箱
            try {
                // send 同步 queue 异步
                Mail::to($email)->queue(new SendVerificationCode($code));
            } catch (\Exception $e) {
                about(500, '邮件发送失败，请检查邮箱地址或稍后重试');
            }
        }

        $redisKey = 'email_code_'.Str::random(15).':'.$email;
        $expiredAt = now()->addMinutes(5);

        // 存储验证码到redis里，5分钟过期（键名格式：email_code:邮箱）
        // Redis::setex($redisKey, 300, $code);
        \Cache::put($redisKey, $code, $expiredAt);
        // 清除图片验证码缓存
        \Cache::forget($captchaCacheKey);

        // \Log::info('发送验证码：', [
        //     'key' => $redisKey,
        //     'code' => $code,
        //     'email' => $email
        // ]);

        return response()->json([
            'key' => $redisKey,
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(201);

    }
}
