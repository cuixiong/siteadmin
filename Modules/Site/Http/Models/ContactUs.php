<?php

namespace Modules\Site\Http\Models;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Models\Base;

class ContactUs extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = [
        'category_id',
        'product_id',
        'name',
        'email',
        'product_name',
        'country_id',
        'province_id',
        'city_id',
        'phone',
        'company',
        'department',
        'address',
        'channel',
        'status',
        'buy_time',
        'content',
        'price_edition',
        'language_version',
        'sort',
        'updated_by',
        'created_by',
    ];
    protected $appends = ['message_name', 'category_name', 'category_style', 'channel_name', 'buy_time_name'];

    // 产品名称获取器
    public function getProductNameAttribute()
    {
        if (isset($this->attributes['product_id']) && !empty($this->attributes['product_id'])) {
            $value = Products::where('id', $this->attributes['product_id'])->value('name');
        } else {
            if (isset($this->attributes['product_name'])) {
                $value = $this->attributes['product_name'];
            } else {
                $value = "";
            }
        }
        return $value;
    }

    // 类型名称获取器
    public function getMessageNameAttribute()
    {
        if (isset($this->attributes['message_id'])) {
            $value = MessageCategory::where('id', $this->attributes['message_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 分类名称获取器
    public function getCategoryNameAttribute()
    {
        if (isset($this->attributes['category_id'])) {
            $value = MessageCategory::where('id', $this->attributes['category_id'])->value('name');
        } else {
            $value = "";
        }
        return $value;
    }

    // 分类样式获取器
    public function getCategoryStyleAttribute()
    {
        if (isset($this->attributes['category_id'])) {
            $value = MessageCategory::where('id', $this->attributes['category_id'])->value('style');
        } else {
            $value = "";
        }
        return !empty($value) ? $value : '';
    }

    // 来源名称获取器
    public function getChannelNameAttribute()
    {
        if (isset($this->attributes['channel'])) {
            $value = DictionaryValue::GetNameAsCode('Channel_Type', $this->attributes['channel']);
        } else {
            $value = "";
        }
        return $value;
    }

    // 购买时间获取器
    public function getBuyTimeNameAttribute()
    {
        if (isset($this->attributes['buy_time'])) {
            $value = $this->attributes['buy_time'] . '天内';
            return $value;
        }
        return '';
    }



    /**
     * 处理查询列表条件数组
     * @param $model moxel
     * @param $search 搜索条件
     */
    public function HandleSearch($model, $search)
    {
        if (!is_array($search)) {
            $search = json_decode($search, true);
        }
        if (!empty($search)) {
            $search = array_filter($search, function ($v) {
                if (!(empty($v) && $v != "0")) {
                    return true;
                }
            });
        }
        $isJoinProductTable = false;
        if (!empty($search)) {
            $timeArray = ['created_at', 'updated_at'];
            foreach ($search as $key => $value) {
                if (in_array($key, ['title', 'phone', 'email', 'company', 'department', 'content', 'address'])) {
                    $model = $model->where('cu.' . $key, 'like', '%' . trim($value) . '%');
                } else if (in_array($key, $timeArray)) {
                    if (is_array($value)) {
                        $model = $model->whereBetween('cu.' . $key, $value);
                    }
                } else if (in_array($key, ['is_bidding', 'accepter'])) {
                    if(!$isJoinProductTable){
                        $model->leftJoin((new Products())->getTable() . ' as p', 'cu.product_id', '=', 'p.id');
                        $isJoinProductTable = true;
                    }
                    if ($key == 'is_bidding' && $value == 1) {
                        $model->whereExists(function ($query) {
                            $query->select(DB::raw(1))
                                ->from('post_subject as ps')
                                ->join('post_subject_link as psl', 'ps.id', '=', 'psl.post_subject_id')
                                ->whereColumn('ps.keywords', 'p.keywords')
                                ->where('psl.created_at', '<', DB::raw('cu.created_at'));
                        });
                    } elseif ($key == 'is_bidding' && $value === 0) {
                        $model->where(function ($query) {
                            $query->whereNull('cu.product_id')
                                ->orWhere('cu.product_id', 0)
                                ->orWhere(function ($query) {
                                    $query->whereNotNull('cu.product_id')
                                        ->whereNotExists(function ($subQuery) {
                                            $subQuery->select(DB::raw(1))
                                                ->from('post_subject as ps')
                                                ->join('post_subject_link as psl', 'ps.id', '=', 'psl.post_subject_id')
                                                ->whereRaw('ps.keywords = p.keywords')
                                                ->where('psl.created_at', '<', DB::raw('cu.created_at'));
                                        });
                                });
                        });
                    } else if ($key == 'accepter' && !empty($value)) {
                        $model->whereExists(function ($query) use ($value) {
                            $query->select(DB::raw(1))
                                ->from('post_subject as ps')
                                ->join('post_subject_link as psl', 'ps.id', '=', 'psl.post_subject_id')
                                ->whereColumn('ps.keywords', 'p.keywords')
                                ->where('psl.created_at', '<', DB::raw('cu.created_at'))
                                ->where('ps.accepter', $value);
                        });
                    }
                } else if (in_array($key, ['created_by', 'updated_by']) && !empty($value)) {
                    $userIds = User::where('nickname', 'like', '%' . $value . '%')->pluck('id');
                    $userIds = $userIds ? $userIds : [];
                    $model = $model->whereIn('cu.' . $key, $userIds);
                } else if (is_array($value) && !in_array($key, $timeArray)) {
                    $model = $model->whereIn('cu.' . $key, $value);
                } else {
                    $model = $model->where('cu.' . $key, $value);
                }
            }
        }
        return $model;
    }
}
