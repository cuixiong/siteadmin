<?php

use Illuminate\Support\Facades\Redis;
use Modules\Admin\Http\Models\Site;

/**
 * 返回JSON格式响应
 *
 * @param $code    状态码=>TRUE是200，false是-200，其他值是等于$code本身
 * @param $message 提示语
 * @param $data    需要返回的数据数组
 */
function ReturnJson($code, $message = '请求成功', $data = []) {
    $code = ($code === true) ? "200" : $code;
    $code = ($code === false) ? 'B001' : $code;
    echo json_encode(
        [
            'code' => $code,
            'msg'  => $message,
            'data' => $data
        ]
    );
    exit;
}

/**
 * 接口请求频率限制
 */
function currentLimit($request, $second = 10, $site = '', $userId = 0) {
    $route = $request->route();
    $actionInfo = $route->getAction();
    $currentLimitKey = $actionInfo['controller'];
    if(empty($site )){
        $site = $request->header('Site');
    }
    $currentLimitKey = $currentLimitKey."_{$site}_{$userId}";
    $isExist = Redis::get($currentLimitKey);
    if (!empty($isExist)) {
        ReturnJson(false, '请求频率过快');
    }
    Redis::setex($currentLimitKey, $second, 1);
}

/**
 * 转换前端需要的Select格式数据
 * @param $list
 *
 * @return array
 */
function convertToFormData($list){
    $selectList = [];
    foreach ($list as $key => $value){
        $data = [];
        $data['label'] = $value;
        $data['value'] = $key;
        $selectList[] = $data;
    }
    return $selectList;
}

function getSiteName(){
    if (php_sapi_name() === 'cli') {
        return false;
    } else {
        return request()->header("Site");
    }
}

function getSiteDomain(){
    if (php_sapi_name() === 'cli') {
        return false;
    } else {
        $siteName = getSiteName();
        $domain = Site::query()->where('name', $siteName)->value("domain");
        if (strpos($domain, '://') === false) {
            $domain = 'https://'.$domain;
        }
        return $domain;
    }
}

function getReportUrl($product) {
    $domain = getSiteDomain();
    if(empty($domain )){
        return '';
    }
    $productId = $product['id'];
    if (!empty($product['url'])) {
        $url = $domain."/reports/{$productId}/{$product['url']}";
    } else {
        $url = $domain."/reports/{$productId}";
    }

    return $url;
}








