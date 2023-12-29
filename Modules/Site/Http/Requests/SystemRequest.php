<?php
namespace Modules\Site\Http\Requests;
use Modules\Site\Http\Requests\BaseRequest;
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
            'english_name' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'english_name.required' => trans('lang.english_name_empty'),
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
            'english_name' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
            'english_name.required' => trans('lang.english_name_empty'),
        ];
        return $this->validateRequest($request, $rules,$message);
    }
    
    public function systemValueStore($request) {
        $rules = [
            'parent_id' => 'required',
            'name' => 'required',
            'key' => 'required|unique:system_values',
            'type' => 'required',
        ];
        $message = [
            'parent_id.required' => '父级ID不能为空',
            'name.required' => '名称不能为空',
            'key.required' => 'key不能为空',
            'key.unique' => 'key已经存在',
            'type.required' => 'type不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }

    public function systemValueUpdate($request) {
        $rules = [
            'id' =>  'required',
            'parent_id' => 'required',
            'name' => 'required',
            'key' => 'required',
            'type' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'parent_id.required' => '父级ID不能为空',
            'name.required' => '名称不能为空',
            'key.required' => 'key不能为空',
            'type.required' => 'type不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }

    public function systemValueDestroy($request)
    {
        return $this->validateRequest($request, ['ids' => 'required'],['ids.required' => 'ID不能为空！']);
    }
}
