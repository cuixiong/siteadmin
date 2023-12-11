<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Site;

class TimedTaskController extends CrudController
{
    public function store(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                ReturnJson(FALSE, trans('lang.add_error'));
            }
            // 根据类型进行生成liunx命令
            if($input['type'] == 'shell'){
                $command = $input['content'];
            } else if($input['type'] == 'http'){
                $command = 'curl '.$input['content'];
            } else {
                ReturnJson(FALSE, trans('lang.add_error'));
            }

            // 根据定时任务来决定是否需要远程SHH进行创建定时任务

            // 定义日志文件路径

            // 执行命令
            ReturnJson(TRUE, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
}
