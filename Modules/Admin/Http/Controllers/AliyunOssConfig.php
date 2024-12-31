<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Site;

class AliyunOssConfig extends CrudController
{
    public function update(Request $request)
    {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $count = $this->ModelInstance()->where('site_id', $request->site_id)->where('id','<>',$request->id)->count();
            if($count > 0){
                ReturnJson(FALSE, trans('lang.aliyun_oss_config_exist'));
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }
    public function option(Request $request)
    {
        $options = [];
        $codes = ['Switch_State'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        $options['Site'] = (new Site)->GetListLabel(['id as value',$NameField],false,'',['status' => 1]);
        ReturnJson(TRUE,'', $options);
    }

    
    public function formByName(Request $request) {
        try {
            $this->ValidateInstance($request);
            $record = $this->ModelInstance()->where('name',$request->name)->first()->toArray();
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
