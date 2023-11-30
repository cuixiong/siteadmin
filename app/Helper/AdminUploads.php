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
        $path = '/'.trim($path,'/').'/'.$name;
        return $path;
    }

    public static function GetRootPath($path = ''){
        $path = trim($path,'/');
        if($path){
            $path = public_path().'/'.self::$DIR.'/'.$path.'/';
        } else {
            $path = public_path().'/'.self::$DIR.'/';
        }
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

    public static function unzip($path,$name,$unzipPath){
        $path = trim($path,'/');
        $RootPath = self::GetRootPath($path);
        $FilePath = $path ? $RootPath. $path .'/' .$name : $RootPath. $name;
        $LocalUnzipPath = $RootPath.$unzipPath.'/';
        if(strpos($name, '.zip') == false){
            return '文件不是ZIP文件';
        }
        if(!file_exists($FilePath)){
            return 'ZIP文件不存在';
        }
        if(is_dir($LocalUnzipPath)){
            return '文件夹已存在';
        }
        $zip = new \ZipArchive();;
        $res = $zip->open($FilePath);
        if($res === true){
            $zip->extractTo($LocalUnzipPath);
            $zip->close();
            return true;
        } else {
            return '文件解压失败';
        }
    }
}