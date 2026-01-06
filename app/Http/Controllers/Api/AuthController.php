<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\ReAuthdis;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\AuthenticationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\Api\SocialAuthorizationRequest;



// 邮箱验证码登录接口
class AuthController extends Controller
{
    // 验证码邮箱登录
    public function loginWithEmailCode(Request $request)
    {
        // 验证请求参数
        $request->validate([
            'key'   =>  'required',
            'email' => 'required|email',
            'code'  =>  'required|digits:6',// 必须是6位数字
        ]);
        $key = $request->key;
        $email = $request->email;
        $code = $request->code;

        // 从redis 获取存储的验证码
        $storedCode = \Cache::get($key);

        // 验证验证码
        if(!$storedCode || !hash_equals($storedCode , $code)) {
            throw ValidationException::withMessages([
                'code' => '验证码错误或已过期'
            ]);
        }

        // 验证码正确，删除Redis 中拿到验证码（防止重复使用）
        \Cache::forget($key);

        // 查询用户，不存在则自动注册
        $user = User::where('email',$email)->first();
        if (!$user) {
            $user = User::create(
                [
                    'email' => $email,
                    'name' => '用户_'.substr($email, 0, strpos($email, '@')), // 自动生成用户名
                    'password' => Hash::make(Str::random(16))
                ]
            );
        }

        // 使用Sanctum生成Token （使用laravel  Sanctum） plainTextToken 是让用户只有第一次才可以看到真正的token，之后是加密过的
        // $token = $user->createToken(
        //     'email_login_token',
        //     ['*'], // 权限
        //     now()->addDays(1)
        // )->plainTextToken;

        // 使用JWT生成Token
        $token = auth('api')->login($user);

        // 返回用户信息，仅仅返回以下几个字段
        $userArray = $user->only(['name','email','phone','avatar']);

        // 返回用户信息和Token
        return $this->respondWithToken($token,$userArray)->setStatusCode(201);
    }

    // 账号密码登录
    public function loginWithPassword(Request $request){
        // 验证请求参数
        $request->validate([
            'username'   =>  'required',
            'password'   =>  'required',
        ]);
        $username = $request->username;
        filter_var($username, FILTER_VALIDATE_EMAIL) ? $credentials['email'] = $username : $credentials['phone'] = $username;
        $credentials['password'] = $request->password;

        $token = \Auth::guard('api')->attempt($credentials);
        $userArray = User::where('email',$username)->orWhere('phone',$username)->first();
        $userArray=$userArray->only(['name','email','phone','avatar']);
        if (!$token) {
            throw new AuthenticationException('用户名或密码错误');
        }

        // 返回token和过期时间expires_in 单位是秒
        return $this->respondWithToken($token,$userArray)->setStatusCode(201);
    }




    # 获取第三方授权URL
    public function getAuthorizationUrl()
    {
        $state = bin2hex(random_bytes(16));
        session(['social_state' => $state]);

        $url = \Socialite::create('wechat')
            ->scopes(['snsapi_userinfo'])
            ->with(['state' => $state])
            ->redirect();

        return response()->json([
            'authorization_url' => $url,
            'state' => $state // 返回state供前端存储
        ]);
    }

    // 第三方登录
     public function socialStore($type, SocialAuthorizationRequest $request)
     {
        // 验证 state 参数（关键：防止 CSRF 攻击）
        $requestState = $request->state;
        $sessionState = session('social_state');
        if(!$requestState || !hash_equals($requestState , $sessionState)) {
            throw new AuthenticationException('无效的请求状态');
        }

        // 验证通过后清除session的state
        session()->forget('social_state');

        $driver = \Socialite::create($type);
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
            throw new AuthenticationException('参数错误，未获取用户信息');
         }

         if (!$oauthUser->getId()) {
            throw new AuthenticationException('参数错误，未获取用户信息');
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

    // 刷新token
    public function update()
    {
        $token = auth('api')->refresh();
        // 返回用户信息，仅仅返回以下几个字段
        return $this->respondWithToken($token);
    }

    // 退出登录
    public function logout(Request $request)
    {
        auth('api')->logout();
        return response(null,204);
    }


    // 返回格式
    protected function respondWithToken($token,$user=null){
        return response()->json([
            'status' => 201,
            'message' => '登录成功',
            'data' => [
                'token' => $token,
                'user'  => $user,
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ]
        ]);
    }
}
