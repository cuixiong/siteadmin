<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;

class ProductsExportLogRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */ 
    public function store($request)
    {
        $rules = [
            'file' => 'required',
        ];
        $message = [
            'file.required' => '文件不能为空',
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
            'file' => 'required',
        ];
        $message = [
            'file.required' => '名称不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
