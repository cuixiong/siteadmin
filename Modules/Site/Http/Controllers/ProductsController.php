<?php

namespace Modules\Site\Http\Controllers;

use App\Const\QueueConst;
use App\Exports\ProductsExport;
use App\Jobs\ExportProduct;
use App\Jobs\HandlerExportExcel;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Foolz\SphinxQL\SphinxQL;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Admin\Http\Models\Server;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\ListStyle;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\Publisher;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsDescription;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsUploadLog;
use Modules\Site\Http\Models\Region;
use Modules\Site\Http\Models\ProductsExcelField;
use Modules\Site\Http\Models\ProductsExportLog;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Modules\Site\Http\Models\SensitiveWords;
use Modules\Site\Http\Models\SystemValue;
use Modules\Site\Http\Models\Template;
use Modules\Site\Http\Models\TemplateCategory;
use Modules\Site\Http\Models\TemplateCateMapping;
use Modules\Site\Services\SenWordsService;
use XS;

class ProductsController extends CrudController {
    public $tcList                  = [];
    public $dictList                = [];
    public $templateCateMappingList = [];
    public $templateList            = [];
    public $categpryName            = [];

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
            $fields = ['id', 'name', 'publisher_id', 'english_name', 'country_id', 'category_id', 'price', 'created_at',
                       'published_date', 'author', 'show_hot', 'show_recommend', 'status', 'sort', 'discount',
                       'discount_amount', 'discount_type', 'discount_time_begin', 'discount_time_end', 'url'];
            $model = $model->select($fields);
            // 数据排序
            $sort = (strtoupper($request->sort) == 'DESC') ? 'DESC' : 'ASC';
            if (!empty($request->order)) {
                $model = $model->orderBy($request->order, $sort);
            } else {
                $model = $model->orderBy('sort', $sort)->orderBy('id', 'DESC');
            }
            $record = $model->get();
            $data = [
                'total'       => $total,
                'list'        => $record,
                'headerTitle' => [],
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 快速搜索
     *
     * @param       $request  请求信息
     * @param int   $page     页码
     * @param int   $pageSize 页数
     * @param Array $where    查询条件数组 默认空数组
     */
    protected function QuickSearch(Request $request) {
        try {
            $data = $this->GetProductList($request);
            $record = $data['list'];
            $product_id_list = array_column($record, 'id');
            $updAtList = DB::table("product_routine")->whereIn('id', $product_id_list)
                           ->pluck('updated_at', 'id')
                           ->toArray();
            $updAtList = (array)$updAtList;
            $total = $data['total'];
            $type = '当前查询方式是：'.$data['type'];
            $this->beforeMatchTemplateData();
            foreach ($record as $key => $item) {
                $productId = $item['id'];
                $record[$key]['published_date'] = date('Y-m-d', $item['published_date']);
                $record[$key]['category_name'] = $this->categpryName[$item['category_id']];
                //$descriptionData = $productsModel->findDescCache($item['id']);
                //根据描述匹配 模版分类
                $year = date('Y', $item['published_date']);
                $description = (new ProductsDescription($year))->where("product_id" , $productId)->value('description');
                //$description = $item['description'] ?? '';
                $templateData = $this->matchTemplateData($description);
                $record[$key]['template_data'] = $templateData;
                $baseTimestamp = $updAtList[$productId] ?? 0;
                $record[$key]['updated_at'] = date("Y-m-d H:i:s", $baseTimestamp);
                //删除描述
                unset($record[$key]['description']);
            }
            //$record = mb_convert_encoding($record, "UTF-8");
            $data = [
                'total'       => $total,
                'list'        => $record,
                'headerTitle' => [],
                'type'        => $type
            ];
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function GetProductList($request) {
        try {
            $hidden = SystemValue::where('key', 'xunsearch')->value('hidden');
            $hidden = 1;
            if ($hidden == 1) {
                //return $this->SearchForXunsearch($request);
                return $this->SearchForSphinx($request);
            } else {
                return $this->SearchForMysql($request);
            }
        } catch (\Exception $e) {
            // return $this->SearchForMysql($request);
            ReturnJson(false, $e->getMessage());
        }
    }

    private function SearchForMysql($request) {
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
            $model = $model->orderBy('sort', $sort)->orderBy('id', 'DESC');
        }
        $record = $model->get();

        return ['list' => $record, 'total' => $total, 'type' => 'mysql'];
    }

    private function SearchForXunsearch($request) {
        $SiteName = $request->header('Site');
        $RootPath = base_path();
        $xs = new XS($RootPath.'/Modules/Site/Config/xunsearch/'.$SiteName.'.ini');
        $search = $xs->search;
        if (!empty($request->type)) {
            $type = $request->type;
            $keyword = $request->keyword;
        } elseif (!empty($request->input('search'))) {
            $searchData = json_decode($request->input('search'), true);
            if (!empty($searchData) && is_array($searchData)) {
                $type = key($searchData);
                $keyword = current($searchData);
            }
        }
        if (empty($type)) {
            throw new \Exception('参数异常:搜索类型不能为空');
        }
        if (!empty($type) && (!isset($keyword) || $keyword == '')) {
            $sorts = ['sort' => false, 'published_date' => false];
            $search->setMultiSort($sorts);
            $search->setQuery('');
        } elseif (filled($keyword)
                  && in_array(
                      $type,
                      ['id', 'category_id', 'author', 'country_id', 'price', 'discount', 'discount_amount', 'show_hot',
                       'show_recommend', 'status']
                  )
        ) {
            $keyword = $type.':'.$keyword;
            //排序字段倒序 , 发布日期倒序
            $sorts = ['sort' => false, 'published_date' => false];
            $search->setMultiSort($sorts);
            $search->setQuery($keyword);
        } else if (!empty($type) && in_array($type, ['created_at', 'published_date']) && $keyword) {
            // 设置搜索排序
            $sorts = array('published_date' => false);
            $search->setMultiSort($sorts);
            $search->setQuery('')->addRange($type, $keyword[0], $keyword[1]);
        } else if ($type == 'name') {
            //中文搜索, 测试明确 需要精确搜索
            //$queryWords = 'name:"'.$keyword.'"';
            $splitKeyword = implode(' ', preg_split('//u', $keyword, null, PREG_SPLIT_NO_EMPTY));
            $queryWords = "name:{$splitKeyword}";
            $search->setQuery($queryWords);
        } elseif ($type == 'english_name') {
            //英文搜索, 需要精确搜索
            //$search->setFuzzy()->setQuery($keyword);
            $sorts = ['sort' => false, 'published_date' => false];
            $search->setMultiSort($sorts);
            $queryWords = 'english_name:"'.$keyword.'"';
            $search->setQuery($queryWords);
        } elseif (empty($type) && empty($keyword)) {
            $sorts = ['sort' => false, 'published_date' => false];
            $search->setMultiSort($sorts);
            $search->setQuery('');
        }
        //不是状态搜索, 状态隐藏不显示
//        if ($type != 'status') {
//            $search->addRange('status', 1, 1);
//        }
        //查询结果分页
        $docs = $search->search();
        $count = $search->count();
        $search->setLimit($request->pageSize, ($request->pageNum - 1) * $request->pageSize);
        $products = [];
        if (!empty($docs)) {
            foreach ($docs as $key => $doc) {
                $product = [
                    'id'              => $doc->id,
                    'name'            => $doc->name,
                    'english_name'    => $doc->english_name,
                    'category_id'     => $doc->category_id,
                    'country_id'      => $doc->country_id,
                    'price'           => $doc->price,
                    'keywords'        => $doc->keywords,
                    'url'             => $doc->url,
                    'published_date'  => $doc->published_date,
                    'status'          => $doc->status,
                    'author'          => $doc->author,
                    'discount'        => $doc->discount,
                    'discount_amount' => $doc->discount_amount,
                    'show_hot'        => $doc->show_hot,
                    'show_recommend'  => $doc->show_recommend,
                    'sort'            => $doc->sort,
                    'description'     => $doc->description,
                ];
                $products[] = $product;
            }
            $data = [
                'list'  => $products,
                'total' => $count,
                'type'  => 'xunsearch'
            ];

            return $data;
        } else {
            // return $this->SearchForMysql($request);
            return $data = [
                'list'  => [],
                'total' => 0,
                'type'  => 'xunsearch'
            ];
        }
    }

    public function SearchForSphinx($request) {
        // TODO: cuizhixiong 2024/5/31 后续多个站点读取配置,连接搜索服务
//        $SiteName = $request->header('Site');
//        $siteInfo = Site::where('name', $SiteName)->firstOrFail();
//        $server_id = $siteInfo->server_id;
//        $serverInfo = Server::find($server_id);
        $comParams = array('host' => '39.108.67.106', 'port' => 9306);
        if (!empty($request->type)) {
            $type = $request->type;
            $keyword = $request->keyword;
        } elseif (!empty($request->input('search'))) {
            $searchData = json_decode($request->input('search'), true);
            if (!empty($searchData) && is_array($searchData)) {
                $type = key($searchData);
                $keyword = current($searchData);
            }
        }
        if (empty($type)) {
            throw new \Exception('参数异常:搜索类型不能为空');
        }
        $conn = new Connection();
        $conn->setParams($comParams);
        $query = (new SphinxQL($conn))->select('*')
                                      ->from('products_rt')
                                      ->orderBy('sort', 'asc')
                                      ->orderBy('published_date', 'desc');
        if (!empty($type) && (!isset($keyword) || $keyword == '')) {
            // TODO: cuizhixiong 2024/5/31
        } elseif (filled($keyword)
                  && in_array(
                      $type,
                      ['id', 'category_id', 'country_id', 'price', 'discount', 'discount_amount', 'show_hot',
                       'show_recommend', 'status']
                  )
        ) {
            $query = $query->where($type, intval($keyword));
        } elseif (filled($keyword) && in_array($type, ['author'])
        ) {
            $query = $query->where($type, $keyword);
        } else if (!empty($type) && in_array($type, ['created_at', 'published_date']) && $keyword) {
            // 设置搜索排序
            $start_time = $keyword[0] ?? 0;
            $end_time = $keyword[1] ?? 0;
            $query = $query->where($type, 'BETWEEN', [intval($start_time), intval($end_time)]);
        } else if ($type == 'name') {
            //中文搜索, 测试明确 需要精确搜索
            $val = '"'.$keyword.'"';
            $query = $query->match($type, $val, true);
        } elseif ($type == 'english_name') {
            //英文搜索, 需要精确搜索
            $query = $query->match($type, $keyword);
        }
        if ($type != 'status') {
            $query = $query->where('status', '=', 1);
        }
        //查询总数
        $countQuery = $query->setSelect('COUNT(*) as cnt');
        $fetchNum = $countQuery->execute()->fetchNum();
        $count = $fetchNum[0] ?? 0;
        //查询结果分页
        $query->limit(($request->pageNum - 1) * $request->pageSize, $request->pageSize);
        $query->setSelect('*');
        $result = $query->execute();
        $products = $result->fetchAllAssoc();
        $data = [
            'list'  => $products,
            'total' => intval($count),
            'type'  => 'sphinx'
        ];

        return $data;
    }

    /**
     * 创建报告
     *
     * @param Request $request
     */
    protected function store(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $input['published_date'] = is_numeric($input['published_date'])
                ? $input['published_date']
                : strtotime(
                    $input['published_date']
                );
            // 开启事务
            // $currentTenant = tenancy()->tenant;
            // DB::connection($currentTenant->getConnectionName())->beginTransaction();
            DB::beginTransaction();
            if (empty($input['sort'])) {
                $input['sort'] = 100;
            }
            if (empty($input['hits'])) {
                $input['hits'] = rand(500, 1000);
            }
            if (empty($input['downloads'])) {
                $input['downloads'] = rand(100, 300);
            }
            $record = $this->ModelInstance()->create($input);
            if (!$record) {
                throw new \Exception(trans('lang.add_error'));
            }
            $year = Products::publishedDateFormatYear($input['published_date']);
            if (!$year) {
                throw new \Exception(trans('lang.add_error').':published_date');
            }
            $productDescription = new ProductsDescription($year);
            $input['product_id'] = $record->id;
            $descriptionRecord = $productDescription->saveWithAttributes($input);
            if (!$descriptionRecord) {
                throw new \Exception(trans('lang.add_error'));
            }
            // DB::connection($currentTenant->getConnectionName())->commit();
            DB::commit();
            // 创建完成后同步到xunsearch
            $this->ModelInstance()->syncSearchIndex($record->id, 'add');
            ReturnJson(true, trans('lang.add_success'), ['id' => $record->id]);
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            // 建表时无法回滚
            // DB::connection($currentTenant->getConnectionName())->rollBack();
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 判断已存在，返回id
     *
     * @param Request $request
     */
    protected function isExist(Request $request) {
        try {
            $name = $request->name;
            if (!isset($name) || empty(trim($name))) {
                ReturnJson(true, trans('lang.request_success'), '');
            }
            $id = $this->ModelInstance()->where(['name' => trim($name)])->value('id');
            if ($id) {
                ReturnJson(true, trans('lang.request_success'), $id);
            } else {
                ReturnJson(true, trans('lang.request_success'), '');
            }
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function checkSensitiveWord(Request $request) {
        try {
            $name = $request->name;
            if (!isset($name) || empty(trim($name))) {
                ReturnJson(true, trans('lang.request_success'), '');
            }
            $checkRes = SenWordsService::checkNewFitter($name);
            if ($checkRes) {
                ReturnJson(false, trans('lang.exist_sensitive_words')." ['{$checkRes}']");
            } else {
                ReturnJson(true, trans('lang.request_success'));
            }
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function handlerSenWordsProduct(Request $request) {
        try {
            //检测数据库 报告昵称已存在的敏感词, 需要筛选出来 , 然后关闭状态,  然后删除索引
            $sensitiveWordsList = SenWordsService::getSenWords();

            $productIdList = Products::query()->orWhere(function ($query) use ($sensitiveWordsList) {
                foreach ($sensitiveWordsList as $value) {
                    $query->orWhere('english_name', 'like', "%{$value}%");
                }
            })->orWhere(function ($query) use ($sensitiveWordsList) {
                foreach ($sensitiveWordsList as $value) {
                    $query->orWhere('url', 'like', "%{$value}%");
                }
            })->orWhere(function ($query) use ($sensitiveWordsList) {
                foreach ($sensitiveWordsList as $value) {
                    $query->orWhere('name', 'like', "%{$value}%");
                }
            })->pluck('id')->toArray();

            $res = Products::query()->whereIn('id', $productIdList)->update(['status' => 0]);
            if (!empty($productIdList)) {
                //删除sphinx的索引
                //实例化
                $comParams = array('host' => '39.108.67.106', 'port' => 9306);
                $conn = new Connection();
                $conn->setParams($comParams);
                $res = (new SphinxQL($conn))->delete()->from('products_rt')->where("id", 'in', $productIdList)->execute();
//                $SiteName = $request->header('Site');
//                $RootPath = base_path();
//                $xs = new XS($RootPath.'/Modules/Site/Config/xunsearch/'.$SiteName.'.ini');
//                $index = $xs->index;
//                $index->openBuffer(); // 开启缓冲区，默认 4MB，如 $index->openBuffer(8) 则表示 8MB
//                // 在此进行批量的删除操作
//                $index->del($productIdList);
//                $index->closeBuffer(); // 关闭缓冲区，必须和 openBuffer 成对使用
//                $index->flushIndex();
            }
            ReturnJson(true, trans('lang.request_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getSenWordsProductCnt() {
        try {
            //状态为1的  匹配敏感词报告数量
            $sensitiveWordsList = SenWordsService::getSenWords();
            $cnt = Products::query()->where("status", 1)->where(function ($query) use ($sensitiveWordsList) {
                foreach ($sensitiveWordsList as $value) {
                    $query->orWhere('name', 'like', "%{$value}%");
                }
            })->count();
            ReturnJson(true, trans('lang.request_success'), ['cnt' => $cnt]);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单个查询
     *
     * @param $request 请求信息
     */
    protected function form(Request $request) {
        try {
            $this->ValidateInstance($request);
            $record = $this->ModelInstance()->findOrFail($request->id);
            $year = date('Y', $record['published_date']);
            $descriptionData = (new ProductsDescription($year))->where('product_id', $record['id'])->first();
            if (!empty($descriptionData)) {
                $record['description'] = $descriptionData['description'] ?? '';
                $record['table_of_content'] = $descriptionData['table_of_content'] ?? '';
                $record['tables_and_figures'] = $descriptionData['tables_and_figures'] ?? '';
                $record['description_en'] = $descriptionData['description_en'] ?? '';
                $record['table_of_content_en'] = $descriptionData['table_of_content_en'] ?? '';
                $record['tables_and_figures_en'] = $descriptionData['tables_and_figures_en'] ?? '';
                $record['companies_mentioned'] = $descriptionData['companies_mentioned'] ?? '';
                $record['definition'] = $descriptionData['definition'] ?? '';
                $record['overview'] = $descriptionData['overview'] ?? '';
            } else {
                $record['description'] = '';
                $record['table_of_content'] = '';
                $record['tables_and_figures'] = '';
                $record['description_en'] = '';
                $record['table_of_content_en'] = '';
                $record['tables_and_figures_en'] = '';
                $record['companies_mentioned'] = '';
                $record['definition'] = '';
                $record['overview'] = '';
            }
            ReturnJson(true, trans('lang.request_success'), $record);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 更新报告
     *
     * @param $request 请求信息
     */
    protected function update(Request $request) {
        try {
            $this->ValidateInstance($request);
            $input = $request->all();
            $input['published_date'] = is_numeric($input['published_date'])
                ? $input['published_date']
                : strtotime(
                    $input['published_date']
                );
            // 开启事务
            // $currentTenant = tenancy()->tenant;
            // DB::connection($currentTenant->getConnectionName())->beginTransaction();
            DB::beginTransaction();
            $model = $this->ModelInstance();
            $record = $model->findOrFail($input['id']);
            //旧纪录年份
            $oldYear = Products::publishedDateFormatYear($record->published_date);
            //新纪录年份
            $newYear = Products::publishedDateFormatYear($input['published_date']);
            // return $oldYear;
            if (empty($input['sort'])) {
                $input['sort'] = 100;
            }
            if (empty($input['hits'])) {
                $input['hits'] = rand(500, 1000);
            }
            if (empty($input['downloads'])) {
                $input['downloads'] = rand(100, 300);
            }
            if (!$record->update($input)) {
                throw new \Exception(trans('lang.update_error'));
            }
            $input['product_id'] = $record->id;
            $newProductDescription = (new ProductsDescription($newYear));
            //出版时间年份更改
            if ($oldYear != $newYear) {
                //删除旧详情
                if ($oldYear) {
                    $oldProductDescription = (new ProductsDescription($oldYear))->where('product_id', $record->id)
                                                                                ->first();
                    $oldProductDescription->delete();
                }
                //然后新增
                $descriptionRecord = $newProductDescription->saveWithAttributes($input);
            } else {
                $newProductDescription = $newProductDescription->where('product_id', $record->id)->first();
                if ($newProductDescription) {
                    //直接更新
                    $descriptionRecord = $newProductDescription->updateWithAttributes($input);
                } else {
                    //不存在新增
                    $descriptionRecord = (new ProductsDescription($newYear))->saveWithAttributes($input);
                }
            }
            if (!$descriptionRecord) {
                throw new \Exception(trans('lang.update_error'));
            }
            DB::commit();
            // 更新完成后同步到xunsearch
            $this->ModelInstance()->syncSearchIndex($record->id, 'update');
            // DB::connection($currentTenant->getConnectionName())->commit();
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            // DB::connection($currentTenant->getConnectionName())->rollBack();
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * AJax单行删除
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
                if (!empty($record)) {
                    $year = Products::publishedDateFormatYear($record->published_date);
                    if ($year) {
                        $recordDescription = (new ProductsDescription($year))->where('product_id', $record->id);
                    }
                    if ($recordDescription) {
                        $recordDescription->delete();
                    }
                    $record->delete();
                    // 删除完成后同步到xunsearch
                    $this->ModelInstance()->syncSearchIndex($id, 'delete');
                }
            }
            ReturnJson(true, trans('lang.delete_success'));
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
            //分类
            $data['category'] = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], true,
                                                                  'pid', ['status' => 1]);
            //国家地区 region
            $data['country'] = (new Region())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1]
            );
            //出版商
            $site = $request->header('Site');
            $publisherIds = Site::where('name', $site)->value('publisher_id');
            $data['publisher'] = [];
            if ($publisherIds) {
                $publisherIdArray = explode(',', $publisherIds);
                $data['publisher'] = (new Publisher())->GetListLabel(['id as value', 'name as label'], false, '',
                                                                     ['status' => 1, 'id' => $publisherIdArray]);
            }
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            //显示首页/热门/推荐
            $data['show_home'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Show_Home_State', 'status' => 1], ['sort' => 'ASC']
            );
            $data['show_hot'] = $data['show_home'];
            $data['show_recommend'] = $data['show_home'];
            $data['have_sample'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Has_Sample', 'status' => 1], ['sort' => 'ASC']
            );
            // 状态开关
            $data['status'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Switch_State', 'status' => 1], ['sort' => 'ASC']
            );
            // 折扣
            $data['discount_type'] = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Discount_Type', 'status' => 1], ['sort' => 'ASC']
            );
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 修改基础价
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changePrice(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->price = $request->price;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            // 更新完成后同步到xunsearch
            $this->ModelInstance()->syncSearchIndex($record->id, 'update');
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 热门开关
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeHot(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->show_hot = $request->show_hot;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            // 更新完成后同步到xunsearch
            $this->ModelInstance()->syncSearchIndex($record->id, 'update');
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 精品开关
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeRecommend(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->show_recommend = $request->show_recommend;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            // 更新完成后同步到xunsearch
            $this->ModelInstance()->syncSearchIndex($record->id, 'update');
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 修改分类折扣
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function discount(Request $request) {
        try {
            $this->ValidateInstance($request);
            $record = $this->ModelInstance()->findOrFail($request->id);
            $type = $request->discount_type;
            $value = $request->discount_value;
            $record->discount_type = $type;
            if ($type == 1) {
                $record->discount = $value;
                $record->discount_amount = 0;
            } elseif ($type == 2) {
                $record->discount = 100;
                $record->discount_amount = $value;
            }
            // else {
            //     throw new \Exception(trans('lang.update_error') . ':discount_type is out of range');
            // }
            //可能恢复原价
            if ($type == 1 && $value == 100) {
                $record->discount_time_begin = null;
                $record->discount_time_end = null;
            } elseif ($type == 2 && $value == 0) {
                $record->discount_time_begin = null;
                $record->discount_time_end = null;
            } else {
                $record->discount_time_begin = $request->discount_time_begin;
                $record->discount_time_end = $request->discount_time_end;
            }
            //验证
            request()->offsetSet('discount', $record->discount);
            request()->offsetSet('discount_amount', $record->discount_amount);
            $this->ValidateInstance($request);
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            $this->ModelInstance()->syncSearchIndex($record->id, 'update');
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
        $field = Products::getBatchUpdateField();
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
        if ($keyword == 'category_id') {
            //分类
            $data = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], true, 'pid',
                                                      ['status' => 1]);
        } elseif ($keyword == 'status') {
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data = (new DictionaryValue())->GetListLabel(
                $field, false, '', ['code' => 'Show_Home_State', 'status' => 1], ['sort' => 'ASC']
            );
        } elseif ($keyword == 'country_id') {
            $data = (new Region())->GetListLabel(['id as value', 'name as label'], false, '', ['status' => 1],
                                                 ['sort' => 'ASC']);
        } elseif ($keyword == 'publisher_id') {
            $site = $request->header('Site');
            $publisherIds = Site::where('name', $site)->value('publisher_id');
            $data = [];
            if ($publisherIds) {
                $publisherIdArray = explode(',', $publisherIds);
                $data = (new Publisher())->GetListLabel(['id as value', 'name as label'], false, '',
                                                        ['status' => 1, 'id' => $publisherIdArray]);
            }
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
                    $this->ModelInstance()->syncSearchIndex($record->id, 'update');
                }
            }
            ReturnJson(true, trans('lang.update_success'));
        }
    }

    /**
     * 批量删除
     *
     * @param $request 请求信息
     */
    public function batchDelete(Request $request) {
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? 1; //1：获取数量;2：执行操作
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
        } elseif ($type == 2) {
            // $data['result_count'] = $model->delete();
            // 批量操作无法触发添加日志的功能，但我领导要求有日志
            $newIds = $model->pluck('id');
            foreach ($newIds as $id) {
                $record = $this->ModelInstance()->find($id);
                $year = Products::publishedDateFormatYear($record->published_date);
                if ($year) {
                    $recordDescription = (new ProductsDescription($year))->where('product_id', $record->id);
                }
                if ($recordDescription) {
                    $recordDescription->delete();
                }
                if ($record) {
                    $record->delete();
                    // 删除完成后同步到xunsearch
                    $this->ModelInstance()->syncSearchIndex($record->id, 'delete');
                }
            }
            ReturnJson(true, trans('lang.delete_success'));
        }
    }

    /**
     * 批量导出
     *
     * @param $request 请求信息
     */
    public function export(Request $request) {
        // return Excel::download(new ProductsExport, 'products.xlsx');
        $input = $request->all();
        $ids = $input['ids'] ?? '';
        $type = $input['type'] ?? ''; //1：获取数量;2：执行操作
        $exportType = (!isset($input['export_type']) || empty($input['export_type'])) ? 'txt'
            : $input['export_type']; //导出
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
            //查询出涉及的id
            $idsData = $model->select('id')->pluck('id')->toArray();
            if (!(count($idsData) > 0)) {
                ReturnJson(true, trans('lang.data_empty'));
            }
            //加入队列
            $dirName = time().rand(10000, 99999);
            $basePath = public_path();
            $dirMiddlePath = '/site/'.$request->header('Site').'/exportDir/';
            //检验目录是否存在
            if (!is_dir($basePath.$dirMiddlePath)) {
                @mkdir($basePath.$dirMiddlePath, 0777, true);
            }
            $dirPath = $basePath.$dirMiddlePath.$dirName;
            if ($exportType == 'txt') {
                //导出记录初始化,每个文件单独一条记录
                $logModel = ProductsExportLog::create([
                                                          'file'  => $dirMiddlePath.$dirName.'.txt',
                                                          'count' => count($idsData),
                                                      ]);
                //域名
                $domain = Site::where('name', $request->header('Site'))->value('domain') ?? '';
                //查询出的id数据分割加入队列
                $groupData = array_chunk($idsData, 100000);
                $jobCount = count($groupData);
                foreach ($groupData as $key => $item) {
                    $data = [
                        'class'    => 'Modules\Site\Http\Controllers\ProductsController',
                        'method'   => 'handleExportTxt',
                        'site'     => $request->header('Site') ?? '',   //站点名称
                        'data'     => $item,    //要导出的报告id数据
                        'dirPath'  => $dirPath,
                        'jobCount' => $jobCount,
                        'chip'     => $key + 1,
                        'log_id'   => $logModel->id,  //写入日志的id
                        'domain'   => $domain,  // 传入域名，用于拼接链接
                    ];
                    $data = json_encode($data);
                    ExportProduct::dispatch($data)->onQueue(QueueConst::QUEEU_EXPORT_PRODUCT);
                }
            } elseif ($exportType == 'excel') {
                //导出记录初始化,每个文件单独一条记录
                $logModel = ProductsExportLog::create([
                                                          'file'  => $dirMiddlePath.$dirName.'.xlsx',
                                                          'count' => count($idsData),
                                                      ]);
                //创建目录
                if (!is_dir($dirPath)) {
                    @mkdir($dirPath, 0777, true);
                }
                //获取表头与字段关系
                $excelTitleList = ProductsExcelField::where(['status' => 1])
                                                    ->orderBy('sort', 'asc')
                                                    ->get()
                                                    ->pluck('name', 'field')
                                                    ->toArray();
                //新增需求, 规模字段根据当前导出的时间为准
                $currentDate = intval(date('Y', time()));
                $excelTitleList['last_scale'] = $currentDate - 1;
                $excelTitleList['current_scale'] = $currentDate;
                $excelTitleList['future_scale'] = $currentDate + 6;
                list($fieldData, $titleData) = Arr::divide($excelTitleList);
                // return $fieldData;
                //查询出的id数据分割加入队列
                $groupData = array_chunk($idsData, 100);
                $jobCount = count($groupData);
                foreach ($groupData as $key => $item) {
                    $data = [
                        'class'    => 'Modules\Site\Http\Controllers\ProductsController',
                        'method'   => 'handleExportExcel',
                        'site'     => $request->header('Site') ?? '',   //站点名称
                        'data'     => $item,    //要导出的报告id数据
                        'dirPath'  => $dirPath,
                        'jobCount' => $jobCount,
                        'chip'     => $key + 1,
                        'title'    => $titleData,  //标题
                        'field'    => $fieldData,  //字段
                        'log_id'   => $logModel->id,  //写入日志的id
                    ];
                    $data = json_encode($data);
                    ExportProduct::dispatch($data)->onQueue(QueueConst::QUEEU_EXPORT_PRODUCT);
                }
            }
            ReturnJson(true, trans('lang.request_success'), $logModel->id);
        }
    }

    /**
     * 批量导出excel-导出到多个文件
     *
     * @param $params
     */
    public function handleExportExcel($params = null) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $dirPath = $params['dirPath'];
        $chip = $params['chip'];
        $jobCount = $params['jobCount'];
        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }
        // 设置当前租户
        tenancy()->initialize($params['site']);
        $title = $params['title'];
        $field = $params['field'];
        try {
            //读取数据
            $record = Products::whereIn('id', $params['data'])->get()->makeHidden((new Products())->getAppends())
                              ->toArray();
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($dirPath.'/'.$chip.'.xlsx');
            foreach ($record as $key => $item) {
                $year = date('Y', $item['published_date']);
                if (empty($year) || !is_numeric($year) || strlen($year) !== 4) {
                    continue;
                }
                $item['published_date'] = date('Y-m-d', $item['published_date']);
                $item['category_id'] = ProductsCategory::query()->where('id', $item['category_id'])->value('name');
                $descriptionData = (new ProductsDescription($year))->where('product_id', $item['id'])->first();
                $item['description'] = $descriptionData['description'] ?? '';
                $item['table_of_content'] = $descriptionData['table_of_content'] ?? '';
                $item['tables_and_figures'] = $descriptionData['tables_and_figures'] ?? '';
                $item['description_en'] = $descriptionData['description_en'] ?? '';
                $item['table_of_content_en'] = $descriptionData['table_of_content_en'] ?? '';
                $item['tables_and_figures_en'] = $descriptionData['tables_and_figures_en'] ?? '';
                $item['companies_mentioned'] = $descriptionData['companies_mentioned'] ?? '';
                $item['definition'] = $descriptionData['definition'] ?? '';
                $item['overview'] = $descriptionData['overview'] ?? '';
                $row = [];
                foreach ($field as $value) {
                    if (empty($value) || !isset($item[$value])) {
                        $row[] = '';
                    } else {
                        $row[] = $item[$value];
                    }
                }
                $rowFromValues = WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }
            // $writer->addRows($record);
            $writer->close();
            // $title = array_keys($data[0]);
            //code...
        } catch (\Exception $th) {
            // file_put_contents('C:\\Users\\Administrator\\Desktop\\123.txt', $th->getMessage(), FILE_APPEND);
            // return ;
            $details = $th->getMessage();
            throw $th;
        }
        //记录任务状态
        $logModel = ProductsExportLog::where(['id' => $params['log_id']])->first();
        $logData = [
            'state' => ProductsExportLog::EXPORT_RUNNING,
        ];
        if (isset($details)) {
            $logData['error_count'] = $logModel->error_count + count($record);
            $logData['details'] = $logModel->details.$details;
        } else {
            $logData['success_count'] = $logModel->success_count + count($record);
        }
        $logModel->update($logData);
        //到达了最后一个
        if ($chip == $jobCount) {
            //记录任务状态
            $logModel = ProductsExportLog::where(['id' => $params['log_id']])->first();
            $logData = [
                'state' => ProductsExportLog::EXPORT_MERGING,
            ];
            $logModel->update($logData);
            $data = [
                'class'   => 'Modules\Site\Http\Controllers\ProductsController',
                'method'  => 'handleMergeFile',
                'data'    => $dirPath,
                'dirPath' => $dirPath,
                'title'   => $params['title'],
                'field'   => $params['field'],
                'log_id'  => $logModel->id,  //写入日志的id
                // 'fieldData' => $fieldData,  //字段与excel表头的对应关系
                // 'pulisher_id' => $pulisher_id,  //出版商id
            ];
            $data = json_encode($data);
            HandlerExportExcel::dispatch($data)->onQueue(QueueConst::QUEEU_HANDLER_EXCEL);
        }
    }

    /**
     * 批量导出-合并文件
     *
     * @param $params
     */
    public function handleMergeFile($params = null) {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
            $dirPath = $params['dirPath'];
            // 扫描目录下的所有文件
            $existingFilePath = scandir($dirPath);
            $existingFilePath = array_values(
                array_filter($existingFilePath, function ($item) {
                    return $item !== '.' && $item !== '..';
                })
            );
            // $existingFilePath = ['0.xlsx', '1.xlsx', '2.xlsx'];
            $dirPath = $params['data'];
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile($dirPath.'.xlsx');
            $style = (new StyleBuilder())->setShouldWrapText(false)->build();
            //写入标题
            $title = $params['title'];
            $row = WriterEntityFactory::createRowFromArray($title, $style);
            $writer->addRow($row);
            //循环读取文件，写入excel
            foreach ($existingFilePath as $key => $path) {
                // we need a reader to read the existing file...
                $reader = ReaderEntityFactory::createXLSXReader();
                $reader->setShouldPreserveEmptyRows(true);
                $reader->open($dirPath.'/'.$path);
                // let's read the entire spreadsheet...
                foreach ($reader->getSheetIterator() as $sheetIndex => $sheet) {
                    // Add sheets in the new file, as we read new sheets in the existing one
                    foreach ($sheet->getRowIterator() as $row) {
                        // ... and copy each row into the new spreadsheet
                        $row = WriterEntityFactory::createRowFromArray($row->toArray(), $style);
                        $writer->addRow($row);
                    }
                }
                $reader->close();
            }
            $writer->close();
        } catch (\Throwable $th) {
            // file_put_contents('C:\\Users\\Administrator\\Desktop\\aaaaa.txt', $th->getLine().$th->getMessage().$th->getTraceAsString(), FILE_APPEND);
            $details = $th->getMessage();
        }
        //记录任务状态
        $logModel = ProductsExportLog::where(['id' => $params['log_id']])->first();
        $logData = [
            'state' => ProductsExportLog::EXPORT_COMPLETE,
        ];
        if (isset($details)) {
            $logData['details'] = $logModel->details.$details;
        }
        $logModel->update($logData);
        //删除临时文件夹
        if ($existingFilePath) {
            foreach ($existingFilePath as $path) {
                @unlink($dirPath.'/'.$path);
            }
            @rmdir($dirPath);
        }
    }

    /**
     * 批量导出-导出txt
     *
     * @param $params
     */
    public function handleExportTxt($params = null) {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $dirPath = $params['dirPath'];
        $chip = $params['chip'];
        $jobCount = $params['jobCount'];
        $domain = $params['domain'] ?? '';
        if (empty($params['site'])) {
            throw new \Exception("site is empty", 1);
        }
        // 设置当前租户
        tenancy()->initialize($params['site']);
        try {
            //读取数据
            $record = Products::whereIn('id', $params['data'])->select(['id', 'url'])->get()->makeHidden(
                (new Products())->getAppends()
            )->toArray();
            $urls = [];
            foreach ($record as $key => $item) {
                $urls[] = $domain.'/reports/'.$item['id'].'/'.$item['url'];
            }
            $urlsStr = implode("\r\n", $urls) ?? '';
            file_put_contents($dirPath.'.txt', $urlsStr, FILE_APPEND);
        } catch (\Throwable $th) {
            $details = $th->getMessage();
        }
        //记录任务状态
        $logModel = ProductsExportLog::where(['id' => $params['log_id']])->first();
        $logData = [
            'state' => ProductsExportLog::EXPORT_RUNNING,
        ];
        if (isset($details)) {
            $logData['error_count'] = $logModel->error_count + count($record);
            $logData['details'] = $logModel->details.$details;
        } else {
            $logData['success_count'] = $logModel->success_count + count($record);
        }
        $logModel->update($logData);
        //到达了最后一个
        if ($chip == $jobCount) {
            //记录任务状态
            $logModel = ProductsExportLog::where(['id' => $params['log_id']])->first();
            $logData = [
                'state' => ProductsExportLog::EXPORT_COMPLETE,
            ];
            $logModel->update($logData);
        }
    }

    /**
     * 导出进度
     *
     * @param $request 请求信息
     */
    public function exportProcess(Request $request) {
        $logId = $request->id;
        if (empty($logId)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $logData = ProductsExportLog::where('id', $logId)->first();
        if ($logData) {
            $logData = $logData->toArray();
        } else {
            ReturnJson(true, trans('lang.data_empty'));
        }
        $data = [
            'result' => true,
            'msg'    => '',
            'file'   => $logData['file'],
        ];
        $text = '';
        $updateTime = 0;
        if ($logData['state'] != ProductsUploadLog::UPLOAD_COMPLETE) {
            $data['result'] = false;
        }
        $updatedTimestamp = strtotime($logData['updated_at']);
        if ($updatedTimestamp > $updateTime) {
            $updateTime = $updatedTimestamp;
        }
        switch ($logData['state']) {
            case ProductsExportLog::EXPORT_INIT:
                $text = trans('lang.export_init_msg');
                break;
            case ProductsExportLog::EXPORT_RUNNING:
                $text = trans('lang.export_running_msg').($logData['success_count'] + $logData['error_count']).'/'
                        .$logData['count'];
                break;
            case ProductsExportLog::EXPORT_MERGING:
                $text = trans('lang.export_merging_msg');
                break;
            case ProductsExportLog::EXPORT_COMPLETE:
                $text = trans('lang.export_complete_msg');
                break;
            default:
                # code...
                break;
        }
        $data['msg'] = $text;
        //五分钟没反应则提示
        if (time() > $updateTime + 60 * 5) {
            $data = [
                'result' => false,
                'msg'    => trans('lang.time_out'),
            ];
        }
        ReturnJson(true, trans('lang.request_success'), $data);
    }

    /**
     * 下载导出文件
     *
     * @param $request 请求信息
     */
    public function exportFileDownload(Request $request) {
        $logId = $request->id;
        if (empty($logId)) {
            ReturnJson(true, trans('lang.param_empty'));
        }
        $logData = ProductsExportLog::where('id', $logId)->first();
        if ($logData) {
            $logData = $logData->toArray();
        } else {
            ReturnJson(true, trans('lang.data_empty'));
        }
        if ($logData['state'] == ProductsExportLog::EXPORT_COMPLETE) {
            $basePath = public_path();
            if (strpos($logData['file'], 'txt') !== false) {
                return response()->download($basePath.$logData['file'], null, [
                    'Content-Type'        => 'text/plain',
                    'Content-Disposition' => 'inline',
                ]);
            } elseif (strpos($logData['file'], 'xlsx') !== false) {
                return response()->download($basePath.$logData['file'], null, [
                    'Content-Type'        => 'application/vnd.ms-excel',
                    'Content-Disposition' => 'inline',
                ]);
            }
        }
        ReturnJson(true, trans('lang.file_not_exist'));
    }

    /**
     * 快速搜索-字典数据
     */
    public function QuickSearchDictionary(Request $request) {
        $options = [];
        $codes = ['Switch_State', 'Show_Home_State', 'Has_Sample', 'Discount_Type', 'Quick_Search'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select(
            'code', 'value', $NameField, 'remark as type'
        )->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                if ($map['code'] == 'Quick_Search') {
                    $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value'],
                                                'type'  => $map['type'] ? intval($map['type']) : 0];
                } else {
                    $options[$map['code']][] = ['label' => $map['label'], 'value' => intval($map['value'])];
                }
            }
        }
        $res['Quick_Search'] = isset($options['Quick_Search']) && !empty($options['Quick_Search'])
            ? $options['Quick_Search'] : [];
        foreach ($res['Quick_Search'] as &$value) {
            switch ($value['value']) {
                case 'status':
                    $value['dictionary'] = $options['Switch_State'];
                    break;
                case 'show_recommend':
                    $value['dictionary'] = $options['Show_Home_State'];
                    break;
                case 'show_hot':
                    $value['dictionary'] = $options['Has_Sample'];
                    break;
                case 'discount_amount':
                    $value['dictionary'] = $options['Discount_Type'];
                    break;
                case 'country_id':
                    $value['dictionary'] = (new Region)->GetList(['id as value', 'name as label'], false, '',
                                                                 ['status' => 1]);
                    break;
                default:
                    $value['dictionary'] = [];
                    break;
            }
        }
        //分类
        $res['category'] = (new ProductsCategory())->GetList(['id as value', 'name as label', 'id', 'pid'], true, 'pid',
                                                             ['status' => 1]);
        ReturnJson(true, '', $res);
    }

    /**
     *  处理模版之前处理数据
     */
    public function beforeMatchTemplateData() {
        $SiteName = request()->header('Site');
        // TODO: cuizhixiong 2024/6/3 后期还要优化速度的话, 需使用缓存参数改为false, 且需要再Observers监听模型的CRUD(删缓存)
        $isNoUseCache = true;
        $tcListKey = 'tc_list_'.$SiteName;
        //模版分类缓存
        $tcList = Redis::get($tcListKey);
        if (empty($tcList) || $isNoUseCache) {
            $tcModel = new TemplateCategory();
            $this->tcList = $tcModel->where("status", 1)
                                    ->orderBy("sort", "desc")
                                    ->select('id', 'name', 'match_words')
                                    ->get()->toArray();
            Redis::set($tcListKey, json_encode($this->tcList));
        } else {
            $this->tcList = json_decode($tcList, true);
        }
        //颜色数据字典缓存
        $dictListKey = 'dict_list_'.$SiteName;
        $dictList = Redis::get($dictListKey);
        if (empty($dictList) || $isNoUseCache) {
            $dictModel = (new DictionaryValue());
            $colorFieldList = ['id', 'name', 'value'];
            $dictList = $dictModel->select($colorFieldList)
                                  ->where("code", 'template_color')
                                  ->where("status", 1)
                                  ->get()->keyBy("id")->toArray();
            $this->dictList = $dictList;
            Redis::setex($dictListKey, 86400, json_encode($dictList));
        } else {
            $this->dictList = json_decode($dictList, true);
        }
        //模版分类映射缓存
        $templateCateMappingListKey = 'temp_cate_map_list_'.$SiteName;
        $templateCateMappingList = Redis::get($templateCateMappingListKey);
        if (empty($templateCateMappingList) || $isNoUseCache) {
            $templateCateMappingList = (new TemplateCateMapping())->select(['id', 'cate_id', 'temp_id'])
                                                                  ->get()->toArray();
            Redis::set($templateCateMappingListKey, json_encode($templateCateMappingList));
            $this->templateCateMappingList = $templateCateMappingList;
        } else {
            $templateCateMappingList = json_decode($templateCateMappingList, true);
            $this->templateCateMappingList = $templateCateMappingList;
        }
        //模版列表缓存
        $templateListKey = 'template_list_'.$SiteName;
        $templateList = Redis::get($templateListKey);
        if (empty($templateList) || $isNoUseCache) {
            $templateList = Template::query()
                                    ->select(['id', 'name', 'type', 'btn_color'])
                                    ->where("status", 1)
                                    ->get()
                                    ->toArray();
            Redis::set($templateListKey, json_encode($templateList));
            $this->templateList = $templateList;
        } else {
            $templateList = json_decode($templateList, true);
            $this->templateList = $templateList;
        }
        //分类昵称缓存
        $categoryNameKey = 'category_name_'.$SiteName;
        $categoryName = Redis::get($categoryNameKey);
        if (empty($categoryName) || $isNoUseCache) {
            $categoryName = ProductsCategory::query()
                                            ->where("status", 1)
                                            ->pluck("name", "id")
                                            ->toArray();
            Redis::set($categoryNameKey, json_encode($categoryName));
            $this->categpryName = $categoryName;
        } else {
            $categoryName = json_decode($categoryName, true);
            $this->categpryName = $categoryName;
        }
    }

    /**
     * 根据报告描述，匹配模版数据
     *
     * @param $description  模版描述
     * @param $productsId   报告id
     *
     * @return array[]
     */
    public function matchTemplateData($description) {
        //测试需求, 模板分类必须所有关键词匹配, 且报告可以匹配多个模板分类
        $rdata = [
            'template_cate_list'    => [],
            'template_title_list'   => [],
            'template_content_list' => [],
        ];
        $tcWordsList = [];
        $tcNoWordsList = [];
        foreach ($this->tcList as $tcInfo) {
            if (!empty($tcInfo['match_words'])) {
                $tcWordsList[] = $tcInfo;
            } else {
                $tcNoWordsList[] = $tcInfo;
            }
        }
        if (!empty($description)) {
            $templateCateList = [];
            foreach ($tcWordsList as $tcInfo) {
                $matchWords = $tcInfo['match_words'];
                if (empty($matchWords)) {
                    continue;
                }
                $matchWordsList = explode(",", $matchWords);
                //关键词， 匹配模版分类
                if (!empty($matchWordsList) && is_array($matchWordsList)) {
                    $matchRes = false;
                    foreach ($matchWordsList as $matchWordsFor) {
                        $pattern = preg_quote($matchWordsFor, '/');
                        $pattern = '/'.$pattern.'/';
                        if (!preg_match($pattern, $description)) {
                            $matchRes = false;
                            break;
                        } else {
                            $matchRes = true;
                        }
                    }
                    if ($matchRes) {
                        //所有关键词匹配 , 放入数组
                        $templateCateList[] = $tcInfo;
                    }
                }
            }
        }
        //一个都没有匹配上  或 报告描述为空  需要匹配没有关键词的模版分类
        if (empty($templateCateList) || empty($description)) {
            $templateCateList = $tcNoWordsList;
        }
        //关键词一个没有匹配上 或者 没有无关键词的模版分类
        if (empty($templateCateList)) {
            return $rdata;
        }
        //根据模版分类id, 获取模版id
        $cateIdList = Arr::pluck($templateCateList, 'id');
        $tempIdList = [];
        foreach ($this->templateCateMappingList as $cateMappingInfo) {
            if (in_array($cateMappingInfo['cate_id'], $cateIdList)) {
                $tempIdList[] = $cateMappingInfo['temp_id'];
            }
        }
        $matchTempLateList = [];
        foreach ($this->templateList as $templateInfo) {
            if (in_array($templateInfo['id'], $tempIdList)) {
                $matchTempLateList[] = $templateInfo;
            }
        }
//        $tempIdList = TemplateCateMapping::whereIn('cate_id', $cateIdList)->pluck('temp_id')->toArray();
//        $matchTempLateList = Template::whereIn('id', $tempIdList)
//                                     ->where("status", 1)
//                                     ->select(['id', 'name', 'type', 'btn_color'])->get()
//                                     ->toArray();
        $template_content_list = [];
        $template_title_list = [];
        //区分标题模板 , 内容模板
        foreach ($matchTempLateList as $forTempInfo) {
            //按钮颜色详情
            $btnColorId = $forTempInfo['btn_color'];
            if (!empty($this->dictList[$btnColorId])) {
                $forTempInfo['btn_info'] = $this->dictList[$btnColorId];
            } else {
                $forTempInfo['btn_info'] = [];
            }
            if ($forTempInfo['type'] == 1) {
                //内容模版
                $template_content_list[] = $forTempInfo;
            } else {
                $template_title_list[] = $forTempInfo;
            }
        }
        $rdata['template_cate_list'] = $templateCateList;
        $rdata['template_content_list'] = $template_content_list;
        $rdata['template_title_list'] = $template_title_list;

        return $rdata;
    }

    /**
     * 修改状态
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeStatus(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->status = $request->status;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            // 更新完成后同步到xunsearch
            $this->ModelInstance()->syncSearchIndex($record->id, 'update');
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    /**
     * 修改排序
     *
     * @param $request 请求信息
     * @param $id      主键ID
     */
    public function changeSort(Request $request) {
        try {
            if (empty($request->id)) {
                ReturnJson(false, 'id is empty');
            }
            $record = $this->ModelInstance()->findOrFail($request->id);
            $record->sort = $request->sort;
            if (!$record->save()) {
                ReturnJson(false, trans('lang.update_error'));
            }
            // 更新完成后同步到xunsearch
            $this->ModelInstance()->syncSearchIndex($record->id, 'update');
            ReturnJson(true, trans('lang.update_success'));
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }
}
