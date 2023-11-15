<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
use Illuminate\Database\Eloquent\Model;
class OperationLog extends Model
{
    protected $fillable = [];

    public static function AddLog($content)
    {
        $model = new self();
        $model->content = $content;
        $model->save();
        return true;
    }
}