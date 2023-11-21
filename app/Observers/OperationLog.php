<?php

namespace App\Observers;

use Modules\Admin\Http\Controllers\OperationLogController;

class OperationLog
{

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
}
