<?php

namespace Modules\Site\Http\Requests;

use Modules\Admin\Http\Requests\BaseRequest;
use Modules\Site\Http\Rules\PostSubjectCheck;

class PostSubjectRequest extends BaseRequest
{
    /**
     * 新增数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function store($request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                new PostSubjectCheck(
                    $request->type, 
                    $request->id,
                    $request->user->id // 传入当前用户ID
                )
            ],
            'product_id' => [
                new PostSubjectCheck($request->type, $request->id,$request->user->id)
            ],
        ]);
        return $validated;

    }
    /**
     * 更新数据验证
     * @param  \Illuminate\Http\Request  $request
     */
    public function update($request)
    {

        $validated = $request->validate([
            'id' => 'required',
            'name' => [
                'required',
                new PostSubjectCheck(
                    $request->type, 
                    $request->id,
                    $request->user->id // 传入当前用户ID
                )
            ],
            'product_id' => [
                new PostSubjectCheck($request->type, $request->id,$request->user->id)
            ],
        ]);
        return $validated;
    }
}
