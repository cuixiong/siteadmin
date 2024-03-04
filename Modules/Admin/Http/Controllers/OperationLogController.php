<?php

namespace Modules\Admin\Http\Controllers;

use App\Services\RabbitmqService;
use Illuminate\Http\Request;
use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\OperationLog;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Department;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;
use Modules\Admin\Http\Models\Site;
use Modules\Admin\Http\Models\User;
use Modules\Site\Http\Models\ProductsCategory;

class OperationLogController extends CrudController
{
    public static function AddLog($model, $type)
    {
        $ClassName = class_basename($model);
        if ($type == 'update') {
            $content = method_exists(new OperationLogController, $ClassName) ? self::$ClassName($model) : self::getContent($model);
        } else if ($type == 'insert') {
            $content = "新增了ID=" . $model->id;
        } else if ($type == 'delete') {
            $content = '删除了ID=' . $model->id . '的数据行。';
        }
        $request = request();
        $site = $request->header('Site');

        $category = $site ? 2 : 1;
        // if(!empty($site)){
        //     $site = Site::where('english_name',$site)->value('name');
        // }
        $name = $request->route()->getName();
        $route = request()->path();

        // $model = new OperationLog();

        $data = [
            'type' => $type,
            'category' => $category,
            'route' => $route,
            'title' => $name,
            'content' => $content,
            'site' => $site,
            'module' => strtolower($ClassName),
            'created_by' => request()->user->id,
            'created_at' => time(),
        ];
        $RabbmitMQ = new RabbitmqService;
        $RabbmitMQ->setQueueName('OperationLog');
        $RabbmitMQ->SimpleModePush("Modules\Admin\Http\Controllers\OperationLogController",'SaveLog',$data);
        $RabbmitMQ->close();
    }

    /**
     * 日志入库（废弃，因为MQ挂了这个日志搜集不到，而且在操作性能上会更差   ）
     * 通过MQ入库是在租户端新增日志会导致日志MYSQL按年份的分表出现错误，中央端则没任何问题
     */
    public function SaveLog($params = [])
    {
        try {
            $params = $params['data'];
            if(!empty($params)){
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
                $res = $model->save();
                return true;
            }
        } catch (\Exception $e) {
            file_put_contents(storage_path('logs').'/'.'operation_log_error.log',"\r".$e->getMessage(),FILE_APPEND);
            return false;
        }
    }

    private static function getContent($model)
    {
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
                $title = $ColumnComment . '从' . $OriginalValue . '更新为：' . $NewValue;
                $contents[] = $title;
            }
        }
        $contents = implode('、', $contents);
        return $contents;
    }

    private static function Rule($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at'])) {
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                switch ($field) {
                    case 'parent_id':
                        $OriginalName = Rule::where('id', $OriginalValue)->value('name');
                        $NewName = Rule::where('id', $value)->value('name');

                        break;

                    case 'type':
                        $OriginalName = DictionaryValue::GetNameAsCode('Menu_Type', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Menu_Type', $value);
                        break;

                    case 'category':
                        $OriginalName = DictionaryValue::GetNameAsCode('Route_Classification', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Route_Classification', $value);
                        break;

                    case 'visible':
                        $OriginalName = $OriginalValue == 1 ? '显示' : '隐藏';
                        $NewName = $value == 1 ? '显示' : '隐藏';
                        break;

                    case 'keepAlive':
                        $OriginalName = DictionaryValue::GetNameAsCode('Cache', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Cache', $value);
                        break;

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State', $value);
                        break;

                    default:
                        $OriginalName = is_array($OriginalValue) ? implode(',', $OriginalValue) : $OriginalValue;
                        $NewName = is_array($value) ? implode(',', $value) : $value;
                        break;
                }
                $title = "[$ColumnComment] 从 “ $OriginalName ” 更新为=> “ $NewName ”";
                $contents[] = $title;
            }
        }
        $contents = implode('、', $contents);
        return $contents;
    }


    private static function Role($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at'])) {
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                switch ($field) {
                    case 'rule_id':
                        $OriginalValue = $OriginalValue ? $OriginalValue : [];
                        $OriginalName = Rule::whereIn('id', $OriginalValue)->pluck('name')->toArray();
                        $OriginalName = $OriginalName ? implode(',', $OriginalName) : '';
                        $value = is_array($value) ? $value : ($value ? explode(',', $value) : []);
                        $NewName = Rule::whereIn('id', $value)->pluck('name')->toArray();
                        $NewName = $NewName ? implode(',', $NewName) : '';

                        break;

                    case 'site_rule_id':
                        $OriginalValue = $OriginalValue ? $OriginalValue : [];
                        $OriginalName = Rule::whereIn('id', $OriginalValue)->pluck('name')->toArray();
                        $OriginalName = $OriginalName ? implode(',', $OriginalName) : '';
                        $value = is_array($value) ? $value : ($value ? explode(',', $value) : []);
                        $NewName = Rule::whereIn('id', $value)->pluck('name')->toArray();
                        $NewName = $NewName ? implode(',', $NewName) : '';
                        break;

                    case 'site_id':
                        $OriginalName = Site::whereIn('id', $OriginalValue)->pluck('name')->toArray();
                        $OriginalName = $OriginalName ? implode(',', $OriginalName) : '';
                        $value = is_array($value) ? $value : ($value ? explode(',', $value) : []);
                        $NewName = Site::whereIn('id', $value)->pluck('name')->toArray();
                        $NewName = $NewName ? implode(',', $NewName) : '';
                        break;

                    case 'is_super':
                        $OriginalName = DictionaryValue::GetNameAsCode('Administrator', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Administrator', $value);
                        break;

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State', $value);
                        break;

                    default:
                        $OriginalName = is_array($OriginalValue) ? implode(',', $OriginalValue) : $OriginalValue;
                        $NewName = is_array($value) ? implode(',', $value) : $value;
                        break;
                }
                $title = "[$ColumnComment] 从 “ $OriginalName ” 更新为=> “ $NewName ”";
                $contents[] = $title;
            }
        }
        $contents = implode('、', $contents);
        return $contents;
    }

    private static function Department($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at'])) {
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                switch ($field) {
                    case 'parent_id':
                        $OriginalName = Department::where('id', $OriginalValue)->value('name');
                        $NewName = Department::where('id', $value)->value('name');
                        break;

                    case 'default_role':
                        $OriginalName = Role::whereIn('id', $OriginalValue)->pluck('name')->toArray();
                        $OriginalName = $OriginalName ? implode(',', $OriginalName) : '';
                        $NewName = Role::whereIn('id', explode(',', $value))->pluck('name')->toArray();
                        $NewName = $NewName ? implode(',', $NewName) : '';
                        break;

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State', $value);
                        break;

                    default:
                        $OriginalName = is_array($OriginalValue) ? implode(',', $OriginalValue) : $OriginalValue;
                        $NewName = is_array($value) ? implode(',', $value) : $value;
                        break;
                }
                $title = "[$ColumnComment] 从 “ $OriginalName ” 更新为=> “ $NewName ”";
                $contents[] = $title;
            }
        }
        $contents = implode('、', $contents);
        return $contents;
    }

    private static function User($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at', 'notice_ids'])) {
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                switch ($field) {
                    case 'role_id':
                        $OriginalName = Role::whereIn('id', explode(',', $OriginalValue))->pluck('name')->toArray();
                        $OriginalName = $OriginalName ? implode(',', $OriginalName) : '';
                        $NewName = Role::whereIn('id', explode(',', $value))->pluck('name')->toArray();
                        $NewName = $NewName ? implode(',', $NewName) : '';
                        break;

                    case 'department_id':
                        $OriginalName = Department::where('id', $OriginalValue)->value('name');
                        $OriginalName = $OriginalName ? $OriginalName : '';
                        $NewName = Department::where('id', $value)->value('name');
                        $NewName = $NewName ? $NewName : '';
                        break;

                    case 'gender':
                        $OriginalName = DictionaryValue::GetNameAsCode('Gender', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Gender', $value);
                        break;

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State', $value);
                        break;

                    default:
                        $OriginalName = is_array($OriginalValue) ? implode(',', $OriginalValue) : $OriginalValue;
                        $NewName = is_array($value) ? implode(',', $value) : $value;
                        break;
                }
                $title = "[$ColumnComment] 从 “ $OriginalName ” 更新为=> “ $NewName ”";
                $contents[] = $title;
            }
        }
        $contents = implode('、', $contents);
        return $contents;
    }

    private static function Site($model)
    {

        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at'])) {

                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);

                switch ($field) {

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State', $value);
                        break;
                    default:
                        $OriginalName = $OriginalValue;
                        $NewName = $value;
                        break;
                }
            } else {
                continue;
            }

            $title = "[$ColumnComment] 从 “$OriginalName($OriginalValue)” 更新为=> “$NewName($value)”";
            $contents[] = $title;
        }

        $contents = implode('、', $contents);
        return $contents;
    }


    private static function Products($model)
    {

        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at'])) {

                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                if($OriginalValue == $value){
                    continue;
                }
                switch ($field) {

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State', $value);
                        break;
                    case 'show_hot':
                        $OriginalName = DictionaryValue::GetNameAsCode('Show_Home_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Show_Home_State', $value);
                        break;
                    case 'show_recommend':
                        $OriginalName = DictionaryValue::GetNameAsCode('Show_Home_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Show_Home_State', $value);
                        break;
                    case 'show_home':
                        $OriginalName = DictionaryValue::GetNameAsCode('Show_Home_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Show_Home_State', $value);
                        break;
                    case 'discount_type':
                        $OriginalName = DictionaryValue::GetNameAsCode('Discount_Type', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Discount_Type', $value);
                        break;
                    case 'have_sample':
                        $OriginalName = DictionaryValue::GetNameAsCode('Has_Sample', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Has_Sample', $value);
                        break;
                    case 'category_id':
                        $OriginalName = ProductsCategory::where('id', $OriginalValue)->value('name');
                        $NewName = DictionaryValue::where('id', $value)->value('name');
                        break;
                    default:
                        $OriginalName = $OriginalValue;
                        $NewName = $value;
                        break;
                }
            } else {
                continue;
            }
            if ($OriginalValue != $value) {
                if ($OriginalName != $OriginalValue) {
                    $title = "[$ColumnComment] 从 “$OriginalName($OriginalValue)” 更新为=> “$NewName($value)”";
                } else {
                    $title = "[$ColumnComment] 从 “" . $OriginalValue . "” 更新为=> “" . $value . "”";
                }
                $contents[] = $title;
            }
        }

        $contents = implode('、', $contents);
        return $contents;
    }
    private static function Order($model)
    {

        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at'])) {

                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                if($OriginalValue == $value){
                    continue;
                }
                switch ($field) {

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State', $value);
                        break;
                    default:
                        $OriginalName = $OriginalValue;
                        $NewName = $value;
                        break;
                }
            } else {
                continue;
            }
            if ($OriginalValue != $value) {
                if ($OriginalName != $OriginalValue) {
                    $title = "[$ColumnComment] 从 “$OriginalName($OriginalValue)” 更新为=> “$NewName($value)”";
                } else {
                    $title = "[$ColumnComment] 从 “" . $OriginalValue . "” 更新为=> “" . $value . "”";
                }
                $contents[] = $title;
            }
        }

        $contents = implode('、', $contents);
        return $contents;
    }

    
    private static function News($model)
    {

        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if (!in_array($field, ['created_by', 'updated_by', 'created_at', 'updated_at'])) {

                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                if($OriginalValue == $value){
                    continue;
                }
                switch ($field) {

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State', $OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State', $value);
                        break;
                    default:
                        $OriginalName = $OriginalValue;
                        $NewName = $value;
                        break;
                }
            } else {
                continue;
            }
            if ($OriginalValue != $value) {
                if ($OriginalName != $OriginalValue) {
                    $title = "[$ColumnComment] 从 “$OriginalName($OriginalValue)” 更新为=> “$NewName($value)”";
                } else {
                    $title = "[$ColumnComment] 从 “" . $OriginalValue . "” 更新为=> “" . $value . "”";
                }
                $contents[] = $title;
            }
        }

        $contents = implode('、', $contents);
        return $contents;
    }

    /**
     * get dict options
     * @return Array
     */
    public function options(Request $request)
    {
        $options = [];
        $codes = ['Route_Classification', 'OperationLogModule'];
        $NameField = $request->HeaderLanguage == 'en' ? 'english_name as label' : 'name as label';
        $data = DictionaryValue::whereIn('code', $codes)->where('status', 1)->select('code', 'value', $NameField)->orderBy('sort', 'asc')->get()->toArray();
        if (!empty($data)) {
            foreach ($data as $map) {
                $options[$map['code']][] = ['label' => $map['label'], 'value' => $map['value']];
            }
        }
        $options['site'] = (new Site)->GetListLabel(['name as value', $NameField], false, '', ['status' => '1']);
        $options['user'] = (new User)->GetListLabel(['id as value', 'name as label'], false, '', ['status' => '1']);
        ReturnJson(TRUE, '', $options);
    }
}
