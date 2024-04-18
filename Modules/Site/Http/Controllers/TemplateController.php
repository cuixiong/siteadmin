<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;

class TemplateController extends CrudController {
    /**
     * 查询列表页 (重写父类该方法)
     *
     * @param       $request  请求信息
     */
    protected function list(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = $ModelInstance->query();
            $model = $ModelInstance->HandleWhere($model, $request);
            // 总数量
            $total = $model->count();
            // 查询偏移量
            if (!empty($request->pageNum) && !empty($request->pageSize)) {
                $model->offset(($request->pageNum - 1) * $request->pageSize);
            }
            // 查询条数
            if (!empty($request->pageSize)) {
                $model->limit($request->pageSize);
            }
            $model = $model->select($ModelInstance->ListSelect);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('created_at', 'DESC');
            }
            $recordList = $model->get();
            foreach ($recordList as $recordInfo) {
                //模板分类的文本
                $cateNameList = $recordInfo->tempCates()->where("status", 1)->pluck('name')->toArray();
                $recordInfo->cate_text = implode(",", $cateNameList);
            }
            $data = [
                'total' => $total,
                'list'  => $recordList
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 获取搜索下拉列表
     *
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request) {
        try {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            //模版分类列表
            $model = new TemplateCategory();
            $temp_cate_list = $model->GetListLabel(['id as value', 'name as label'], false, '',
                                                   ['status' => 1]);
            $data['temp_cate_list'] = $temp_cate_list;
            // 颜色列表
            $data['show_home'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'template_color', 'status' => 1], ['sort' => 'ASC']
            );
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 单个新增
     *
     * @param $request 请求信息
     */
    public function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $cate_ids = $input['cate_ids'];
            $cate_id_list = explode(",", $cate_ids);
            $modelInstance = $this->ModelInstance();
            $record = $modelInstance->create($input);
            //先移除后添加
            $record->tempCates()->detach();
            $record->tempCates()->attach($cate_id_list);
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
            }
            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单个更新
     *
     * @param $request 请求信息
     */
    protected function update(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $record = $this->ModelInstance()->findOrFail($request->id);
            if (!$record->update($input)) {
                ReturnJson(false, trans('lang.update_error'));
            }
            //维护中间表
            $cate_ids = $input['cate_ids'];
            $cate_id_list = explode(",", $cate_ids);
            $record->tempCates()->detach();
            $record->tempCates()->attach($cate_id_list);
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 删除
     *
     * @param $ids 主键ID
     */
    protected function destroy(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ids = $request->ids;
            if (!is_array($ids)) {
                $ids = explode(",", $ids);
            }
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $res = $record->delete();
                    if ($res > 0) {
                        $record->tempCates()->detach();
                    }
                }
            }
            ReturnJson(true, trans('lang.delete_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     *  根据模板返回拷贝内容
     */
    public function copyWordByTemplate(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $templateId = $input['templateId'];
            $template = Template::findOrFail($templateId);
            $productId = $input['productId'];
            $product = Products::findOrFail($productId);
            $templateWords = $this->templateWirteData($template, $product);
            ReturnJson(true, trans('lang.copy_success'), ['words' => $templateWords]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function templateWirteData($template, $product) {
        //查询模板描述数据
        $productId = $product->id;
        $pdModel = new ProductsDescription();
        $pd_obj = $pdModel->where("product_id", $productId)->first();

        // TODO List 处理所有模板变量
        $tempContent = $template->content;
        // 处理模板变量   {{year}}
        $tempContent = $this->writeTempWord($tempContent, '{{year}}', date("Y"));
        // 处理模板变量   {{month}}
        $tempContent = $this->writeTempWord($tempContent, '{{month}}', date("m"));
        // 处理模板变量   {{day}}
        $tempContent = $this->writeTempWord($tempContent, '{{day}}', date("d"));
        // 处理模板变量   @@@@
        $keywords = $product->keywords;
        $tempContent = $this->writeTempWord($tempContent, '@@@@', $keywords);

        // 处理模板变量   {{seo_description}}
        $replaceWords = $pd_obj->description;
        //取描述第一段 ,  如果没有\n换行符就取一整段
        $strIndex = strpos($replaceWords, "\n");
        if ($strIndex !== false) {
            // 使用 substr() 函数获取第一个段落
            $replaceWords = substr($replaceWords, 0, $strIndex);
        }
        $tempContent = $this->writeTempWord($tempContent, '{{seo_description}}', $replaceWords);

        // 处理模板变量   {{toc}}
        $replaceWords = $pd_obj->table_of_content;
        $tempContent = $this->writeTempWord($tempContent, '{{toc}}', $replaceWords);

        // 处理模板变量   {{company}}   (换行)
        $replaceWords = $pd_obj->companies_mentioned;
        $replaceWords = $this->addChangeLineStr($replaceWords);
        $tempContent = $this->writeTempWord($tempContent, '{{company}}', $replaceWords);

        // 处理模板变量  {{company_str}}  (不换行)
        $replaceWords = $pd_obj->companies_mentioned;
        $tempContent = $this->writeTempWord($tempContent, '{{company_str}}', $replaceWords);


        // 处理模板变量  {{definition}}
        $replaceWords = $pd_obj->definition;
        $tempContent = $this->writeTempWord($tempContent, '{{definition}}', $replaceWords);

        // 处理模板变量  {{overview}}
        $replaceWords = $pd_obj->overview;
        $tempContent = $this->writeTempWord($tempContent, '{{overview}}', $replaceWords);

        // 处理模板变量  {{type}}   换行
        $replaceWords = $product->classification;
        $replaceWords = $this->addChangeLineStr($replaceWords);
        $tempContent = $this->writeTempWord($tempContent, '{{type}}', $replaceWords);

        // 处理模板变量  {{type_str}}
        $replaceWords = $product->classification;
        $tempContent = $this->writeTempWord($tempContent, '{{type_str}}', $replaceWords);

        // 处理模板变量  {{application}}   换行
        $replaceWords = $product->application;
        $replaceWords = $this->addChangeLineStr($replaceWords);
        $tempContent = $this->writeTempWord($tempContent, '{{application}}', $replaceWords);

        // 处理模板变量  {{application_str}}
        $replaceWords = $product->application;
        $tempContent = $this->writeTempWord($tempContent, '{{application_str}}', $replaceWords);

        // 处理模板变量  {{link}}
        $replaceWords = $product->url;
        $tempContent = $this->writeTempWord($tempContent, '{{link}}', $replaceWords);

        return $tempContent;
    }

    /**
     *
     * @param $sourceContent  string 源串
     * @param $templateVar    string 模板变量
     * @param $replaceWords   string 变量的值
     *
     * @return array|string|string[]|null
     */
    private function writeTempWord($sourceContent, $templateVar, $replaceWords) {
        $pattern = '/'.preg_quote($templateVar).'/';

        //变量不存在, 为空字符串
        if(!isset($replaceWords)){
            $replaceWords = '';
        }
        return preg_replace($pattern, $replaceWords, $sourceContent);
    }

    /**
     * 添加换行符
     *
     * @param $sorceStr
     *
     * @return string
     */
    private function addChangeLineStr($sorceStr) {
        return $sorceStr." <br/>";
    }
}
