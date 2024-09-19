<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\System;
use Modules\Site\Http\Models\SystemValue;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;

class TemplateController extends CrudController {
    public $classificationSubCode = 'classificationSubRules';
    public $applicationSubCode    = 'applicationSubRules';

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
            $dictModel = (new DictionaryValue());
            foreach ($recordList as $recordInfo) {
                //模板分类按钮信息
                $btnColorId = $recordInfo->btn_color;
                $dictInfo = $dictModel->find($btnColorId);
                if (!empty($dictInfo)) {
                    $dictInfo = $dictInfo->toArray();
                    $dictInfo = Arr::only($dictInfo, ['name', 'id', 'value']);
                    $recordInfo->btn_info = $dictInfo;
                } else {
                    $recordInfo->btn_info = [];
                }
                //模板分类的文本
                $cateNameList = $recordInfo->tempCates()->where("status", 1)->pluck('name', 'cate_id')->toArray();
                if (!empty($cateNameList)) {
                    [$keys, $values] = Arr::divide($cateNameList);
                    $cateInfo['cate_text'] = implode(",", $values);
                    $cateInfo['cate_ids'] = implode(",", $keys);
                    $recordInfo->cate_info = $cateInfo;
                } else {
                    $recordInfo->cate_info = [];
                }
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
                $lngField = 'english_name as label';
            } else {
                $field = ['name as label', 'value'];
                $lngField = 'name as label';
            }
            //模版分类列表
            $model = new TemplateCategory();
            $temp_cate_list = $model->GetListLabel(['id as value', 'name as label'], false, '',
                                                   ['status' => 1]);
            $data['temp_cate_list'] = $temp_cate_list;
            // 颜色列表
            $data['color_list'] = (new DictionaryValue())->GetListLabel(
                ['id as value', $lngField], false, '', ['code' => 'template_color', 'status' => 1], ['sort' => 'ASC']
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
            $cate_id_list = [];
            if (!empty($input['cate_ids'])) {
                $cate_ids = $input['cate_ids'];
                $cate_id_list = explode(",", $cate_ids);
            }
            $modelInstance = $this->ModelInstance();
            $record = $modelInstance->create($input);
            //先移除后添加
            $record->tempCates()->detach();
            if (!empty($cate_id_list)) {
                $record->tempCates()->attach($cate_id_list);
            }
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
            $cate_id_list = [];
            if (!empty($input['cate_ids'])) {
                $cate_ids = $input['cate_ids'];
                $cate_id_list = explode(",", $cate_ids);
            }
            $record->tempCates()->detach();
            if (!empty($cate_id_list)) {
                $record->tempCates()->attach($cate_id_list);
            }
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
        $year = date("Y", $product->published_date);
        $pdModel = new ProductsDescription($year);
        $pdObj = $pdModel->where("product_id", $productId)->first();
        list($productArrData, $pdArrData) = $this->handlerData($product, $pdObj);
        // TODO List 处理所有模板变量
        $tempContent = $template->content;

        //过滤模版标签的换行
        $tempContent = preg_replace('/(<\/[a-zA-Z][a-zA-Z0-9]*>)\r?\n/', '$1', $tempContent);

        // 处理模板变量   {{year}}
        $tempContent = $this->writeTempWord($tempContent, '{{year}}', date("Y"));
        // 处理模板变量   {{month}}
        $tempContent = $this->writeTempWord($tempContent, '{{month}}', date("m"));
        // 处理模板变量   {{day}}
        $tempContent = $this->writeTempWord($tempContent, '{{day}}', date("d"));
        // 处理模板变量   @@@@
        $tempContent = $this->writeTempWord($tempContent, '@@@@', $productArrData['keywords']);
        // 处理模板变量  {{id}}
        $tempContent = $this->writeTempWord($tempContent, '{{id}}', $productId);
        // 处理模板变量  {{title_en}}
        $tempContent = $this->writeTempWord($tempContent, '{{title_en}}', $productArrData['english_name']);
        // 处理模板变量   {{seo_description}}
        $tempContent = $this->writeTempWord($tempContent, '{{seo_description}}', $pdArrData['description']);
        // 处理模板变量   {{toc}}
        $tempContent = $this->writeTempWord($tempContent, '{{toc}}', $pdArrData['table_of_content']);

        // 处理模板变量   {{tof}}
        $tempContent = $this->writeTempWord($tempContent, '{{tof}}', $pdArrData['tables_and_figures']);

        // 处理模板变量   {{company}}   (换行)
        $replaceWords = $pdArrData['companies_mentioned'];
        $replaceWords = $this->addChangeLineStr($replaceWords);
        $tempContent = $this->writeTempWord($tempContent, '{{company}}', $replaceWords);
        // 处理模板变量  {{company_str}}  (不换行)
        $temp_companies_mentioned = $this->handlerLineSymbol($pdArrData['companies_mentioned']);
        $tempContent = $this->writeTempWord($tempContent, '{{company_str}}', $temp_companies_mentioned);
        // 处理模板变量  {{definition}}
        $tempContent = $this->writeTempWord($tempContent, '{{definition}}', $pdArrData['definition']);
        // 处理模板变量  {{overview}}
        $tempContent = $this->writeTempWord($tempContent, '{{overview}}', $pdArrData['overview']);
        // 处理模板变量  {{type}}   换行
        $replaceWords = $productArrData['classification'];
        $replaceWords = $this->addChangeLineStr($replaceWords);
        $tempContent = $this->writeTempWord($tempContent, '{{type}}', $replaceWords);
        // 处理模板变量  {{type_str}}  不换行
        $tempClassification = $this->handlerLineSymbol($productArrData['classification']);
        $tempContent = $this->writeTempWord($tempContent, '{{type_str}}', $tempClassification);
        // 处理模板变量  {{application}}   换行
        $replaceWords = $productArrData['application'];
        $replaceWords = $this->addChangeLineStr($replaceWords);
        $tempContent = $this->writeTempWord($tempContent, '{{application}}', $replaceWords);
        // 处理模板变量  {{application_str}} 不换行
        $tempApplication = $this->handlerLineSymbol($productArrData['application']);
        $tempContent = $this->writeTempWord($tempContent, '{{application_str}}', $tempApplication);
        // 处理模板变量  {{link}}
        $tempContent = $this->writeTempWord($tempContent, '{{link}}', $productArrData['url']);

        $tempContent = $this->handlerMuchLine($tempContent);
        $tempContent = str_replace(' ', '&nbsp;', $tempContent);

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
        if (!isset($replaceWords)) {
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

    /**
     *  处理换行符(处理为1行)
     */
    private function handlerLineSymbol($lineStr) {
        return str_replace("\n", "、 ", $lineStr);
    }

    /**
     * 处理多行
     * @param $sourceStr
     *
     * @return array|string|string[]
     */
    private function handlerMuchLine($sourceStr) {
        return str_replace("\n", "<br/>", $sourceStr);
    }

    public function getReportUrl($product)
    {
        //获取当前站点域名
        $domain = Site::where('name', request()->header('Site'))->value('domain');
        if(!empty($domain)){
            $domain = strpos($domain,'http') !== 0 ? 'https://' . $domain : '';
        }else{
            $domain = '';
        }
        //暂时使用线上的域名
        // $domain = "https://www.marketmonitorglobal.com.cn";
        if (!empty($product->url)) {
            $url = $domain . "/reports/{$product->id}/$product->url";
        } else {
            $url = $domain . "/reports/{$product->id}";
        }
        $url = <<<EOF
<a style="word-wrap:break-word;word-break:break-all;" href="{$url}" target="_blank" rel="noopener noreferrer nofollow">{$url}</a>
EOF;

        return $url;
    }

    public function handlerData($product, $pdObj) {
        $productArrData = [];
        //英文标题
        if (isset($product->english_name)) {
            $productArrData['english_name'] = $product->english_name;
        } else {
            $productArrData['english_name'] = '';
        }
        //关键字
        if (isset($product->keywords)) {
            $productArrData['keywords'] = $product->keywords;
        } else {
            $productArrData['keywords'] = '';
        }
        //类型
        if (isset($product->classification)) {
            $productArrData['classification'] = $product->classification;
        } else {
            $productArrData['classification'] = $this->handlerSubRules(
                $pdObj->description, $this->classificationSubCode
            );
        }
        //应用
        if (isset($product->application)) {
            $productArrData['application'] = $product->application;
        } else {
            $productArrData['application'] = $this->handlerSubRules($pdObj->description, $this->applicationSubCode);
        }
        //访问url
        $productArrData['url'] = $this->getReportUrl($product);
        $pdArrData = [];
        //描述第一段
        if (!empty($pdObj->description)) {
            $replaceWords = $pdObj->description;
            //取描述第一段 ,  如果没有\n换行符就取一整段
            $strIndex = strpos($replaceWords, "\n");
            if ($strIndex !== false) {
                // 使用 substr() 函数获取第一个段落
                $pdArrData['description'] = substr($replaceWords, 0, $strIndex);
            } else {
                $pdArrData['description'] = $pdObj->description;
            }
        } else {
            $pdArrData['description'] = '';
        }
        //目录
        if (isset($pdObj->table_of_content)) {
            $pdArrData['table_of_content'] = $this->autoIndent($pdObj->table_of_content);
        } else {
            $pdArrData['table_of_content'] = '';
        }
        //企业
        if (isset($pdObj->companies_mentioned)) {
            $pdArrData['companies_mentioned'] = $pdObj->companies_mentioned;
        } else {
            $pdArrData['companies_mentioned'] = '';
        }
        //定义
        if (isset($pdObj->definition)) {
            $pdArrData['definition'] = $pdObj->definition;
        } else {
            $pdArrData['definition'] = '';
        }
        //概况
        if (isset($pdObj->overview)) {
            $pdArrData['overview'] = $pdObj->overview;
        } else {
            $pdArrData['overview'] = '';
        }

        //报告图表
        if (isset($pdObj->tables_and_figures)) {
            $pdArrData['tables_and_figures'] = $pdObj->tables_and_figures;
        } else {
            $pdArrData['tables_and_figures'] = '';
        }


        return [$productArrData, $pdArrData];
    }

    public function handlerSubRules($description, $subRulesCode) {
        if (empty($description) || empty($subRulesCode)) {
            return '';
        }
        $systemId = System::query()->where("alias", $subRulesCode)->value("id");
        if (empty($systemId)) {
            return '';
        }
        $applicton = '';
        $rulesList = SystemValue::query()->where("parent_id", $systemId)
                                         ->where("hidden" , 1)
                                         ->pluck("value")->toArray();
        foreach ($rulesList as $forRule) {
            $pattern = '/'.$forRule.'[\r\n]+((?:(?:\s+[^\r\n]*[\r\n]+))*)/';
            if (preg_match($pattern, $description, $matches)) {
                // 打印提取的部分
                $applicton = $matches[1];
            }
        }
        return $applicton;
    }


    public function autoIndent($text) {
        // 分割换行
        $lines = explode("\n", $text);
        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // 匹配 ( "1.1 ", "1.2.1 ")
            if (preg_match('/^(\d+(\.\d+)*)(.*)$/', $line, $matches)) {
                $indentLevel = substr_count($matches[1], '.');
                $indentedLine = str_repeat("  ", $indentLevel) . $line;
                $result[] = $indentedLine;
            } else {
                $result[] = $line; // No change if the line does not match
            }
        }

        return implode("\n", $result);
    }

}
