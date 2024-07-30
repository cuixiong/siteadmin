<?php

namespace Modules\Site\Http\Models;

use Modules\Site\Http\Models\Base;

class ProductsCategory extends Base {
    protected $table = 'product_category';
    // 设置允许入库字段,数组形式
    protected $fillable
        = [
            'name',
            'link',
            'pid',
            'thumb',
            'home_thumb',
            'icon',
            'icon_hover',
            'sort',
            'status',
            'is_recommend',
            'is_hot',
            'discount',
            'discount_amount',
            'discount_type',
            'discount_time_begin',
            'discount_time_end',
            'seo_title',
            'seo_keyword',
            'seo_description',
            'show_home',
            'email',
            'keyword_suffix',   //关键词后缀
            'product_tag',  //产品标签
        ];
    protected $attributes
        = [
            'show_home' => 0,
            'status'    => 1,
            'sort'      => 100,
        ];

    /**
     * 处理查询列表条件数组
     *
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model, $search) {
        //id
        if (isset($search->id) && !empty($search->id)) {
            $model = $model->where('id', $search->id);
        }
        //pid
        if (isset($search->pid) && !empty($search->pid)) {
            $model = $model->where('pid', $search->pid);
        }
        //name
        if (isset($search->name) && !empty($search->name)) {
            $model = $model->where('name', 'like', '%'.$search->name.'%');
        }
        //link
        if (isset($search->link) && !empty($search->link)) {
            $model = $model->where('link', 'like', '%'.$search->link.'%');
        }
        //seo_title
        if (isset($search->seo_title) && !empty($search->seo_title)) {
            $model = $model->where('seo_title', 'like', '%'.$search->seo_title.'%');
        }
        //seo_keyword
        if (isset($search->seo_keyword) && !empty($search->seo_keyword)) {
            $model = $model->where('seo_keyword', 'like', '%'.$search->seo_keyword.'%');
        }
        //seo_description
        if (isset($search->seo_description) && !empty($search->seo_description)) {
            $model = $model->where('seo_description', 'like', '%'.$search->seo_description.'%');
        }
        //email
        if (isset($search->email) && !empty($search->email)) {
            $model = $model->where('email', 'like', '%'.$search->email.'%');
        }
        //keyword_suffix
        if (isset($search->keyword_suffix) && !empty($search->keyword_suffix)) {
            $model = $model->where('keyword_suffix', 'like', '%'.$search->keyword_suffix.'%');
        }
        //product_tag
        if (isset($search->product_tag) && !empty($search->product_tag)) {
            $model = $model->where('product_tag', 'like', '%'.$search->product_tag.'%');
        }
        //discount
        if (isset($search->discount) && $search->discount != '') {
            $model = $model->where('discount', $search->discount);
        }
        //discount_amount
        if (isset($search->discount_amount) && $search->discount_amount != '') {
            $model = $model->where('discount_amount', $search->discount_amount);
        }
        //discount_type
        if (isset($search->discount_type) && $search->discount_type != '') {
            $model = $model->where('discount_type', $search->discount_type);
        }
        // sort 排序
        if (isset($search->sort) && $search->sort != '') {
            $model = $model->where('sort', $search->sort);
        }
        //status 状态
        if (isset($search->status) && $search->status != '') {
            $model = $model->where('status', $search->status);
        }
        //show_home 状态
        if (isset($search->show_home) && $search->show_home != '') {
            $model = $model->where('show_home', $search->show_home);
        }

        if (isset($search->is_recommend) && $search->is_recommend != '') {
            $model = $model->where('is_recommend', $search->is_recommend);
        }
        
        if (isset($search->is_hot) && $search->is_hot != '') {
            $model = $model->where('is_hot', $search->is_hot);
        }
        //时间为数组形式
        //创建时间
        if (isset($search->created_at) && !empty($search->created_at)) {
            $createTime = $search->created_at;
            $model = $model->where('created_at', '>=', $createTime[0]);
            $model = $model->where('created_at', '<=', $createTime[1]);
        }
        //更新时间
        if (isset($search->updated_at) && !empty($search->updated_at)) {
            $updateTime = $search->updated_at;
            $model = $model->where('updated_at', '>=', $updateTime[0]);
            $model = $model->where('updated_at', '<=', $updateTime[1]);
        }
        //折扣开始时间
        if (isset($search->discount_time_begin) && !empty($search->discount_time_begin)) {
            $discountTimeBegin = $search->discount_time_begin;
            $model = $model->where('discount_time_begin', '>=', $discountTimeBegin[0]);
            $model = $model->where('discount_time_begin', '<=', $discountTimeBegin[1]);
        }
        //折扣结束时间
        if (isset($search->discount_time_end) && !empty($search->discount_time_end)) {
            $discountTimeEnd = $search->discount_time_end;
            $model = $model->where('discount_time_end', '>=', $discountTimeEnd[0]);
            $model = $model->where('discount_time_end', '<=', $discountTimeEnd[1]);
        }

        return $model;
    }

    /**
     * 列表数据
     *
     * @param array/string $field 字段，全部则不传
     * @param       $isTree  是否返回递归类型
     * @param       $treeKey 递归类型的key
     * @param array $search  查询条件
     *
     * @return array $res
     */
    public function GetList($field = '*', $isTree = false, $treeKey = 'parent_id', $search = []) {
        $model = self::query();
        if (!empty($search) && is_object($search)) {
            $model = $this->HandleWhere($model, $search);
        }elseif(!empty($search ) && is_array($search)){
            $model = $model->where($search);
        }
        $list = $model->select($field)->orderBy('sort', 'ASC')->orderBy('id', 'DESC')->get()->toArray();
        if (!empty($list)) {
            if ($isTree) {
                $minPid = array_column($list, $treeKey);
                $minPid = min($minPid);
                $list = array_column($list, null, 'id');
                $list = $this->tree($list, $treeKey, $minPid);
            }
        }

        return $list;
    }

    public function getAdminList($field = '*', $isTree = false, $treeKey = 'parent_id', $search = []) {
        $model = self::query();
        if (!empty($search)) {
            $model = $this->HandleWhere($model, $search);
        }else{
            $model = $model->where("pid", 0);
        }
        $model = $model->select($field)
                       ->orderBy('sort', 'ASC')
                       ->orderBy('id', 'DESC');
        // 总数量
        $total = $model->count();
        $request = request();
        // 查询偏移量
        if (!empty($request->pageNum) && !empty($request->pageSize)) {
            $model = $model->offset(($request->pageNum - 1) * $request->pageSize);
        }
        // 查询条数
        if (!empty($request->pageSize)) {
            $model = $model->limit($request->pageSize);
        }
        $list = $model->get()->toArray();
        if (!empty($list)) {
            if ($isTree) {
                foreach ($list as $key => $value) {
                    $childCategoryList = [];
                    $this->getChildList($value, $childCategoryList, $field);
                    $list[$key]['children'] = $childCategoryList;
                }
            }
        }

        return [$total, $list];
    }

    public function getChildList($categoryInfo, &$childCategoryList, $field) {
        if (empty($categoryInfo)) {
            return false;
        }
        $childList = ProductsCategory::query()->select($field)
                                     ->where("pid", $categoryInfo['id'])
                                     //->where("status", 1)
                                     ->get()->toArray();
        foreach ($childList as $value) {
            $forChildList = [];
            $this->getChildList($value, $forChildList, $field);
            $value['children'] = $forChildList;
            $childCategoryList[] = $value;
        }

        return true;
    }

    /**
     * 列表数据
     *
     * @param array/string $field 字段，全部则不传
     * @param          $isTree    是否返回递归类型
     * @param          $treeKey   递归类型的key
     * @param array    $search    查询条件
     * @param int|null $withoutId 排除的分类id
     *
     * @return array $res
     */
    public function GetListWithoutSelf(
        $field = '*', $isTree = false, $treeKey = 'parent_id', $search = [], $withoutId = null
    ) {
        $model = self::query();
        if (!empty($search)) {
            $model = $this->HandleWhere($model, $search);
        }
        $list = $model->select($field)->orderBy('sort', 'ASC')->orderBy('id', 'DESC')->get()->toArray();
        if (!empty($list)) {
            $list = array_map(function ($item) {
                return $item;
            }, $list);
            if ($withoutId) {
                $list = array_filter($list, function ($item) use ($withoutId) {
                    if ($item['id'] != $withoutId) {
                        return true;
                    }
                });
            }
            if ($isTree) {
                $minPid = array_column($list, $treeKey);
                $minPid = min($minPid);
                $list = array_column($list, null, 'id');
                $list = $this->tree($list, $treeKey, $minPid);
            }
        }

        return $list;
    }

    /**
     * 递归获取树状列表数据
     *
     * @param $list
     * @param $key      需要递归的键值，这个键值的值必须为整数型
     * @param $parentId 父级默认值
     *
     * @return array $res
     */
    public function tree($list, $key, $parentId = 0) {
        $tree = [];
        foreach ($list as $index => $item) {
            if ($item[$key] == $parentId) {
                $list[$index]['pid_array'] = [];
                $list[$index]['pid_array'] = $list[$item[$key]]['pid_array'] ?? [];
                array_push($list[$index]['pid_array'], $item['id']);
                $item['pid_array'] = $list[$index]['pid_array'];
                $children = $this->tree($list, $key, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }

    // /**
    //  * 递归获取树状列表数据
    //  * @param $list
    //  * @param $key 需要递归的键值，这个键值的值必须为整数型
    //  * @param $parentId 父级默认值
    //  * @return array $res
    //  */
    // public function tree($list, $key, $data = [])
    // {
    //     $list = array_column($list, null, 'id');
    //     $childrenPids = array_keys($list);
    //     $childrenList = self::whereIn('pid', $childrenPids)->get()->toArray();
    //     if (count($childrenList) > 0) {
    //         foreach ($childrenList as $key => $item) {
    //             $childrenList[$key]['pid_array'] = $list[$item['pid']]['pid_array'];
    //             $childrenList[$key]['pid_array'][] = $item['id'];
    //         }
    //     }
    //     $data[] = array_values($list);
    //     if (count($childrenList) == 0) {
    //         return self::handleTree($data);
    //     }
    //     return self::tree($childrenList, $key, $data);
    // }
    // public function handleTree($data)
    // {
    //     $temp = [];
    //     // return $newData;
    //     for ($i = count($data) - 1; $i > 0; $i--) {
    //         $temp = $data[$i - 1];
    //         $children = collect($data[$i])->groupBy('pid');
    //         // $newData[] = $i;
    //         if (count($children) > 0) {
    //             foreach ($temp as $key => $item) {
    //                 $temp[$key]['children'] = [];
    //                 if (isset($children[$item['id']])) {
    //                     $temp[$key]['children'] = $children[$item['id']];
    //                 }
    //             }
    //         }
    //         $data[$i - 1] = $temp;
    //     }
    //     return $data[0];
    // }
}
