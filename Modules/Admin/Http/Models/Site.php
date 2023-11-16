<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use PDO;

class Site extends Base
{
    // 设置可以入库的字段
    protected $fillable = [
        'name',
        'english_name',
        'domain',
        'country_id',
        'publisher_id',
        'language_id',
        'status',
        'database_id',
        'server_id',
        'api_repository',
        'frontend_repository',
        // 'db_host', 
        // 'db_port', 
        // 'db_database', 
        // 'db_username', 
        // 'db_password', 
        // 'table_prefix', 
        'updated_by',
        'created_by'
    ];

    //将虚拟字段追加到数据对象列表里去
    protected $appends = ['publisher', 'country', 'language'];

    /**
     * 出版商获取器
     */
    public function getPublisherAttribute()
    {
        $text = '';
        if (isset($this->attributes['publisher_id'])) {
            $publisherIds = explode(',', $this->attributes['publisher_id']);
            $text = Publisher::whereIn('id', $publisherIds)->pluck('name')->toArray();
            $text = implode(';', $text);
        }
        return $text;
    }


    /**
     * 国家地区获取器
     */
    public function getCountryAttribute()
    {
        $text = '';
        if (isset($this->attributes['country_id'])) {
            $text = Region::where('id', $this->attributes['country_id'])->value('name');
        }
        return $text;
    }

    /**
     * 语言获取器
     */
    public function getLanguageAttribute()
    {
        $text = '';
        if (isset($this->attributes['language_id'])) {
            $text = Language::where('id', $this->attributes['language_id'])->value('name');
        }
        return $text;
    }


    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $request)
    {
        $search = json_decode($request->input('search'));
        //id 
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }

        //name
        if (isset($search->name) && !empty($search->name)) {
            $model = $model->where('name', 'like', '%' . $search->name . '%');
        }

        //domain
        if (isset($search->domain) && !empty($search->domain)) {
            $model = $model->where('domain', 'like', '%' . $search->domain . '%');
        }

        //english_name
        if (isset($search->english_name) && !empty($search->english_name)) {
            $model = $model->where('english_name', 'like', '%' . $search->english_name . '%');
        }

        //publisher_id 出版商
        if (isset($search->publisher_id) && !empty($search->publisher_id)) {
            $model = $model->whereRaw("FIND_IN_SET(?, publisher_id) > 0", [$search->publisher_id]);
        }

        //country_id 国家地区
        if (isset($search->country_id) && !empty($search->country_id)) {
            $model = $model->where('country_id', $search->country_id);
        }

        //language_id 语言
        if (isset($search->language_id) && !empty($search->language_id)) {
            $model = $model->where('language_id', $search->language_id);
        }

        //status 状态
        if (isset($search->status) && !empty($search->status)) {
            $model = $model->where('status', $search->status);
        }

        //时间为数组形式
        //创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }

        //更新时间
        if (isset($search->updated_at) && !empty($search->updated_at)) {
            $updateTime = $search->updated_at;
            $model = $model->where('updated_at', '>=', $updateTime[0]);
            $model = $model->where('updated_at', '<=', $updateTime[1]);
        }

        return $model;
    }


    //打印sql
    public function printSql($model)
    {
        $sql = $model->toSql();
        $bindings = $model->getBindings();

        // 替换问号占位符
        foreach ($bindings as $binding) {
            $sql = preg_replace('/\?/', "'$binding'", $sql, 1);
        }
        return $sql;
    }

    
    /**
     * 初始化一个租户
     * @param bool $is_create 是否需要创建数据库
     * @param string $name 租户的英文标识
     * @param string $domain 租户域名
     * @param string $DB_HOST 数据库HOST
     * @param string $DB_DATABASE 数据库DATABASE
     * @param string $DB_USERNAME 数据库用户名
     * @param string $DB_PASSWORD 数据库密码
     * @param string $DB_PORT 数据库端口
     */
    public static function initTenant($is_create, $name, $domain, $DB_HOST, $DB_DATABASE, $DB_USERNAME, $DB_PASSWORD, $DB_PORT)
    {
        try {
            if ($is_create == 1) {
                // 创建租户
                $tenant = Tenant::create([
                    'id' => $name,
                    'tenancy_db_host' => $DB_HOST,
                    'tenancy_db_name' => $DB_DATABASE,
                    'tenancy_db_username' => $DB_USERNAME,
                    'tenancy_db_password' => $DB_PASSWORD,
                    'tenancy_db_port' => $DB_PORT,
                ]);
                $tenant->domains()->create([
                    'domain' => $domain,
                ]);
                // 保存租户配置
                $tenant->save();

                
            } else {
                $time = date('Y-m-d H:i:s', time());
                $data = [
                    'created_at' => $time,
                    'updated_at' => $time,
                    'tenancy_db_host' => $DB_HOST,
                    'tenancy_db_name' => $DB_DATABASE,
                    'tenancy_db_port' => $DB_PORT,
                    'tenancy_db_password' => $DB_PASSWORD,
                    'tenancy_db_username' => $DB_USERNAME,
                ];
                $data = json_encode($data);
                // 入库tenants表
                DB::table('tenants')->insert(['id' => $name, 'created_at' => $time, 'updated_at' => $time, 'data' => $data]);
                // 入库domains
                DB::table('domains')->insert(['domain' => $domain, 'tenant_id' => $name, 'created_at' => $time, 'updated_at' => $time]);
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
