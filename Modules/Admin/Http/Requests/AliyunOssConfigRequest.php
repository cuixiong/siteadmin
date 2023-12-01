<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class AliyunOssConfigRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'access_key_id' => 'required',
            'access_key_secret' => 'required',
            'endpoint' => 'required',
            'bucket' => 'required',
            'site_id' => 'required|unique:aliyun_oss_configs', 
            'domain' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'access_key_id.required' => 'accessKeyId不能为空',
            'access_key_secret.required' => 'accessKeySecret不能为空',
            'endpoint.required' => 'endpoint不能为空',
            'bucket.required' => 'bucket不能为空',
            'site_id.required' => 'site_id不能为空',
            'site_id.unique' => '选择的站点已绑定其他空间，请重新选择',
            'domain.required' => '域名不能为空',
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
            'name' => 'required',
            'access_key_id' => 'required',
            'access_key_secret' => 'required',
            'endpoint' => 'required',
            'bucket' => 'required',
            'site_id' => 'required', 
            'domain' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'access_key_id.required' => 'accessKeyId不能为空',
            'access_key_secret.required' => 'accessKeySecret不能为空',
            'endpoint.required' => 'endpoint不能为空',
            'bucket.required' => 'bucket不能为空',
            'site_id.required' => 'site_id不能为空',
            'domain.required' => '域名不能为空',
        ];
        return $this->validateRequest($request, $rules);
    }
}
