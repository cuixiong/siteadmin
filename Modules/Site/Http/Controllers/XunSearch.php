<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Site\Http\Models\ProductsDescription;
use XS;
use XSDocument;
use Modules\Site\Http\Models\Products;
use XSTokenizerScws;
use XSTokenizerXlen;

class XunSearch extends CrudController
{
    public function search(Request $request)
    {
        $this->handlerDescRules();
        dd("ok");
        $keyword = $request->keyword;
        $xs = new XS('/www/wwwroot/yadmin/admin/Modules/Site/Config/xunsearch/MMG_CN.ini');
        $search = $xs->search;
        $docs = $search->search($keyword);
        $count = $search->count($keyword);
        var_dump($docs, $count);
        die;
    }

    public function handlerDescRules() {
        $product = Products::query()->where("id" , 471)->first()->toArray();
        $year =  date("Y" , $product['published_date']);
        $desc_info = (new ProductsDescription($year))->where("product_id" , $product['id'])->first();
        $rulues_type = [
            '2023年市场份额',
            '2024年市场份额',
        ];
        $description = $desc_info['description'];
        $description = $desc_info->description;
//        dd($description);


        foreach ($rulues_type as $rule_type){
            //$pattern = '/2023年市场份额\r\n((?:(?:\s+[^\r\n]*\r\n))*)/';
            $pattern = '/'.$rule_type.'[\r\n]+((?:(?:\s+[^\r\n]*[\r\n]+))*)/';
            if (preg_match($pattern, $description, $matches)) {
                // 打印提取的部分
                $applicton = $matches[1];
                if(!empty($applicton )){
                    $applicton = str_replace(' ', '', $applicton);
                }
                dd($applicton);
                $rs = Products::query()->where("id" , 471)->update(['application' => $applicton]);
                dd($rs);
            }else{
                dd("eror");
            }
        }




    }

    public function add(Request $request) {
        $xs = new XS('/www/wwwroot/yadmin/admin/Modules/Site/Config/xunsearch/MMG_CN.ini');
        $index = $xs->index;
        $data = Products::where('id', '>', 0)->where('id', '<', 51)->limit(50)->get()->toArray();
        foreach ($data as $map) {
            $ini = [
                "pid"                 => $map['id'],
                "name"                => $map['name'],
                "english_name"        => $map['english_name'],
                "thumb"               => $map['thumb'],
                "publisher_id"        => $map['publisher_id'],
                "category_id"         => $map['category_id'],
                "country_id"          => $map['country_id'],
                "price"               => $map['price'],
                "keywords"            => $map['keywords'],
                "url"                 => $map['url'],
                "published_date"      => $map['published_date'],
                "status"              => $map['status'],
                "author"              => $map['author'],
                "show_home"           => $map['show_home'],
                "have_sample"         => $map['have_sample'],
                "discount"            => $map['discount'],
                "discount_amount"     => $map['discount_amount'],
                "discount_type"       => $map['discount_type'],
                "discount_time_begin" => $map['discount_time_begin'],
                "discount_time_end"   => $map['discount_time_end'],
                "pages"               => $map['pages'],
                "tables"              => $map['tables'],
                "hits"                => $map['hits'],
                "show_hot"            => $map['show_hot'],
                "show_recommend"      => $map['show_recommend'],
                "sort"                => $map['sort'],
                "updated_at"          => $map['updated_at'],
                "created_at"          => $map['created_at'],
                "updated_by"          => $map['updated_by'],
                "created_by"          => $map['created_by'],
                "downloads"           => $map['downloads'],
            ];
            $doc = new XSDocument();
            $doc->setFields($ini);
            $index->add($doc);
        }
    }

    public function clean(Request $request) {
        $xs = new XS('/www/wwwroot/yadmin/admin/Modules/Site/Config/xunsearch/MMG_CN.ini');
        $index = $xs->index;
        $index->clean();
    }

    public function AddToMQ(Request $request) {
        // 设置当前脚本最大执行时间为 120 秒
        set_time_limit(-1);
        ini_set('memory_limit', -1);
        $ids = Products::pluck('id');
        $model = new Products();
        foreach ($ids as $id) {
            $model->syncSearchIndex($id, 'add');
        }
    }
}
