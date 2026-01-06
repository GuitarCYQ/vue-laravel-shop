<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Http\Requests\Api\AuthorizationRequest;


class AuthorizationsController extends Controller
{
    // 用户名密码登录
    public function store(AuthorizationRequest $request)
    {
        $username = $request->username;

        // 用户可以通过邮箱或者手机号进行登录
        filter_var($username, FILTER_VALIDATE_EMAIL) ?
        $credentials['email'] = $username :
        $credentials['phone'] = $username;

        $credentials['password'] = $request->password;
        $token = \Auth::guard('api')->attempt($credentials);
        if (!$token) {
            throw new AuthenticationException('用户名或密码错误');
        }

        // 返回token和过期时间expires_in 单位是秒
        return $this->respondWithToken($token)->setStatusCode(201);
    }




    // 第三方登录
    public function socialStore($type, Request $request)
    {
        $httpClient = new \GuzzleHttp\Client([
            'verify' => false, // 开发环境临时禁用 SSL 验证
            // 生产环境应配置为证书路径：'verify' => storage_path('certs/cacert.pem')
        ]);
        $driver = \Socialite::create('wechat',$httpClient);

        try {
            if ($code = $request->code) {
                $oauthUser = $driver->userFromCode($code);
            } else {
                // 微信需要增加 openid
                if ($type == 'wechat') {
                    $driver->withOpenid($request->openid);
                }

                $oauthUser = $driver->userFromToken($request->access_token);
            }
        } catch (\Exception $e) {
           throw new AuthenticationException('参数错误，未获取用户信息1',[$e]);
        }

        if (!$oauthUser->getId()) {
           throw new AuthenticationException('参数错误，未获取用户信息2');
        }

        switch ($type) {
            case 'wechat':
                $unionid = $oauthUser->getRaw()['unionid'] ?? null;

                if ($unionid) {
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                // 没有用户，默认创建一个用户
                if (!$user) {
                    $user = User::create([
                        'name' => $oauthUser->getNickname() ?: '微信用户_'.Str::random(8),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }

                break;
        }
        $token = auth('api')->login($user);
        return $this->respondWithToken($token)->setStatusCode(201);
    }



    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
