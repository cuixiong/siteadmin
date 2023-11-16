<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\EmailScene;

class EmailLogController extends CrudController{
    public function option(Request $request)
    {
        $data = [
            'status'    =>  DictionaryValue::GetOption('EmailLog_Status'),
            'scence'    =>  (new EmailScene)->GetListLabel(['id as value', 'name as label']),
        ];
        ReturnJson(true,'',$data);
    }
}