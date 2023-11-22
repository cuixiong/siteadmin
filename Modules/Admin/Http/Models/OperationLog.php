<?php

namespace Modules\Admin\Http\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Admin\Http\Models\Base;
class OperationLog extends Base
{
    protected $appends = ['category_text','type_text'];

    public function __construct()
    {
        $this->SetTableName();
        if(Schema::hasTable($this->table) == false){
            $this->CreateTable();
        }
    }
    public function getCategoryTextAttribute($value)
    {
        if(isset($this->attributes['category'])){
            $text = DictionaryValue::GetNameAsCode('Route_Classification',$this->attributes['category']);
            return $text;
        }

    }

    public function getTypeTextAttribute($value)
    {
        if(isset($this->attributes['type'])){
            $text = DictionaryValue::GetNameAsCode('OperationLog_Type',$this->attributes['type']);
            return $text;
        }
    }

    protected function SetTableName($year = '')
    {
        $year = $year ? $year : date('Y');
        $table = 'operation_log_'. $year;
        $this->table = $table;
        return $table;
    }

    private function CreateTable()
    {
        $res = DB::select("SHOW CREATE TABLE `operation_logs` ");
        $array = get_object_vars($res[0]);
        $createTableStatement = '';
        foreach ($array as $key => $value) {
            if($key == 'Create Table'){
                $createTableStatement = $value;
            }
        }
        $createTableStatement = str_replace('operation_logs', $this->table, $createTableStatement);
        DB::unprepared($createTableStatement);
    }
}