<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use XS;
use XSDocument;
use Modules\Site\Http\Models\Products;

class TestController extends CrudController
{
    public function test(Request $request) {
        $input = $request->all();
        $optType = $input['opt_type'];
        $id = $input['id'];
        $proModel = new Products();
        dump(microtime(true));
        $rs = $proModel->excuteXunSearchReq($id , $optType);
        dump(microtime(true));
        dd($rs);
    }

    public function searchTest(Request $request) {

        $input = $request->all();
        $searchword = $input['searchword'];
        $SiteName = $request->header('Site');
        dump(microtime(true));
        $RootPath = base_path();
        $xs = new XS($RootPath.'/Modules/Site/Config/xunsearch/'.$SiteName.'.ini');
        $search = $xs->search;
        $docs = $search->setFuzzy()->search($searchword);
        foreach ($docs as $doc){
            dd($doc['keywords']);
        }
        dump(microtime(true));
        dd([$docs]);
    }


}
