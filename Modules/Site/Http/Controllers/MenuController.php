<?php
namespace Modules\Site\Http\Controllers;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;

class MenuController extends CrudController{
    /**
     * 字典数据
     */
    public function options(Request $request){
        $options = [];
        $codes = ['Switch_State','Navigation_Menu_Type','Is_Single_Page'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code',$codes)->where('status',1)->select('code','value',$NameField)->orderBy('sort','asc')->get()->toArray();
        if(!empty($data)){
            foreach ($data as $map){
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['menus'] = $this->ModelInstance()->GetListLabel(['id as value', 'name as label'],false,'',['status' => 1]);
        ReturnJson(TRUE,'', $options);
    }

    /**
     * 修改是否单页
     */
    public function isSingle(Request $request){
        if(empty($request->id)){
            ReturnJson(FALSE,'', '参数错误');
        }
        $menu = $this->ModelInstance()->find($request->id);
        $menu->is_single = $request->is_single;
        $menu->save();
        ReturnJson(TRUE,'', trans('lang.request_success'));
    }
}