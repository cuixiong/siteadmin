<?php

namespace App\Helper;

class BtPanel
{
    // 接口文档 https://www.bt.cn/data/api-doc.pdf
    // 论坛 https://www.bt.cn/bbs/forum.php?mod=viewthread&tid=20376&highlight=%E6%8E%A5%E5%8F%A3

    private $BT_KEY = "c5bcMa7D9De3NtDi440eEidltvgpIWdI";  //接口密钥
    private $BT_PANEL = "http://8.219.5.215:18754/";       //面板地址

    //如果希望多台面板，可以在实例化对象时，将面板地址与密钥传入
    public function __construct($bt_panel = null, $bt_key = null)
    {
        if ($bt_panel) $this->BT_PANEL = $bt_panel;
        if ($bt_key) $this->BT_KEY = $bt_key;
    }

    //示例取面板日志	
    public function getLogs()
    {
        //拼接URL地址
        $url = $this->BT_PANEL . '/data?action=getData';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名
        $p_data['table'] = 'logs';
        $p_data['limit'] = 10;
        $p_data['tojs'] = 'test';

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    /**
     * 构造带有签名的关联数组
     */
    private function GetKeyData()
    {
        $now_time = time();
        $p_data = array(
            'request_token'    =>    md5($now_time . '' . md5($this->BT_KEY)),
            'request_time'    =>    $now_time
        );
        return $p_data;
    }


    /**
     * 发起POST请求
     * @param String $url 目标网填，带http://
     * @param Array|String $data 欲提交的数据
     * @return string
     */
    private function HttpPostCookie($url, $data, $timeout = 60)
    {
        //定义cookie保存位置
        $cookie_file = './' . md5($this->BT_PANEL) . '.cookie';
        if (!file_exists($cookie_file)) {
            $fp = fopen($cookie_file, 'w+');
            fclose($fp);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 系统状态相关接口
     *  
     */

    //获取系统基础统计
    public function getSystemTotal()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/system?action=GetSystemTotal';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    // 获取磁盘分区信息
    public function getDiskInfo()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/system?action=GetDiskInfo';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    // 获取磁盘分区信息
    public function getNetWork()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/system?action=GetNetWork';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }


    /**
     * 网站管理
     *  
     */
    // 获取网站列表
    public function getSiteList()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/data?action=getData&table=sites';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        // $p_data['p'] = 1; // 当前分页 [可选]
        $p_data['limit'] = 15; // 取回的数据行数 [必传]
        // $p_data['type'] = -1; // 分类标识; -1: 全部部分类 0: 默认分类 [可选]
        // $p_data['order'] = 'id desc'; // 排序规则 使用 id 降序：id desc 使用名称升序：name desc [可选]
        // $p_data['tojs'] = 'get_site_list'; // 分页 JS 回调,若不传则构造 URI 分页连接 [可选]
        // $p_data['search'] = 'www'; // 搜索内容 [可选]

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }
    // 获取网站分类
    public function getSiteTypes()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=get_site_types';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    // 获取网站分类
    public function getPHPVersion()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=GetPHPVersion';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    // 创建网站
    public function addSite()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=AddSite';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        $p_data['webname'] = json_encode(["domain" => "	qycnadmin.qyrdata.com", "domainlist" => [], "count" => 0]); //网站主域名和域名列表请传JSON[必传]
        $p_data['path'] = '/www/wwwroot/qy-cn/new-adminqy-cn/backend/web'; // 根目录 [必传]
        $p_data['type_id'] = 0; //分类标识 [必传]
        $p_data['type'] = 'PHP'; //项目类型 请传PHP [必传]
        $p_data['version'] = 74; //PHP 版本 请从PHP 版本列表中选择[必传]
        $p_data['port'] = 80; // 网站端口 [必传]
        $p_data['ps'] = 'qy-cn测试后台'; //网站备注 [必传]
        $p_data['ftp'] = false; //是否创建 FTP [必传]
        $p_data['sql'] = false; //是否创建数据库[必传]

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    // 删除网站
    public function deleteSite()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=DeleteSite';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        $p_data['id'] = 40; // 网站 ID [必传]
        $p_data['webname'] = 'w1.hao.com'; // 网站名称 [必传]

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    // 申请Let's Encrypt 证书 + 设置SSL
    public function applyCert()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/acme?action=apply_cert_api';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        $p_data['domains'] = json_encode(["qycnadmin.qyrdata.com"]);
        $p_data['auth_type'] = 'http';
        $p_data['auth_to'] = 41;
        $p_data['auto_wildcard'] = 0;
        $p_data['id'] = 41;
        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        // return $data;
        if ($data) {
            //申请后设置证书

            //拼接URL地址
            $sslUrl = $this->BT_PANEL . '/site?action=SetSSL';

            //准备POST数据
            $p_data2 = $this->GetKeyData();        //取签名

            $p_data2['type'] = 1;
            $p_data2['siteName'] = $data['domains'][0];
            $p_data2['key'] = $data['private_key'];
            $p_data2['csr'] = $data['cert'];

            //请求面板接口
            $result2 = $this->HttpPostCookie($sslUrl, $p_data2);

            //解析JSON数据
            $data2 = json_decode($result2, true);
            return [$data, $data2];
        }
        return $data;
    }

    // 申请进度
    public function getLines()
    {

        //拼接URL地址
        $url = $this->BT_PANEL . '/ajax?action=get_lines';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        $p_data['num'] = 10;
        $p_data['filename'] = '/www/server/panel/logs/letsencrypt.log';

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    // 设置SSL
    public function setSSL($key,$csr)
    {
        
        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=SetSSL';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        $p_data['type'] = 1;
        $p_data['siteName'] = 'qycn.qyrdata.com';
        $p_data['key'] = $key;
        $p_data['csr'] = $csr;

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    // 获取SSL
    public function getSSL()
    {
        
        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=GetSSL';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        $p_data['siteName'] = 'qycnadmin.qyrdata.com';

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    //强制使用https
    public function httpToHttps()
    {
        
        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=HttpToHttps';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        $p_data['siteName'] = 'qycnadmin.qyrdata.com';

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    //取消强制使用https
    public function closeToHttps()
    {
        
        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=CloseToHttps';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        $p_data['siteName'] = 'qycnadmin.qyrdata.com';

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }

    
}
