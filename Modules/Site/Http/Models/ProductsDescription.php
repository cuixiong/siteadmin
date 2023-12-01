<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductsDescription extends Base
{

    protected $table = 'product_description';

    // 设置允许入库字段,数组形式
    protected $fillable = [
        'product_id',
        'description',
        'description_en',
        'table_of_content',
        'table_of_content_en',
        'tables_and_figures',
        'tables_and_figures_en',
        'companies_mentioned',
    ];

    public function __construct($year = '')
    {
        parent::__construct();
        // //年份必传、数字且为四位
        // if (empty($year) || !is_numeric($year) || strlen($year) !== 4) {
        //     throw new \InvalidArgumentException("Invalid year provided: $year. Year must be a four-digit numeric value.");
        // }
        if (!empty($year) && is_numeric($year) && strlen($year) == 4) {

            $this->setTableName($year);
            $this->checkAndCreateTable();
        }
    }

    //设置表名
    protected function setTableName($year = '')
    {
        $year = $year ? $year : date('Y');
        $table = 'product_description_' . $year;
        $this->table = $table;
        return $table;
    }

    //查询是否存在表否则新建
    private function checkAndCreateTable()
    {
        if (!Schema::hasTable($this->table)) {
            $this->createTable();
        }
    }

    //数据库新建表
    private function createTable()
    {
        // 显式启动事务
        // CREATE TABLE会引起隐式提交事务，导致新增修改报告事务失效报错There is no active transaction 
        // https://qa.1r1g.com/sf/ask/4703871091/ 
        // https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html
        DB::beginTransaction();
        $res = DB::select("SHOW CREATE TABLE `product_description` ");
        $array = get_object_vars($res[0]);
        $createTableStatement = '';
        foreach ($array as $key => $value) {
            if ($key == 'Create Table') {
                $createTableStatement = $value;
            }
        }
        $createTableStatement = str_replace('product_description', $this->table, $createTableStatement);
        DB::unprepared($createTableStatement);
    }

    public function getTableName()
    {
        return $this->table;
    }

    public function saveWithAttributes($attributes)
    {

        $attributes = \Illuminate\Support\Arr::only($attributes, $this->fillable);
        $attributes['updated_at'] = time();

        return DB::table($this->table)->insert($attributes);
    }


    public function updateWithAttributes($attributes)
    {
        $attributes = \Illuminate\Support\Arr::only($attributes, $this->fillable);
        $attributes['updated_at'] = time();
        return DB::table($this->table)->update($attributes);
    }
}
