<?php

namespace App\Helper;
class AdminUploads
{
    private static $DIR = 'admin';// 一级目录

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
        $path = public_path().'/'.self::$DIR.'/'.$path.'/';
        return $path;
    }

    /**
     * 下载文件
     * @param $path 上传路径
     * @param $name 上传文件名
     */
    public static function download($path,$name){
        $FilePath = self::GetRootPath($path);
        if(!file_exists($FilePath.$name)){
            return false;
        }
        return $FilePath.$name;
    }
}