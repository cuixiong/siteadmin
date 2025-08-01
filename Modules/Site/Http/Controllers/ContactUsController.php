<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\City;
use Modules\Admin\Http\Models\Country;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Models\ContactUs;
use Modules\Site\Http\Models\MessageCategory;
use Modules\Site\Http\Models\MessageLanguageVersion;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\Region;
use Modules\Site\Http\Models\PriceEditionValue;
use Modules\Site\Http\Models\Language;
use Modules\Site\Http\Models\PostPlatform;
use Modules\Site\Http\Models\PostSubject;
use Modules\Site\Http\Models\PostSubjectLink;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ContactUsController extends CrudController {
    public $signKey = '62d9048a8a2ee148cf142a0e6696ab26';

    /**
     * 查询列表页
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function list(Request $request) {
        try {
            $this->ValidateInstance($request);
            $ModelInstance = $this->ModelInstance();
            $model = ContactUs::from('contact_us as cu');
            $model = $ModelInstance->HandleSearch($model, $request->search);
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
            $model = $model->select(['cu.*']);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy('cu.'.$request->order, $sort);
            } else {
                $model = $model->orderBy('cu.'.'sort', $sort)->orderBy('cu.'.'created_at', 'DESC');
            }
            $record = $model->get()->toArray();
            foreach ($record as &$value) {
                if (!empty($value['send_email_time'])) {
                    $value['send_email_time_str'] = date('Y-m-d H:i:s', $value['send_email_time']);
                } else {
                    $value['send_email_time_str'] = '';
                }
                if (!empty($value['product_id'])) {
                    $value['url'] = Products::query()->where('id', $value['product_id'])->value('url');
                } else {
                    $value['url'] = '';
                }
                // 价格版本
                $priceVersionName = '';
                if (!empty($value['price_edition'])) {
                    $priceEditionRecord = PriceEditionValue::query()->select(['name', 'language_id'])->where(
                        'id', $value['price_edition']
                    )->first();
                    if ($priceEditionRecord) {
                        $priceEditionData = $priceEditionRecord->toArray();
                        $languageName = Language::where('id', $priceEditionData['language_id'])->value('name');
                        $priceVersionName = (!empty($languageName) ? $languageName : '').' '
                                            .(!empty($priceEditionRecord['name']) ? $priceEditionRecord['name'] : '');
                    }
                }
                $value['price_edition_name'] = $priceVersionName;
            }
            // 平台列表
            $platformList = PostPlatform::query()->pluck("name", "id")->toArray();
            // 查询留言是否与课题有关联 (关键词关联、已宣传、留言创建时间大于任一帖子宣传时间)
            $productIdsArray = array_column($record, 'product_id');
            $productsData = Products::query()->select(['id', 'keywords', 'url'])->whereIn('id', $productIdsArray)->get()
                                    ?->toArray() ?? [];
            $keywordsData = array_column($productsData, 'keywords', 'id');
            $keywordsData = array_filter($keywordsData, function ($item) {
                return $item !== "" && $item !== null;
            });
            $productUrlData = array_column($productsData, 'url', 'id');
            $productUrlData = array_filter($productUrlData, function ($item) {
                return $item !== "" && $item !== null;
            });
            $postSubjectData = [];
            if ($keywordsData && count($keywordsData) > 0) {
                // 查询课题
                $postSubjectData = PostSubject::query()->select(['id', 'keywords', 'accepter'])
                                              ->whereIn('keywords', $keywordsData)
                                              ->where('propagate_status', 1)
                                              ->get()?->toArray() ?? [];
            }
            $urlData = [];
            if ($postSubjectData && count($postSubjectData) > 0) {
                // 课题查询帖子链接
                $postSubjecIdsArray = array_column($postSubjectData, 'id');
                $urlData = PostSubjectLink::query()
                                          ->select(['id', 'link', 'post_subject_id', 'post_platform_id', 'created_at'])
                                          ->whereIn('post_subject_id', $postSubjecIdsArray)
                                          ->get()->toArray();
                $urlData = array_map(function ($urlItem) use ($platformList) {
                    $urlItem['platform_name'] = $platformList[$urlItem['post_platform_id']] ?? '';

                    return $urlItem;
                }, $urlData);
            }
            // 重新排列一下课题与链接数据的结构
            $newUrlData = [];
            foreach ($urlData as $key => $item) {
                if (!isset($newUrlData[$item['post_subject_id']])) {
                    $newUrlData[$item['post_subject_id']] = [];
                }
                $newUrlData[$item['post_subject_id']][] = $item;
            }
            $newPostSubjectData = [];
            foreach ($postSubjectData as $key => $item) {
                if (!isset($newPostSubjectData[$item['keywords']])) {
                    $newPostSubjectData[$item['keywords']] = [];
                }
                $newPostSubjectData[$item['keywords']][] = $item;
            }
            // 给留言记录附上课题、帖子信息
            foreach ($record as $key => $item) {
                $record[$key]['referer_platform'] = $platformList[$item['referer_alias_id']] ?? '';
                $keywords = $keywordsData[$item['product_id']] ?? '';
                $record[$key]['keywords'] = $keywords;
                if (empty($keywords)) {
                    continue;
                }
                $record[$key]['product_url'] = $productUrlData[$item['product_id']] ?? '';
                $record[$key]['accepter_data'] = [];
                $record[$key]['url_data'] = [];
                $recordPostSubjectData = $newPostSubjectData[$keywords] ?? [];
                foreach ($recordPostSubjectData as $recordPostSubjectItem) {
                    $recordPostSubjectUrlData = $newUrlData[$recordPostSubjectItem['id']] ?? [];
                    $isBefore = false;
                    $tempUrlData = [];
                    foreach ($recordPostSubjectUrlData as $recordPostSubjectUrlItem) {
                        // 需要任一个帖子时间早于留言时间才能算中标
                        if (strtotime($recordPostSubjectUrlItem['created_at']) < strtotime($item['created_at'])) {
                            $isBefore = true;
                        }
                        $tempUrlData[] = [
                            // 'id' => $recordPostSubjectUrlItem['id'],
                            'link'             => $recordPostSubjectUrlItem['link'],
                            'post_subject_id'  => $recordPostSubjectUrlItem['post_subject_id'],
                            'post_platform_id' => $recordPostSubjectUrlItem['post_platform_id'],
                            'platform_name'    => $recordPostSubjectUrlItem['platform_name'],
                            'created_at'       => $recordPostSubjectUrlItem['created_at'],
                        ];
                    }
                    if ($isBefore) {
                        $record[$key]['url_data'] = array_merge($record[$key]['url_data'], $tempUrlData);
                        $record[$key]['accepter_data'][] = [
                            'post_subject_id' => $recordPostSubjectItem['id'],
                            'accepter'        => $recordPostSubjectItem['accepter'],
                            'accepter_name'   => User::query()->where('id', $recordPostSubjectItem['accepter'])->value(
                                    'nickname'
                                ) ?? '未知',
                            'keywords'        => $recordPostSubjectItem['keywords'],
                        ];
                    }
                }
            }
            $data = [
                'total' => $total,
                'list'  => $record
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function options(Request $request) {
        $options = [];
        $codes = ['Switch_State', 'Channel_Type', 'Buy_Time', 'Message_Is_Bidding'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)
                               ->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
            }
        }
        $options['categorys'] = (new MessageCategory)->GetListLabel(
            ['id as value', 'name as label'],
            false,
            '',
            ['status' => 1]
        );
        $options['language_version'] = (new MessageLanguageVersion())->GetListLabel(
            ['id as value', 'name as label'],
            false,
            '',
            ['status' => 1]
        );
        $options['country'] = Country::where('status', 1)->select('id as value', 'name as label')->orderBy(
            'sort',
            'asc'
        )->get()->toArray();
        $provinces = City::where(['status' => 1, 'type' => 1])->select('id as value', 'name as label')->orderBy(
            'id',
            'asc'
        )->get()->toArray();
        foreach ($provinces as $key => $province) {
            $cities = City::where(['status' => 1, 'type' => 2, 'pid' => $province['value']])->select(
                'id as value',
                'name as label'
            )->orderBy('id', 'asc')->get()->toArray();
            $provinces[$key]['children'] = $cities;
        }
        $options['city'] = $provinces;
        // 领取人/发帖用户
        $options['accepter'] = (new TemplateController())->getSitePostUser();
        //平台列表
        $options['post_platform'] = (new PostPlatform())->GetListLabel(
            ['id as value', 'name as label'],
            false,
            '',
            ['status' => 1]
        );
        array_unshift($options['post_platform'], ['label' => '未知', 'value' => 0]);
        //浏览器下拉列表
        $broswser_list = [
            'edge',
            'chrome',
            'firefox',
            'safari',
            'opr',
            'opr_legacy',
            'ie',
            'qqbrowser',
            'ucbrowser',
            'baidubox',
            'sogoumse',
            '360',
            'Unknown',
        ];
        foreach ($broswser_list as $for_browser) {
            $real_broswser_list[] = [
                'label' => $for_browser,
                'value' => $for_browser,
            ];
        }
        $options['broswser_list'] = $real_broswser_list;
        ReturnJson(true, '', $options);
    }

    /**
     * 修改状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeStatus(Request $request) {
        try {
            $ids = $request->ids;
            if (empty($ids)) {
                if (empty($request->id)) {
                    ReturnJson(false, 'id is empty');
                }
                $ids = [$request->id];
            } else {
                if (!is_array($ids)) {
                    $ids = explode(",", $ids);
                }
            }
            foreach ($ids as $id) {
                $record = $this->ModelInstance()->findOrFail($id);
                $record->status = $request->status;
                if (!$record->save()) {
                    ReturnJson(false, trans('lang.update_error'));
                }
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 批量修改下拉参数
     *
     * @param $request 请求信息
     */
    public function batchUpdateParam(Request $request) {
        $field = [
            [
                'name'  => '状态',
                'value' => 'status',
                'type'  => '2',
            ],
        ];
        array_unshift($field, ['name' => '请选择', 'value' => '', 'type' => '']);
        ReturnJson(true, trans('lang.request_success'), $field);
    }

    /**
     * 批量修改下拉参数子项
     *
     * @param $request 请求信息
     */
    public function batchUpdateOption(Request $request) {
        $input = $request->all();
        $keyword = $input['keyword'];
        $data = [];
        if ($keyword == 'status') {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data = (new DictionaryValue())->GetListLabel(
                $field,
                false,
                '',
                ['code' => 'Show_Home_State', 'status' => 1],
                ['sort' => 'ASC']
            );
        } elseif ($keyword == 'country_id') {
            $data = (new Region())->GetListLabel(
                ['id as value', 'name as label'],
                false,
                '',
                ['status' => 1],
                ['sort' => 'ASC']
            );
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 批量修改
     *
     * @param $request 请求信息
     */
    public function batchUpdate(Request $request) {
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $keyword = $input['keyword'] ?? '';
        $value = $input['value'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作
        $ModelInstance = $this->ModelInstance();
        $model = $ModelInstance->query();
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty').':ids');
            }
            $model = $ModelInstance->whereIn('id', $ids);
        } else {
            //筛选
            $model = $ModelInstance->HandleWhere($model, $request);
        }
        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            // $data['result_count'] = $model->update([$keyword => $value]);
            // 批量操作无法触发添加日志的功能，但我领导要求有日志
            $newIds = $model->pluck('id');
            foreach ($newIds as $id) {
                $record = $this->ModelInstance()->find($id);
                if ($record) {
                    $record->$keyword = $value;
                    $record->save();
                }
            }
            ReturnJson(true, trans('lang.update_success'));
        }
    }

    public function batchUpdateReferer(Request $request) {
        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '1024M');
        try {
            //所有平台
            $post_platform_list = PostPlatform::query()->where("status", 1)
                                              ->pluck('keywords', 'id')->toArray();
            $contact_us_list = ContactUs::query()->select(["id", "referer"])->get()->toArray();
            foreach ($contact_us_list as $for_contact_us){
                $for_referer = $for_contact_us['referer'] ?? '';
                $for_contact_id = $for_contact_us['id'];
                $aliasId = 0;
                if(!empty($for_referer )){
                    foreach ($post_platform_list as $forid => $forKeyword) {
                        if (strpos($for_referer, $forKeyword) !== false) {
                            $aliasId = $forid;
                            break;
                        }
                    }
                }
                ContactUs::query()->where("id" , $for_contact_id)
                    ->update(["referer_alias_id" => $aliasId]);
            }
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            \Log::error('返回结果数据:'.$e->getMessage());
            ReturnJson(false, '未知错误');
        }
    }

    /**
     *  重新发送邮件
     */
    public function againSendEmail(Request $request) {
        try {
            $ids = [];
            if (!empty($request->ids)) {
                if (is_array($request->ids)) {
                    $ids = $request->ids;
                } else {
                    $ids = [$request->ids];
                }
            } elseif (!empty($request->id)) {
                $ids = [$request->id];
            }
            $site = $request->header('site');
            $domain = Site::where('name', $site)->value("domain");
            if (empty($domain)) {
                ReturnJson(false, '站点配置异常');
            }
            if (strpos($domain, '://') === false) {
                $domain = 'https://'.$domain;
            }
            $url = $domain.'/api/third/send-email';
            $sucCnt = 0;
            $errMsg = [];
            $mcate_list = MessageCategory::query()->pluck('code', 'id')->toArray();
            foreach ($ids as $id) {
                $record = (new ContactUs())->findOrFail($id);
                if (empty($record)) {
                    continue;
                }
                //已支付与已完成
                $code = $mcate_list[$record->category_id];
                $reqData = [
                    'id'   => $id,
                    'code' => $code,
                ];
                $reqData['sign'] = $this->makeSign($reqData, $this->signKey);
                //\Log::error('返回结果数据:'.json_encode([$url, $reqData]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
                $response = Http::post($url, $reqData);
                $resp = $response->json();
                if (!empty($resp) && $resp['code'] == 200) {
                    $sucCnt++;
                } else {
                    $errMsg[] = $resp;
                }
            }
            if (empty($errMsg)) {
                ReturnJson(true, "发送成功:{$sucCnt}次");
            } else {
                \Log::error('返回结果数据:'.json_encode($errMsg));
                ReturnJson(false, '发送失败,未知错误');
            }
        } catch (\Exception $e) {
            ReturnJson(true, '未知错误');
        }
    }

    public function makeSign($data, $signkey) {
        unset($data['sign']);
        $signStr = '';
        ksort($data);
        foreach ($data as $key => $value) {
            $signStr .= $key.'='.$value.'&';
        }
        $signStr .= "key={$signkey}";

        //dump($signStr);
        return md5($signStr);
    }

    /**
     * 导出留言
     */
    public function export(Request $request) {
        ini_set('max_execution_time', '0'); // no time limit，不设置超时时间（根据实际情况使用）
        ini_set("memory_limit", '-1'); // 不限制内存
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作
        $model = ContactUs::query();
        if ($ids) {
            //选中
            $ids = explode(',', $ids);
            if (!(count($ids) > 0)) {
                ReturnJson(true, trans('lang.param_empty').':ids');
            }
            $model = $model->whereIn('id', $ids);
        } else {
            //筛选
            $search = $request->input('search') ?? [];
            $model = (new ContactUs())->HandleSearch($model, $search);
        }
        $data = [];
        if ($type == 1) {
            // 总数量
            $data['count'] = $model->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } else {
            // //查询出涉及的id
            // $idsData = $model->select('id')->pluck('id')->toArray();
            $filterData = $model->orderBy('id', 'desc')->get()->toArray();
            if (!(count($filterData) > 0)) {
                ReturnJson(true, trans('lang.data_empty'));
            }
            // 平台列表
            $platformList = PostPlatform::query()->pluck("name", "id")->toArray();
            $productIdsArray = array_unique(array_column($filterData, 'product_id'));
            $productsData = Products::query()->select(['id', 'keywords'])->whereIn('id', $productIdsArray)->get()
                                    ?->toArray() ?? [];
            $keywordsData = array_column($productsData, 'keywords', 'id');
            $keywordsData = array_filter($keywordsData, function ($item) {
                return $item !== "" && $item !== null;
            });
            $postSubjectData = [];
            if ($keywordsData && count($keywordsData) > 0) {
                // 查询课题
                $postSubjectData = PostSubject::query()->select(['id', 'keywords', 'accepter'])
                                              ->whereIn('keywords', $keywordsData)
                                              ->where('propagate_status', 1)
                                              ->get()?->toArray() ?? [];
            }
            $urlData = [];
            if ($postSubjectData && count($postSubjectData) > 0) {
                // 课题查询帖子链接
                $postSubjecIdsArray = array_column($postSubjectData, 'id');
                $urlData = PostSubjectLink::query()
                                          ->select(['id', 'link', 'post_subject_id', 'post_platform_id', 'created_at'])
                                          ->whereIn('post_subject_id', $postSubjecIdsArray)
                                          ->get()->toArray();
                $urlData = array_map(function ($urlItem) use ($platformList) {
                    $urlItem['platform_name'] = $platformList[$urlItem['post_platform_id']] ?? '';

                    return $urlItem;
                }, $urlData);
            }
            // 重新排列一下课题与链接数据的结构
            $newUrlData = [];
            foreach ($urlData as $key => $item) {
                if (!isset($newUrlData[$item['post_subject_id']])) {
                    $newUrlData[$item['post_subject_id']] = [];
                }
                $newUrlData[$item['post_subject_id']][] = $item;
            }
            $newPostSubjectData = [];
            foreach ($postSubjectData as $key => $item) {
                if (!isset($newPostSubjectData[$item['keywords']])) {
                    $newPostSubjectData[$item['keywords']] = [];
                }
                $newPostSubjectData[$item['keywords']][] = $item;
            }
        }
        $date = date('Ymd', time());
        $domain = env('APP_DOMAIN');
        $site = request()->header("Site");
        $excelHeader = [
            '编号',
            '分类',
            '报告名称',
            '关键词',
            '用户',
            '邮箱',
            '公司',
            '部门',
            '电话',
            '购买时间',
            '价格版本',
            '语言版本',
            '渠道',
            '地区(国家)',
            '地区(省市)',
            '创建时间',
            '内容',
            '领取人',
            '平台链接',
            '浏览器',
            '来源',
        ];
        // 创建 Spreadsheet 对象
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // 设置标题行
        $sheet->fromArray([$excelHeader], null, 'A1');
        // 填充数据
        $rowIndex = 1;
        $sheet->getColumnDimension('A')->setWidth(10);  // 设置 A 列宽度
        $sheet->getColumnDimension('B')->setWidth(15);  // 设置 B 列宽度
        $sheet->getColumnDimension('C')->setWidth(30);  // 设置 C 列宽度
        $sheet->getColumnDimension('D')->setWidth(15);  // 设置 D 列宽度
        $sheet->getColumnDimension('E')->setWidth(15);  // 设置 E 列宽度
        $sheet->getColumnDimension('F')->setWidth(20);  // 设置 F 列宽度
        $sheet->getColumnDimension('G')->setWidth(20);  // 设置 G 列宽度
        $sheet->getColumnDimension('H')->setWidth(10);  // 设置 H 列宽度
        $sheet->getColumnDimension('I')->setWidth(20);  // 设置 I 列宽度
        $sheet->getColumnDimension('J')->setWidth(10);  // 设置 J 列宽度
        $sheet->getColumnDimension('K')->setWidth(10);  // 设置 K 列宽度
        $sheet->getColumnDimension('L')->setWidth(10);  // 设置 L 列宽度
        $sheet->getColumnDimension('M')->setWidth(15);
        $sheet->getColumnDimension('N')->setWidth(15);
        $sheet->getColumnDimension('O')->setWidth(20);
        $sheet->getColumnDimension('P')->setWidth(60);
        $sheet->getColumnDimension('Q')->setWidth(30);
        $sheet->getColumnDimension('R')->setWidth(60);
        $sheet->getColumnDimension('S')->setWidth(30);
        $sheet->getColumnDimension('T')->setWidth(30);
        foreach ($filterData as $item) {
            $keywords = $keywordsData[$item['product_id']] ?? '';
            $sheet->setCellValue([1, $rowIndex + 1], $item['id']); // ID
            $sheet->setCellValue([2, $rowIndex + 1], $item['category_name'] ?? '');  // 分类
            $sheet->setCellValue([3, $rowIndex + 1], $item['product_name'] ?? '');  // 报告名称
            $sheet->setCellValue([4, $rowIndex + 1], $keywords);  // 关键词
            $sheet->setCellValue([5, $rowIndex + 1], $item['name'] ?? '');    // 用户
            $sheet->setCellValue([6, $rowIndex + 1], $item['email'] ?? '');
            $sheet->setCellValue([7, $rowIndex + 1], $item['company'] ?? '');
            $sheet->setCellValue([8, $rowIndex + 1], $item['department'] ?? '');
            $sheet->setCellValue([9, $rowIndex + 1], $item['phone'] ?? '');
            $sheet->setCellValue([10, $rowIndex + 1], $item['buy_time'] ?? '');
            // 价格版本
            $priceVersionName = '';
            if (!empty($item['price_edition'])) {
                $priceEditionRecord = PriceEditionValue::query()->select(['name', 'language_id'])->where(
                    'id', $item['price_edition']
                )->first();
                if ($priceEditionRecord) {
                    $priceEditionData = $priceEditionRecord->toArray();
                    $languageName = Language::where('id', $priceEditionData['language_id'])->value('name');
                    $priceVersionName = (!empty($languageName) ? $languageName : '').' '
                                        .(!empty($priceEditionRecord['name']) ? $priceEditionRecord['name'] : '');
                }
            }
            $sheet->setCellValue([11, $rowIndex + 1], $priceVersionName ?? '');
            $sheet->setCellValue([12, $rowIndex + 1], $item['language_version'] ?? '');
            $sheet->setCellValue([13, $rowIndex + 1], $item['channel_name'] ?? '');
            $countryName = '';
            if (!empty($item['country_id'])) {
                $countryName = Country::getCountryName($item['country_id']);
            }
            $sheet->setCellValue([14, $rowIndex + 1], $countryName);
            $provinceName = '';
            if (!empty($item['province_id'])) {
                $provinceName = City::query()->where('id', $item['province_id'])->value('name');
            }
            $cityName = '';
            if (!empty($item['city_id'])) {
                $cityName = City::query()->where('id', $item['city_id'])->value('name');
            }
            $sheet->setCellValue([15, $rowIndex + 1], $provinceName.' '.$cityName);
            $sheet->setCellValue([16, $rowIndex + 1], $item['created_at'] ?? '');
            $sheet->setCellValue([17, $rowIndex + 1], $item['content'] ?? '');
            if (!empty($keywords)) {
                $recordPostSubjectData = $newPostSubjectData[$keywords] ?? [];
                foreach ($recordPostSubjectData as $recordPostSubjectKey => $recordPostSubjectItem) {
                    $recordPostSubjectUrlData = $newUrlData[$recordPostSubjectItem['id']] ?? [];
                    $isBefore = false;
                    $tempUrlData = [];
                    foreach ($recordPostSubjectUrlData as $recordPostSubjectUrlItem) {
                        // 需要任一个帖子时间早于留言时间才能算中标
                        if (strtotime($recordPostSubjectUrlItem['created_at']) < strtotime($item['created_at'])) {
                            $isBefore = true;
                        }
                        $tempUrlData[] = [
                            // 'id' => $recordPostSubjectUrlItem['id'],
                            'link'             => $recordPostSubjectUrlItem['link'],
                            'post_subject_id'  => $recordPostSubjectUrlItem['post_subject_id'],
                            'post_platform_id' => $recordPostSubjectUrlItem['post_platform_id'],
                            'platform_name'    => $recordPostSubjectUrlItem['platform_name'],
                            'created_at'       => $recordPostSubjectUrlItem['created_at'],
                        ];
                    }
                    if ($isBefore) {
                        if ($recordPostSubjectKey > 0) {
                            $rowIndex++;
                        }
                        $accepterName = User::query()->where('id', $recordPostSubjectItem['accepter'])->value(
                            'nickname'
                        ) ?? '未知';
                        if (!empty($recordPostSubjectItem['id'])) {
                            $subjectUpdateLink = $domain.'/#/'.$site.'/postTopicList/EditPostTopic?id='
                                                 .$recordPostSubjectItem['id'];
                        } else {
                            $subjectUpdateLink = '';
                        }
                        // 添加领取人,链接是课题链接
                        $sheet->setCellValue([18, $rowIndex + 1], $accepterName);
                        $sheet->getCell([18, $rowIndex + 1])->getHyperlink()->setUrl($subjectUpdateLink);
                        $sheet->getStyle([18, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB(
                            '0000FF'
                        );
                        // 添加平台,链接是帖子链接
                        foreach ($tempUrlData as $tempUrlKey => $tempUrlItem) {
                            if ($tempUrlKey > 0) {
                                $rowIndex++;
                            }
                            $sheet->setCellValue([19, $rowIndex + 1], $tempUrlItem['platform_name']);
                            $sheet->getCell([19, $rowIndex + 1])->getHyperlink()->setUrl($tempUrlItem['link']);
                            $sheet->getStyle([19, $rowIndex + 1])->getFont()->setUnderline(true)->getColor()->setARGB(
                                '0000FF'
                            );
                        }
                    }
                }
            }
            if (!empty($item['referer_alias_id'])) {
                $referer_platform = $platformList[$item['referer_alias_id']] ?? '';
            } else {
                $referer_platform = $item['referer'] ?? '';
            }
            if ($item['ua_browser_name'] == 'Unknown') {
                $ua_browser = $item['ua_info'] ?? '';
            } else {
                $ua_browser = $item['ua_browser_name'] ?? '';
            }
            $sheet->setCellValue([20, $rowIndex + 1], $ua_browser);
            $sheet->setCellValue([21, $rowIndex + 1], $referer_platform);
            $rowIndex++;
        }
        // 设置 HTTP 头部并导出文件
        $date = date('Ymd');
        $filename = 'export-messages-'.count($filterData).'-'.$date.'.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
