<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class RuleRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'category' => 'required',
            'visible' => 'required',
            'english_name' => 'required',
        ];
        return $this->validateRequest($request, $rules,[]);
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
            'type' => 'required',
            'category' => 'required',
            'visible' => 'required',
            'english_name' => 'required',
        ];
        return $this->validateRequest($request, $rules,[]);
    }
}
