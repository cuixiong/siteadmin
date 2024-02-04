<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use XS;
use XSDocument;
use Modules\Site\Http\Models\Products;

class XunSearch extends CrudController
{
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $xs = new XS('/www/wwwroot/yadmin/admin/Modules/Site/Config/xunsearch/product.ini');
        $search = $xs->search;
        $docs = $search->search($keyword);
        $count = $search->count($keyword);
        var_dump($docs,$count);die;
    }
    public function add(Request $request)
    {
        $xs = new XS('/www/wwwroot/yadmin/admin/Modules/Site/Config/xunsearch/product.ini');
        $index = $xs->index;
        $data = Products::where('id','>',0)->where('id','<',51)->limit(50)->get()->toArray();
        foreach ($data as $map){
            $doc = new XSDocument();
            $doc->setFields($map);
            $index->add($doc); 
        }
    }

    public function clean(Request $request)
    {
        $xs = new XS('/www/wwwroot/yadmin/admin/Modules/Site/Config/xunsearch/product.ini');
        $index = $xs->index;
        $index->clean();
    }
}
