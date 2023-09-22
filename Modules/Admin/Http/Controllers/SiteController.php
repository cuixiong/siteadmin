<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Admin\Http\Models\Site as SiteModel;

class SiteController extends Controller
{
    /**
     * 创建一个站点
     * @param Request $request
     * @return Renderable
     */
    public function create(Request $request)
    {
        $data['english_name'] = $request->post('english_name'); // 英文名
        $data['name'] = $request->post('name'); // 站点名称
        $data['domain'] = $request->post('domain'); // 站点域名
        $data['country_id'] = $request->post('country_id'); // 部署国家ID
        $data['publisher_id'] = $request->post('publisher_id'); // 出版商ID
        $data['language_id'] = $request->post('language_id'); // 语言ID
        $data['status'] = $request->post('status'); // 状态
        $data['db_host'] = $request->post('db_host'); // 数据库HOST
        $data['db_port'] = (int)$request->post('db_port'); // 数据库端口
        $data['db_database'] = $request->post('db_database'); // 数据库名
        $data['db_username'] = $request->post('db_username'); // 数据库登陆名
        $data['db_password'] = $request->post('db_password'); // 数据库密码
        // var_dump($data);die;

        $model = new SiteModel();
        $res = $model->insert($data);
        $Tenant = new TenantController();
        $Tenant->initTenant($data['english_name'],$data['domain'],$data['db_host'],$data['db_database'],$data['db_username'],$data['db_password'],$data['db_port']);
        return '成功';
    }

    // git 命令
    public function git()
    {
        //1.项目目录不对
        //2.已经更细 string(19) "Already up to date."
        //3.有冲突
        array(1) {
            [0]=>
            string(25) "Updating 31c0247..644415a"
          }
          int(1)
        //4.正常更新
        // array(5) {
        //     [0]=>
        //     string(25) "Updating f59d9b0..31c0247"
        //     [1]=>
        //     string(12) "Fast-forward"
        //     [2]=>
        //     string(12) " a.txt | 1 +"
        //     [3]=>
        //     string(31) " 1 file changed, 1 insertion(+)"
        //     [4]=>
        //     string(25) " create mode 100644 a.txt"
        //   }
        //   int(0)
        // array(4) {
        //     [0]=>
        //     string(25) "Updating 31c0247..644415a"
        //     [1]=>
        //     string(12) "Fast-forward"
        //     [2]=>
        //     string(13) " a.txt | 2 +-"
        //     [3]=>
        //     string(46) " 1 file changed, 1 insertion(+), 1 deletion(-)"
        //   }
        //   int(0)

        // 获取项目的根目录路径
        // $RootPath = base_path();
        // $RootPath = 'G:\phpstudy_pro\WWW\MyPor';
        // $exec = "cd ".$RootPath;
        // $exec .= " & git pull";
        // // var_dump($exec);die;
        // exec($exec,$res,$status);
        exec("git pull",$res,$status);
        var_dump($res,$status);
    }
}
