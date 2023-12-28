<?php
namespace Modules\Site\Http\Requests;
use Modules\Admin\Http\Requests\BaseRequest;
class MenuRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $rules = [
            'name' => 'required',
            'is_single' => 'required',
            'type' => 'required',
            'banner_pc' => 'required',
            'banner_mobile' => 'required',
            'banner_title' => 'required',
            'link' => 'required',
            'seo_title' => 'required',
            'seo_keyword' => 'required',
            'seo_description' => 'required',
            'status' => 'required',
            'sort' => 'required',
        ];
        $message = [
            'name.required' => '名称不能为空',
            'is_single.required' => '是否单页不能为空',
            'type.required' => '类型不能为空',
            'banner_pc.required' => '图片不能为空',
            'banner_mobile.required' => '移动端不能为空',
            'banner_title.required' => '背景图短标题不能为空',
            'link.required' => '链接不能为空',
            'seo_title.required' => 'SEO标题不能为空',
            'seo_keyword.required' => 'SEO关键词不能为空',
            'seo_description.required' => 'SEO描述不能为空',
            'status.required' => '名称不能为空',
            'sort.required' => '名称不能为空',
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
            'is_single' => 'required',
            'type' => 'required',
            'banner_pc' => 'required',
            'banner_mobile' => 'required',
            'banner_title' => 'required',
            'link' => 'required',
            'seo_title' => 'required',
            'seo_keyword' => 'required',
            'seo_description' => 'required',
            'status' => 'required',
            'sort' => 'required',
        ];
        $message = [
            'id.required' => 'ID不能为空',
            'name.required' => '名称不能为空',
            'is_single.required' => '是否单页不能为空',
            'type.required' => '类型不能为空',
            'banner_pc.required' => '图片不能为空',
            'banner_mobile.required' => '移动端不能为空',
            'banner_title.required' => '背景图短标题不能为空',
            'link.required' => '链接不能为空',
            'seo_title.required' => 'SEO标题不能为空',
            'seo_keyword.required' => 'SEO关键词不能为空',
            'seo_description.required' => 'SEO描述不能为空',
            'status.required' => '名称不能为空',
            'sort.required' => '名称不能为空',
        ];
        return $this->validateRequest($request, $rules,$message);
    }
}
