<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class TeamMemberRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'image' => 'required',
            'position' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'image.required' => '图片不能为空',
            'position.required' => '职位不能重复',
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
            'image' => 'required',
            'position' => 'required',
        ];
        $message = [
            'id' => 'required',
            'name.required' => '名称不能为空',
            'image.required' => '图片不能为空',
            'position.required' => '职位不能重复',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
