<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class SystemRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'alias_name' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'alias_name.required' => trans('lang.alias_name_empty'),
        ];
        return $this->validateRequest($request, $rules,$message);
    }
    /**
     * 更新数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function update($request)
    {
        $rules = [
            'id' => 'required',
            'name' => 'required',
            'alias_name' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
    
    public function systemValueStore($request) {
        $rules = [
            'parent_id' => 'required',
            'name' => 'required',
            'key' => 'required|unique:system_values',
            'value' => 'required',
            'type' => 'required',
            'status' => 'required',
        ];
        $message = [
            'parent_id.required' => '父级ID不能为空',
            'name.required' => '名称不能为空',
            'key.required' => 'key不能为空',
            'key.unique' => 'key已经存在',
            'value.required' => 'value不能为空',
            'type.required' => 'type不能为空',
            'status.required' => 'status不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }

    public function systemValueUpdate($request) {
        $rules = [
            'id' =>  'required',
            'parent_id' => 'required',
            'name' => 'required',
            'key' => 'required',
            'value' => 'required',
            'type' => 'required',
            'status' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'parent_id.required' => '父级ID不能为空',
            'name.required' => '名称不能为空',
            'key.required' => 'key不能为空',
            'value.required' => 'value不能为空',
            'type.required' => 'type不能为空',
            'status.required' => 'status不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }

    public function systemValueDestroy($request)
    {
        return $this->validateRequest($request, ['ids' => 'required'],['ids.required' => 'ID不能为空！']);
    }
}
