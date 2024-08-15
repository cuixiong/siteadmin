<?php

namespace Modules\Site\Http\Models;
class OssFile extends Base {
    protected $table = 'oss_file';
    // 设置允许入库字段,数组形式
    protected $fillable
        = [
            'path',
            'oss_path',
            'file_fullpath',
            'file_name',
            'file_size',
            'file_suffix',
        ];
}
