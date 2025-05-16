<?php

namespace Modules\Site\Http\Controllers;

use App\Const\QueueConst;
use App\Observers\SiteOperationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Models\Authority;
use Modules\Site\Http\Models\Information;
use Modules\Site\Http\Models\Menu;
use Modules\Site\Http\Models\News;
use Modules\Site\Http\Models\OperationLog;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\ProductsCategory;
use Modules\Site\Http\Models\ProductsExportLog;
use Modules\Site\Http\Models\SystemValue;
use Stancl\Tenancy\Facades\Tenancy;

class OperationLogController extends CrudController {
    public static function AddLog($model, $type) {
        if (php_sapi_name() === 'cli') {
            // 请求来自 Artisan 命令行 , 会导致 $request->route() 返回 null, 因此不记录日志
            return false;
        }
        $ClassName = class_basename($model);
        $content = '';
        if ($type == 'update') {
            $content = method_exists(new OperationLogController, $ClassName) ? self::$ClassName($model)
                : self::getContent($model);
        } else if ($type == 'insert') {
            $content = "新增了ID=".$model->id;
        } else if ($type == 'delete') {
            $content = '删除了ID='.$model->id.'的数据行。';
        }
        //如果没有内容,直接不添加
        if (empty($content)) {
            return false;
        }
        $request = request();
        $site = $request->header('Site');
        $category = $site ? 2 : 1;
        // if(!empty($site)){
        //     $site = Site::where('english_name',$site)->value('name');
        // }
        $name = $request->route()->getName();
        $route = request()->path();
        $groupClassName = ['systemvalue', 'system'];
        $module = strtolower($ClassName);
        if (in_array($module, $groupClassName)) {
            $module = strtolower(class_basename(SystemValue::class));
        }
        $data = [
            'class'  => OperationLogController::class,
            'method' => 'SaveLog',
            'site'   => $site,
            'data'   => [
                'type'       => $type,
                'category'   => $category,
                'route'      => $route,
                'title'      => $name,
                'content'    => $content,
                'site'       => $site,
                'module'     => $module,
                'created_by' => request()->user->id,
                'created_at' => time(),
            ]
        ];
        $data = json_encode($data);
        \App\Jobs\OperationLog::dispatch($data)->onQueue(QueueConst::QUEEU_OPERATION_LOG);
    }

    /**
     * 日志入库（废弃，因为MQ挂了这个日志搜集不到，而且在操作性能上会更差   ）
     * 通过MQ入库是在租户端新增日志会导致日志MYSQL按年份的分表出现错误，中央端则没任何问题
     */
    public function SaveLog($params = []) {
        try {
            $params = $params['data'];
            tenancy()->initialize($params['site']);
            if (!empty($params)) {
                $model = new OperationLog();
                $model->type = $params['type'];
                $model->category = $params['category'];
                $model->route = $params['route'];
                $model->title = $params['title'];
                $model->content = $params['content'];
                $model->site = $params['site'];
                $model->module = $params['module'];
                $model->created_by = $params['created_by'];
                $model->created_at = $params['created_at'];
                //\Log::error('返回结果数据:'.json_encode([$model]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
                $res = $model->save();

                return true;
            }
        } catch (\Exception $e) {
            $fileName = base_path()."/storage/logs/operation_log_error.log";
            file_put_contents($fileName, "\r".$e->getMessage(), FILE_APPEND);

            //file_put_contents(storage_path('logs').'/'.'operation_log_error.log',"\r".$e->getMessage(),FILE_APPEND);
            return false;
        }
    }

    private static function getContent($model) {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at'])) {
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                $OriginalValue = is_array($OriginalValue) ? implode(',', $OriginalValue) : $OriginalValue;
                $NewValue = is_array($value) ? implode(',', $value) : $value;
                if (empty($OriginalValue) && empty($NewValue)) {
                    continue;
                }
                $title = $ColumnComment.'从'.$OriginalValue.'更新为：'.$NewValue;
                $contents[] = $title;
            }
        }
        $contents = implode('、', $contents);

        return $contents;
    }

    /**
     * get dict options
     *
     * @return Array
     */
    public function options(Request $request) {
        $options = [];
        $codes = ['Route_Classification', 'OperationLogModule'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
//        $codes = ['Route_Classification', 'OperationLogModule'];
//        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)
//                               ->orderBy('sort', 'asc')->get()->toArray();
//        if (!empty($data)) {
//            foreach ($data as $map) {
//                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
//            }
//        }
        //strtolower(class_basename(Products::class));
        // TODO: cuizhixiong 2024/9/13 后续优化
        $addData = [];
        $addData['value'] = strtolower(class_basename(Products::class));
        $addData['label'] = '报告模块';
        $options['OperationLogModule'][] = $addData;
        $addData['value'] = strtolower(class_basename(News::class));
        $addData['label'] = '新闻模块';
        $options['OperationLogModule'][] = $addData;
        $addData['value'] = strtolower(class_basename(Order::class));
        $addData['label'] = '订单模块';
        $options['OperationLogModule'][] = $addData;
        $addData['value'] = strtolower(class_basename(Menu::class));
        $addData['label'] = '菜单模块';
        $options['OperationLogModule'][] = $addData;
        $addData['value'] = strtolower(class_basename(Information::class));
        $addData['label'] = '资讯模块';
        $options['OperationLogModule'][] = $addData;
        $addData['value'] = strtolower(class_basename(Authority::class));
        $addData['label'] = '权威引用模块';
        $options['OperationLogModule'][] = $addData;
        $addData['value'] = strtolower(class_basename(SystemValue::class));
        $addData['label'] = '网点配置模块';
        $options['OperationLogModule'][] = $addData;
        $addData['value'] = strtolower(class_basename(ProductsExportLog::class));
        $addData['label'] = '导出记录模块';
        $options['OperationLogModule'][] = $addData;
        //$options['site'] = (new Site)->GetListLabel(['name as value', $NameField], false, '', ['status' => '1']);
//
        $siteId = getSiteId();
        $role_id_list = Role::query()->where('site_id', 'like', '%'.$siteId.'%')
                            ->orWhere('is_super', 1)
                            ->pluck('id')->toArray();
        $options['user'] = User::query()
                               ->whereIn('role_id', $role_id_list)
                               ->where("status", 1)
                               ->selectRaw('id as value,nickname as label')
                               ->get()->toArray();
        ReturnJson(true, '', $options);
    }
}
