<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FileManagement extends Controller{

    public function FileList(Request $request)
    {
        $rootDirectory = public_path(); // 项目根目录
        $directoryToSearch = $request->path; // 要搜索的目录名字

        $directoryPath = $rootDirectory . '/' . $directoryToSearch;
        // var_dump($directoryPath);die;
        $files = $this->getFolderFiles($directoryPath);
        var_dump($files);die;
    }

    function getFolderFiles($directory, $filename = null)
    {
        $result = array();

        if (is_dir($directory) && $directory != '/') {
            $files = scandir($directory);

            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }

                $path = $directory . '/' . $file;

                if (is_dir($path)) {
                    $subFiles = $this->getFolderFiles($path, $file);

                    if (!empty($subFiles)) {
                        $result = array_merge($result, $subFiles);
                    }
                } else {
                    $result[] = array('name' => $file, 'size' => filesize($path), 'modified' => filemtime($path));
                }
            }
        }

        return $result;
    }

}