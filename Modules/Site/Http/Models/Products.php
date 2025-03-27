<?php

namespace Modules\Site\Http\Models;

use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Foolz\SphinxQL\SphinxQL;
use Illuminate\Support\Facades\Redis;
use Modules\Site\Http\Models\Region;
use Modules\Site\Http\Models\Base;
use Modules\Admin\Http\Models\Publisher;
use Modules\Site\Services\SphinxService;
use XS;
use XSDocument;

class Products extends Base {
    protected $table = 'product_routine';
    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['category', 'country', 'published_date_format', 'publisher'];
    // 设置允许入库字段,数组形式
    protected $fillable
        = [
            'name', //报告名称
            'english_name', //报告名称
            'thumb',    //图片,无则显示分类图片
            'publisher_id',  //出版商id
            'category_id',  //行业id
            'country_id', //国家区域id
            'price',    //基础价
            'status',   //状态
            'keywords',  //关键词
            'keywords_cn',
            'keywords_en',
            'keywords_jp',
            'keywords_kr',
            'keywords_de',
            'url',  //自定义链接
            'published_date', //出版日期
            'sort', //排序
            'updated_by',   //修改者
            'created_by',   //创建者
            'author',   //报告作者
            'pages',      //页数
            'tables',   //图表数
            'hits',         //点击量
            'downloads',  //下载量
            'discount',     //折扣率
            'discount_amount',  //折扣金额
            'discount_type',  //折扣类型
            'discount_time_begin',  //折扣开始时间
            'discount_time_end', //折扣结束时间
            "have_sample",  //是否有样本
            //各类状态
            'show_home',    //首页显示
            'show_hot',    // 热门
            'show_recommend', // 推荐
            //新增字段
            'classification', //产品类型/分类
            'application',    //产品应用领域
            'cagr',           //6年复合年均增长率
            'last_scale',    //去年规模
            'current_scale',    //当前规模
            'future_scale',    //未来规模
            'year',    //数据年
            'third_sync_id',    //第三方同步数据的id
        ];
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::created(function ($model) {
    //         // 创建完成后
    //         $model->syncSearchIndex($model->id,'add');
    //     });
    //     static::updated(function ($model) {
    //         // 更新完成后
    //         $model->syncSearchIndex($model->id,'update');
    //     });
    //     static::deleted(function ($model) {
    //         // 删除完成后
    //         $model->syncSearchIndex($model->id,'delete');
    //     });
    // }
    /**
     * 处理查询列表条件数组
     *
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request) {
        $search = json_decode($request->input('search'));
        //id
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }
        // name
        if (isset($search->name) && !empty($search->name)) {
            $model = $model->where('name', 'like', '%'.$search->name.'%');
        }
        // english_name
        if (isset($search->english_name) && !empty($search->english_name)) {
            $model = $model->where('english_name', 'like', $search->english_name.'%');
        }
        // category_id
        if (isset($search->category_id) && $search->category_id != '') {
            $model = $model->where('category_id', $search->category_id);
        }
        // country_id
        if (isset($search->country_id) && $search->country_id != '') {
            $model = $model->where('country_id', $search->country_id);
        }
        // price
        if (isset($search->price) && $search->price != '') {
            $model = $model->where('price', $search->price);
        }
        // status 状态
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }
        // publisher_id
        if (isset($search->publisher_id) && $search->publisher_id != '') {
            $model = $model->where('publisher_id', $search->publisher_id);
        }
        // keywords
        if (isset($search->keywords) && !empty($search->keywords)) {
            $model = $model->where('keywords', 'like', '%'.$search->keywords.'%');
        }
        // keywords_cn
        if (isset($search->keywords_cn) && !empty($search->keywords_cn)) {
            $model = $model->where('keywords_cn', 'like', '%'.$search->keywords_cn.'%');
        }
        // keywords_en
        if (isset($search->keywords_en) && !empty($search->keywords_en)) {
            $model = $model->where('keywords_en', 'like', '%'.$search->keywords_en.'%');
        }
        // keywords_jp
        if (isset($search->keywords_jp) && !empty($search->keywords_jp)) {
            $model = $model->where('keywords_jp', 'like', '%'.$search->keywords_jp.'%');
        }
        // keywords_kr
        if (isset($search->keywords_kr) && !empty($search->keywords_kr)) {
            $model = $model->where('keywords_kr', 'like', '%'.$search->keywords_kr.'%');
        }
        // keywords_de
        if (isset($search->keywords_de) && !empty($search->keywords_de)) {
            $model = $model->where('keywords_de', 'like', '%'.$search->keywords_de.'%');
        }
        // url
        if (isset($search->url) && !empty($search->url)) {
            $model = $model->where('url', 'like', '%'.$search->url.'%');
        }
        // sort
        if (isset($search->sort) && $search->sort != '') {
            $model = $model->where('sort', $search->sort);
        }
        // author
        if (isset($search->author) && !empty($search->author)) {
            $model = $model->where('author', 'like', '%'.$search->author.'%');
        }
        // pages
        if (isset($search->pages) && $search->pages != '') {
            $model = $model->where('pages', $search->pages);
        }
        // tables
        if (isset($search->tables) && $search->tables != '') {
            $model = $model->where('tables', $search->tables);
        }
        // hits
        if (isset($search->hits) && $search->hits != '') {
            $model = $model->where('hits', $search->hits);
        }
        // downloads
        if (isset($search->downloads) && $search->downloads != '') {
            $model = $model->where('downloads', $search->downloads);
        }
        // discount
        if (isset($search->discount) && $search->discount != '') {
            $model = $model->where('discount', $search->discount);
        }
        // discount_amount
        if (isset($search->discount_amount) && $search->discount_amount != '') {
            $model = $model->where('discount_amount', $search->discount_amount);
        }
        // discount_type
        if (isset($search->discount_type) && $search->discount_type != '') {
            $model = $model->where('discount_type', $search->discount_type);
        }
        // have_sample
        if (isset($search->have_sample) && $search->have_sample != '') {
            $model = $model->where('have_sample', $search->have_sample);
        }
        // show_home 状态
        if (isset($search->show_home) && $search->show_home != '') {
            $model = $model->where('show_home', $search->show_home);
        }
        // show_hot 状态
        if (isset($search->show_hot) && $search->show_hot != '') {
            $model = $model->where('show_hot', $search->show_hot);
        }
        // show_recommend 状态
        if (isset($search->show_recommend) && $search->show_recommend != '') {
            $model = $model->where('show_recommend', $search->show_recommend);
        }
        // 时间为数组形式
        // 创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }
        // 更新时间
        if (isset($search->updated_at) && !empty($search->updated_at)) {
            $updateTime = $search->updated_at;
            $model = $model->where('updated_at', '>=', $updateTime[0]);
            $model = $model->where('updated_at', '<=', $updateTime[1]);
        }
        // 出版时间
        if (isset($search->published_date) && !empty($search->published_date)) {
            $publishedTime = $search->published_date;
            $model = $model->where('published_date', '>=', $publishedTime[0]);
            $model = $model->where('published_date', '<=', $publishedTime[1]);
        }
        // 折扣开始时间
        if (isset($search->discount_time_begin) && !empty($search->discount_time_begin)) {
            $discountTimeBegin = $search->discount_time_begin;
            $model = $model->where('discount_time_begin', '>=', $discountTimeBegin[0]);
            $model = $model->where('discount_time_begin', '<=', $discountTimeBegin[1]);
        }
        // 折扣结束时间
        if (isset($search->discount_time_end) && !empty($search->discount_time_end)) {
            $discountTimeEnd = $search->discount_time_end;
            $model = $model->where('discount_time_end', '>=', $discountTimeEnd[0]);
            $model = $model->where('discount_time_end', '<=', $discountTimeEnd[1]);
        }

        return $model;
    }

    /**
     * 处理查询列表条件数组
     */
    public function newHandleWhere($params) {
        $model = $this;
        $search = json_decode($params);
        //id
        if (isset($search->id) && !empty($search->id)) {
            $model = $this->where('id', $search->id);
        }
        // name
        if (isset($search->name) && !empty($search->name)) {
            $model = $model->where('name', 'like', '%'.$search->name.'%');
        }
        // english_name
        if (isset($search->english_name) && !empty($search->english_name)) {
            $model = $model->where('english_name', 'like', $search->english_name.'%');
        }
        // category_id
        if (isset($search->category_id) && $search->category_id != '') {
            $model = $model->where('category_id', $search->category_id);
        }
        // country_id
        if (isset($search->country_id) && $search->country_id != '') {
            $model = $model->where('country_id', $search->country_id);
        }
        // price
        if (isset($search->price) && $search->price != '') {
            $model = $model->where('price', $search->price);
        }
        // status 状态
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }
        // publisher_id
        if (isset($search->publisher_id) && $search->publisher_id != '') {
            $model = $model->where('publisher_id', $search->publisher_id);
        }
        // keywords
        if (isset($search->keywords) && !empty($search->keywords)) {
            $model = $model->where('keywords', 'like', '%'.$search->keywords.'%');
        }
        // keywords_cn
        if (isset($search->keywords_cn) && !empty($search->keywords_cn)) {
            $model = $model->where('keywords_cn', 'like', '%'.$search->keywords_cn.'%');
        }
        // keywords_en
        if (isset($search->keywords_en) && !empty($search->keywords_en)) {
            $model = $model->where('keywords_en', 'like', '%'.$search->keywords_en.'%');
        }
        // keywords_jp
        if (isset($search->keywords_jp) && !empty($search->keywords_jp)) {
            $model = $model->where('keywords_jp', 'like', '%'.$search->keywords_jp.'%');
        }
        // keywords_kr
        if (isset($search->keywords_kr) && !empty($search->keywords_kr)) {
            $model = $model->where('keywords_kr', 'like', '%'.$search->keywords_kr.'%');
        }
        // keywords_de
        if (isset($search->keywords_de) && !empty($search->keywords_de)) {
            $model = $model->where('keywords_de', 'like', '%'.$search->keywords_de.'%');
        }
        // url
        if (isset($search->url) && !empty($search->url)) {
            $model = $model->where('url', 'like', '%'.$search->url.'%');
        }
        // sort
        if (isset($search->sort) && $search->sort != '') {
            $model = $model->where('sort', $search->sort);
        }
        // author
        if (isset($search->author) && !empty($search->author)) {
            $model = $model->where('author', 'like', '%'.$search->author.'%');
        }
        // pages
        if (isset($search->pages) && $search->pages != '') {
            $model = $model->where('pages', $search->pages);
        }
        // tables
        if (isset($search->tables) && $search->tables != '') {
            $model = $model->where('tables', $search->tables);
        }
        // hits
        if (isset($search->hits) && $search->hits != '') {
            $model = $model->where('hits', $search->hits);
        }
        // downloads
        if (isset($search->downloads) && $search->downloads != '') {
            $model = $model->where('downloads', $search->downloads);
        }
        // discount
        if (isset($search->discount) && $search->discount != '') {
            $model = $model->where('discount', $search->discount);
        }
        // discount_amount
        if (isset($search->discount_amount) && $search->discount_amount != '') {
            $model = $model->where('discount_amount', $search->discount_amount);
        }
        // discount_type
        if (isset($search->discount_type) && $search->discount_type != '') {
            $model = $model->where('discount_type', $search->discount_type);
        }
        // have_sample
        if (isset($search->have_sample) && $search->have_sample != '') {
            $model = $model->where('have_sample', $search->have_sample);
        }
        // show_home 状态
        if (isset($search->show_home) && $search->show_home != '') {
            $model = $model->where('show_home', $search->show_home);
        }
        // show_hot 状态
        if (isset($search->show_hot) && $search->show_hot != '') {
            $model = $model->where('show_hot', $search->show_hot);
        }
        // show_recommend 状态
        if (isset($search->show_recommend) && $search->show_recommend != '') {
            $model = $model->where('show_recommend', $search->show_recommend);
        }
        // 时间为数组形式
        // 创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }
        // 更新时间
        if (isset($search->updated_at) && !empty($search->updated_at)) {
            $updateTime = $search->updated_at;
            $model = $model->where('updated_at', '>=', $updateTime[0]);
            $model = $model->where('updated_at', '<=', $updateTime[1]);
        }
        // 出版时间
        if (isset($search->published_date) && !empty($search->published_date)) {
            $publishedTime = $search->published_date;
            $model = $model->where('published_date', '>=', $publishedTime[0]);
            $model = $model->where('published_date', '<=', $publishedTime[1]);
        }
        // 折扣开始时间
        if (isset($search->discount_time_begin) && !empty($search->discount_time_begin)) {
            $discountTimeBegin = $search->discount_time_begin;
            $model = $model->where('discount_time_begin', '>=', $discountTimeBegin[0]);
            $model = $model->where('discount_time_begin', '<=', $discountTimeBegin[1]);
        }
        // 折扣结束时间
        if (isset($search->discount_time_end) && !empty($search->discount_time_end)) {
            $discountTimeEnd = $search->discount_time_end;
            $model = $model->where('discount_time_end', '>=', $discountTimeEnd[0]);
            $model = $model->where('discount_time_end', '<=', $discountTimeEnd[1]);
        }

        return $model;
    }

    /**
     * 分类获取器
     */
    public function getCategoryAttribute() {
        $text = '';
        if (isset($this->attributes['category_id'])) {
            $text = ProductsCategory::where('id', $this->attributes['category_id'])->value('name') ?? '';
        }

        return $text;
    }

    /**
     * 国家地区获取器
     */
    public function getCountryAttribute() {
        $text = '';
        if (isset($this->attributes['country_id'])) {
            $text = Region::where('id', $this->attributes['country_id'])->value('name') ?? '';
        }

        return $text;
    }

    /**
     * 出版商获取器
     */
    public function getPublisherAttribute() {
        $text = '';
        if (isset($this->attributes['publisher_id'])) {
            $text = Publisher::where('id', $this->attributes['publisher_id'])->value('name') ?? '';
        }

        return $text;
    }

    /**
     * 出版时间获取器
     */
    public function getPublishedDateFormatAttribute() {
        $text = '';
        if (isset($this->attributes['published_date'])) {
            $text = date('Y-m-d', $this->attributes['published_date']);
        }

        return $text;
    }

    public static function publishedDateFormatYear($timestamp) {
        $year = is_numeric($timestamp) ? date('Y', $timestamp) : date('Y', strtotime($timestamp));
        if (empty($year) || !is_numeric($year) || strlen($year) !== 4) {
            return false;
        }

        return $year;
    }

    //可修改的字段
    public static function getBatchUpdateField() {
        return [
            [
                'name'  => '行业',
                'value' => 'category_id',
                'type'  => '2',
            ],
            [
                'name'  => '国家地区',
                'value' => 'country_id',
                'type'  => '2',
            ],
            [
                'name'  => '出版商',
                'value' => 'publisher_id',
                'type'  => '2',
            ],
            [
                'name'  => '价格',
                // 'name' => trans('lang.price'),
                'value' => 'price',
                'type'  => '1',
            ],
            [
                'name'  => '状态',
                'value' => 'status',
                'type'  => '2',
            ],
        ];
    }

    /**
     * 推送消息队列
     *
     * @param $id           报告id
     * @param $action       操作类型
     * @param $siteName     站点标识
     *
     */
    public function excuteXunSearchReq($id, $action, $siteName = '') {
        //先测试讯搜数据业务数据
        if ($action == 'delete') {
            $data['id'] = $id;
        } else {
            $data = $this->handlerProductData($id);
        }
        if (empty($data)) {
            return false;
        }
        $this->excuteXs($siteName, $action, $data);

        return true;
    }

    /**
     * 处理sphinx搜索服务
     *
     * @param $id           报告id
     * @param $action       string    操作类型
     * @param $siteName     站点标识
     *
     */
    public function excuteSphinxReq($reqData, $action, $siteName = '') {
        try {
            if (empty($reqData)) {
                return false;
            }
            if (is_array($reqData) && !empty($reqData['id'])) {
                $data = $reqData;
                $id = $data['id'];
            } else {
                $id = $reqData;
            }
            //获取sphinx链接
            $conn = (new SphinxService($siteName))->getConnection();
            if ($action == 'delete') {
                $res = (new SphinxQL($conn))->delete()->from('products_rt')->where("id", intval($id))->execute();

                return $res->getAffectedRows();
            }
            if (empty($data)) {
                $data = $this->handlerProductData($id);
            }
            if (empty($data)) {
                return false;
            }
            if ($action == 'add') {
                $res = (new SphinxQL($conn))->insert()->into('products_rt')->set($data)
                                            ->execute();

                return $res->getAffectedRows();
            } else {
                //修改
                (new SphinxQL($conn))->delete()->from('products_rt')->where("id", $id)->execute();
                $res = (new SphinxQL($conn))->insert()->into('products_rt')->set($data)
                                            ->execute();

                //$res = (new SphinxQL($conn))->update('products_rt')->where("id", $id)->set($data)->execute();
                return $res->getAffectedRows();
            }
        } catch (\Exception $e) {
            throw $e;

            return false;
        }

        return true;
    }

    public function syncSearchIndex($productId, $action, $siteName = '') {
        //先 暂时不使用队列, 立即处理
        //return $this->excuteXunSearchReq($productId, $action, $siteName);
        return $this->excuteSphinxReq($productId, $action, $siteName);
    }

    private function handlerProductData($id) {
        if ($id) {
            $data = Products::find($id);
            if (empty($data)) {
                return [];
            }
            $price = $data['price'] ?? 0;
            $discount = $data['discount'] ?? 0;
            $discount_amount = $data['discount_amount'] ?? 0;
            $handlerData = [
                'id'              => $data['id'],
                'name'            => $data['name'] ?? '',
                'english_name'    => $data['english_name'] ?? '',
                'country_id'      => $data['country_id'] ?? 0,
                'category_id'     => $data['category_id'] ?? 0,
                'publisher_id'    => $data['publisher_id'] ?? 0,
                'price'           => floatval($price),
                'discount'        => floatval($discount),
                'discount_amount' => floatval($discount_amount),
                'created_at'      => strtotime($data['created_at']),
                'published_date'  => $data['published_date'] ?? 0,
                'author'          => $data['author'] ?? '',
                'show_hot'        => $data['show_hot'] ?? 1,
                'show_recommend'  => $data['show_recommend'] ?? 1,
                'status'          => $data['status'] ?? 1,
                'keywords'        => $data['keywords'] ?? '',
                'year'            => $data['year'] ?? 0,
                'degree_keyword'  => strlen($data['keywords']),
                'sort'            => $data['sort'] ?? 100,
                'url'             => $data['url'] ?? '',
            ];
//            $year = date('Y', $data['published_date']);
//            $description = (new ProductsDescription($year))->where('product_id', $data['id'])->value('description');
//            $handlerData['description'] = $description;
        } else {
            $handlerData = [];
        }

        return $handlerData;
    }

    /**
     * 获取产品数据
     */
    private function GetProductData($data) {
        if ($data) {
            $data = Products::find($data['id']);
            $ini = [
                "pid"                 => $data['id'],
                "id"                  => $data['id'],
                "name"                => $data['name'],
                "english_name"        => $data['english_name'],
                "thumb"               => $data['thumb'],
                "publisher_id"        => $data['publisher_id'],
                "category_id"         => $data['category_id'],
                "country_id"          => $data['country_id'],
                "price"               => $data['price'],
                "keywords"            => $data['keywords'],
                "url"                 => $data['url'],
                "published_date"      => is_int($data['published_date'])
                    ? $data['published_date']
                    : strtotime(
                        $data['published_date']
                    ),
                "status"              => $data['status'],
                "author"              => $data['author'],
                "show_home"           => $data['show_home'],
                "have_sample"         => $data['have_sample'],
                "discount"            => $data['discount'],
                "discount_amount"     => $data['discount_amount'],
                "discount_type"       => $data['discount_type'],
                "discount_time_begin" => $data['discount_time_begin'],
                "discount_time_end"   => $data['discount_time_end'],
                "pages"               => $data['pages'],
                "tables"              => $data['tables'],
                "hits"                => $data['hits'],
                "show_hot"            => $data['show_hot'],
                "show_recommend"      => $data['show_recommend'],
                "sort"                => $data['sort'],
                "updated_at"          => $data->getRawOriginal('updated_at'),
                "created_at"          => $data->getRawOriginal('created_at'),
                "updated_by"          => $data->getRawOriginal('updated_by'),
                "created_by"          => $data->getRawOriginal('created_by'),
                "downloads"           => $data['downloads'],
                //新增几个字段
                "classification"      => $data['classification'],
                "application"         => $data['application'],
                "cagr"                => $data['cagr'],
                "last_scale"          => $data['last_scale'],
                "current_scale"       => $data['current_scale'],
                "future_scale"        => $data['future_scale'],
                "year"                => $data['year'],
            ];
        } else {
            $ini = [];
        }

        return $ini;
    }

    /**
     *
     * @param $id
     *
     * @return string
     */
    public function getRedisKey($id): string {
        $site = request()->header('Site', '');
        $redisKey = $site."_".class_basename($this)."_".$id;

        return $redisKey;
    }

    /**
     *
     * @param $id
     * @param $isExt  是否查询扩展字段, 默认不查询
     *
     * @return array|mixed
     */
    public function findOrCache($id) {
        $cacheKey = $this->getRedisKey($id);
        $data = Redis::get($cacheKey);
        //缓存命中直接返回
        if (!empty($data)) {
            return json_decode($data, true);
        }
        $data = self::find($id);
        if (!empty($data)) {
            //放入缓存
            $data = $data->toArray();
            Redis::set($cacheKey, json_encode($data));

            return $data;
        }

        return [];
    }

    public function findDescCache($id) {
        $pdescRedisKey = $this->getPDescRedisKey($id);
        $pdescData = Redis::get($pdescRedisKey);
        if (!empty($pdescData)) {
            return json_decode($pdescData, true);
        } else {
            return $this->getPdescDataByProductId($id);
        }

        return [];
    }

    public function getPdescDataByProductId($productId) {
        $products = $this->findOrCache($productId);
        if (!empty($products)) {
            $year = date('Y', $products['published_date']);
            $descriptionData = (new ProductsDescription($year))->where('product_id', $products['id'])->first();
            if (!empty($descriptionData)) {
                $descriptionData = $descriptionData->toArray();
                $pdescRedisKey = $this->getPDescRedisKey($productId);
                Redis::set($pdescRedisKey, json_encode($descriptionData));

                return $descriptionData;
            }
        }

        return [];
    }

    public function getPDescRedisKey($id): string {
        $site = request()->header('Site', '');
        $redisKey = $site."_".class_basename(ProductsDescription::class)."_".$id;

        return $redisKey;
    }

    /**
     *
     * @param 站点标识 $siteName
     * @param 操作类型 $action
     * @param array    $data
     *
     */
    public function excuteXs($siteName, $action, $data) {
        $siteName = $siteName ? $siteName : request()->header('Site');
        $confIniPath = base_path("Modules/Site/Config/xunsearch/{$siteName}.ini");
        $xs = new XS($confIniPath);
        $index = $xs->index;
        if ($action == 'add') {
            // 创建文档对象
            $doc = new XSDocument;
            $doc->setFields($data);
            $index->add($doc)->flushIndex();
        } elseif ($action == 'update') {
            // 创建文档对象
            $doc = new XSDocument;
            $doc->setFields($data);
            $index->update($doc)->flushIndex();
        } elseif ($action == 'delete') {
            $index->del($data['id'])->flushIndex();;
        }
    }

    /**
     * 根据商品id批量更新sphinx索引
     *
     * @param $product_id_list
     * @param $site
     *
     */
    public function batchUpdateSphinx($product_id_list, $site) {
        // 设置当前租户
        tenancy()->initialize($site);
        // TODO: cuizhixiong 2025/1/3 'publisher_id',后续加上
        $fieldList = ['id', 'name', 'english_name', 'category_id', 'country_id', 'price', 'keywords', 'year', 'url',
                      'publisher_id',
                      'published_date', 'status', 'author', 'discount', 'discount_amount', 'show_hot', 'show_recommend',
                      'sort', 'created_at'];
        $productList = Products::query()->whereIn('id', $product_id_list)->select($fieldList)->get()->toArray();
        $handlerProductList = [];
        foreach ($productList as $data) {
            $price = $data['price'] ?? 0;
            $discount = $data['discount'] ?? 0;
            $discount_amount = $data['discount_amount'] ?? 0;
            $handlerData = [
                'id'              => $data['id'],
                'name'            => $data['name'] ?? '',
                'english_name'    => $data['english_name'] ?? '',
                'country_id'      => $data['country_id'] ?? 0,
                'category_id'     => $data['category_id'] ?? 0,
                'price'           => floatval($price),
                'discount'        => floatval($discount),
                'discount_amount' => floatval($discount_amount),
                'created_at'      => strtotime($data['created_at']),
                'published_date'  => $data['published_date'] ?? 0,
                'author'          => $data['author'] ?? '',
                'show_hot'        => $data['show_hot'] ?? 1,
                'show_recommend'  => $data['show_recommend'] ?? 1,
                'status'          => $data['status'] ?? 1,
                'keywords'        => $data['keywords'] ?? '',
                'year'            => $data['year'] ?? 0,
                'degree_keyword'  => strlen($data['keywords']),
                'publisher_id'    => $data['publisher_id'],
                'sort'            => $data['sort'] ?? 100,
                'url'             => $data['url'] ?? '',
            ];
            $handlerProductList[] = $handlerData;
        }
        if (!empty($handlerProductList)) {
            //同步sphinx索引
            $conn = (new SphinxService($site))->getConnection();
            $product_id_num_list = array_map('intval', $product_id_list);
            (new SphinxQL($conn))->delete()->from('products_rt')->where('id', 'IN', $product_id_num_list)->execute();
//            $sphinxFields = array_keys($handlerProductList[0]);
//            $query = (new SphinxQL($conn))->insert()->into('products_rt')->columns($sphinxFields);
//            foreach ($handlerProductList as $forData) {
//                $values = array_values($forData);
//                $query->values($values);
//            }
//            $result = $query->execute();
//            return $result->getAffectedRows();
            //稳妥起见还是一条一条的插入吧 (假设失败,会导致大批量的索引得不到插入)
            foreach ($handlerProductList as $forData) {
                $res = (new SphinxQL($conn))->insert()->into('products_rt')->set($forData)->execute();
            }
        }
    }
}
