<?php

namespace Modules\Admin\Http\Controllers;

use Modules\Admin\Http\Controllers\CrudController;
use Modules\Admin\Http\Models\OperationLog;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Models\Department;
use Modules\Admin\Http\Models\DictionaryValue;
use Modules\Admin\Http\Models\Role;
use Modules\Admin\Http\Models\Rule;
use Modules\Admin\Http\Models\Site;

class OperationLogController extends CrudController
{
    public static function AddLog($model,$type)
    {
        $ClassName = class_basename($model);
        $content = method_exists(new OperationLogController,$ClassName) ? self::$ClassName($model) : self::getContent($model);

        $request = request();
        $site = $request->header('Site');

        $category = $site ? 2 : 1;
        if(!empty($site)){
            $site = Site::where('english_name',$site)->value('name');
        }
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
        foreach ($dirty as $field => $value) {
            if(!in_array($field,['created_by','updated_by','created_at','updated_at'])){
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $title = $ColumnComment .'从'.$model->getOriginal($field) .'更新为：'. $value;
                $contents[] = $title;
            }
        }
        $contents = implode('、',$contents);
        return $contents;
    }

    private static function Rule($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if(!in_array($field,['created_by','updated_by','created_at','updated_at'])){
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                switch ($field) {
                    case 'parent_id':
                        $OriginalName = Rule::where('id', $OriginalValue)->value('name');
                        $NewName = Rule::where('id', $value)->value('name');

                    break;

                    case 'type':
                        $OriginalName = DictionaryValue::GetNameAsCode('Menu_Type',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Menu_Type',$value);
                    break;

                    case 'category':
                        $OriginalName = DictionaryValue::GetNameAsCode('Route_Classification',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Route_Classification',$value);
                    break;

                    case 'visible':
                        $OriginalName = $OriginalValue == 1? '显示' : '隐藏';
                        $NewName = $value == 1? '显示' : '隐藏';
                    break;

                    case 'keepAlive':
                        $OriginalName = DictionaryValue::GetNameAsCode('Cache',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Cache',$value);
                    break;

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State',$value);
                    break;
                    
                    default:
                        $OriginalName = $OriginalValue;
                        $NewName = $value;
                    break;
                }
                $title = "[$ColumnComment] 从 “$OriginalName($OriginalValue)” 更新为=> “$NewName($value)”";
                $contents[] = $title;
            }
        }
        $contents = implode('、',$contents);
        return $contents;
    }


    private static function Role($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if(!in_array($field,['created_by','updated_by','created_at','updated_at'])){
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                switch ($field) {
                    case 'rule_id':
                        $OriginalName = Rule::whereIn('id', $OriginalValue)->pluck('name');
                        $OriginalName = $OriginalName ? implode(',',$OriginalName) : '';
                        $NewName = Rule::whereIn('id', $value)->pluck('name');
                        $NewName = $NewName ? implode(',',$NewName) : '';

                    break;

                    case 'site_rule_id':
                        $OriginalName = Rule::whereIn('id', $OriginalValue)->pluck('name');
                        $OriginalName = $OriginalName ? implode(',',$OriginalName) : '';
                        $NewName = Rule::whereIn('id', $value)->pluck('name');
                        $NewName = $NewName ? implode(',',$NewName) : '';
                    break;

                    case 'site_id':
                        $OriginalName = Site::whereIn('id', $OriginalValue)->pluck('name');
                        $OriginalName = $OriginalName ? implode(',',$OriginalName) : '';
                        $NewName = Site::whereIn('id', $value)->pluck('name');
                        $NewName = $NewName ? implode(',',$NewName) : '';
                    break;

                    case 'is_super':
                        $OriginalName = DictionaryValue::GetNameAsCode('Administrator',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Administrator',$value);
                    break;

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State',$value);
                    break;
                    
                    default:
                        $OriginalName = $OriginalValue;
                        $NewName = $value;
                    break;
                }
                $title = "[$ColumnComment] 从 “$OriginalName($OriginalValue)” 更新为=> “$NewName($value)”";
                $contents[] = $title;
            }
        }
        $contents = implode('、',$contents);
        return $contents;
    }

    private static function Department($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if(!in_array($field,['created_by','updated_by','created_at','updated_at'])){
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                switch ($field) {
                    case 'parent_id':
                        $OriginalName = Department::where('id', $OriginalValue)->value('name');
                        $NewName = Department::where('id', $value)->value('name');
                    break;

                    case 'default_role':
                        $OriginalName = Role::whereIn('id', $OriginalValue)->pluck('name');
                        $OriginalName = $OriginalName ? implode(',',$OriginalName) : '';
                        $NewName = Role::whereIn('id', $value)->pluck('name');
                        $NewName = $NewName ? implode(',',$NewName) : '';
                    break;

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State',$value);
                    break;
                    
                    default:
                        $OriginalName = $OriginalValue;
                        $NewName = $value;
                    break;
                }
                $title = "[$ColumnComment] 从 “$OriginalName($OriginalValue)” 更新为=> “$NewName($value)”";
                $contents[] = $title;
            }
        }
        $contents = implode('、',$contents);
        return $contents;
    }

    private static function User($model)
    {
        $dirty = $model->getDirty();
        $contents = [];
        $DbManager = DB::getDoctrineSchemaManager()->listTableDetails($model->getTable());
        foreach ($dirty as $field => $value) {
            if(!in_array($field,['created_by','updated_by','created_at','updated_at'])){
                $ColumnComment = $DbManager->getColumn($field)->getComment();
                $ColumnComment = $ColumnComment ? $ColumnComment : $field;
                $OriginalValue = $model->getOriginal($field);
                switch ($field) {
                    case 'role_id':
                        $OriginalName = Role::whereIn('id', $OriginalValue)->pluck('name');
                        $OriginalName = $OriginalName ? implode(',',$OriginalName) : '';
                        $NewName = Role::whereIn('id', $value)->pluck('name');
                        $NewName = $NewName ? implode(',',$NewName) : '';
                    break;

                    case 'department_id':
                        $OriginalName = Department::where('id', $OriginalValue)->value('name');
                        $OriginalName = $OriginalName ? implode(',',$OriginalName) : '';
                        $NewName = Department::where('id', $value)->value('name');
                        $NewName = $NewName ? implode(',',$NewName) : '';
                    break;

                    case 'gender':
                        $OriginalName = DictionaryValue::GetNameAsCode('Gender',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Gender',$value);
                    break;

                    case 'status':
                        $OriginalName = DictionaryValue::GetNameAsCode('Switch_State',$OriginalValue);
                        $NewName = DictionaryValue::GetNameAsCode('Switch_State',$value);
                    break;
                    
                    default:
                        $OriginalName = $OriginalValue;
                        $NewName = $value;
                    break;
                }
                $title = "[$ColumnComment] 从 “$OriginalName($OriginalValue)” 更新为=> “$NewName($value)”";
                $contents[] = $title;
            }
        }
        $contents = implode('、',$contents);
        return $contents;
    }
}
