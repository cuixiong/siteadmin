<?php

namespace App\Observers;

use Modules\Admin\Http\Models\OperationLog as ModelsOperationLog;

class OperationLog
{
    /**
     * 处理「创建」事件。
     *
     * @return void
     */
    public function created($model)
    {
        var_dump($model->all());die;
        // 在保存 User 模型之前执行的逻辑
        ModelsOperationLog::AddLog('新增了'.get_class($model).'数据');
    }

    /**
     * 处理「更新」事件。
     *
     * @return void
     */
    public function updated($model)
    {
        // 在保存 User 模型之后执行的逻辑
    }

    /**
     * 处理「删除」事件。
     *
     * @return void
     */
    public function deleted($model)
    {
        // 在删除 Activity 模型之后执行的逻辑
    }
}
