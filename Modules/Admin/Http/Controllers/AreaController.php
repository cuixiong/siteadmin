<?php

namespace Modules\Admin\Http\Controllers;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Modules\Admin\Http\Models\Area;

class AreaController extends CrudController
{
    public function getArea(Request $request)
    {
        $area_id = $request->input('area_id');
        if(empty($area_id)){
            $list = Area::where('pid',1)->select('id','name')->get()->toArray();
        }else{
            $list = Area::where('pid', $area_id)->select('id','name')->get()->toArray();
        }

        ReturnJson(TRUE,'请求成功',$list);
    }
}
