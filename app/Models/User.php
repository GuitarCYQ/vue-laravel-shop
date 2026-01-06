<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Support\Facades\Auth;
// use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Encore\Admin\Traits\DefaultDatetimeFormat;

class User extends Authenticatable implements MustVerifyEmail,JWTSubject
{
    use Traits\LastActivedAtHelper;
    use Traits\ActiveUserHelper;
    // use HasRoles;
    use HasApiTokens, HasFactory, MustVerifyEmailTrait;
    use DefaultDatetimeFormat;

    use Notifiable {
        notify as protected laravelNotify;
    }
    public function notify($instance)
    {
        // 如果要通知的人是当前用户，就不必通知了！
        if ($this->id == Auth::id()) {
            return;
        }

        // 只有数据库类型通知才需提醒，直接发送 Email 或者其他的都 Pass
        if (method_exists($instance, 'toDatabase')) {
            $this->increment('notification_count');
        }

        $this->laravelNotify($instance);
    }

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'introduction',
        'avatar',
        'weixin_openid',
        'weixin_unionid'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'weixin_openid',
        'weixin_unionid'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function isAuthorOf($model)
    {
        return $this->id == $model->user_id;
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();
        $this->unreadNotifications->markAsRead();
    }

    public function setPasswordAttribute($value)
    {
        // 如果值的长度等于 60，即认为是已经做过加密的情况
        if (strlen($value) != 60) {

            // 不等于 60，做密码加密处理
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    public function setAvatarAttribute($path)
    {
        // 如果不是 `http` 子串开头，那就是从后台上传的，需要补全 URL
        if ( ! Str::startsWith($path, 'http')) {

            // 拼接完整的 URL
            $path = config('app.url') . "/uploads/images/avatars/$path";
        }

        $this->attributes['avatar'] = $path;
    }

    # JWT 令牌认证
    # 返回User的id
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    # 在JWT令牌里的额外自定义内容
    public function getJWTCustomClaims()
    {
        return [];
    }

    # 关联用户地址
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    # 关联购物车
    public function cartItems() {
        return $this->hasMany(CartItem::class);
    }
}
