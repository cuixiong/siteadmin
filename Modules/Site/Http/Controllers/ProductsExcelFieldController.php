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
    protected function resetSort(Request $request)
    {
        $ids = $request->ids;
        if (!is_array($ids)) {
            $ids = explode(",", $ids);
        }
        foreach ($ids as $key => $id) {
            $record = $this->ModelInstance()->find($id);
            if ($record) {
                $record->update([
                    'sort' => $key + 1,
                ]);
            }
        }
        ReturnJson(TRUE, trans('lang.request_success'));
    }
}
