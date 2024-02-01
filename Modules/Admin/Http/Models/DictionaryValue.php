<?php

namespace Modules\Admin\Http\Models;

use Illuminate\Support\Facades\Redis;
use Modules\Admin\Http\Models\Base;
class DictionaryValue extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['name','value','parent_id','code','status','sort','remark','english_name','updated_by','created_by'];
    public $ListSelect = ['*'];

     /**
     * Register the model events for updating.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // 在创建成功后触发
        static::created(function ($model) {
            self::RedisSet($model);
        });

        // 在更新成功后触发
        static::updating(function ($model) {
            self::RedisSet($model);
        });

        // 在删除成功后触发
        static::deleted(function ($mode) {
            self::RedisDelete($mode->code,$mode->id);
        });
    }
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

    /**
     * get name by code and value
     * @param $code code
     * @param $value value
     * @return mixed
     */
    public static function GetNameAsCode($code,$value){
        $name = DictionaryValue::where('code',$code)->where('value',$value)->value('name');
        return $name;
    }

    public static function GetOption($code){
        $request = request();
        $filed = $request->HeaderLanguage == 'en'? ['english_name as label','value'] : ['name as label','value'];
        $option = self::where('code',$code)->where('status',1)->select($filed)->get();
        return $option;
    }

    // redis 创建/更新hash
    private static function RedisSet($model)
    {
        $model = $model->toArray();
        $dictionary = json_encode($model);
        $redis = new Redis();
        $res = $redis::hget('dictionary_'.$model['code'],$model['id']);
        // 先删除缓存再更新缓存
        if($res){
            $redis::hdel(
                'dictionary_'.$model['code'], 
                $model['id'], $dictionary,
            );
        }
        $redis::hset(
            'dictionary_'.$model['code'], 
            $model['id'], $dictionary,
        );
    }

    // redis 删除hash
    private static function RedisDelete($code,$value)
    {
        Redis::hdel(
            'dictionary_'. $code, 
            $value,
        );
    }
}
