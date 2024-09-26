<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Controllers\CrudController;

class DatabaseController extends CrudController
{
    // jump to ip phpmyadmin
    public function HrefMyAdmin(Request $request)
    {
        if ($request->id) {
            $database = \Modules\Admin\Http\Models\Database::find($request->id);
            if ($database) {
                $param = [
                    'auth_type' => 'config',
                    'host' => $database->ip,
                    'port' => $database->port,
                    'user' => $database->username,
                    'password' => $database->password,
                    'db' => $database->name,
                ];
                $param = http_build_query($param);
                $ip = $_SERVER['SERVER_ADDR'];
                // $url = "http://$ip:888/".env('PHPMYADMIN_URL')."/?".$param;
                $url = "http://39.108.67.106:888/" . env('PHPMYADMIN_URL') . "/?" . $param;
                return redirect($url);
            }
        }
    }

    /**
     * 导入数据库基本的数据库结构/初始化数据库
     *
     * @param Request $request
     */
    public function initDatabase(Request $request)
    {

        // 读取 SQL 文件内容
        $sqlFilePath = public_path() . '/sql/init.sql';
        $sqlContent = file_get_contents($sqlFilePath);

        if (!$sqlContent) {
            ReturnJson(false, 'sql文件不存在');
        }
        
        // 从当前数据库中读取目标数据库的连接信息
        $databaseConnectionInfo = \Modules\Admin\Http\Models\Database::find($request->id);
        // ReturnJson(true, $request->id);

        if (!$databaseConnectionInfo) {
            ReturnJson(false, '无法找到目标数据库的连接信息');
        }

        // 获取连接信息
        $targetHost = $databaseConnectionInfo->ip;
        $targetPort = $databaseConnectionInfo->port ?? 3306;
        $targetDatabase = $databaseConnectionInfo->name; // 数据库名
        $targetUsername = $databaseConnectionInfo->username;
        $targetPassword = $databaseConnectionInfo->password;

        // 第二步：动态配置数据库连接（不指定数据库名）
        $config = [
            'driver'    => 'mysql',
            'host'      => $targetHost,
            'port'      => $targetPort,
            'database'  => '', // 不指定数据库名
            'username'  => $targetUsername,
            'password'  => $targetPassword,
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ];

        // 添加新的数据库连接到 DB facade
        DB::purge('dynamic_mysql'); // 清除之前的动态连接（如果有）
        config(['database.connections.dynamic_mysql' => $config]);

        // 使用新的数据库连接
        $connection = DB::connection('dynamic_mysql');

        // 第三步：检查目标数据库是否存在
        $databaseExists = $connection->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$targetDatabase]);

        if (!empty($databaseExists)) {
            ReturnJson(false, '数据库已存在，执行已停止');
        }else{
            // 创建数据库
            if (empty($databaseExists)) {
                $connection->statement("CREATE DATABASE `$targetDatabase` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } 
        }

        // 重新配置连接，指定数据库名
        $config['database'] = $targetDatabase;
        config(['database.connections.dynamic_mysql' => $config]);
        $connection = DB::connection('dynamic_mysql');

        // 执行 SQL 语句
        $sqlContent = 'use `' . $targetDatabase . '`;' . $sqlContent;
        $connection->unprepared($sqlContent);

        ReturnJson(true, trans('lang.add_success'));
    }
}
