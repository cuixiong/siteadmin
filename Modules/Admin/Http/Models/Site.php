<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;
use phpseclib3\Net\SSH2;
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
        'api_path',
        'frontend_path',
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
    protected $appends = ['publisher', 'country', 'language', 'server', 'database'];

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
     * 服务器获取器
     */
    public function getServerAttribute()
    {
        $text = '';
        if (isset($this->attributes['server_id'])) {
            $text = Server::where('id', $this->attributes['server_id'])->value('name');
        }
        return $text;
    }

    /**
     * 数据库获取器
     */
    public function getDatabaseAttribute()
    {
        $text = '';
        if (isset($this->attributes['database_id'])) {
            $text = Database::where('id', $this->attributes['database_id'])->value('name');
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
        if (isset($search->status) && $search->status != '') {
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
     * 连接远程服务器 执行命令 处理输出
     * @param int $siteId 站点ID
     * @param string $type 操作类型
     * @param array $option 额外参数
     * @param array|string $commands shell命令
     */
    protected static function executeRemoteCommand($siteId, $type, $option = [])
    {
        set_time_limit(100);

        //获取站点配置
        $site = self::findOrFail($siteId);
        //获取服务器配置
        $server = Server::find($site->server_id);
        //获取数据库配置
        $database = Database::find($site->database_id);


        $checkParamEmpty = [
            'server_model_empty' => $server,
            'database_model_empty' => $database,
            'site_name_empty' => $site->name ?? '',
            'site_api_repository_empty' => $site->api_repository ?? '',
            'site_frontend_repository_empty' => $site->frontend_repository ?? '',
            'site_api_path_empty' => $site->api_path ?? '',
            'site_frontend_path_empty' => $site->frontend_path ?? '',
            'server_ip_empty' => $server->ip ?? '',
            'server_username_empty' => $server->username ?? '',
            'server_password_empty' => $server->password ?? '',
            'database_ip_empty' => $database->ip ?? '',
            'database_username_empty' => $database->username ?? '',
            'database_password_empty' => $database->password ?? '',
        ];
        //判断参数是否为空
        foreach ($checkParamEmpty as $key => $value) {
            if (empty($value)) {
                return [
                    'result' => false,
                    'output' => !empty(trans('lang.' . $key)) ? trans('lang.' . $key) : trans('lang.param_empty'),
                ];
            }
        }

        //连接远程服务器
        $ssh = new SSH2($server->ip);

        if (!$ssh->login($server->username, $server->password)) {
            return [
                'result' => false,
                'output' => trans('lang.server_login_fail'),
            ];
        }

        // // 项目所在外层路径
        // $siteBasePath = '/www/wwwroot/platform_test/' . $site->name . '/';
        // 接口/后台代码项目路径
        $apiDirName = $site->api_path;
        // 前台代码仓库别名
        $frontedDirName = $site->frontend_path;

        //根据类型获取命令
        $commands = [];
        if ($type == 'add_site') {
            //部署站点
            // 接口/后台代码仓库地址
            $apiRepository = $site->api_repository;
            // 前台代码仓库地址
            $frontendRepository = $site->frontend_repository;
            $commands = self::getAddSiteCommands($apiRepository, $apiDirName, $frontendRepository, $frontedDirName, $database);
        } elseif ($type == 'pull_code') {
            //拉取代码、升级站点
            $commands = self::getPullCodeCommands($apiDirName, $frontedDirName);
        } elseif ($type == 'current_hash') {
            //当前版本hash及hash短格式
            $commands = self::getCurrentHashCommands($apiDirName, $frontedDirName);
        } elseif ($type == 'commit_history') {
            //历史提交记录、返回哈希值及注释
            $pageNum = $option['pageNum'] ?? 0;
            $pageSize = $option['pageSize'] ?? 0;
            $commands = self::getCommitHistoryCommands($apiDirName, $frontedDirName, $pageNum, $pageSize);
        } elseif ($type == 'commit_history_count') {
            //历史提交记录总数
            $commands = self::getCommitHistoryCountCommands($apiDirName, $frontedDirName);
        } elseif ($type == 'available_pull') {
            $commands = self::getAvailablePullCommands($apiDirName, $frontedDirName);
        } elseif ($type == 'rollback_code') {
            $hash = $option['hash'];
            $commands = self::getRollbackCodeCommands($apiDirName, $frontedDirName, $hash);
        }
        // return $commands;
        //执行命令
        //输出的$output['result']为true时只代表命令正常执行，不代表达到预期结果
        $output = self::executeCommands($ssh, $commands);
        // return $commands;
        // return $output;
        //是否写入日志
        $writeUpdateLog = false;
        $message = $output['output'] ?? '';

        //结果需要处理一下
        if ($type == 'add_site') {
            $writeUpdateLog = true;
            $message = 'add_site';
        } elseif ($type == 'pull_code') {
            $writeUpdateLog = true;

            //需要拉取记录
            if ($output['result']) {
                if (strpos($output['output'], 'Already up to date') !== false) {
                    //拉取了但没可用更新
                    $output['result'] = true;
                    $message = 'Already up to date';
                } elseif (strpos($output['output'], 'Updating') !== false || strpos($output['output'], 'Fast-forward') !== false) {
                    //拉取了但有可用
                    $output['result'] = true;
                    $message = 'Updating...Fast-forward..';
                } else {
                    $output['result'] = false;
                }
            }
        } elseif ($type == 'commit_history') {

            if ($output['result']) {
                // return $output['output'];
                $logData = [];

                if (!empty(trim($output['output']))) {
                    $logArray = explode("\n", trim($output['output']));
                    foreach ($logArray as $logItem) {
                        // 解析每一行 log 条目
                        list($hash, $hashSample, $authorName, $authorEmail, $date, $message) = explode('|', $logItem);
                        // 构建数组
                        $logData[] = [
                            'hash' => $hash,
                            'hashSample' => $hashSample,
                            'authorName' => $authorName,
                            'authorEmail' => $authorEmail,
                            'date' => $date,
                            'message' => $message,
                        ];
                    }
                }
                $output['output'] = $logData;
            }
        } elseif ($type == 'available_pull') {

            if ($output['result']) {
                //判断是否有可用更新
                if (strpos($output['output'], 'Your branch is behind') !== false) {
                    $output['result'] = true;
                } elseif (strpos($output['output'], 'Your branch is up to date') !== false) {
                    $output['result'] = false;
                } else {
                    $output['result'] = false;
                }
            }
        } elseif ($type == 'rollback_code') {
            $writeUpdateLog = true;
        }

        //拉取、回滚等操作要写到站点更新日志里
        if ($writeUpdateLog) {
            //因为还需记录版本号，只能再调用一次
            $currentHashCommands = self::getCurrentHashCommands($apiDirName, $frontedDirName);
            $currentHashOutput = self::executeCommands($ssh, $currentHashCommands);

            $currentHash = '';
            $currentHashSample = '';
            if ($currentHashOutput['result']) {
                $temp_array = explode("\n", $currentHashOutput['output']);
                $currentHash = $temp_array[0];
                $currentHashSample = $temp_array[1];
            }

            SiteUpdateLog::insert(
                [
                    'site_id' => $site->id,
                    'site_name' => $site->name,
                    'message' => $message,
                    'output' => $output['output'],
                    'exec_status' => $output['result'] ? 1 : 0,
                    'command' => $output['command'] ?? '',
                    'created_at' => time(),
                    'updated_at' => time(),
                    'created_by' => $option['created_by'] ?? '',
                    'hash' => $currentHash,
                    'hash_sample' => $currentHashSample,
                ],
            );
        }

        return $output;
    }

    /**
     * 远程服务器执行命令
     * @param \phpseclib3\Net\SSH2 $ssh 
     * @param array|string $commands
     */
    private static function executeCommands($ssh, $commands)
    {

        if (is_array($commands)) {

            foreach ($commands as $command) {
                # code...
                if (!empty($command)) {

                    $output = $ssh->exec($command);
                    if ($ssh->getExitStatus() !== 0) {
                        // 执行失败
                        return [
                            'result' => false,
                            'output' => $output,
                            'command' => $command,
                        ];
                    }
                }
            }
        } elseif (!empty($commands)) {

            $output = $ssh->exec($commands);
        }
        return [
            'result' => true,
            'output' => $output,
        ];
    }


    /**
     * 获取新建站点/部署项目命令
     * @param string apiRepository 接口仓库地址
     * @param string apiDirName 接口仓库路径
     * @param string frontend_repository 网站仓库路径
     * @param string frontedDirName 网站仓库别名
     * @param Modules\Admin\Http\Models\Database database 数据库模型对象
     * @return array|string commands 命令
     */
    private static function getAddSiteCommands($apiRepository, $apiDirName, $frontendRepository, $frontedDirName, $database)
    {

        //数据库信息，用于替换项目配置的数据库信息
        $dbHost = $database->ip;
        $dbName = $database->name;
        $dbUsername = $database->username;
        $dbPassword = $database->password;
        $tablePrefix = empty($database->prefix) ? $database->prefix : '';
        //前台暂未部署

        $commands = [
            /** 
             * 一、第一次克隆代码
             * 克隆代码时需事先在服务器记住码云用户名密码，不然在克隆时需携带用户名及密码：
             * 原：git clone 仓库地址 [新建的目录名]
             * 例: git clone https://gitee.com/qyresearch/admin.qyrsearch.com.git qy_en
             * 携带密码：git clone https://用户名:密码@仓库地址 [新建的目录名]
             * 例: git clone https://6953%40qq.com:1234567acde@gitee.com/qyresearch/admin.qyrsearch.com.git qy_en
             * 用户名密码有@这些特殊符号需转义
             */
            'git clone ' . $apiRepository . ' ' . $apiDirName,
            /**
             * 二、下载依赖
             * 因为每一句命令独立运行，所以每次都要cd到指定目录
             */
            'cd ' . $apiDirName . ' &&  composer install ',
            /**
             * 三、配置文件连接数据库
             * 复制env模板文件,替换其中的占位符，如果已存在env文件，须事先进行备份以免误覆盖
             */
            // 检查是否存在 .env 文件,备份当前的 .env 文件为时间戳命名的文件
            'cd ' . $apiDirName . ' && if [ -f .env ]; then mv .env "$(date +\'' . time() . '\')_env.backup" fi',
            // 替换操作
            'cd ' . $apiDirName . ' && cp .env.template .env',
            'cd ' . $apiDirName . ' && sed -i "s/{{DB_HOST}}/' . $dbHost . '/g" .env',
            'cd ' . $apiDirName . ' && sed -i "s/{{DB_DATABASE}}/' . $dbName . '/g" .env',
            'cd ' . $apiDirName . ' && sed -i "s/{{DB_USERNAME}}/' . $dbUsername . '/g" .env',
            'cd ' . $apiDirName . ' && sed -i "s/{{DB_PASSWORD}}/' . $dbPassword . '/g" .env',

            /**
         * 
         * 三、修改项目的权限
         * 因为每一句命令独立运行，所以每次都要指定目录
         */
            // 'chown -R www:www ' . $siteBasePath . $apiDirName . ' && chmod -R 755 ' . $siteBasePath . $apiDirName,
            /**
         * 四、配置初始化
         * 基于第三步的修改权限，不然提示没权限
         * --env=Development --overwrite=n 默认使用Development模式，选择不覆盖重名文件
         */
            // 'cd ' . $siteBasePath . $apiDirName . ' && ./init --env=Development --overwrite=n',
        ];
        return $commands;
    }


    /**
     * 拉取代码
     * @param string apiDirName 接口仓库路径
     * @param string frontedDirName 网站仓库路径
     * @return array|string commands 命令
     */
    private static function getPullCodeCommands($apiDirName, $frontedDirName)
    {
        //没有更新依赖
        //前台暂无

        $commands = [
            /** 
             * 拉取代码命令
             * 所有者的更改(部署项目时chown命令)可能导致提示
             * fatal: detected dubious ownership in repository at 'xxx' To add an exception for this directory, call:
             * 需执行命令 git config --global --add safe.directory 项目路径
             */
            'cd ' . $apiDirName . ' &&  git pull',
        ];
        return $commands;
    }


    /**
     * 提交记录
     * @param string apiDirName 接口仓库路径
     * @param string frontedDirName 网站仓库路径
     * @return array|string commands 命令
     */
    private static function getCommitHistoryCommands($apiDirName, $frontedDirName, $pageNum, $pageSize)
    {
        //前台暂无
        $param = [];
        $param[] = '--pretty=format:"%H|%h|%an|%ae|%ad|%s"';
        $param[] = '--date=format:"%Y-%m-%d %H:%M:%S"';
        if ($pageSize > 0) {
            $param[] = '-n ' . $pageSize;
        }
        if ($pageNum > 0) {
            $param[] = '--skip=' . ($pageNum - 1) * ($pageSize > 0 ? $pageSize : 0);
        }
        $paramStr = implode(' ', $param);
        $commands = [
            /** 
             * 提交记录命令
             * 参数:
             * -n 5 指定返回5条
             * --pretty=format:"%H|%an|%ae|%ad|%s" 展示格式
             */
            'cd ' . $apiDirName  . ' &&  git log ' . $paramStr,

        ];
        return $commands;
    }

    /**
     * 提交记录总数
     * @param string apiDirName 接口仓库路径
     * @param string frontedDirName 网站仓库路径
     * @return array|string commands 命令
     */
    private static function getCommitHistoryCountCommands($apiDirName, $frontedDirName)
    {
        //前台暂无

        $commands = [
            /** 
             * 获取提交记录总数量命令
             * 参数:
             * count 总数，需git 2.7以上版本
             * HEAD 当前分支
             */
            'cd ' . $apiDirName  . ' &&  git rev-list --count HEAD',

        ];
        return $commands;
    }

    /**
     * 获取当前提交版本的hash值
     * @param string apiDirName 接口仓库路径
     * @param string frontedDirName 网站仓库路径
     * @return array|string commands 命令
     */
    private static function getCurrentHashCommands($apiDirName, $frontedDirName)
    {
        //前台暂无

        $commands = [
            'cd ' . $apiDirName . ' &&  git rev-parse HEAD && git rev-parse --short HEAD',

        ];
        return $commands;
    }

    /**
     * 是否可更新
     * @param string apiDirName 接口仓库路径
     * @param string frontedDirName 网站仓库路径
     * @return array|string commands 命令
     */
    private static function getAvailablePullCommands($apiDirName, $frontedDirName)
    {
        //前台暂无

        $commands = [
            /** 
             * 是否可更新
             * 首先 git fetch 先获取远程仓库的更新，再使用 git status 去获取是否含有以下信息：
             * ---------------------------------------------------------------------------------------
             * Your branch is behind 'origin/master' by 16 commits, and can be fast-forwarded.
             *  (use "git pull" to update your local branch)
             *
             * Untracked files:
             *  (use "git add <file>..." to include in what will be committed)
             * ---------------------------------------------------------------------------------------
             * 一般判断 Your branch is behind 即可
             * 
             * 如果没有可更新则显示：
             * ---------------------------------------------------------------------------------------
             * On branch master
             * Your branch is up to date with 'origin/master'.
             * nothing to commit, working tree clean
             * -------------------------------------------------------------------------------------
             * 则判断 Your branch is up to date 即可
             * 
             */
            'cd ' . $apiDirName . ' &&  git fetch && git status',

        ];
        return $commands;
    }


    /**
     * 回退站点版本/git回退
     * @param string apiDirName 接口仓库路径
     * @param string frontedDirName 网站仓库路径
     * @param string hash 回退版本的完整hash值
     * @return array|string commands 命令
     */
    private static function getRollbackCodeCommands($apiDirName, $frontedDirName, $hash)
    {
        //前台暂无

        $commands = [
            /**
             * git回退
             * 执行成功时提示 HEAD is now at [简短的hash] [填写的注释]\n"
             * 其实也可以用于往前推进几个版本(只要远程仓库更新了)
             * 回退了就不要用强制git push了，不然这版本之后的代码都没了(好像阿里云的服务器有限制无法推送)
             * git revert [hash值] 也是回退到之前的代码，但本质是一个新的提交
             * 
             */
            'cd ' . $apiDirName . ' &&  git reset --hard ' . $hash,

        ];
        return $commands;
    }
}
