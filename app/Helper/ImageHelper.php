<?php

namespace App\Helper;

use Intervention\Image\Facades\Image;

class ImageHelper
{

    /**
     * 保存图片
     * @param $binaryData 二进制图片数据
     * @param $outputDir 保存的路径
     * @param $outputName 保存的文件名
     * @param $width 图片宽度
     * @param $height 图片高度
     * @return $outputPath 返回文件路径
     */
    public static function SaveImage($binaryData, $outputName, $outputDir = '/uploads/', $width = 0, $height = 0)
    {
        //简单上传一下图片

        // 创建 Intervention Image 对象
        $image = Image::make($binaryData);

        // 调整大小
        if ($width > 0 && $height > 0) {
            $image->resize($width, $height);
        }
        
        $basePath = resource_path();
        //检验目录是否存在
        if (!is_dir($basePath . $outputDir)) {
            @mkdir($basePath.$outputDir, 0777, true);
        }
        //输出的目录
        $outputPath = $outputDir . $outputName;
        // 保存图像
        $image->save($basePath.$outputPath);

        // 返回处理后的图像路径
        return $outputPath;
    }
}
