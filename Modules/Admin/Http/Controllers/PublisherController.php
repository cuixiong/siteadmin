<?php

namespace Modules\Admin\Http\Controllers;

use App\Const\NotityTypeConst;
use App\Helper\ImageHelper;
use Modules\Admin\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Admin\Http\Models\Publisher;
use Modules\Admin\Http\Models\DictionaryValue;

class PublisherController extends CrudController
{
    public function changeEnable(Request $request)
    {
        try {
            $params = $request->input();;
            if (!isset($params['id']) || !isset($params['status'])) {
                ReturnJson(FALSE, '修改失败！');
            }

            $record = $this->ModelInstance()->findOrFail($params['id']);
            $input['status'] = $params['status'];
            if (!$record->update($input)) {
                ReturnJson(FALSE, trans('lang.update_error'));
            }
            ReturnJson(TRUE, trans('lang.update_success'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), []);
        }
        ReturnJson(TRUE, '修改成功！');
    }

    public function getPublisher(Request $request)
    {
        $data = Publisher::select('id', 'name')->get()->toArray();

        ReturnJson(TRUE, trans('lang.request_success'), $data);
    }


    /**
     * 获取搜索下拉列表
     * @param $request 请求信息
     */
    public function searchDroplist(Request $request)
    {
        try {
            $data = [];
            // 状态开关
            if ($request->HeaderLanguage == 'en') {
                $field = ['english_name as label', 'value'];
            } else {
                $field = ['name as label', 'value'];
            }
            $data['status'] = (new DictionaryValue())->GetListLabel($field, false, '', ['code' => 'Switch_State','status' => 1], ['sort' => 'ASC']);

            ReturnJson(TRUE, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(FALSE, $e->getMessage());
        }
    }


    /**
     * 上传logo
     * @param $request 请求信息
     *
     */
    public function uploadLogo(Request $request)
    {

        $file = $request->file('file');
        if (!isset($file) || empty($file)) {
            ReturnJson(FALSE, trans('lang.param_empty'));
        }
        $originalName = $file->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME) . '_' . time() . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
        ReturnJson(TRUE, trans('lang.request_success'), ImageHelper::SaveImage($file, $filename, '/uploads/publisher/'));
    }

    public function syncSiteDb() {
        syncSiteDbByType(NotityTypeConst::SYNC_SITE_PUBLISHER);
        ReturnJson(true, trans('lang.request_success'));
    }

}
