<?php
/**
 * SiteCrontabController.php UTF-8
 * 同步脚本(按钮)接口调用
 *
 * @date    : 2024/6/20 10:11 上午
 *
 * @license 这不是一个自由软件，未经授权不许任何使用和传播。
 * @author  : cuizhixiong <cuizhixiong@qyresearch.com>
 * @version : 1.0
 */

namespace Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;

class SiteCrontabController extends Controller {
    public $site  = '';
    public $isCli = false;

    public function handlerProductStatus() {
        try {
            //兼容site
            $site = request()->header('Site');
            if (empty($site) && !empty($this->site)) {
                $site = $this->site;
            }
            if (empty($site)) {
                throw new \Exception("site is empty");

                return false;
            }
            tenancy()->initialize($site);
            $this->handlerShowHotData($site);
            $this->handlerRecommendData($site);
        } catch (\Exception $e) {
            // 处理异常
            if ($this->isCli) {
                throw $e;
            } else {
                \Log::error('处理报告状态异常:'.json_encode([$e->getMessage()]));
                ReturnJson(false, $e->getMessage());
            }
        }
    }

    public function handlerShowHotData($site) {
        //1. 获取所有精品报告的id
        $productIds = Products::query()->where("show_hot", 1)->pluck("id")->toArray();
        //2. 全部设置为非精品
        $updData = [
            'show_hot' => 0,
        ];
        $rs = Products::query()->whereIn("id", $productIds)->update($updData);
        $products = new Products();
        //3. 同步sphinx
        $this->syncProductSphinx($rs, $productIds, $products, $site);
        //4. 获取最新的报告, 设置为精品
        $id_list = [];
        $keywords_list = [];
        $this->getHotProductList($id_list, $keywords_list);
        $productIds = $id_list;
//        $productIds = Products::query()->where("status", 1)
//                              ->orderBy("sort", "asc")
//                              ->orderBy("published_date", "desc")
//                              ->orderBy("id", "desc")
//                              ->limit(6)->pluck("id")->toArray();
        $updData = [
            'show_hot' => 1,
        ];
        $rs = Products::query()->whereIn("id", $productIds)->update($updData);
        //5. 同步sphinx
        $this->syncProductSphinx($rs, $productIds, $products, $site);
    }

    public function handlerRecommendData($site) {
        //1. 获取所有推荐报告的id
        $productIds = Products::query()->where("show_recommend", 1)->pluck("id")->toArray();
        //2. 全部设置为非推荐
        $updData = [
            'show_recommend' => 0,
        ];
        $rs = Products::query()->whereIn("id", $productIds)->update($updData);
        $products = new Products();
        //3. 同步sphinx
        $this->syncProductSphinx($rs, $productIds, $products, $site);
        //4. 获取所有推荐分类
        $pcIdList = ProductsCategory::query()->where("status", 1)
                                    ->where("is_recommend", 1)
                                    ->pluck("id")->toArray();
        if (!empty($pcIdList)) {
            $updData = [
                'show_recommend' => 1
            ];
            foreach ($pcIdList as $pcId) {
                //5. 每个分类下, 获取最新的6条报告, 并修改为推荐状态, 并同步sphinx
                $id_list = [];
                $keywords_list = [];
                $this->getRecomProductList($id_list, $keywords_list, $pcId);
//                $productIds = Products::query()->where("category_id", $pcId)
//                                      ->where("status", 1)
//                                      ->orderBy("sort", "asc")
//                                      ->orderBy("published_date", "desc")
//                                      ->orderBy("id", "desc")
//                                      ->limit(6)->pluck("id")->toArray();
                $productIds = $id_list;
                $rs = Products::query()->whereIn("id", $productIds)->update($updData);
                $this->syncProductSphinx($rs, $productIds, $products, $site);
            }
        }
    }

    /**
     *
     * @param int      $rs
     * @param array    $productIds
     * @param Products $products
     * @param          $site
     *
     */
    private function syncProductSphinx(int $rs, array $productIds, Products $products, $site): void {
        if ($rs > 0) {
            foreach ($productIds as $id) {
                $products->syncSearchIndex($id, 'update', $site);
            }
        }
    }

    public function getHotProductList(&$id_list, &$keywords_list, $layer = 0, $limit = 6) {
        if ($limit <= $layer) {
            return true;
        }
        $pinfo = Products::query()->where("status", 1)
                         ->where("author", "完成报告")
                         ->orderBy("sort", "asc")
                         ->orderBy("published_date", "desc")
                         ->orderBy("id", "desc");
        if(!empty($keywords_list )){
            //关键词不能重复
            $pinfo = $pinfo->whereNotIn('keywords', $keywords_list);
        }
        if(!empty($id_list )){
            //不能连续ID，中间间隔20
            $last_id = end($id_list);
            $last_id -= 20;
            $pinfo = $pinfo->where("id" , "<=" , $last_id);
        }

        $pinfo = $pinfo->select(["id", "keywords"])->first();
        if (!empty($pinfo)) {
            $id_list[] = $pinfo->id;
            $keywords_list[] = $pinfo->keywords;
        }
        return $this->getHotProductList($id_list, $keywords_list, ++$layer, $limit);
    }


    public function getRecomProductList(&$id_list, &$keywords_list, $cate_id, $layer = 0, $limit = 6) {
        if ($limit <= $layer) {
            return true;
        }
        $pinfo = Products::query()->where("status", 1)
                         ->where("category_id", $cate_id)
                         ->where("author", "完成报告")
                         ->orderBy("sort", "asc")
                         ->orderBy("published_date", "desc")
                         ->orderBy("id", "desc");
        if(!empty($keywords_list )){
            //关键词不能重复
            $pinfo = $pinfo->whereNotIn('keywords', $keywords_list);
        }
        if(!empty($id_list )){
            //不能连续ID，中间间隔20
            $last_id = end($id_list);
            $last_id -= 20;
            $pinfo = $pinfo->where("id" , "<=" , $last_id);
        }

        $pinfo = $pinfo->select(["id", "keywords"])->first();
        if (!empty($pinfo)) {
            $id_list[] = $pinfo->id;
            $keywords_list[] = $pinfo->keywords;
        }
        return $this->getRecomProductList($id_list, $keywords_list, $cate_id, ++$layer, $limit);
    }

}
