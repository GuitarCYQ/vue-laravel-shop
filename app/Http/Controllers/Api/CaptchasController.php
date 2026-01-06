<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Gregwar\Captcha\CaptchaBuilder;
use App\Http\Requests\Api\CaptchaRequest;

class CaptchasController extends Controller
{
    public function store(CaptchaRequest $request, CaptchaBuilder $captchaBuilder)
    {
        $key = Str::random(15);
        $cacheKey = 'captcha_'.$key;
        $email = $request->email;

        $captcha = $captchaBuilder->build();
        $expiredAt = now()->addMinutes(2);
        //getPhrase() 获取验证码文本
        \Cache::put($cacheKey, ['email' => $email, 'code' => $captcha->getPhrase()], $expiredAt);

        // inline 获取的base64 图片验证码
        $result = [
            'captcha_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            'captcha_image_content' => $captcha->inline()
        ];

        return response()->json($result)->setStatusCode(201);
    }
}
