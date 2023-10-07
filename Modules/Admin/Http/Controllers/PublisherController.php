<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Http\Models\Publisher;

class PublisherController extends CrudController
{
    public function changeEnable(Request $request)
    {
        try {
            $params = $request->input();;
            if(!isset($params['id']) || !isset($params['status'])){
                ReturnJson(FALSE,'修改失败！');
            }

            $record = $this->ModelInstance()->findOrFail($params['id']);
            $input['status'] = $params['status'];
            if(!$record->update($input)){
                ReturnJson(FALSE,'更新失败');
            }
            ReturnJson(TRUE,'更新成功');

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), []);
        }
        ReturnJson(TRUE,'修改成功！');
    }

    public function getPublisher(Request $request)
    {
        $data = Publisher::select('id','name')->get()->toArray();

        ReturnJson(TRUE,'请求成功',$data);
    }
}
