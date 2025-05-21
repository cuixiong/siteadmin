<?php

namespace Modules\Site\Http\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User as Admin;

class OperationLog extends Base
{
    protected $appends = ['category_text','type_text'];

    public function __construct()
    {
        // // 获取当前租户信息
        // $currentTenant = tenancy()->tenant;
        // if($currentTenant){
        //     // 获取当前租户的标识
        //     $tenantId = $currentTenant->getTenantKey();
        //     // 如果当前处于租户则切换回中央数据库
        //     tenancy()->end();
        // }

        $this->SetTableName();
        if(Schema::hasTable($this->table) == false){
            $this->CreateTable();
        }

        // if($currentTenant){
        //     //再切换回租户
        //     tenancy()->initialize($tenantId);
        // }
    }

    public function getCategoryTextAttribute($value)
    {
        if(isset($this->attributes['category'])){
            $text = DictionaryValue::GetNameAsCode('Route_Classification',$this->attributes['category']);
            return $text;
        }

    }

    public function getTypeTextAttribute($value)
    {
        if(isset($this->attributes['type'])){
            $text = DictionaryValue::GetNameAsCode('OperationLog_Type',$this->attributes['type']);
            return $text;
        }
    }

    protected function SetTableName($year = '')
    {
        $year = $year ? $year : date('Y');
        $table = 'operation_log_'. $year;
        $this->table = $table;
        return $table;
    }

    private function CreateTable()
    {
        $res = DB::select("SHOW CREATE TABLE `operation_logs` ");
        $array = get_object_vars($res[0]);
        $createTableStatement = '';
        foreach ($array as $key => $value) {
            if($key == 'Create Table'){
                $createTableStatement = $value;
            }
        }
        $createTableStatement = str_replace('operation_logs', $this->table, $createTableStatement);
        DB::unprepared($createTableStatement);
    }


    /**
     * 处理查询列表条件数组
     * @param $model moxel
     * @param $search 搜索条件
     */
    public function HandleSearch($model,$search){
        if(!is_array($search)){
            $search = json_decode($search,true);
        }
        $search = array_filter($search,function($v){
            if(!(empty($v) && $v != "0")){
                return true;
            }
        });
        if(!empty($search)){
            $timeArray = ['created_at','updated_at'];
            foreach ($search as $key => $value) {
                if(in_array($key,['operate_id'])){
                    $model = $model->where('created_by',$value);
                } elseif(in_array($key,['name','english_name','title'])){
                    $model = $model->where($key,'like','%'.trim($value).'%');
                } else if (in_array($key,$timeArray)){
                    if(is_array($value)){
                        $model = $model->whereBetween($key,$value);
                    }
                } else if(is_array($value) && !in_array($key,$timeArray)){
                    $model = $model->whereIn($key,$value);
                } else if (in_array($key, ['created_by','updated_by']) && !empty($value)) {
                    $userIds = Admin::where('nickname', 'like', '%'.$value.'%')->pluck('id');
                    $userIds = $userIds ? $userIds : [];
                    $model = $model->whereIn($key, $userIds);
                } else {
                    $model = $model->where($key,$value);
                }
            }
        }
        return $model;
    }


}
