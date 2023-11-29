<?php

namespace App\Helper;
class SiteUploads
{
    private static $DIR = 'site';// 一级目录
    private static $SiteDir;// 站点目录

    private static function OssClient(){
        // 查询出站点OSS的配置信息
        $config['accessKey_id'] = env('OSS_ACCESS_KEY_ID');
        $config['accessKey_secret'] = env('OSS_ACCESS_KEY_SECRET');
        $config['endpoint'] = env('OSS_ACCESS_KEY_ENDPOINT');
        $config['bucket'] = env('OSS_ACCESS_KEY_BUCKET');
        $ossClient = new AliyuncsOss($config['accessKey_id'],$config['accessKey_secret'],$config['endpoint'],$config['bucket']);
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
        $FilePath = self::GetRootPath($path);
        $file->move($FilePath, $name);
        if(env('OSS_ACCESS_IS_OPEN') == true){
            $ossClient = self::OssClient();
            $ossClient->uploads($path.'/'. $name, $FilePath.$name);
        }
        return '/'.trim($path,'/').'/'.$name;
    }

    public static function GetRootPath($path = ''){
        $request = request();
        if(!$request->header('Site')){
            ReturnJson(false,'当前站点的请求头为空');
        }
        self::$SiteDir = $request->header('Site');
        $RootPath = public_path().'/'.self::$DIR.'/'.self::$SiteDir;
        if(!is_dir($RootPath)){
            mkdir($RootPath,0777,true);
        }
        $path = trim($path,'/');
        $path = $RootPath.'/'.$path.'/';
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
                $ossClient->delete($path);
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
            $oldPath = str_replace(self::GetRootPath(),'', $oldPath);
            $newPath = str_replace(self::GetRootPath(),'', $newPath);
            $ossClient = self::OssClient();
            $ossClient->rename($oldPath,$newPath);
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
        } elseif (file_exists($newPath) && $overWrite == true) {
            self::delete($newPath);
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
}