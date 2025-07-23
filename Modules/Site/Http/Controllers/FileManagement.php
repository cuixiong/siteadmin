<?php

namespace Modules\Site\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helper\SiteUploads;
use Illuminate\Support\Facades\File;
use Mockery\Exception;
use Modules\Site\Http\Models\System;
use Modules\Site\Http\Models\SystemValue;
use Modules\Site\Http\Models\OssFile;
use Nette\Utils\FileInfo;

class FileManagement extends Controller {
    private $RootPath;
    private $i                        = 1;
    public  $watermarkKey             = 'newsWatermark';
    public  $watermarkImageKey        = 'newsWatermarkImage'; // 水印图片路径
    public  $watermarkOpacityKey      = 'newsWatermarkOpacity'; // 透明度
    public  $watermarkLocationKey     = 'newsWatermarkLocation'; // 位置
    public  $watermarkOffsetWidthKey  = 'newsWatermarkOffsetWidth'; // 水平偏移
    public  $watermarkOffsetHeightKey = 'newsWatermarkOffsetHeight'; // 垂直偏移

    public function __construct() {
        $request = request();
        $action = $request->route()->getActionName();
        list($class, $action) = explode('@', $action);
        $this->RootPath = SiteUploads::GetRootPath();
        // if($action != 'download'){
        //     $client = SiteUploads::OssClient();
        // }
    }

    public function FileList(Request $request) {
        //排序方式
        $sortBy = $request->sort_file ?? 'time'; // 默认按时间排序 name|time
        $orderType = $request->order_type ?? 'desc'; // 默认降序 asc|desc
        $path = $request->path ?? '';
        $path = ltrim($path, '/');
        $filename = $this->RootPath.$path;
        if (!is_dir($filename)) {
            mkdir($filename, 0755, true);
            chmod($filename, 0755);
        } elseif (!file_exists($filename)) {
            mkdir($filename, 0755, true);
            chmod($filename, 0755);
        } elseif ($path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false, '超过文件管理范围');
        }
        $result = [];
        $path_array = explode('/', str_replace('\\', '/', $path));
        //面包屑
        $bread_crumbs = [];
        $bread_crumb_temp = '';
        foreach ($path_array as $k => $v) {
            if ($v == "." || $v == "..") {
                continue;
            } else {
                $result['prev_path'] = $bread_crumb_temp;
                $bread_crumb_temp .= (!empty($bread_crumb_temp) ? '/' : '').$v;
                $bread_crumbs[] = ['name' => $v, 'path' => $bread_crumb_temp];
            }
        }
        $result['bread_crumbs'] = $bread_crumbs;    //面包屑数组
        $result['current_path'] = $bread_crumb_temp; //当前路径
        // 扫描目录下的所有文件
        $tempArray = scandir($filename);
        $fileNameArray = [];
        $fileNameDirArray = []; // 存放文件夹数据
        $fileNameFileArray = []; // 存放其它文件数据
        if (is_array($tempArray)) {
            foreach ($tempArray as $k => $v) {
                // 跳过两个特殊目录
                if ($v == "." || $v == "..") {
                    continue;
                } else {
                    $info = [];
                    $info['type'] = self::filetype($filename.'/'.$v);
                    if ($info['type'] == 'dir') {
                        $info['size'] = "";
                    } else {
                        $info['size'] = self::converFileSize(filesize($filename.'/'.$v));
                    }
                    $info['is_file'] = ['name' => $v];
                    $info['path'] = $path ? str_replace(public_path(), '', $this->RootPath.trim($path, '/').'/'.$v)
                        : str_replace(public_path(), '', $this->RootPath.$v);
                    $info['orignal_path'] = $path.$v;
                    if ($info['type'] == 'image') {
                        $ImageSize = @getimagesize($filename.'/'.$v);
                        $info['width'] = $ImageSize[0] ?? 0 .' px';
                        $info['height'] = $ImageSize[1] ?? 0 .' px';
                    }
                    $info['extension'] = pathinfo($filename.'/'.$v, PATHINFO_EXTENSION);
                    clearstatcache();
                    $info['active_time'] = date('Y-m-d H:i:s', fileatime($filename.'/'.$v)) ?? ''; //上次访问时间
                    clearstatcache();
                    $info['create_time'] = date('Y-m-d H:i:s', filectime($filename.'/'.$v)) ?? ''; //创建时间
                    clearstatcache();
                    $info['update_time'] = date('Y-m-d H:i:s', filemtime($filename.'/'.$v)) ?? ''; //修改时间
                    if ($info['type'] == 'dir') {
                        $fileNameDirArray[$v] = $info;
                    } else {
                        $fileNameFileArray[$v] = $info;
                    }
                }
                // 排序规则函数
                $sortFunction = function ($a, $b) use ($sortBy, $orderType) {
                    if ($sortBy == 'time') {
                        // 按修改时间排序
                        $aTime = strtotime($a['update_time']);
                        $bTime = strtotime($b['update_time']);

                        return $orderType == 'asc' ? $aTime - $bTime : $bTime - $aTime;
                    } else {
                        // 按文件名排序
                        return $orderType == 'asc'
                            ? strcmp($a['is_file']['name'], $b['is_file']['name'])
                            : strcmp(
                                $b['is_file']['name'], $a['is_file']['name']
                            );
                    }
                };
                // 对文件夹和文件分别排序
                uasort($fileNameDirArray, $sortFunction);
                uasort($fileNameFileArray, $sortFunction);
                // 合并文件夹和文件结果
                $fileNameArray = array_merge(array_values($fileNameDirArray), array_values($fileNameFileArray));
            }
        } else {
            $fileNameArray = [];
        }
        //查询是否有oss的大文件上传
        //        $ossbasePath = str_replace(public_path(), '', SiteUploads::GetRootPath());
        //        $ossbasePath.= ltrim($path , '/');
        //        $ossbasePath = rtrim($ossbasePath, '/');
        //        $ossFileList = (new OssFile())->where('path', $ossbasePath)->orderBy('id', 'desc')->get()->toArray();
        //
        //        foreach ($ossFileList as $forossFile){
        //            $forData = [];
        //            $forData['active_time'] = $forossFile['created_at'];
        //            $forData['create_time'] = $forossFile['created_at'];
        //            $forData['update_time'] = $forossFile['updated_at'];
        //            $forData['is_file']['name'] = $forossFile['file_name'];
        //            $forData['orignal_path'] = $forossFile['oss_path'];
        //            $forData['path'] = $forossFile['oss_path'];
        //            $forData['type'] = $forossFile['file_suffix'];
        //            $forData['extension'] = $forossFile['file_suffix'];
        //            $forData['size'] = $forossFile['file_size'];
        //            $forData['is_oss'] = true;
        //            $fileNameArray[] = $forData;
        //        }
        //create_time 降序
        // array_multisort(array_column($fileNameArray, 'create_time'), SORT_DESC, $fileNameArray);
        $result['data'] = $fileNameArray;
        ReturnJson(true, trans('lang.request_success'), $result);
    }

    //文件大小换算
    public static function converFileSize($size) {
        if (!is_numeric($size)) {
            return 'unknown';
        }
        $bytes = [0, pow(1024, 1), 'B'];
        $kb = [pow(1024, 1), pow(1024, 2), 'KB'];
        $mb = [pow(1024, 2), pow(1024, 3), 'MB'];
        $gb = [pow(1024, 3), pow(1024, 4), 'GB'];
        if ($size > $kb[0] && $size < $kb[1]) {
            return number_format($size / $kb[0], 2).' '.$kb[2];
        } elseif ($size > $mb[0] && $size < $mb[1]) {
            return number_format($size / $mb[0], 2).' '.$mb[2];
        } elseif ($size > $gb[0]) {
            return number_format($size / $gb[0], 2).' '.$gb[2];
        } else {
            return $size.' '.$bytes[2];
        }

        return 'unknown';
    }

    // 计算文件类型
    public static function filetype($path) {
        if (is_dir($path)) {
            return 'dir';
        } else if (is_file($path)) {
            // 使用pathinfo()函数获取文件路径信息
            $fileinfo = pathinfo($path);
            if (empty($fileinfo) || empty($fileinfo['extension'])) {
                return 'file';
            }
            // 获取文件类型
            $filetype = $fileinfo['extension'];
            if (isset($filetype)) {
                switch ($filetype) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'bmp':
                    case 'webp':
                    case 'svg':
                    case 'ico':
                        return 'image';
//                    case'zip':
//                        return 'zip';
                    case $filetype:
                        return $filetype;
                    default:
                        return 'file';
                }
            }

            return 'file';
        }
    }

    //新建文件夹
    public function CreateDir(Request $request) {
        $path = $request->path ?? '';
        $name = $request->name ?? '';
        if (empty($name)) {
            ReturnJson(false, '文件夹名未传入');
        }
        $path = $path ? trim($path, '/').'/'.$name : $name;
        $res = SiteUploads::CreateDir($path);
        if ($res == true) {
            ReturnJson(true, '文件夹创建成功');
        } else {
            ReturnJson(false, $res);
        }
    }

    //重命名
    public function rename(Request $request) {
        $path = $request->path ?? '';
        $old_name = $request->old_name ?? '';
        $new_name = $request->new_name ?? '';
        $ext = pathinfo($new_name, PATHINFO_EXTENSION);
        $base_param = $this->RootPath;
        $old_full_path = $base_param.$path.'/'.$old_name;
        $new_full_path = $base_param.$path.'/'.$new_name;
        if (empty($old_name)) {
            ReturnJson(false, '旧文件/文件夹名未传入');
        } elseif (empty($new_name)) {
            ReturnJson(false, '新文件/文件夹名未传入');
        } elseif ($path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false, '超过文件管理范围');
        } elseif (!file_exists($old_full_path)) {
            ReturnJson(false, '选择的文件不存在');
        } elseif (file_exists($new_full_path)) {
            ReturnJson(false, '命名的文件已存在');
        } else if (is_file($old_full_path) && empty($ext)) {
            ReturnJson(false, "没有文件扩展名");
        } else {
            $res = SiteUploads::rename($old_full_path, $new_full_path);
            if ($res) {
                ReturnJson(true, '重命名成功');
            } else {
                ReturnJson(false, '重命名失败');
            }
        }
    }

    //删除
    public function delete(Request $request) {
        $base_param = $this->RootPath;
        $path = $request->path ?? '';
        $name = $request->name ?? '';
        $nameArray = is_array($name) ? $name : explode(",", $name);
        if (!is_array($nameArray) || count($nameArray) <= 0) {
            ReturnJson(false, '文件夹名未传入');
        } elseif ($path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false, '超过文件管理范围');
        } else {
            $fiter_full_path = [];
            foreach ($nameArray as $k => $v) {
                if (empty($v)) {
                    ReturnJson(false, '文件夹名未传入');
                }
                $full_path = $base_param.$path.'/'.$v;
                if (!file_exists($full_path)) {
                    ReturnJson(false, '旧文件/文件夹不存在:'.$v);
                }
                array_push($fiter_full_path, $full_path);
            }
            foreach ($fiter_full_path as $k => $v) {
                SiteUploads::delete($v);
            }
        }
        $fail = [];
        foreach ($fiter_full_path as $k => $v) {
            if (file_exists($v)) {
                array_push($fail, $v);
            }
        }
        if (count($fail) == 0) {
            ReturnJson(true, trans('lang.delete_success'));
        } else {
            ReturnJson(false, trans('lang.delete_error'));
        }
    }

    //复制或移动
    public function CopyAndMove(Request $request) {
        $base_param = $this->RootPath;
        $names = $request->names;
        $copy_or_move = $request->copy_or_move ?? ''; //1:复制;2:移动
        $old_path = $request->old_path ?? '';
        $new_path = $request->new_path ?? '';
        $overwrite = false;//1:覆盖;2:不覆盖
        if (empty($names)) {
            ReturnJson(false, '旧文件/文件夹名未传入');
        }
        if ($old_path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false, '超过文件管理范围');
        }
        if ($new_path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false, '超过文件管理范围');
        }
        if ($old_path == $new_path) {
            ReturnJson(false, '复制或移动的目标相同，请正确操作');
        }
        foreach ($names as $name) {
            $leftStr = trim(trim($old_path, '/').'/'.$name, '/').'/';
            $rightStr = trim($new_path, '/').'/';
            if (strpos($rightStr, $leftStr) === 0) {
                return ReturnJson(false, '复制或移动的目标相同，请正确操作');
            }
        }
        $IsExistsFiles = [];
        foreach ($names as $name) {
            $old_full_path = $base_param.$old_path.'/'.$name;
            $new_full_path = $base_param.$new_path.'/'.$name;
            if (!file_exists($old_full_path)) {
                ReturnJson(false, '选择的文件不存在');
            }
            if (is_dir($old_full_path)) {
                switch ($copy_or_move) {
                    case 1:
                        $res = $this->copyDir($old_full_path, $new_full_path, $overwrite);
                        break;
                    case 2:
                        $res = $this->moveDir($old_full_path, $new_full_path, $overwrite);
                        break;
                    default:
                        $res = false;
                        break;
                }
                // 把移动/复制文件夹中存在相同文件的合并放入到$IsExistsFiles容器中返回给前端
                if ($res !== false) {
                    $IsExistsFiles = array_merge($IsExistsFiles, $res);
                }
            } else if (is_file($old_full_path)) {
                //文件不存在则进行移动/复制，存在则放入到$IsExistsFiles容器中返回给前端
                if (!file_exists($new_full_path)) {
                    switch ($copy_or_move) {
                        case 1:
                            SiteUploads::copyFile($old_full_path, $new_full_path, $overwrite);
                            break;
                        case 2:
                            SiteUploads::moveFile($old_full_path, $new_full_path, $overwrite);
                            break;
                        default:
                            break;
                    }
                } else {
                    $IsExistsFiles[] = [
                        'old_path' => $old_full_path,
                        'new_path' => $new_full_path,
                        'name'     => $name,
                    ];
                }
            }
        }
        ReturnJson(true, trans('lang.request_success'), $IsExistsFiles);
    }

    // 强制覆盖文件（移动/复制操作，用户确认覆盖操作之后请求的方法）
    public function ForceFileOverwrite(Request $request) {
        ReturnJson(true, trans('lang.request_success'));
        $old_path = $request->old_path ?? '';
        $copy_or_move = $request->copy_or_move ?? ''; //1:复制;2:移动
        $names = $request->names;
        $datas = $request->data;
        foreach ($datas as $data) {
            // $data = json_decode($data, true);
            switch ($copy_or_move) {
                case 1:
                    SiteUploads::copyFile($data['old_path'], $data['new_path'], true);
                    break;
                case '2':
                    SiteUploads::moveFile($data['old_path'], $data['new_path'], true);
                    break;
                default:
                    break;
            }
        }
        // 如果是移动操作，把文件夹进行移除
        if ($copy_or_move == 2) {
            foreach ($names as $name) {
                $CleanDir = rtrim($this->RootPath, '/').'/'.trim($old_path, '/').'/'.$name;
                $this->TreeDeleteDir($CleanDir);
            }
        }
        ReturnJson(true, trans('lang.request_success'));
    }

    // 递归删除空文件夹
    private function TreeDeleteDir($dir) {
        if (is_dir($dir) === true && is_readable($dir) === true) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    $object = $dir.'/'.$object;
                    if (is_dir($object) === true) {
                        if ($this->isFolderEmpty($object) === true) {
                            rmdir($object);
                        }
                    }
                }
            }
            $res = true;
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    $res = false;
                }
            }
            if ($res) {
                rmdir($dir);
            }

            return true;
        }
    }

    /**
     *移动文件夹
     *
     * @param string  $oldDir
     * @param string  $aimDir
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     *
     * @return boolean
     */
    public function moveDir($oldDir, $aimDir, $overWrite = false) {
        $IsExistsFiles = [];
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir.'/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir.'/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!file_exists($aimDir)) {
            mkdir($aimDir, 0755, true);
            chmod($aimDir, 0755);
        }
        $dirHandle = scandir($oldDir);
        foreach ($dirHandle as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir.$file)) {
                if (file_exists($aimDir.$file) && !$overWrite) {
                    $IsExistsFiles[] = [
                        'old_path' => $oldDir.$file,
                        'new_path' => $aimDir.$file,
                    ];
                } else {
                    SiteUploads::moveFile($oldDir.$file, $aimDir.$file, $overWrite);
                }
            } else {
                $res = $this->moveDir($oldDir.$file, $aimDir.$file, $overWrite);
                if ($res !== false) {
                    $IsExistsFiles = array_merge($IsExistsFiles, $res);
                }
            }
        }
        if (empty($IsExistsFiles)) {
            rmdir($oldDir);
        }

        return $IsExistsFiles;
    }

    /**
     * 复制文件夹
     *
     * @param string  $oldDir
     * @param string  $aimDir
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     *
     * @return boolean
     */
    function copyDir($oldDir, $aimDir, $overWrite = false) {
        $IsExistsFiles = [];
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir.'/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir.'/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!file_exists($aimDir)) {
            mkdir($aimDir, 0755, true);
            chmod($aimDir, 0755);
        }
        $dirHandle = scandir($oldDir);
        foreach ($dirHandle as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir.$file)) {
                if (file_exists($aimDir.$file) && !$overWrite) {
                    $IsExistsFiles[] = [
                        'old_path' => $oldDir.$file,
                        'new_path' => $aimDir.$file,
                        'name'     => $file,
                    ];
                } else {
                    SiteUploads::copyFile($oldDir.$file, $aimDir.$file, $overWrite);
                }
            } else {
                $res = $this->copyDir($oldDir.$file, $aimDir.$file, $overWrite);
                if ($res !== false) {
                    $IsExistsFiles = array_merge($IsExistsFiles, $res);
                }
            }
        }

        return $IsExistsFiles;
    }

    //递归函数
    public static function getDirSize($full_path, $size_array = []) {
        $tempArray = scandir($full_path);
        $fileNameArray = [];
        foreach ($tempArray as $k => $v) {
            // 跳过两个特殊目录,跳过export导出目录
            if ($v == "." || $v == ".." || $v == "export") {
                continue;
            } else {
                $file_type = filetype($full_path.'/'.$v);
                if ($file_type == 'dir') {
                    $size_array = array_merge($size_array, self::getDirSize($full_path.'/'.$v));
                } else {
                    $size_array[] = filesize($full_path.'/'.$v);
                }
            }
        }

        return $size_array;
    }

    //压缩
    public function cmpress(Request $request) {
        $base_param = $this->RootPath;
        $path = $request->path ?? '';
        $name = $request->name ?? '';
        $full_path = $path ? $base_param.$path.'/' : $base_param;
        if (empty($name)) {
            ReturnJson(false, '文件夹名未传入');
        } elseif ($path == '..' || $name == '..') {
            //不能进去基本路径的上层
            ReturnJson(false, '超过文件管理范围');
        }
        $files = [];
        foreach ($name as $map) {
            if (!file_exists($full_path.$map)) {
                ReturnJson(false, '选择路径不存在');
            }
            if (is_file($full_path.$map)) {
                $files[] = $full_path.$map;
            } else {
                $files = array_merge($files, glob($full_path.$map.'/*'));
            }
        }
        //如果是文件夹,path为空
        $dateStr = date('YmdHis');
        if (empty($path)) {
            $compressName = $name[0]."_".$dateStr.'.zip';
        } else {
            $compressName = $dateStr.'.zip';
        }
        $zipFileName = $full_path.$compressName;
        // var_dump($files, $zipFileName);die;
        $res = self::zipDir($files, $zipFileName);
        if (file_exists($zipFileName)) {
            ReturnJson(true, '压缩成功', ['path' => $zipFileName]);
        } else {
            ReturnJson(false, '压缩失败，请检查是否是空文件夹');
        }
    }

    /**
     * 压缩文件
     * 使用:
     *   $pathArray = array( '/path/to/sourceDir', '/path/to/sourceDir2' );
     *   HZip::zipDir( pathArray, '/path/to/out.zip' );
     *
     * @param array  $pathArray  需要压缩的文件夹路径数组
     * @param string $outZipPath 压缩文件夹路径
     */
    private static function zipDir($pathArray, $outZipPath) {
        if (empty($pathArray)) {
            return false;
        }
        $z = new \ZipArchive();
        // 初始化
        $z->open($outZipPath, \ZipArchive::CREATE);
        // 新建压缩文件
        try {
            foreach ($pathArray as $key => $sourcePath) {
                //linux服务器需要注释
                // $sourcePath = trim($sourcePath, '/');
                // 去除后缀，防止压缩包内出现文件夹名带有前缀“/”
                $sourcePath = trim($sourcePath, '\\');
                $pathInfo = pathinfo($sourcePath);
                // var_dump( $pathInfo );
                // echo '<br/>';
                $arr = explode('/', $pathInfo['dirname']);
                $ZipDir = array_pop($arr);
                $pathInfo['dirname'] = implode('/', $arr);
                $parentPath = $pathInfo['dirname'];
                $dirName = $pathInfo['basename'];
                if (empty($pathInfo['extension'])) {
                    // $z->addEmptyDir($dirName);
                    // 添加一个新目录
                    self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
                } else {
                    // 单文件压缩
                    if (file_exists($sourcePath)) {
                        $z->addFile($sourcePath, $ZipDir.'/'.$dirName);
                    }
                }
            }
            // 关闭存档
            $z->close();
            // 关闭存档
        } catch (\Throwable $th) {
            return false;
        }
        if (!is_dir($outZipPath)) {
            //压缩文件上传到oss
            //\Log::error('返回结果数据:'.$outZipPath);
            SiteUploads::multipartUpload($outZipPath);

            return true;
        }

        return false;
    }

    /**
     * 把文件打包成 zip
     *
     * @param $folder          需要压缩的文件夹
     * @param $zipFile         压缩文件
     * @param $exclusiveLength 截取上级文件夹路径的长度，以递归新建目录
     */
    private static function folderToZip($folder, &$zipFile, $exclusiveLength) {
        $handle = opendir($folder);
        $zipFile->addEmptyDir(substr($folder, $exclusiveLength));
        //打开一个目录
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // 截取的上级文件夹路径
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    //添加文件
                    $a = $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    //添加新目录
                    $zipFile->addEmptyDir($localPath);
                    //递归
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
        //关闭
    }

    // 上传文件
    public function uploads(Request $request) {
        try {
            $path = $request->path;
            $files = $request->file('file');
            $is_replace = $request->is_replace ?? false;
            if (empty($files)) {
                ReturnJson(false, '请选择上传文件');
            }
            $res = [];
            $watermarkConfig = null;
            $site = getSiteName();
            foreach ($files as $file) {
                $file_real_path = '';
                $file_real_path = public_path("/site/{$site}/");
                $temp_path = $path;
                $originalName = $file->getClientOriginalName();
                $name = $originalName;
                // 是否新建年份文件夹
                $createDateFolder = false;
                // 是否使用水印
                $watermark = null;
                if (strpos($path, 'news/') !== false) {
                    //新闻模块需要做年月路径
                    $createDateFolder = true;
                    //新闻图片需要添加水印
                    $watermarkConfig = !empty($watermarkConfig) ? $watermarkConfig : $this->getWatermarkConfig();
                    $watermark = $watermarkConfig;
                    //临时路径
                    if (strpos($temp_path, '-') === false) {
                        //拼接年月日路径
                        $year = date('Y');
                        $shortYear = (int)$year % 100; // 提取年份的最后两位
                        $temp_path = $temp_path.'/'.$shortYear.'-'.date('m');
                    }
                }
                $file_real_path .= "/".$temp_path."/".$originalName;
                if (file_exists($file_real_path)) {
                    //文件存在,
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $file_name = date('YmdHis').rand(100000, 999999);
                    $name = $file_name.'.'.$extension;
                }
                $res[] = SiteUploads::uploads($file, $path, $name, $createDateFolder, $watermark);
            }
            ReturnJson(true, '上传成功', $res);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function uploadsByWebPath(Request $request) {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '2048M');
            $webpath = $request->webpath;
            $storepath = $request->storepath;
            if (empty($webpath) || empty($storepath)) {
                ReturnJson(false, '参数错误');
            }
            $res = [];
            $watermarkConfig = null;
            $site = getSiteName();
            $savePath = public_path("/site/{$site}/{$storepath}");
            //额外的处理新闻路径
            $createDateFolder = false;
            if (strpos($savePath, 'news/') !== false) {
                if (strpos($savePath, '-') === false) {
                    $createDateFolder = true;
                    //拼接年月日路径
                    $year = date('Y');
                    $shortYear = (int)$year % 100; // 提取年份的最后两位
                    $savePath = $savePath.'/'.$shortYear.'-'.date('m');
                }
            }
            // 获取图片二进制数据
            //$imageData = @file_get_contents($webpath);
            $imageData = $this->getSteamByPath($webpath);
            if ($imageData !== false) {
                if (!is_dir($savePath)) {
                    mkdir($savePath, 0777, true);
                }
                $file_name = basename(parse_url($webpath, PHP_URL_PATH));
                if (file_exists($savePath.'/'.$file_name)) {
                    $file_name = date('YmdHis').rand(100000, 999999);
                    $extension = pathinfo($webpath, PATHINFO_EXTENSION);
                    $file_name = $file_name.'.'.$extension;
                } else {
                    $file_name = rawurlencode($file_name); //重新编码
                }
                $savePath .= '/'.$file_name;
                // 保存到本地
                if (file_put_contents($savePath, $imageData)) {
                } else {
                    ReturnJson(false, '保存失败!');
                }
            } else {
                ReturnJson(false, '图片地址错误!');
            }
            //压缩图片业务
            // 压缩阈值判断（200KB）
            $threshold = 200 * 1024; // 200KB
            $originalSize = filesize($savePath);
            if ($originalSize > $threshold) {
                list($savePath, $file_name) = $this->compressImage($savePath);
            }

            // 是否使用水印
            $watermark = null;
            if (strpos($savePath, 'news/') !== false) {
                //新闻图片需要添加水印
                $watermarkConfig = !empty($watermarkConfig) ? $watermarkConfig : $this->getWatermarkConfig();
                $watermark = $watermarkConfig;
            }
            $file = new FileInfo($savePath);
            $res = SiteUploads::uploads($file, $storepath, $file_name, $createDateFolder, $watermark, true);
            ReturnJson(true, '上传成功', $res);
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    // 获取水印配置
    public function getWatermarkConfig() {
        $isOpen = System::query()->where("alias", $this->watermarkKey)->value('status');
        if ($isOpen && $isOpen == 1) {
            $keyList = [
                $this->watermarkImageKey,
                $this->watermarkOpacityKey,
                $this->watermarkLocationKey,
                $this->watermarkOffsetWidthKey,
                $this->watermarkOffsetHeightKey,
            ];
            $data = SystemValue::query()->whereIn("key", $keyList)->where("status", 1)->pluck('value', 'key')->toArray(
            );
            $result = [];
            if (!isset($data[$this->watermarkImageKey]) || empty($data[$this->watermarkImageKey])) {
                return null;
            }
            $result['image'] = $data[$this->watermarkImageKey];
            $result['opacity'] = $data[$this->watermarkOpacityKey];
            $result['location'] = $data[$this->watermarkLocationKey];
            $result['offsetWidth'] = $data[$this->watermarkOffsetWidthKey];
            $result['offsetHeight'] = $data[$this->watermarkOffsetHeightKey];

            return $result;
        }

        return null;
    }

    // 下载文件
    public function download(Request $request) {
        $path = $request->path;
        $name = base64_decode($request->name);
        $site = $request->site;
        if (empty($name)) {
            ReturnJson(false, '请选择下载文件名称');
        }
        if (empty($site)) {
            ReturnJson(false, '站点名称为空');
        }
        $RootPath = SiteUploads::getRootPath();
        $filePath = rtrim($RootPath, '/');
        if (!empty($path)) {
            $filePath = $filePath.'/'.trim($path, '/');
        }
        $filePath = $filePath.'/'.$name;
        if (!file_exists($filePath)) {
            ReturnJson(false, '下载文件不存在');
        }
        $res = SiteUploads::download($path, $name);
        if ($res == false) {
            ReturnJson(false, '下载失败');
        }

        return response()->download($res);
    }

    public function newDownload(Request $request) {
        $path = $request->path;
        $name = base64_decode($request->name);
        $site = $request->site;
        if (empty($name)) {
            ReturnJson(false, '请选择下载文件名称');
        }
        if (empty($site)) {
            ReturnJson(false, '站点名称为空');
        }
        $RootPath = SiteUploads::getRootPath();
        $filePath = rtrim($RootPath, '/');
        if (!empty($path)) {
            $filePath = $filePath.'/'.trim($path, '/');
        }
        $filePath = $filePath.'/'.$name;
        if (!file_exists($filePath)) {
            ReturnJson(false, '下载文件不存在');
        }
        $res = SiteUploads::download($path, $name);
        if ($res == false) {
            ReturnJson(false, '下载失败');
        }
        $fileAbsultPath = str_replace(public_path(), '', $res);
        $domain = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'];
        $newDownUrl = $domain.$fileAbsultPath;
        ReturnJson(true, '请求成功', ['downloadUrl' => $newDownUrl]);
    }

    // 根目录查询文件夹
    public function DirList(Request $request) {
        $RootPath = SiteUploads::getRootPath();
        $DirList = $this->listFolderFiles($RootPath, $RootPath);
        $res = [];
        $res[] = ['value' => '', 'label' => '根目录', 'children' => $DirList];
        ReturnJson(true, trans('lang.request_success'), $res);
    }

    // 递归查询文件夹
    public function listFolderFiles($dir, $RootPath) {
        $dir = rtrim($dir, '/');
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir.'/'.$value)) {
                    $this->i = $this->i + 1;
                    $tempRoot = str_replace(rtrim($RootPath, '/'), '', $dir).'/';
                    $result[] = ['value'    => trim($tempRoot.$value, '/'), 'label' => $value,
                                 'children' => $this->listFolderFiles($dir.'/'.$value, $RootPath)];
                }
            }
        }

        return $result;
    }

    // 计算文件夹大小
    public function DirSize(Request $request) {
        $path = $request->path;
        $name = $request->name;
        if (empty($name)) {
            ReturnJson(false, '文件夹目录为空');
        }
        $RootPath = SiteUploads::getRootPath();
        $path = rtrim($RootPath, '/').'/'.trim($path, '/').'/'.$name;
        if (!is_dir($path)) {
            ReturnJson(false, '文件夹不存在');
        }
        $SizeList = self::getDirSize($path, []);
        $size = array_sum($SizeList);
        $size = $this->converFileSize($size);
        ReturnJson(true, trans('lang.request_success'), $size);
    }

    public function unzip(Request $request) {
        $path = $request->path;
        $name = $request->name;
        $unzipPath = $request->unzipPath;
        if (empty($name)) {
            ReturnJson(false, '请选择需要解压的文件名称');
        }
        $res = SiteUploads::unzip($path, $name, $unzipPath);
        if ($res === true) {
            ReturnJson(true, '文件解压成功');
        } else {
            ReturnJson(false, $res);
        }
    }

    public function IsExists($path, $name) {
        $RootPath = SiteUploads::getRootPath();
        $path = $path ? rtrim($RootPath, '/').'/'.trim($path, '/').'/'.$name : rtrim($RootPath, '/').'/'.$name;
        if (file_exists($path)) {
            ReturnJson(201, '文件存在');
        }
        if (is_dir($path)) {
            ReturnJson(201, '目录存在');
        }
    }

    // 根目录查询文件夹
    public function DirListOne(Request $request) {
        set_time_limit(0);
        $RootPath = SiteUploads::getRootPath();
        $path = $request->path ? $request->path.'/' : '';
        $level = $request->level ? $request->level : 0;
        if (empty($path) && empty($level)) {
            $res[] = [
                'value'  => '',
                'label'  => '根目录',
                'isLeaf' => false,
                'path'   => ''
            ];
            ReturnJson(true, trans('lang.request_success'), $res);
        }
        $DirList = $this->listFolderFilesOne($RootPath.$path, $RootPath);
        ReturnJson(true, trans('lang.request_success'), $DirList);
    }

    // 递归查询文件夹
    public function listFolderFilesOne($dir, $RootPath) {
        $dir = rtrim($dir, '/');
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $value) {
            $isLeaf = true;
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir.'/'.$value)) {
                    $this->i = $this->i + 1;
                    $tempRoot = str_replace(rtrim($RootPath, '/'), '', $dir);
                    $tempRoot = trim($tempRoot, '/') ? $tempRoot.'/'.$value : $value;
                    $ccdir = scandir($dir.'/'.$value);
                    if (count($ccdir) > 2) {
                        // 查询该文件夹下是否还有文件夹
                        foreach ($ccdir as $value2) {
                            if (is_dir($dir.'/'.$value.'/'.$value2) && !in_array($value2, array(".", ".."))) {
                                $isLeaf = false;
                                break;
                            }
                        }
                    }
                    $result[] = [
                        'value'  => trim($tempRoot, '/'),
                        'label'  => $value,
                        'isLeaf' => $isLeaf,
                        'path'   => $dir.'/'.$value
                    ];
                }
            }
        }

        return $result;
    }

    /**
     *  oss大文件上传,添加相关数据
     */
    public function ossFileAdd(Request $request) {
        ini_set('memory_limit', '1024M');
        try {
            $path = $request->input('path', '');
            $oss_path = $request->input('oss_path', '');
            $file_name = $request->input('file_name', '');
            $file_size = $request->input('file_size', '');
            $file_suffix = $request->input('file_suffix', '');
            if (empty($path) || empty($oss_path) || empty($file_name) || empty($file_size) || empty($file_suffix)) {
                ReturnJson(false, '参数错误');
            }
            $file_fullpath = rtrim($path, "/")."/".$file_name;
            $data = [
                'path'          => $path,
                'file_fullpath' => $file_fullpath,
                'oss_path'      => $oss_path,
                'file_name'     => $file_name,
                'file_size'     => $file_size,
                'file_suffix'   => $file_suffix,
                'created_at'    => date('Y-m-d H:i:s')
            ];
            $rs = (new OssFile())->create($data);
            try {
                // 获取 OSS 文件内容
                $ossClient = SiteUploads::OssClient();
                $content = $ossClient->download($file_fullpath);
                // 保存到本地
                $localPath = rtrim(public_path(), "/").$file_fullpath;
                file_put_contents($localPath, $content);
                chmod($localPath, 0775);
            } catch (\Exception $e) {
                ReturnJson(false, '添加失败'.$e->getMessage());
            }
            if ($rs) {
                ReturnJson(true, '添加成功');
            } else {
                ReturnJson(false, '添加失败');
            }
        } catch (\Exception $e) {
            ReturnJson(false, $e->getMessage());
        }
    }

    public function getSteamByPath($url) {
        try {
            $client = new Client();
            $response = $client->get($url, [
                'headers' => ['User-Agent' => 'Mozilla/5.0']
            ]);
            $contentType = $response->getHeaderLine('Content-Type');
            if (!preg_match('/^image\//', $contentType)) {
                //不是图片直接返回null
                return null;
            }

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new Exception("获取图片失败".$e->getMessage());
        }
    }

    private function compressImage(string $savePath) {
        if (!file_exists($savePath)) {
            throw new Exception("原文件不存在: {$savePath}");
        }
        $originalSize = filesize($savePath);
        $fileInfo = pathinfo($savePath);
        $image = $this->loadImageResource($savePath);
        if (!$image) {
            throw new Exception("不支持的图片格式或损坏文件");
        }
        $fileName = "{$fileInfo['filename']}.webp";
        $webpPath = "{$fileInfo['dirname']}/{$fileName}";
        // 压缩阈值判断（200KB）
        $threshold = 200 * 1024; // 200KB
        if ($originalSize > $threshold) {
            $quality = 60; // 压缩质量（70-90为佳）
        } else {
            $quality = 100;
        }
        $result = imagewebp($image, $webpPath, $quality);
        // 8. 释放内存
        imagedestroy($image);
        if (!$result) {
            throw new Exception("WebP转换失败");
        }
        //删除原文件
        unlink($savePath);

        return [$webpPath, $fileName];
    }

    private function loadImageResource(string $filePath) {
        $sourceImage = imagecreatefromstring(file_get_contents($filePath));
        if (!$sourceImage) {
            throw new Exception("加载图片失败: {$filePath} | GD库可能未启用相关扩展");
        }

        // 检查是否为调色板模式
        if (imageistruecolor($sourceImage) === false) {
            // 创建真彩色画布
            $trueColorImage = imagecreatetruecolor(imagesx($sourceImage), imagesy($sourceImage));

            // 复制像素并填充背景（解决透明区域问题）
            $transparentColor = imagecolorallocatealpha($trueColorImage, 0, 0, 0, 127);
            imagefill($trueColorImage, 0, 0, $transparentColor);
            imagecopy($trueColorImage, $sourceImage, 0, 0, 0, 0, imagesx($sourceImage), imagesy($sourceImage));

            // 释放原始图像资源
            imagedestroy($sourceImage);
            $sourceImage = $trueColorImage;
        }

        return $sourceImage;
    }
}
