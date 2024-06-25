<?php
namespace Modules\Site\Http\Controllers;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Menu;

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


    protected function list(Request $request) {
        try {
            $this->ValidateInstance($request);
            $model = Menu::query();
            $model->where("parent_id" , 0);
            $model->where("status" , 1);
            if(!empty($request->keywords)){
                $model = $model->where('name','like','%'.$request->keywords.'%');
            }
            if(!empty($request->search)){
                $model = (new Menu())->HandleSearch($model,$request->search);
            }

            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $list = $model->get()->toArray();

            foreach ($list as $key => $value) {
                $childList = [];
                $this->getChildList($value, $childList);
                $list[$key]['children'] = $childList;
            }

            $data = [
                'total' => $total,
                'list'  => $list
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }


    public function getChildList($menuInfo, &$childMenuList) {
        if (empty($menuInfo)) {
            return false;
        }
        $childList = Menu::query()->where("parent_id", $menuInfo['id'])
                                     ->where("status", 1)
                                     ->get()->toArray();
        foreach ($childList as $value) {
            $forChildList = [];
            $this->getChildList($value, $forChildList);
            $value['children'] = $forChildList;
            $childMenuList[] = $value;
        }

        return true;
    }


}
