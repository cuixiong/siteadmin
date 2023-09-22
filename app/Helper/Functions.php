<?php
/** 
 * 返回JSON格式响应
 * @param $code 状态码=>TRUE是200，false是-200，其他值是等于$code本身
 * @param $message 提示语
 * @param $data 需要返回的数据数组
 */
function ReturnJson($code,$message = '请求成功',$data = []){
    $code = ($code == TRUE) ? 200 : $code;
    $code = ($code == FALSE) ? -200 : $code;
    echo json_encode(
        [
            'code' => $code,
            'message' => $message,
            'data' => $data
        ]);
    exit;
}
