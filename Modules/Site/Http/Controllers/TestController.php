<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use XS;
use XSDocument;
use Modules\Site\Http\Models\Products;

class TestController extends CrudController
{
    public function test() {

        $proModel = new Products();
        // 2.88s
        $rs = $proModel->PushNewXunSearchMQ(478 , 'add');

        // 2.81s
      //  $rs = $proModel->PushNewXunSearchMQ(478 , 'update');

        //3.83s
//        $rs = $proModel->PushNewXunSearchMQ(478 , 'delete');
        dd($rs);


    }


}
