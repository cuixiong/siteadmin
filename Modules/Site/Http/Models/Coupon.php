<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Site\Http\Models\User;
use Modules\Admin\Http\Models\DictionaryValue;

class Coupon extends Base
{

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['time_begin_format', 'time_end_format', 'is_effect', 'usernames'];

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'code',         // 优惠券编号
        'type',         // 优惠券类型
        'value',        // 优惠券值
        'user_ids',     // 分配给具体的用户id
        'time_begin',   // 有效起始时间
        'time_end',     // 有效结束时间
        'sort',         // 排序
        'status',       // 状态
        'updated_by',   // 修改者
        'created_by',   // 创建者
    ];

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request)
    {

        $search = json_decode($request->input('search'));
        //id 
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }

        // code
        if (isset($search->code) && !empty($search->code)) {
            $model = $model->where('code', 'like', '%' . $search->code . '%');
        }

        // is_pay 
        if (isset($search->type) && $search->type != '') {
            $model = $model->where('type', $search->type);
        }
        // value 
        if (isset($search->value) && $search->value != '') {
            $model = $model->where('value', $search->value);
        }

        // user_ids 
        if (isset($search->user_ids) && $search->user_ids != '') {
            $model = $model->where('user_ids', $search->user_ids);
        }

        // sort 
        if (isset($search->sort) && $search->sort != '') {
            $model = $model->where('sort', $search->sort);
        }

        // status 
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }

        // 时间为数组形式
        // 创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }

        // 更新时间
        if (isset($search->updated_at) && !empty($search->updated_at)) {
            $updateTime = $search->updated_at;
            $model = $model->where('updated_at', '>=', $updateTime[0]);
            $model = $model->where('updated_at', '<=', $updateTime[1]);
        }

        // 生效起始时间
        if (isset($search->time_begin) && !empty($search->time_begin)) {
            $effectBeginTime = $search->time_begin;
            $model = $model->where('time_begin', '>=', $effectBeginTime[0]);
            $model = $model->where('time_begin', '<=', $effectBeginTime[1]);
        }

        // 生效结束时间
        if (isset($search->time_end) && !empty($search->time_end)) {
            $effectEndTime = $search->time_end;
            $model = $model->where('time_end', '>=', $effectEndTime[0]);
            $model = $model->where('time_end', '<=', $effectEndTime[1]);
        }

        //是否生效
        if (isset($search->is_effect)) {
            if ($search->is_effect === 0 || $search->is_effect === '0') {
                $model->where(function ($query) {
                    $query->where('time_begin', '<', time())
                        ->orWhere('time_end', '>', time());
                });
            } else {
                $model = $model->where('time_begin', '>=', time());
                $model = $model->where('time_end', '<=', time());
            }
        }

        return $model;
    }

    /**
     * 生效起始时间格式化
     */
    public function getTimeBeginFormatAttribute()
    {
        $text = '';
        if (isset($this->attributes['time_begin'])) {
            $text = date('Y-m-d H:i:s', $this->attributes['time_begin']);
        }
        return $text;
    }

    /**
     * 生效截至时间格式化
     */
    public function getTimeEndFormatAttribute()
    {
        $text = '';
        if (isset($this->attributes['time_end'])) {
            $text = date('Y-m-d H:i:s', $this->attributes['time_end']);
        }
        return $text;
    }

    /**
     * 是否生效判断
     */
    public function getIsEffectAttribute()
    {
        $text = '';

        // 获取请求对象
        $request = request();

        if ($request->HeaderLanguage == 'en') {
            $field = 'english_name';
        } else {
            $field = 'name';
        }
        if (
            isset($this->attributes['time_begin']) && isset($this->attributes['time_end'])
            && !empty($this->attributes['time_begin']) && !empty($this->attributes['time_end'])
            && time() >= $this->attributes['time_begin'] && time() <= $this->attributes['time_end']
        ) {
            $text = DictionaryValue::where('code', 'Coupon_State')->where('value', 1)->value($field) ?? true;
        } else {
            $text = DictionaryValue::where('code', 'Coupon_State')->where('value', 0)->value($field) ?? false;
        }
        return $text;
    }

    /**
     * 用户名获取器
     */
    public function getUsernamesAttribute()
    {
        $text = '';
        if (isset($this->attributes['user_ids'])) {
            $userIdsArray = explode(',', $this->attributes['user_ids']);
            $nameArray = User::query()->whereIn('id', $userIdsArray)->pluck('name')->toArray();
            $text = ($nameArray && count($nameArray) > 0) ? implode(',', $nameArray) : '';
        }
        return $text;
    }
}
