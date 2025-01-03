<?php

namespace Modules\Site\Http\Controllers;

use App\Helper\SiteUploads;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\Admin\Http\Models\AliyunOssConfig;
use Modules\Admin\Http\Models\Site;
use Modules\Site\Http\Models\ContactUs;
use Modules\Site\Http\Models\EmailLog;
use Modules\Site\Http\Models\IpBanLog;
use Modules\Site\Http\Models\OperationLog;
use Modules\Site\Http\Models\Order;
use Modules\Site\Http\Models\OssFile;
use Modules\Site\Http\Models\Products;
use Modules\Site\Http\Models\RequestLog;
use Modules\Site\Http\Models\SyncLog;

class PublicController extends Controller {
    public function getNoReadMsgCnt() {
        try {
            $data = [];
            $data['orderViewCnt'] = Order::query()->where('status', 0)->count();
            $data['contactUsViewCnt'] = ContactUs::query()->where('status', 0)->count();
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getInitData() {
        try {
            $data = [];
            $data['oss_base_path'] = str_replace(public_path(), '', SiteUploads::GetRootPath());
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function truncateTable(Request $request) {
        try {
            $type = $request->input('type', '');
            if (empty($type)) {
                ReturnJson(false, trans('lang.param_error'));
            }
            //站点端邮件日志，封禁日志，操作日志，同步日志加一个清空数据库的按钮
            if ($type == 'email') {
                EmailLog::truncate();
            } elseif ($type == 'operation') {
                OperationLog::truncate();
            } elseif ($type == 'ip_ban') {
                IpBanLog::truncate();
            } elseif ($type == 'sync_log') {
                SyncLog::truncate();
            } elseif ('ua_ban') {
                RequestLog::truncate();
            }
            ReturnJson(true, trans('lang.request_success'), []);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getSiteSecurityConf() {
        try {
            $site = getSiteName();
            $site_id = Site::query()->where("name", $site)->value('id');
            if (empty($site_id)) {
                ReturnJson(false, trans('lang.param_error'));
            }
            //aliyun_oss_config
            $site_oss_conf = AliyunOssConfig::query()->where('site_id', $site_id)->first();
            if (empty($site_oss_conf)) {
                ReturnJson(false, trans('lang.param_error').'2');
            }
            $site_oss_conf = $site_oss_conf->toArray();
            $site_core_oss_conf = Arr::only($site_oss_conf, ['access_key_id', 'access_key_secret', 'endpoint', 'bucket']
            );
            $encrypted = json_encode($site_core_oss_conf);
            //$data = [];
            // 使用私钥加密数据
//            $conf_json = json_encode($site_core_oss_conf);
//            $privateKeyPath = resource_path('keys/yadmin_private_key.pem');
//            $privateKeyContent = file_get_contents($privateKeyPath);
//            $privateKey = openssl_pkey_get_private($privateKeyContent);
//            openssl_private_encrypt($conf_json, $encrypted, $privateKey);
            $data['site_oss_conf'] = base64_encode($encrypted);
            ReturnJson(true, trans('lang.request_success'), $data);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function test() {
        $encryptedDataBase64
            = 'QeGxSrbckPSpr0Q8MYa5UFxv6dM9iQ7xBa+YkLS+q/CS96KjRBxvDC7OrsbSJoT8n3TYpxee65Tuv32L8Eex8Uw4AugtC0Oq5NbWMpXb5t6kx8qhtZUjSll6+oThkOLOqiAePN6ptJA7f71vIb8m6hMKwrKvns1MnpQ/L0mMrDSBgBJjZhtUmpYkDlUqjtl/r5jdbPlQ3QPcRHIv9efAHB4eaXcd3XvZf6wG8f49k1T7eTC2DcYERP46dnYQLYWipogU6DUODcyXIy1yMGyXeYYd22CxDMa0jXSy3gsW6mTpQfxliLP7A5++H5w/dfB4FSq1PQu/H065L90dat7kwg==';
        $encryptedData = base64_decode($encryptedDataBase64);
        // 公钥文件路径，根据实际存放位置调整
        $publicKeyPath = resource_path('keys/yadmin_public_key.pem');
        $publicKeyContent = file_get_contents($publicKeyPath);
        $publicKeyResource = openssl_pkey_get_public($publicKeyContent);
        if ($publicKeyResource === false) {
            while ($msg = openssl_error_string()) {
                echo $msg."\n";
            }
            exit;
        }
        // 进行解密操作
        openssl_public_decrypt($encryptedData, $decryptedData, $publicKeyResource);
        echo "解密后的数据: ".$decryptedData;
        // 释放公钥资源
        openssl_pkey_free($publicKeyResource);
    }
}
