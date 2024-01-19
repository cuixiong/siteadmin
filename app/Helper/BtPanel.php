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
    public function GetLogs()
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
    public function GetSystemTotal()
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
    public function GetDiskInfo()
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
    public function GetNetWork()
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
    public function GetSiteList()
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

    // 创建网站
    public function addSite()
    {
        
        //拼接URL地址
        $url = $this->BT_PANEL . '/site?action=AddSite';

        //准备POST数据
        $p_data = $this->GetKeyData();        //取签名

        //请求面板接口
        $result = $this->HttpPostCookie($url, $p_data);
        
        // $p_data['p'] = 1; // 当前分页 [可选]
        $p_data['limit'] = 15; // 取回的数据行数 [必传]
        // $p_data['type'] = -1; // 分类标识; -1: 全部部分类 0: 默认分类 [可选]
        // $p_data['order'] = 'id desc'; // 排序规则 使用 id 降序：id desc 使用名称升序：name desc [可选]
        // $p_data['tojs'] = 'get_site_list'; // 分页 JS 回调,若不传则构造 URI 分页连接 [可选]
        // $p_data['search'] = 'www'; // 搜索内容 [可选]

        //解析JSON数据
        $data = json_decode($result, true);
        return $data;
    }


}
