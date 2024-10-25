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
            //$model->where("status" , 1);
            if(!empty($request->keywords)){
                $model = $model->where('name','like','%'.$request->keywords.'%');
            }
            if(!empty($request->search)){
                $model = (new Menu())->HandleSearch($model,$request->search);
            }else{
                $model->where("parent_id" , 0);
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
                                     //->where("status", 1)
                                     ->get()->toArray();
        foreach ($childList as $value) {
            $forChildList = [];
            $this->getChildList($value, $forChildList);
            $value['children'] = $forChildList;
            $childMenuList[] = $value;
        }

        return true;
    }



    /**
     * 批量修改下拉参数
     *
     * @param $request 请求信息
     */
    public function batchUpdateParam(Request $request) {
        $field = [
            [
                'name'  => '状态',
                'value' => 'status',
                'type'  => '2',
            ],
        ];
        array_unshift($field, ['name' => '请选择', 'value' => '', 'type' => '']);
        ReturnJson(true, trans('lang.request_success'), $field);
    }

    /**
     * 批量修改下拉参数子项
     *
     * @param $request 请求信息
     */
    public function batchUpdateOption(Request $request) {
        $input = $request->all();
        $keyword = $input['keyword'];
        $data = [];
        if ($keyword == 'status') {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Show_Home_State', 'status' => 1], ['sort' => 'ASC']
            );
        } elseif ($keyword == 'country_id') {
            $data = (new Region())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1],
                                                 ['sort' => 'ASC']);
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 批量修改
     *
     * @param $request 请求信息
     */
    public function batchUpdate(Request $request) {
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $keyword = $input['keyword'] ?? '';
        $value = $input['value'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作
        $ModelInstance = $this->ModelInstance();
        $model = $ModelInstance->query();
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty').':ids');
            }
            $model = $ModelInstance->whereIn('id', $ids);
        } else {
            //筛选
            $model = $ModelInstance->HandleWhere($model, $request);
        }
        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            // $data['result_count'] = $model->update([$keyword => $value]);
            // 批量操作无法触发添加日志的功能，但我领导要求有日志
            $newIds = $model->pluck('id');
            foreach ($newIds as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $record->$keyword = $value;
                    $record->save();
                }
            }
            ReturnJson(true, trans('lang.update_success'));
        }
    }


}
