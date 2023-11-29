<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Http\Controllers\CrudController as AdminCrudController;

class CrudController extends AdminCrudController
{
    protected $model; // 模型类名:若没有指定模型，则根据控制器名找到对应的模型
    protected $action; // 请求方法名称
    protected $validate; // 请求方法名称
    public function __construct()
    {
        // 模型类名:若没有指定模型，则根据控制器名找到对应的模型
        if (empty($this->model)) {
            $Controller = (new \ReflectionClass($this))->getShortName(); // 控制器名
            $name = str_replace('Controller', '', $Controller);
            $model = 'Modules\Site\Http\Models\\' . $name; // Model(模型)
            $validate = 'Modules\Site\Http\Requests\\' . $name . 'Request'; // Validate(数据验证)
            $this->model = $model;
            $this->validate = $validate;
        }
    }
}
