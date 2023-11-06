<?php

namespace Modules\Admin\Http\Models;
use Modules\Admin\Http\Models\Base;
class DictionaryValue extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','value','parent_id','code','status','sort','remark','english_name','updated_by','created_by'];
    public $ListSelect = ['id','name','value','status','english_name'];
    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model,$request){
        if(!empty($request->code)){
            $model = $model->where('code',$request->code);
        }
        if(!empty($request->search)){
            $model = $this->HandleSearch($model,$request->search);
        }
        return $model;
    }
}
