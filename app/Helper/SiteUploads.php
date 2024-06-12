<?php

namespace App\Helper;

use Modules\Admin\Http\Models\AliyunOssConfig;
use Modules\Admin\Http\Models\Site;

class SiteUploads
{
    public static $DIR = 'site';// 一级目录
    private static $SiteDir;// 站点目录

    public static function OssClient(){
        $site = request()->header('site');
        $siteId = Site::where('name',$site)->value('id');
        $config = AliyunOssConfig::where('site_id',$siteId)->first();
        if(empty($config)){
            ReturnJson(false,"当前站点未配置阿里云OSS信息,请配置完整信息再上传");
        }
        $config = $config->toArray();
        if(empty($config['access_key_id']) || empty($config['access_key_secret']) || empty($config['endpoint']) || empty($config['bucket'])){
            ReturnJson(false,"阿里云OSS配置信息不完整");
        }
        // 查询出站点OSS的配置信息
        // $config['access_key_id'] = env('OSS_ACCESS_KEY_ID');
        // $config['access_key_secret'] = env('OSS_ACCESS_KEY_SECRET');
        // $config['endpoint'] = env('OSS_ACCESS_KEY_ENDPOINT');
        // $config['bucket'] = env('OSS_ACCESS_KEY_BUCKET');
        try{
            $ossClient = new AliyuncsOss($config['access_key_id'],$config['access_key_secret'],$config['endpoint'],$config['bucket']);
        } catch (\Exception $e){
            ReturnJson(false,$e->getMessage());
        }
        return $ossClient;
    }
    /**
     * 上传文件
     * @param $file 文件上传OBJ
     * @param $path 上传路径
     * @param $name 上传文件名
     * @return string 文件URL
     */
    public static function uploads($file,$path,$name){
        //拼接年月日路径
        $year = date('Y');
        $shortYear = (int)$year % 100; // 提取年份的最后两位
        $path = $path.'/'.$shortYear.'-'.date('m');
        $FilePath = self::GetRootPath($path);
        $file->move($FilePath, $name);
        $ossPath = '/'.self::$DIR.'/'.self::$SiteDir.'/'.$path.'/'.$name;
        if(env('OSS_ACCESS_IS_OPEN') == true){
            $ossClient = self::OssClient();
            $res = $ossClient->uploads($ossPath, $FilePath.$name);
            if($res !== true){
                ReturnJson(false,$res);
            }
        }
        return $ossPath;
    }

    public static function GetRootPath($path = ''){
        $request = request();
        if(!$request->header('Site')){
            if(!$request->site){
                ReturnJson(false,'当前站点的请求头为空');
            }
        }
        self::$SiteDir = $request->header('Site') ? $request->header('Site') : $request->site;
        $RootPath = public_path().'/'.self::$DIR.'/'.self::$SiteDir;
        if(!is_dir($RootPath)){
            mkdir($RootPath,0777,true);
        }
        $path = trim($path,'/');
        $path = $path ? $RootPath.'/' .$path.'/' : $RootPath.'/';
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

    public static function delete($path)
    {
        //如果是目录则继续
        if (is_dir($path)) {
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach ($p as $val) {
                //排除目录中的.和..
                if ($val != "." && $val != "..") {
                    //如果是目录则递归子目录，继续操作
                    if (is_dir($path . '/' . $val)) {
                        //子目录中操作删除文件夹和文件
                        self::delete($path . '/' . $val);
                        //目录清空后删除空文件夹
                        @rmdir($path . '/' . $val);
                    } else {
                        //如果是文件直接删除
                        unlink($path . '/' . $val);
                        if(env('OSS_ACCESS_IS_OPEN') == true){
                            $ossClient = self::OssClient();
                            $ossClient->delete($path);
                        }
                    }
                }
            }
            //目录清空后删除总文件夹
            @rmdir($path);
            if(env('OSS_ACCESS_IS_OPEN') == true){
                $path = str_replace(self::GetRootPath(),'', $path);
                $ossClient = self::OssClient();
                $ossClient->DeleteDir($path);
            }
        } else {
            @unlink($path);
            if(env('OSS_ACCESS_IS_OPEN') == true){
                $path = str_replace(self::GetRootPath(),'', $path);
                $ossClient = self::OssClient();
                $ossClient->delete($path);
            }
        }
    }


    public static function rename($oldPath,$newPath){
        if(!file_exists($oldPath)){
            return false;
        }
        rename($oldPath,$newPath);
        if(!file_exists($newPath)){
            return false;
        }
        if(env('OSS_ACCESS_IS_OPEN') == true){
            if(is_dir($newPath)){
                $oldPath = str_replace(self::GetRootPath(),'', $oldPath);
                $newPath = str_replace(self::GetRootPath(),'', $newPath);
                $ossClient = self::OssClient();
                $ossClient->RenameDir($oldPath,$newPath);
            } else {
                $oldPath = str_replace(self::GetRootPath(),'', $oldPath);
                $newPath = str_replace(self::GetRootPath(),'', $newPath);
                $ossClient = self::OssClient();
                $ossClient->rename($oldPath,$newPath);
            }
        }
        return true;
    }

    /**
     * 复制文件
     * @param string $oldPath
     * @param string $newPath
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    public static function copyFile($oldPath, $newPath, $overWrite = false)
    {
        if (!file_exists($oldPath)) {
            return false;
        }
        if (file_exists($newPath) && $overWrite == false) {
            return false;
        }
        $aimDir = dirname($newPath);
        if (!file_exists($aimDir)) {
            mkdir($aimDir, 0755, true);
            chmod($aimDir, 0755);
        }
        copy($oldPath, $newPath);
        if(env('OSS_ACCESS_IS_OPEN') == true){
            $newPath = str_replace(self::GetRootPath(),'', $newPath);
            $ossClient = self::OssClient();
            $ossClient->uploads($newPath, $oldPath);
        }
        return true;
    }

    /**
     *移动文件
     *@param string $newPath
     *@param string $oldPath
     *@param boolean $overWrite 该参数控制是否覆盖原文件
     *@return boolean
     */
    public static function moveFile($newPath, $oldPath, $overWrite = false)
    {
        if (!file_exists($newPath)) {
            return false;
        }
        if (file_exists($oldPath) && $overWrite = false) {
            return false;
        } elseif (file_exists($oldPath) && $overWrite = true) {
            SiteUploads::delete($oldPath);
        }
        $aimDir = dirname($oldPath);
        if (!file_exists($aimDir)) {
            mkdir($aimDir, 0755, true);
            chmod($aimDir, 0755);
        }
        rename($newPath, $oldPath);
        if(env('OSS_ACCESS_IS_OPEN') == true){
            $newPath = str_replace(self::GetRootPath(),'', $newPath);
            $oldPath = str_replace(self::GetRootPath(),'', $oldPath);
            $ossClient = self::OssClient();
            $ossClient->move($newPath, $oldPath);
        }
        return true;
    }

    /**
     * 创建文件夹
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return bool
     */

     public static function CreateDir($path, $mode = 0775)
    {
        $RootPath = self::GetRootPath();
        $DirPath = $RootPath.trim($path,'/');
        if ($path == '..') {
            //不能进去基本路径的上层
            return '超过文件管理范围';
        } else if (file_exists($DirPath)) {
            return '文件夹名称已存在';
        } else {
            mkdir($DirPath, $mode, true);
            chmod($DirPath, $mode);
        }
        if (file_exists($DirPath)) {
            if(env('OSS_ACCESS_IS_OPEN') == true){
                $ossClient = self::OssClient();
                $ossClient->CreateDir(trim($path,'/').'/');
            }
            return true;
        } else {
            return '文件夹创建失败';
        }
    }

    public static function unzip($path,$name,$unzipPath){
        $path = trim($path,'/');
        $RootPath = self::GetRootPath();
        $FilePath = $path ? $RootPath. $path .'/' .$name : $RootPath. $name;
        $LocalUnzipPath = $RootPath.$unzipPath.'/';
        if(strpos($name, '.zip') == false){
            return '文件不是ZIP文件';
        }
        if(!file_exists($FilePath)){
            return 'ZIP文件不存在';
        }
        $zip = new \ZipArchive();;
        $res = $zip->open($FilePath);
        if($res === true){
            $zip->extractTo($LocalUnzipPath);
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if(is_dir($RootPath.$filename)){
                    if(env('OSS_ACCESS_IS_OPEN') == true){
                        $ossClient = self::OssClient();
                        $toPath = $unzipPath ? $unzipPath.'/'.trim($filename,'/').'/' : trim($filename,'/').'/';
                        $ossClient->CreateDir($toPath);
                    }
                } else {
                    if(env('OSS_ACCESS_IS_OPEN') == true){
                        $ossClient = self::OssClient();
                        $toPath = $unzipPath ? $unzipPath.'/'.trim($filename,'/') : trim($filename,'/');
                        $ossClient->uploads($toPath,$LocalUnzipPath.$filename);
                    }
                }
            }
            $zip->close();
            return true;
        } else {
            return '文件解压失败';
        }
    }
}
