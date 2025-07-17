<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\DictionaryValue;

class Invoice extends Base
{

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['username', 'order_number', 'invoice_type_text'];

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'company_name',         // 公司名称
        'company_address',      // 公司地址
        'tax_code',             // 纳税人识别码
        'invoice_type',         // 发票类型:1代表普通发票,2代表专用发票。
        'price',                // 发票金额
        'user_id',              // 用户编号
        'order_id',             // 订单编号
        'title',                // 发票抬头
        'contact_person',       // 联系人
        'contact_detail',       // 内容
        'status',               // 状态
        'apply_status',         // 开票状态;0：未开票，1：已开票
        'phone',                // 注册电话
        'bank_name',            // 开户银行
        'bank_account',         // 银行账户
        'updated_by',           // 修改者
        'created_by',           // 创建者
        'email',                // 邮箱
        'department',           // 部门
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

        // order_number
        if (isset($search->order_number) && !empty($search->order_number)) {

            $orderIds = Order::query()->select(['id'])->where('order_number', 'like', '%' . $search->order_number . '%')->where('status', 1)->pluck('id');
            if ($orderIds) {
                $model = $model->whereIn('order_id', $orderIds);
            }
        }

        // company_name
        if (isset($search->company_name) && !empty($search->company_name)) {
            $model = $model->where('company_name', 'like', '%' . $search->company_name . '%');
        }

        // company_address
        if (isset($search->company_address) && !empty($search->company_address)) {
            $model = $model->where('company_address', 'like', '%' . $search->company_address . '%');
        }

        if (isset($search->department) && !empty($search->department)) {
            $model = $model->where('department', 'like', '%' . $search->department . '%');
        }
        
        if (isset($search->email) && !empty($search->email)) {
            $model = $model->where('email', 'like', '%' . $search->email . '%');
        }

        // tax_code
        if (isset($search->tax_code) && !empty($search->tax_code)) {
            $model = $model->where('tax_code', 'like', '%' . $search->tax_code . '%');
        }

        // invoice_type
        if (isset($search->invoice_type) && $search->invoice_type != '') {
            $model = $model->where('invoice_type', $search->invoice_type);
        }
        // price
        if (isset($search->price) && $search->price != '') {
            $model = $model->where('price', $search->price);
        }

        // user_id
        if (isset($search->user_id) && $search->user_id != '') {
            if (is_numeric($search->user_id)) {
                $model = $model->where('user_id', $search->user_id);
            } else {
                $userIds = User::query()->select(['id'])->where('name', 'like', '%' . $search->user_id . '%')->where('status', 1)->pluck('id');
                $model = $model->whereIn('user_id', $userIds);
            }
        }

        // order_id
        if (isset($search->order_id) && $search->order_id != '') {
            $model = $model->where('order_id', $search->order_id);
        }

        if (isset($search->title) && !empty($search->title)) {
            $model = $model->where('title', 'like', '%' . $search->title . '%');
        }
        // contact_person
        if (isset($search->contact_person) && !empty($search->contact_person)) {
            $model = $model->where('contact_person', 'like', '%' . $search->contact_person . '%');
        }
        // contact_detail
        if (isset($search->contact_detail) && !empty($search->contact_detail)) {
            $model = $model->where('contact_detail', 'like', '%' . $search->contact_detail . '%');
        }

        // status
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }
        // apply_status
        if (isset($search->apply_status) && $search->apply_status != '') {
            $model = $model->where('apply_status', $search->apply_status);
        }

        // phone
        if (isset($search->phone) && !empty($search->phone)) {
            $model = $model->where('phone', 'like', '%' . $search->phone . '%');
        }

        // bank_name
        if (isset($search->bank_name) && !empty($search->bank_name)) {
            $model = $model->where('bank_name', 'like', '%' . $search->bank_name . '%');
        }
        // bank_account
        if (isset($search->bank_account) && !empty($search->bank_account)) {
            $model = $model->where('bank_account', 'like', '%' . $search->bank_account . '%');
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

        return $model;
    }

    /**
     * 用户名获取器
     */
    public function getUsernameAttribute()
    {
        $text = '';
        if (isset($this->attributes['user_id'])) {
            $text = User::query()->where('id', $this->attributes['user_id'])->value('username') ?? '';
        }
        return $text;
    }

    /**
     * 订单编号获取器
     */
    public function getOrderNumberAttribute()
    {
        $text = '';
        if (isset($this->attributes['order_id'])) {
            $text = Order::query()->where('id', $this->attributes['order_id'])->value('order_number') ?? '';
        }
        return $text;
    }


    /**
     * 发票类型获取器
     */
    public function getInvoiceTypeTextAttribute()
    {
        $text = '';
        if (isset($this->attributes['invoice_type'])) {

            // 获取请求对象
            $request = request();

            if ($request->HeaderLanguage == 'en') {
                $field = 'english_name';
            } else {
                $field = 'name';
            }
            $text = DictionaryValue::where('code', 'Invoice_Type')->where('value', $this->attributes['invoice_type'])->value($field) ?? '';
        }
        return $text;
    }
}
