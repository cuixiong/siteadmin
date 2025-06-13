<?php

namespace Modules\Site\Http\Controllers;

use Foolz\SphinxQL\SphinxQL;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\System;
use Modules\Site\Http\Models\SystemValue;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;
use Modules\Site\Http\Models\TemplateUse;
use Modules\Site\Services\SphinxService;

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
            //use_name_id
            $searchStr = $request->input('search');
            $search = @json_decode($searchStr, true);
            if (!empty($search['use_name_id'])) {
                $tempIdList = TemplateUse::query()->whereIn('user_id', [$search['use_name_id']])->pluck("temp_id")
                                         ->toArray();
                $model = $model->whereIn("id", $tempIdList);
            }
            //不是超级管理员, 只展示当前角色已分配的模版
            $type = $request->type ?? 1;
            if (!$request->user->is_super) {
                $isExistRule = $this->checkEditRule($type, $request->user);
            } else {
                $isExistRule = true;
            }
            if (!$isExistRule) {
//                $postUsrList = $this->getSitePostUser();
//                $userIds = array_column($postUsrList, 'value');
                $userIds = [$request->user->id];
                $tempIdList = TemplateUse::query()->whereIn('user_id', $userIds)->pluck("temp_id")->toArray();
                $model = $model->whereIn("id", $tempIdList);
            }
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
                //模版使用者昵称
                $userIdList = TemplateUse::query()->where('temp_id', $recordInfo->id)->pluck('user_id')->toArray();
                if (!empty($userIdList)) {
                    $userList = User::query()->whereIn('id', $userIdList)->selectRaw('id as value , nickname as label ')
                                    ->get()->toArray();
                    $afterUserList = [];
                    foreach ($userList as $userInfo) {
                        $addData = [];
                        $addData['value'] = $userInfo['value'];
                        $addData['label'] = $userInfo['label'];
                        $afterUserList[] = $addData;
                    }
                    $recordInfo->use_name_list = $afterUserList;
                } else {
                    $recordInfo->use_name_list = [];
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
     *
     */
    public function checkEditRule($type, $user) {
        if ($type == 1) {
            $rule_perm = 'products:template:normaledit';
        } else {
            $rule_perm = 'products:title:normaledit';
        }
        $rule_id = Rule::query()->where("perm", $rule_perm)->value("id");
        $role_ids = $user->role_id;
        $role_id_list = explode(",", $role_ids);
        $rule_id_list = Role::query()->whereIn("id", $role_id_list)->pluck('site_rule_id')->toArray();
        $merge_rule_id_list = [];
        foreach ($rule_id_list as $forIdList) {
            $merge_rule_id_list = array_merge($merge_rule_id_list, $forIdList);
        }
        $merge_rule_id_list = array_unique($merge_rule_id_list);
        if (!in_array($rule_id, $merge_rule_id_list)) {
            return false;
        }

        return true;
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
            //发帖用户
            $postUserList = $this->getSitePostUser();
            $data['post_user_list'] = $postUserList;
            //所有管理员用户
            $data['admin_user_list'] = User::query()->where("status", 1)->selectRaw('id as value , nickname as label ')
                                           ->get()->toArray();
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
            if (!$record) {
                ReturnJson(false, trans('lang.add_error'));
            }
            //先移除后添加
            $record->tempCates()->detach();
            if (!empty($cate_id_list)) {
                $record->tempCates()->attach($cate_id_list);
            }
            $this->tempUseEdit($record->id, $input, false);
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
            $input['updated_at'] = time();
            $record->fill($input);
            if (!$record->save()) {
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
            $this->tempUseEdit($request->id, $input);
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
            $productId = $input['productId'];
            $templateWords = $this->getTempContent($templateId, $productId);
            ReturnJson(true, trans('lang.copy_success'), ['words' => $templateWords]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     *  批量修改模板使用状态
     */
    public function batchEditTemplateUse(Request $request) {
        try {
            $input = $request->input();
            $template_ids = $input['temp_ids'] ?? '';
            $user_ids = $input['user_ids'] ?? '';
            $TemplateUse = new TemplateUse();

            if(empty($template_ids )){
                $modelInstance = $this->ModelInstance();
                $model = $modelInstance->query();
                $model = $modelInstance->HandleWhere($model, $request);
                //use_name_id
                $searchStr = $request->input('search');
                $search = @json_decode($searchStr, true);
                if (!empty($search['use_name_id'])) {
                    $tempIdList = TemplateUse::query()->whereIn('user_id', [$search['use_name_id']])->pluck("temp_id")
                                             ->toArray();
                    $model = $model->whereIn("id", $tempIdList);
                }
                //不是超级管理员, 只展示当前角色已分配的模版
                $type = $request->type ?? 1;
                if (!$request->user->is_super) {
                    $isExistRule = $this->checkEditRule($type, $request->user);
                } else {
                    $isExistRule = true;
                }
                if (!$isExistRule) {
//                $postUsrList = $this->getSitePostUser();
//                $userIds = array_column($postUsrList, 'value');
                    $userIds = [$request->user->id];
                    $tempIdList = TemplateUse::query()->whereIn('user_id', $userIds)->pluck("temp_id")->toArray();
                    $model = $model->whereIn("id", $tempIdList);
                }
                $template_ids = $model->pluck("id")->toArray();
            }

            if(empty($input['is_update'] )){
                ReturnJson(true, trans('lang.request_success') , ['count' => count($template_ids)]);
            }

            if (!empty($template_ids) && !empty($user_ids)) {
                if(!is_array($template_ids)) {
                    $template_ids = explode(",", $template_ids);
                }
                $user_ids = explode(",", $user_ids);
                foreach ($template_ids as $template_id) {
                    //删除之前模版记录
                    $TemplateUse->where("temp_id", $template_id)->delete();
                    foreach ($user_ids as $user_id) {
                        $add_data = [
                            'user_id' => $user_id,
                            'temp_id' => $template_id,
                        ];
                        $TemplateUse->insert($add_data);
                    }
                }
            } else {
                ReturnJson(false, trans('lang.param_error'));
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     *
     * @param $template     object 模版对象
     * @param $product      object 报告对象
     * @param $pdObj        object 报告详情对象
     * @param $is_auto_post bool 是否自动发帖
     *
     * @return array|string|string[]
     */
    public function templateWirteData($template, $product, $pdObj, $is_auto_post = false) {
        list($productArrData, $pdArrData) = $this->handlerData($product, $pdObj);
        // TODO List 处理所有模板变量
        $tempContent = $template['content'];
        //过滤模版标签的换行
        $tempContent = preg_replace('/(<\/[a-zA-Z][a-zA-Z0-9]*>)\r?\n/', '$1', $tempContent);
        // 处理模板变量   {{year}}
        $tempContent = $this->writeTempWord($tempContent, '{{year}}', date("Y"));
        // 处理模板变量   {{month}}
        $tempContent = $this->writeTempWord($tempContent, '{{month}}', date("n"));
        // 处理模板变量   {{month_en}}
        $tempContent = $this->writeTempWord($tempContent, '{{month_en}}', date("M"));
        // 处理模板变量   {{day}}
        $tempContent = $this->writeTempWord($tempContent, '{{day}}', date("j"));
        // 处理模板变量   @@@@
        $tempContent = $this->writeTempWord($tempContent, '@@@@', $productArrData['keywords']);
        // 处理模板变量   keywords 兼容@@@@
        $tempContent = $this->writeTempWord($tempContent, '{{keywords}}', $productArrData['keywords']);
        // 处理模板变量   五种语言 keywords
        $tempContent = $this->writeTempWord($tempContent, '{{keywords_cn}}', $product['keywords_cn']);
        $tempContent = $this->writeTempWord($tempContent, '{{keywords_en}}', $product['keywords_en']);
        $tempContent = $this->writeTempWord($tempContent, '{{keywords_jp}}', $product['keywords_jp']);
        $tempContent = $this->writeTempWord($tempContent, '{{keywords_kr}}', $product['keywords_kr']);
        $tempContent = $this->writeTempWord($tempContent, '{{keywords_de}}', $product['keywords_de']);
        //处理新变量  产品划分, 细分市场 , 产品类别
        $tempContent = $this->writeTempWord($tempContent, '{{product_class}}', $product['product_class']);
        $tempContent = $this->writeTempWord($tempContent, '{{segment}}', $product['segment']);
        $tempContent = $this->writeTempWord($tempContent, '{{division}}', $product['division']);

        //页数,图表
        $tempContent = $this->writeTempWord($tempContent, '{{pages}}', $product['pages']);
        $tempContent = $this->writeTempWord($tempContent, '{{tables}}', $product['tables']);
        //规模等数据
        $tempContent = $this->writeTempWord($tempContent, '{{cagr}}', $product['cagr']);
        $tempContent = $this->writeTempWord($tempContent, '{{last_year}}', $product['last_scale']);
        $tempContent = $this->writeTempWord($tempContent, '{{this_year}}', $product['current_scale']);
        $tempContent = $this->writeTempWord($tempContent, '{{six_year}}', $product['future_scale']);
        //跳转A标签(左右)
        $prourl = $this->handlerUrl($product);
        $tempContent = $this->writeTempWord(
            $tempContent, '{{link_tag_left}}', "<a href='{$prourl}' target='_blank'>"
        );
        $tempContent = $this->writeTempWord($tempContent, '{{link_tag_right}}', "</a>");
        //特殊站点独有标签
        $scopeText = $this->handlerSpecialLabels('{{scope}}', $pdObj->description);
        $tempContent = $this->writeTempWord($tempContent, '{{scope}}', $scopeText);
        $keyFeaturesText = $this->handlerSpecialLabels('{{key_features}}', $pdObj->description);
        $tempContent = $this->writeTempWord($tempContent, '{{key_features}}', $keyFeaturesText);
        //处理相关报告标签
        $productId = $product->id;
        $tempContent = $this->handlerRelatedReport($tempContent, $product);
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
        if (!$is_auto_post) {
            $tempContent = str_replace(' ', '&nbsp;', $tempContent);
        }

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
        if (in_array($templateVar, ['@@@@', '{{keywords}}', '{{keywords_cn}}', '{{keywords_jp}}', '{{keywords_en}}',
                                    '{{keywords_kr}}', '{{keywords_de}}'])) {
            if (empty($replaceWords)) {
                return str_replace($templateVar, '', $sourceContent);
            }
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
     *
     * @param $sourceStr
     *
     * @return array|string|string[]
     */
    private function handlerMuchLine($sourceStr) {
        return str_replace("\n", "<br/>", $sourceStr);
    }

    public function handlerUrl($product) {
        $domain = getSiteDomain();
        if (!empty($product->url)) {
            $url = $domain."/reports/{$product->id}/$product->url";
        } else {
            $url = $domain."/reports/{$product->id}";
        }

        return $url;
    }

    public function getReportUrl($product) {
        $url = $this->handlerUrl($product);
        $reportUrl = <<<EOF
<a style="word-wrap:break-word;word-break:break-all;" href="{$url}" target="_blank" rel="noopener noreferrer nofollow">{$url}</a>
EOF;

        return $reportUrl;
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
        //关键字(中)
        if (isset($product->keywords_cn)) {
            $productArrData['keywords_cn'] = $product->keywords_cn;
        } else {
            $productArrData['keywords_cn'] = '';
        }
        //关键字(英)
        if (isset($product->keywords_en)) {
            $productArrData['keywords_en'] = $product->keywords_en;
        } else {
            $productArrData['keywords_en'] = '';
        }
        //关键字(日)
        if (isset($product->keywords_jp)) {
            $productArrData['keywords_jp'] = $product->keywords_jp;
        } else {
            $productArrData['keywords_jp'] = '';
        }
        //关键字(韩)
        if (isset($product->keywords_kr)) {
            $productArrData['keywords_kr'] = $product->keywords_kr;
        } else {
            $productArrData['keywords_kr'] = '';
        }
        //关键字(德)
        if (isset($product->keywords_de)) {
            $productArrData['keywords_de'] = $product->keywords_de;
        } else {
            $productArrData['keywords_de'] = '';
        }
        //类型
        if (!empty($product->classification)) {
            $productArrData['classification'] = $product->classification;
        } else {
            $productArrData['classification'] = $this->handlerSubRules(
                $pdObj->description, $this->classificationSubCode
            );
        }
        //应用
        if (!empty($product->application)) {
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
                                ->where("hidden", 1)
                                ->pluck("value")->toArray();
        foreach ($rulesList as $forRule) {
            $pattern = '/'.$forRule.'[\r\n]+((?:(?:\s+[^\r\n]*[\r\n]+))*)/';
            if (preg_match($pattern, $description, $matches)) {
                // 打印提取的部分
                $applicton = $matches[1];
                break;
            }
            $pattern2 = '/'.$forRule.'.*?\r?\n([\s\S]*?)(?:\r?\n\S|$)/';
            if (preg_match($pattern2, $description, $matches)) {
                // 打印提取的部分
                $applicton = $matches[1];
                break;
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
                $indentedLine = str_repeat("  ", $indentLevel).$line;
                $result[] = $indentedLine;
            } else {
                $result[] = $line; // No change if the line does not match
            }
        }

        return implode("\n", $result);
    }

    /**
     *
     * @param $label       string 标签名
     * @param $description string 描述
     * @param $siteName    string 站点名称(保留字段)
     *
     */
    public function handlerSpecialLabels($label, $description, $siteName = '') {
        $descriptionSpilt = explode("\n", $description);
        $descText = $descriptionSpilt ? (($descriptionSpilt[0] ?? '').($descriptionSpilt[1] ?? '')) : '';
        if ($label == '{{scope}}') {
            //截取Report Scope的部分
            $strposWord = "Report Scope";
            $position = strpos($description, $strposWord);
            if ($position !== false) {
                // 截取关键词之前的部分
                $descText = substr($description, 0, $position);
            }
        } elseif ($label == '{{key_features}}') {
            $strposWord = "Key Features";
            $position = strpos($description, $strposWord);
            if ($position !== false) {
                // 截取关键词之前的部分
                $descText = substr($description, 0, $position);
            } else {
                $strposWord = "Highlights and key features of the study";
                $position = strpos($description, $strposWord);
                if ($position !== false) {
                    // 截取关键词之前的部分
                    $descText = substr($description, 0, $position);
                }
            }
        }

        return $this->addChangeLineStr($descText);
    }

    /**
     * 相关报告
     *
     * @param $tempContent      string  模版内容
     * @param $productArrData   mixed   报告对象
     *
     */
    public function handlerRelatedReport($tempContent, $productArrData) {
        //判断是否有这个标签
        if (strpos($tempContent, '{{related_reports}}') !== false) {
            if (!empty($productArrData['keywords'])) {
                $conn = (new SphinxService())->getConnection();
                $query = (new SphinxQL($conn))->select('*')
                                              ->from('products_rt')
                                              ->orderBy('sort', 'asc')
                                              ->orderBy('year', 'desc')
                                              ->orderBy('degree_keyword', 'asc')
                                              ->orderBy('published_date', 'desc')
                                              ->orderBy('id', 'desc');
                $query = $query->where('status', '=', 1);
                $query = $query->where("published_date", "<", time());
                $query = $query->where("id", "<>", $productArrData['id']);
                $query = $query->match('name', $productArrData['keywords'], true);
                $query = $query->limit(0, 20);
                $query = $query->option('max_matches', 40);
                $query = $query->setSelect('*');
                $result = $query->execute();
                $product_list = $result->fetchAllAssoc();
                $related_reports = '';
                $domain = getSiteDomain();
                foreach ($product_list as $productInfo) {
                    $productLink = $domain.'/reports/'.$productInfo['id'].'/'.$productInfo['url'];
                    $related_reports .= '<a href="'.$productLink.'"  title="'.$productInfo['name'].'" target="blank">'
                                        .$productInfo['name'].'</a>'.'<br />';
                }
                //if (!empty($related_reports)) {
                $tempContent = $this->writeTempWord($tempContent, '{{related_reports}}', $related_reports);
                //}
            }
        }

        return $tempContent;
    }

    public function getSitePostUser() {
        //当前站点, 所有的使用者
        $siteName = getSiteName();
        $currentSiteId = Site::query()->where("name", $siteName)->value("id");
        $roleList = Role::query()->where("status", 1)
                        ->where("code", 'like', "%POST%")
                        ->get()->toArray();
        $afterRoleIdList = [];
        foreach ($roleList as $roleInfo) {
            if (!empty($roleInfo['site_id']) && is_array($roleInfo['site_id'])) {
                $siteIds = $roleInfo['site_id'];
                if (in_array($currentSiteId, $siteIds)) {
                    $afterRoleIdList[] = $roleInfo['id'];
                }
            }
        }
        $userList = [];
        if (!empty($afterRoleIdList)) {
            $userList = User::query()
                            ->where("status", 1)
                            ->where(function ($query) use ($afterRoleIdList) {
                                foreach ($afterRoleIdList as $afRoleId) {
                                    $query->orWhere('role_id', 'like', "%{$afRoleId}%");
                                }
                            })
                            ->selectRaw('id as value, nickname as label')
                            ->get()
                            ->toArray();
        }
        $afterUserList = [];
        foreach ($userList as $userInfo) {
            $addData = [];
            $addData['value'] = $userInfo['value'];
            $addData['label'] = $userInfo['label'];
            $afterUserList[] = $addData;
        }

        return $afterUserList;
    }

    /**
     *
     * @param       $tempId
     * @param mixed $input
     *
     */
    private function tempUseEdit($tempId, array $input, $isEdit = true) {
        //判断当前是否拥有该权限
        //不是超级管理员
//        if (!request()->user->is_super) {
//            $siteName = getSiteName();
//            $currentSiteId = Site::query()->where("name", $siteName)->value("id");
//            $ruleIds = Rule::query()->whereIn("perm", ['products:template:normaledit', 'products:title:normaledit'])
//                           ->pluck("id")
//                           ->toArray();
//            if (!empty($ruleIds)) {
//                $currentUserId = request()->user->id;
//                $role_ids = User::query()->where('id', $currentUserId)->value('role_id');
//                $role_ids = explode(",", $role_ids);
//                $cnt = Role::query()->whereIn('id', $role_ids)
//                           ->where('status', 1)
//                           ->where('site_id', 'like', "%{$currentSiteId}%")
//                           ->where(function ($query) use ($ruleIds) {
//                               foreach ($ruleIds as $afRuleId) {
//                                   $query->orWhere('site_rule_id', 'like', "%{$afRuleId}%");
//                               }
//                           })->count();
//                //没权权限
//                if ($cnt <= 0) {
//                    return false;
//                }
//            } else {
//                //没配置权限
//                return false;
//            }
//        }
        //维护模版使用者
        TemplateUse::query()->where('temp_id', $tempId)->delete();
        if (!empty($input['use_user_ids'])) {
            $use_user_ids = $input['use_user_ids'];
            $use_user_ids = explode(",", $use_user_ids);
        } else {
            $use_user_ids = [];
            if (!$isEdit) {
                $use_user_ids[] = request()->user->id;
            }
        }
        foreach ($use_user_ids as $user_id) {
            $temp_use_data = [
                'temp_id' => $tempId,
                'user_id' => $user_id,
            ];
            TemplateUse::query()->create($temp_use_data);
        }
    }

    /**
     *
     * @param mixed $templateId
     * @param mixed $productId
     *
     * @return array|string|string[]
     */
    private function getTempContent(mixed $templateId, mixed $productId) {
        if (empty($templateId) || empty($productId)) {
            return '';
        }
        $template = Template::query()->find($templateId);
        $product = Products::query()->find($productId);
        if (empty($template) || empty($product)) {
            return '';
        }
        // $checkErr = $this->checkTempWarnRule($template, $product);
        // if ($checkErr) //查询模板描述数据
        // {
        //     return '';
        // }
        $year = date("Y", $product['published_date']);
        $pdModel = new ProductsDescription($year);
        $pdObj = $pdModel->where("product_id", $productId)->first();

        return $this->templateWirteData($template, $product, $pdObj);
    }

    public function checkFlagEmpty($flag, $temp_content, $product) {
        if (strpos($temp_content, $flag) !== false) {
            $temp_content = str_replace($flag, '', $temp_content);
            $product['keywords'] = '';
            $product['keywords_cn'] = '';
            $product['keywords_jp'] = '';
            $product['keywords_en'] = '';
            $product['keywords_kr'] = '';
            $product['keywords_de'] = '';
            $product['last_year'] = '';
            $product['this_year'] = '';
            $product['six_year'] = '';
            $product['cagr'] = '';
        }
    }

    private function checkTempWarnRule($template, $product) {
        $err = false;
        $template_content = $template['content'];
        if ($template['type'] == 1) {
            if (strpos($template_content, '@@@@') !== false && empty($product['keywords'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords}}') !== false && empty($product['keywords'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_cn}}') !== false && empty($product['keywords_cn'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_jp}}') !== false && empty($product['keywords_jp'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_en}}') !== false && empty($product['keywords_en'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_kr}}') !== false && empty($product['keywords_kr'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_de}}') !== false && empty($product['keywords_de'])) {
                return true;
            }
        } else {
            //标题模版
            if (strpos($template_content, '@@@@') !== false && empty($product['keywords'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords}}') !== false && empty($product['keywords'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_cn}}') !== false && empty($product['keywords_cn'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_jp}}') !== false && empty($product['keywords_jp'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_en}}') !== false && empty($product['keywords_en'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_kr}}') !== false && empty($product['keywords_kr'])) {
                return true;
            }
            if (strpos($template_content, '{{keywords_de}}') !== false && empty($product['keywords_de'])) {
                return true;
            }
            if (strpos($template_content, '{{last_year}}') !== false && empty($product['last_scale'])) {
                return true;
            }
            if (strpos($template_content, '{{this_year}}') !== false && empty($product['current_scale'])) {
                return true;
            }
            if (strpos($template_content, '{{six_year}}') !== false && empty($product['future_scale'])) {
                return true;
            }
            if (strpos($template_content, '{{cagr}}') !== false && empty($product['cagr'])) {
                return true;
            }
        }

        return false;
    }
}
