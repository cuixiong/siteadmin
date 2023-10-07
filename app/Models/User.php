<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Casts\Attribute;
class User extends Authenticatable implements JWTSubject
{
    // 把自动维护的时间字段修改为时间戳格式保存
    protected $dateFormat = 'U';
    /** 隐藏不需要输出的字段 */
    protected $hidden = ["password"];
    /**
     * 创建时间获取器
     * @param \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function createdAt(): Attribute
    {

        return Attribute::make(
            get: fn ($value) => date('Y-m-d H:i:s',strtotime($value)),
        );
    }
    /**
     * 更新时间获取器
     * @param \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('Y-m-d H:i:s',strtotime($value)),
        );
    }
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}