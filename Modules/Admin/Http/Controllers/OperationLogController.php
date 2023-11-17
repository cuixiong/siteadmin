<?php

namespace Modules\Admin\Http\Controllers;

use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\OperationLog;
use Illuminate\Support\Facades\DB;
class OperationLogController extends CrudController
{
    public static function AddLog($model,$type)
    {
        $ClassName = get_class($model);
        $content = method_exists($model,$ClassName) ? self::$ClassName($model) : self::getContent($model);

        $request = request();
        $site = $request->header('Site');
        $category = $site ? 2 : 1;
        $name = $request->route()->getName();
        $route = request()->path();

        $model = new OperationLog();

        $model->type = $type;
        $model->category = $category;
        $model->route = $route;
        $model->title = $name;
        $model->content = $content;
        $model->site = $site;
        $model->save();
    }

    private static function getContent($model)
    {
        $dirty = $model->getDirty();

        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        $ageColumnComment = $DbManager->getColumn('id')->getComment();
        $sexColumnComment = $DbManager->getColumn('sex')->getComment();
        var_dump($DbManager);die;
        foreach ($dirty as $field => $value) {
            if(!in_array($field,['created_by','updated_by','created_at','updated_at'])){
                $FiledName = $DbManager[$field]->getName();

                var_dump($FiledName);die;
                $data = [
                    'key' => $field,
                    'value' => $value,
                    'original' => $model->getOriginal($field)
                ];
                $contents[] = $data;
            }
        }
    }

    private static function Rule($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        foreach ($dirty as $field => $value) {
            if(!in_array($field,['created_by','updated_by','created_at','updated_at'])){
                $data = [
                    'key' => $field,
                    'value' => $value,
                    'original' => $model->getOriginal($field)
                ];
                $contents[] = $data;
            }
        }
    }
}
