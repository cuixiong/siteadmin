<?php

namespace Modules\Admin\Http\Models;

use Illuminate\Database\Eloquent\Model;
class UserModel extends Model
{
    // CRUD公共控制器表单验证方法
    public $FieldRule = [
        'destroy' => [
            'id'  => 'required'
        ]
    ];
    // CRUD公共控制器表单验证提示语
    public $FieldMessage = [
        'destroy' => [
            'id.required' => 'ID不能为空'
        ],// 删除
    ];
}