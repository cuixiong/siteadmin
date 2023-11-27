<?php

namespace App\Helper;
class SiteUploads
{
    private static $DIR = 'site';// 一级目录
    private static $SiteDir;// 站点目录

    public function __construct()
    {
        $request = request();
        if(!$request->header('Site')){
            ReturnJson(false,'当前站点的请求头为空');
        }
        self::$SiteDir = $request->header('Site');
    }

    /**
     * 上传文件
     * @param $file 文件上传OBJ
     * @param $path 上传路径
     * @param $name 上传文件名
     * @return string 文件URL
     */
    public static function uploads($file,$path,$name){
        $FilePath = self::GetRootPath($path);
        $file->move($FilePath, $name);
        return '/'.trim($path,'/').'/'.$name;
    }

    public static function GetRootPath($path = ''){
        $path = trim($path,'/');
        $path = public_path().'/'.self::$DIR.'/'.self::$SiteDir.'/'.$path.'/';
        return $path;
    }
}