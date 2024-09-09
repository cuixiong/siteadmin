<?php

namespace App\Observers;


use Modules\Site\Http\Controllers\OperationLogController;

class SiteOperationLog
{
    /**
     * 处理「创建」事件。
     *
     * @return void
     */
    public function created($model)
    {
        $className = class_basename($model);
        if($className != 'OperationLog'){
            OperationLogController::AddLog($model,'insert');
        }
    }

    /**
     * 处理「更新」事件。
     *
     * @return void
     */
    public function updated($model)
    {
        $className = class_basename($model);
        if($className != 'OperationLog'){
            OperationLogController::AddLog($model,'update');
        }
    }

    /**
     * 处理「删除」事件。
     *
     * @return void
     */
    public function deleting($model)
    {
        $className = class_basename($model);
        if($className != 'OperationLog'){
            OperationLogController::AddLog($model,'delete');
        }
    }
}
