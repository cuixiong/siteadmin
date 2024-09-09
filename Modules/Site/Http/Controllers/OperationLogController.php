<?php

namespace Modules\Site\Http\Controllers;

use App\Const\QueueConst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Site\Http\Models\OperationLog;
use Modules\Site\Http\Models\ProductsCategory;
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
        $request = request();
        $site = $request->header('Site');
        $category = $site ? 2 : 1;
        // if(!empty($site)){
        //     $site = Site::where('english_name',$site)->value('name');
        // }
        $name = $request->route()->getName();
        $route = request()->path();
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
                'module'     => strtolower($ClassName),
                'created_by' => request()->user->id,
                'created_at' => time(),
            ]
        ];
        $data = json_encode($data);
        \Log::error('返回结果数据:'.$data.'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
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
                \Log::error('返回结果数据:'.json_encode([$model]).'  文件路径:'.__CLASS__.'  行号:'.__LINE__);
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
                $title = $ColumnComment.'从'.$OriginalValue.'更新为：'.$NewValue;
                $contents[] = $title;
            }
        }
        $contents = implode('、', $contents);

        return $contents;
    }
}
