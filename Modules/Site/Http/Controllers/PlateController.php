<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\Menu;
use Modules\Site\Http\Models\PlateValue;

class PlateController extends CrudController
{
    public function options(Request $request){
        $options = [];
        $codes = ['Switch_State'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['pages'] = (new Menu())->GetListLabel(['id as value','name as label'],false,'',['status' => 1]);
        ReturnJson(TRUE,'', $options);
    }
    // 获取子级列表
    public function children(Request $request)
    {
        if(empty($request->id)){
            ReturnJson(FALSE,'父级ID为空');
        }
        $res = PlateValue::where('parent_id',$request->id)->where('status',1)->get()->toArray();
        ReturnJson(TRUE,'', $res);
    }

    /**
     * 修改状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeStatus(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->status = $request->status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }

            $plateValueModel = new plateValue();
            $plateValueModel->where("parent_id" , $request->id)->update(["status" => $request->status]);

            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


}
