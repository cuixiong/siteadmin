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
//        $input = $request->all();
//        $optType = $input['opt_type'];
//        $id = $input['id'];
        $proModel = new Products();
        $data = $proModel->findOrCache(2);
        $dataDesc = $proModel->findDescCache(2);
        dd([$data , $dataDesc]);

//        dump(microtime(true));
//        $rs = $proModel->excuteXunSearchReq($id , $optType);
//        dump(microtime(true));
//        dd($rs);

        $model = new Products();
        $record = $model->findOrCache(1);
        dump($record);
//        $record = $model::find(1);
//        $record->delete();
//        $record->name = '广州东站';
//        $record->english_name = 'word';
//        $record->save();
        $record = $model->findOrCache(1);
        dd($record);
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
