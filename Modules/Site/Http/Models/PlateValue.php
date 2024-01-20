<?php

namespace Modules\Site\Http\Models;
use Modules\Site\Http\Models\Base;
class PlateValue extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['parent_id','title', 'short_title', 'link', 'alias','image','icon','content','sort','status', 'updated_by', 'created_by'];

    // Icon修改器
    public function setIconAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['icon'] = $value;
        return $value;
    }
    // Icon获取器
    public function getIconAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }
    // image修改器
    public function setImageAttribute($value){
        $value = $value && is_array($value) ? implode(",",$value) : "";
        $this->attributes['image'] = $value;
        return $value;
    }
    // image获取器
    public function getImageAttribute($value){
        $value = $value ? explode(",",$value) : [];
        return $value;
    }

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model,$request){
        if(!empty($request->id)){
            $model = $model->where('parent_id',$request->id);
        }
        if(!empty($request->search)){
            $model = $this->HandleSearch($model,$request->search);
        }
        return $model;
    }
}