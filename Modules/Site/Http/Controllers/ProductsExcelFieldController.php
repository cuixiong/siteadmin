<?php

namespace Modules\Site\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Site\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductsExcelFieldController extends CrudController
{


    /**
     * 调整排序
     * @param Request $request
     */
    protected function resetSort(Request $request){

    }
    
}
