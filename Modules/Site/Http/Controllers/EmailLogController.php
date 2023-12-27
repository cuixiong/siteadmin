<?php
namespace Modules\Site\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Site\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Site\Http\Models\Email;
use Modules\Site\Http\Models\EmailScene;

class EmailLogController extends CrudController{
    public function option(Request $request)
    {
        $data = [
            'status'    =>  DictionaryValue::GetOption('EmailLog_Status'),
            'scence'    =>  (new EmailScene)->GetListLabel(['id as value', 'name as label']),
            'email'    =>  (new Email)->GetListLabel(['id as value', 'email as label']),
        ];
        ReturnJson(true,'',$data);
    }
}