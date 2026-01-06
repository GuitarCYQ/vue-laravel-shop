<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Api\UserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Auth\AuthenticationException;
use App\Models\Image;

class UsersController extends Controller
{
    // 创建用户
    public function store(UserRequest $request)
    {
        $cacheKey = 'verificationCode_'.$request->verification_key;
        $verifyData = \Cache::get($cacheKey);

        if (!$verifyData) {
            abort(403, '验证码已失效');
        }

        if (!hash_equals($verifyData['code'], $request->verification_code)) {
            // 返回 401
            throw new AuthenticationException('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => $request->password,
        ]);

        // 清除验证码缓存
        \Cache::forget($cacheKey);

        return (new UserResource($user))->showSensitiveFields();
    }

    public function show(User $user, Request $request)
    {
        return new UserResource($user);
    }

    public function me(Request $request)
    {
        return (new UserResource($request->user()))->showSensitiveFields();
    }


    public function update(UserRequest $request)
    {
        $user = $request->user();

        // 只接受三个字段
        $attributes = $request->only(['name', 'introduction']);

        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }

        $user->update($attributes);

        return (new UserResource($user))->showSensitiveFields();
    }


}
