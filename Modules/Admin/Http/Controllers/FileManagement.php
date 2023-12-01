<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helper\AdminUploads;

class FileManagement extends Controller{
    private $RootPath;
    public function __construct()
    {
        $this->RootPath = AdminUploads::GetRootPath();
    }

    public function FileList(Request $request)
    {   
        $path = $request->path ?? '';
        $filename = $this->RootPath . $path;

        if (!is_dir($filename)) {
            ReturnJson(false,'该路径非文件夹');
        } elseif (!file_exists($filename)) {
            ReturnJson(false,'路径不存在');
        } elseif ($path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false,'超过文件管理范围');
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
                $bread_crumb_temp .= (!empty($bread_crumb_temp) ? '/' : '') . $v;
                $bread_crumbs[] = ['name' => $v, 'path' => $bread_crumb_temp];
            }
        }
        $result['bread_crumbs'] = $bread_crumbs;    //面包屑数组
        $result['current_path'] = $bread_crumb_temp; //当前路径
        // 扫描目录下的所有文件
        $tempArray = scandir($filename);
        $fileNameArray = [];
        if (is_array($tempArray)) {
            foreach ($tempArray as $k => $v) {
                // 跳过两个特殊目录,跳过export导出目录
                if ($v == "." || $v == ".." || $v == "export") {
                    continue;
                } else {
                    $info = [];
                    $info['type'] = self::filetype($filename . '/' . $v);
                    if ($info['type'] == 'dir') {
                        $info['size'] = "";
                    } else {
                        $info['size'] = self::converFileSize(filesize($filename . '/' . $v));
                    }
                    if($info['type'] == 'image'){
                        $url = $path ? str_replace(public_path(),'',$this->RootPath. $path. '/'. $v) : str_replace(public_path(),'', $this->RootPath. $v);
                        $info['is_file'] = ['name' => $v,'path' => $url];
                    } else {
                        $info['is_file'] = ['name' => $v];
                    }

                    $info['extension'] = pathinfo($filename . '/' . $v, PATHINFO_EXTENSION);
                    clearstatcache();
                    $info['active_time'] = date('Y-m-d H:i:s', fileatime($filename . '/' . $v)) ?? ''; //上次访问时间
                    clearstatcache();
                    $info['create_time'] = date('Y-m-d H:i:s', filectime($filename . '/' . $v)) ?? ''; //创建时间
                    clearstatcache();
                    $info['update_time'] = date('Y-m-d H:i:s', filemtime($filename . '/' . $v)) ?? ''; //修改时间

                    $fileNameArray[] = $info;
                }
            }
        } else {
            $fileNameArray =  [];
        }
        $result['data'] = $fileNameArray;
        ReturnJson(true,trans('lang.request_success'),$result);
    }

    //文件大小换算
    public static function converFileSize($size)
    {
        if (!is_numeric($size)) {
            return 'unknown';
        }
        $bytes = [0, pow(1024, 1), 'B'];
        $kb = [pow(1024, 1), pow(1024, 2), 'KB'];
        $mb = [pow(1024, 2), pow(1024, 3), 'MB'];
        $gb = [pow(1024, 3), pow(1024, 4), 'GB'];

        if ($size > $kb[0] && $size < $kb[1]) {
            return number_format($size / $kb[0], 2) . ' ' . $kb[2];
        } elseif ($size > $mb[0] && $size < $mb[1]) {
            return number_format($size / $mb[0], 2) . ' ' . $mb[2];
        } elseif ($size > $gb[0]) {
            return number_format($size / $gb[0], 2) . ' ' . $gb[2];
        } else {
            return  $size . ' ' . $bytes[2];
        }
        return 'unknown';
    }

    // 计算文件类型
    public static function filetype ($path)
    {

        if(is_dir($path)){
            return 'dir';
        } else if(is_file($path)) {
            // 使用pathinfo()函数获取文件路径信息
            $fileinfo = pathinfo($path);
            // 获取文件类型
            $filetype = $fileinfo['extension'];
            if(isset($filetype)){
                switch ($filetype) {
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'bmp':
                        return 'image';
                    case'zip':
                        return 'zip';
                    default:
                        return 'file';
                }
            }
            return 'file';
        }
    }

    //新建文件夹
    public function CreateDir(Request $request)
    {
        $path = $request->path ?? '';
        $name = $request->name ?? '';

        $full_path = $this->RootPath . $path . '/' . $name;
        if (empty($name)) {
            ReturnJson(false,'文件夹名未传入');
        } elseif ($path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false,'超过文件管理范围');
        } elseif (file_exists($full_path)) {
            ReturnJson(false,'文件夹名称已存在');
        } else {
            mkdir($full_path, 0755, true);
            chmod($full_path, 0755);
        }
        if (file_exists($full_path)) {
            ReturnJson(true,'文件夹创建成功');
        } else {
            ReturnJson(true,'文件夹创建失败');
        }
    }

    //重命名
    public function rename(Request $request)
    {
        $path = $request->path?? '';
        $old_name = $request->old_name?? '';
        $new_name = $request->new_name?? '';

        $base_param = $this->RootPath;
        $old_full_path = $base_param . $path . '/' . $old_name;
        $new_full_path = $base_param . $path . '/' . $new_name;
        if (empty($old_name)) {
            ReturnJson(false,'旧文件/文件夹名未传入');
        } elseif (empty($new_name)) {
            ReturnJson(false,'新文件/文件夹名未传入');
        } elseif ($path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false,'超过文件管理范围');
        } elseif (!file_exists($old_full_path)) {
            ReturnJson(false,'选择的文件不存在');
        } elseif (file_exists($new_full_path)) {
            ReturnJson(false,'命名的文件已存在');
        } else {
            rename($old_full_path, $new_full_path);
        }

        if (file_exists($new_full_path)) {
            ReturnJson(true,'重命名成功');
        } else {
            ReturnJson(false,'重命名失败');
        }
    }

    //删除
    public function delete(Request $request)
    {
        $base_param = $this->RootPath;
        $path = $request->path ?? '';
        $name = $request->name ?? '';
        $nameArray = explode(",",$name);

        if (!is_array($nameArray) || count($nameArray) <= 0) {
            ReturnJson(false,'文件夹名未传入');
        } elseif ($path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false,'超过文件管理范围');
        } else {
            $fiter_full_path = [];
            foreach ($nameArray as $k => $v) {
                if (empty($v)) {
                    ReturnJson(false,'文件夹名未传入');
                }
                $full_path = $base_param . $path . '/' . $v;
                if (!file_exists($full_path)) {
                    ReturnJson(false,'旧文件/文件夹不存在:' . $v);
                }
                array_push($fiter_full_path, $full_path);
            }
            foreach ($fiter_full_path as $k => $v) {
                $this->delDir($v);
            }
        }

        $fail = [];
        foreach ($fiter_full_path as $k => $v) {
            if (file_exists($v)) {
                array_push($fail, $v);
            }
        }
        if (count($fail) == 0) {
            ReturnJson(true,trans('lang.delete_success'));
        } else {
            ReturnJson(false,trans('lang.delete_error'));
        }
    }

    //复制或移动
    public function CopyAndMove(Request $request)
    {
        $base_param = $this->RootPath;

        $old_path = $request->old_path ?? '';
        $new_path = $request->new_path ?? '';
        $name = $request->name ?? '';
        $copy_or_move = $request->copy_or_move ?? ''; //1:复制;2:移动
        $overwrite = $request->overwrite ?? ''; //1:覆盖;2:不覆盖

        $old_full_path = $base_param . $old_path . '/' . $name;
        $new_full_path = $base_param . $new_path . '/' . $name;
        if (empty($name)) {
            ReturnJson(false,'旧文件/文件夹名未传入');
        } elseif ($old_path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false,'超过文件管理范围');
        } elseif ($new_path == '..') {
            //不能进去基本路径的上层
            ReturnJson(false,'超过文件管理范围');
        } elseif (!file_exists($old_full_path)) {
            ReturnJson(false,'选择的文件不存在');
        } elseif ($old_path == $new_path) {
            ReturnJson(false,'复制或移动的目标相同，请正确操作');
        } else {
            if ($overwrite == 1) {
                $overwrite = true;
            } elseif ($overwrite == 2) {
                $overwrite = false;
            } else {
                ReturnJson(false,'未选择覆盖模式');
            }
            if (is_dir($old_full_path)) {
                if ($copy_or_move == 1) {
                    $this->copyDir($old_full_path, $new_full_path, $overwrite);
                } elseif ($copy_or_move == 2) {
                    $this->moveDir($old_full_path, $new_full_path, $overwrite);
                    // moveDir();
                }
            } elseif (is_file($old_full_path)) {
                if (!$overwrite) {
                    // return 
                    //文件存在并且不覆盖，则不进行任何处理
                    if (!file_exists($new_full_path)) {

                        if ($copy_or_move == 1) {
                            $this->copyFile($old_full_path, $new_full_path, $overwrite);
                        } elseif ($copy_or_move == 2) {
                            $this->moveFile($old_full_path, $new_full_path, $overwrite);
                        }
                    }
                } elseif ($overwrite) {

                    if ($copy_or_move == 1) {
                        $this->copyFile($old_full_path, $new_full_path, $overwrite);
                    } elseif ($copy_or_move == 2) {
                        $this->moveFile($old_full_path, $new_full_path, $overwrite);
                    }
                }
            }
        }

        if (file_exists($new_full_path)) {
            ReturnJson(true,trans('lang.request_success'));
        } else {
            ReturnJson(true,trans('lang.request_error'));
        }
    }

    public function delDir($path)
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
                        $this->delDir($path . '/' . $val);
                        //目录清空后删除空文件夹
                        @rmdir($path . '/' . $val);
                    } else {
                        //如果是文件直接删除
                        unlink($path . '/' . $val);
                    }
                }
            }
            //目录清空后删除总文件夹
            @rmdir($path);
        } else {
            @unlink($path);
        }
    }

    /**
     *移动文件夹
     *
     *@param string $oldDir
     *@param string $aimDir
     *@param boolean $overWrite 该参数控制是否覆盖原文件
     *@return boolean
     */
    public function moveDir($oldDir, $aimDir, $overWrite = false)
    {
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
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
            if (!is_dir($oldDir . $file)) {
                $this->moveFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                $this->moveDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        return rmdir($oldDir);
    }

    /**
     *移动文件
     *
     *@param string $fileUrl
     *@param string $aimUrl
     *@param boolean $overWrite 该参数控制是否覆盖原文件
     *@return boolean
     */
    function moveFile($fileUrl, $aimUrl, $overWrite = false)
    {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite = false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite = true) {
            $this->delDir($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        if (!file_exists($aimDir)) {
            mkdir($aimDir, 0755, true);
            chmod($aimDir, 0755);
        }
        rename($fileUrl, $aimUrl);
        return true;
    }

    /**
     * 复制文件夹
     *
     * @param string $oldDir
     * @param string $aimDir
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    function copyDir($oldDir, $aimDir, $overWrite = false)
    {
        $aimDir = str_replace('', '/', $aimDir);
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
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
            if (!is_dir($oldDir . $file)) {
                $this->copyFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                $this->copyDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
    }

    /**
     * 复制文件
     *
     * @param string $fileUrl
     * @param string $aimUrl
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    function copyFile($fileUrl, $aimUrl, $overWrite = false)
    {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite == false) {
            return false;
        }
        $aimDir = dirname($aimUrl);
        if (!file_exists($aimDir)) {
            mkdir($aimDir, 0755, true);
            chmod($aimDir, 0755);
        }
        copy($fileUrl, $aimUrl);
        return true;
    }

    //递归函数
    public static function getDirSize($full_path, $size_array = [])
    {

        $tempArray = scandir($full_path);
        $fileNameArray = [];
        foreach ($tempArray as $k => $v) {
            // 跳过两个特殊目录,跳过export导出目录
            if ($v == "." || $v == ".." || $v == "export") {
                continue;
            } else {
                $file_type = filetype($full_path . '/' . $v);
                if ($file_type == 'dir') {
                    $size_array = array_merge($size_array, self::getDirSize($full_path . '/' . $v));
                } else {
                    $size_array[] = filesize($full_path . '/' . $v);
                }
            }
        }
        return $size_array;
    }

    //压缩
    public function cmpress(Request $request)
    {
        $base_param = $this->RootPath;
        $path = $request->path ?? '';
        $name = $request->name ?? '';
        $full_path = $path ? $base_param . $path . '/' . $name : $base_param . $name;

        if (empty($name)) {
            ReturnJson(false,'文件夹名未传入');
        } elseif ($path == '..' || $name == '..') {
            //不能进去基本路径的上层
            ReturnJson(false,'超过文件管理范围');
        } elseif (!file_exists($full_path)) {
            ReturnJson(false,'选择路径不存在');
        } else {
            $rand = rand(10000, 99999);
            $zipFileName = $full_path . '_' . $rand . '.zip';
            $res = self::zipDir(glob($full_path . '/*'), $zipFileName);
        }
        if (file_exists($zipFileName)) {
            ReturnJson(true,'压缩成功',['path' => trim($path . '/' . $name . '_' . $rand . '.zip', "/")]);
        } else {
            ReturnJson(false,'压缩失败，请检查是否是空文件夹');
        }
    }

    /**
     * 压缩文件
     * 使用:
     *   $pathArray = array( '/path/to/sourceDir', '/path/to/sourceDir2' );
     *   HZip::zipDir( pathArray, '/path/to/out.zip' );
     *
     * @param array $pathArray 需要压缩的文件夹路径数组
     * @param string $outZipPath 压缩文件夹路径
     */
    private static function zipDir($pathArray, $outZipPath)
    {
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
            $z->close();
            // 关闭存档
        } catch (\Throwable $th) {
            return false;
        }
        if (!is_dir($outZipPath)) {
            return true;
        }

        return false;
    }

    /**
     * 把文件打包成 zip
     *
     * @param $folder 需要压缩的文件夹
     * @param $zipFile 压缩文件
     * @param $exclusiveLength 截取上级文件夹路径的长度，以递归新建目录
     */
    private static function folderToZip($folder, &$zipFile, $exclusiveLength)
    {
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
    public function uploads(Request $request)
    {
        $path = $request->path;
        $files = $request->file('file');
        if (empty($files)) {
            ReturnJson(false, '请选择上传文件');
        }
        $res = [];
        foreach ($files as $file) {
            $name = $file->getClientOriginalName();
            $res[] = AdminUploads::uploads($file, $path,$name);
        }
        ReturnJson(true, '上传成功', $res);
    }

    // 下载文件
    public function download(Request $request)
    {
        $path = $request->path;
        $name = $request->name;
        if (empty($name)) {
            ReturnJson(false, '请选择下载文件名称');
        }
        $RootPath = AdminUploads::getRootPath();
        $filePath = rtrim($RootPath, '/').'/'. trim($path, '/'). '/'. $name;
        if(!file_exists($filePath)){
            ReturnJson(false, '下载文件不存在');
        }
        $res = AdminUploads::download($path,$name);
        if($res == false){
            ReturnJson(false, '下载失败');
        }
        return response()->download($res);
    }

    // 根目录查询文件夹
    public function DirList(Request $request)
    {
        $RootPath = AdminUploads::getRootPath();
        $DirList = $this->listFolderFiles($RootPath);
        $res = array_map(function ($v) use ($RootPath) {
            return str_replace($RootPath, '', $v);
        }, $DirList);
        ReturnJson(true, trans('lang.request_success'), $res);
    }

    // 递归查询文件夹
    public function listFolderFiles($dir){
        $dir = rtrim($dir, '/');
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $value){
            if (!in_array($value,array(".",".."))){
                if (is_dir($dir . '/' . $value)){
                    $result[] = ['value'=>$dir . '/' . $value,'label' => $dir . '/' . $value];
                    $result = array_merge($result, $this->listFolderFiles($dir . '/' . $value));
                }
            }
        }
        return $result;
    }

    // 计算文件夹大小
    public function DirSize(Request $request){
        $path = $request->path;
        $name = $request->name;
        if (empty($name)) {
            ReturnJson(false, '文件夹目录为空');
        }
        $RootPath = AdminUploads::getRootPath();
        $path = rtrim($RootPath, '/') . '/'.trim($path,'/').'/'. $name;
        if(!is_dir($path)){
            ReturnJson(false, '文件夹不存在');
        }
        $SizeList = self::getDirSize($path,[]);
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
        $res = AdminUploads::unzip($path, $name, $unzipPath);
        if($res === true){
            ReturnJson(true, '文件解压成功');
        } else {
            ReturnJson(false, $res);
        }
    }
}