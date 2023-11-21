<?php

namespace Modules\Admin\Http\Models;

use Modules\Admin\Http\Models\Base;

class ListStyle extends Base
{
    // 设置允许入库字段,数组形式
    protected $fillable = ['user_id', 'model', 'header_title', 'status', 'updated_by', 'created_by'];

    /**
     * 写入表头数据
     * @param string $modelName; 模型名称
     * @param int $user_id; 用户id
     */
    public function setHeaderTitle($modelName, $user_id, $title_json)
    {
        $listStyleModel = self::where(['user_id' => $user_id, 'model' => $modelName])->first();
        $id = '';
        if ($listStyleModel) {

            self::update(
                [
                    'header_title' => $title_json,
                    'status' => 1,
                    'updated_at' => time(),
                    'updated_by' => $user_id,
                ],
                [
                    'id' => $listStyleModel->id
                ]
            );
            $id = $listStyleModel->id;
            $data = self::where(['id' => $id])->first()->toArray();
        } else {
            $data = self::create([
                'user_id' => $user_id,
                'model' => $modelName,
                'header_title' => $title_json,
                'created_at' => time(),
                'created_by' => $user_id,
                'updated_at' => time(),
            ]);
        }

        return $data;
    }

    /**
     * 获取表头数据
     * @param string $modelName; 模型名称
     * @param int $user_id; 用户id
     */
    public function getHeaderTitle($modelName, $user_id)
    {
        $headerTitle = self::where(['user_id' => $user_id, 'model' => $modelName, 'status' => 1])->pluck('header_title');
        return $headerTitle;
    }
}
