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
            $dirty = $model->getDirty();
            $contents = [];
            foreach ($dirty as $field => $value) {
                if(!in_array($field,['created_by','updated_by','created_at','updated_at'])){
                    $contents[] = $field.'字段从“'.$model->getOriginal($field).'”修改为“'. $value.'”';
                }
            }
            $contents = implode('、',$contents);
            OperationLogController::AddLog($className,'update',$contents);
        }
    }
}
