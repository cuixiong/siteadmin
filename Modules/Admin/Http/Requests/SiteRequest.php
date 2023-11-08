<?php
namespace Modules\Admin\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class SiteRequest extends BaseRequest
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
            'domain' => 'required',
            'country_id' => 'required',
            // 'database_id' => 'required',
            'db_host' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
            'db_password' => 'required',
            'is_create' => 'required',
        ];
        return $this->validateRequest($request, $rules);
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
            'domain' => 'required',
            'country_id' => 'required',
            // 'database_id' => 'required',
            'db_host' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
            'db_password' => 'required',
            'is_create' => 'required',
        ];
        return $this->validateRequest($request, $rules);
    }
}