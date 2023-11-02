<?php
namespace Modules\Admin\Http\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\Request;
use Stancl\Tenancy\Database\Concerns\ResourceSyncing;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Illuminate\Support\Facades\Auth;
class Base extends Model
{
    // use ResourceSyncing, CentralConnection;
    // 时间戳
    protected $dateFormat = 'U';
    // 下面是设置数据表名，如果不设置，则使用类名的复数形式作为表名，如Users
	// protected $table = "student";
	// `$primaryKey`属性（可选），值是主键名称，在主键不是id的时候则需要指定主键
	// protected $primaryKey = "uid";
    // 下面即是允许入库的字段，数组形式，name age sex三个字段允许入库
    // protected $fillable = [];
    // 定义`$timestamps`属性，不设置为`false`，默认操作表中的`create_at`和`updated_at`字段，我们表中一般没有这个字段，所以设置为`false`，表示不要操作这两个字段
    // public $timestamps = false;
    // 下面用于设置不允许入库字段，一般和$fillable存在一个即可
    // protected $guarded = [];
    // 列表输出字段
    public $ListSelect = ['*'];

    /**
     * 模型的“引导”方法。
     * 使用闭包的方式进行使用模型事件
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $user = Auth::user();
            if(isset($user->id)){
                $model->created_by = $user->id;
            } else {
                $model->created_by = 0;
            }
        });

        static::updating(function ($model) {
            $user = Auth::user();
            if(isset($user->id)){
                $model->updated_by = $user->id;
            }
        });
    }
    /**
     * 创建时间获取器
     * @param \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function createdAt(): Attribute
    {

        return Attribute::make(
            get: fn ($value) => date('Y-m-d H:i:s',strtotime($value)),
        );
    }
    /**
     * 更新时间获取器
     * @param \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('Y-m-d H:i:s',strtotime($value)),
        );
    }

    /**
     * 创建者获取器
     */
    public function getCreatedByAttribute()
    {
        $res = User::where('id',$this->attributes['created_by'])->value('name');
        return $res;
    }
    /**
     * 更新者获取器
     */
    public function getUpdatedByAttribute()
    {
        $res = User::where('id',$this->attributes['updated_by'])->value('name');
        return $res;
    }

    /**
     * 递归获取树状列表数据
     * @param $list
     * @param $key 需要递归的键值，这个键值的值必须为整数型
     * @param $parentId 父级默认值
     * @return array $res
     */
    public function tree($list,$key,$parentId = 0) {

        $tree = [];
        foreach ($list as $item) {
            if ($item[$key] == $parentId) {
                $children = $this->tree($list,$key,$item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }

        }
        return $tree;
    }

    /**
     * 列表数据
     * @param array/string $filed 字段，全部则不传
     * @param $isTree 是否返回递归类型
     * @param $treeKey 递归类型的key
     * @param array $where 查询条件
     * @return array $res
     */
    public function GetList($filed = '*',$isTree = false,$treeKey = 'parent_id',$where = [])
    {
        $model = self::query();

        foreach ($where as $key => $value) {
            if(is_array($value)){
                if($value[0] == 'like' && count($value) == 2){
                    $model = $model->where($key,$value[0],$value[1]);
                } else {
                    $model = $model->whereIn($key,$value);
                }
            } else {
                $model = $model->where($key,$value);
            }
        }
        $list = $model->select($filed)->get()->toArray();
        if(!empty($list)){

            if($isTree){
                $minPid = array_column($list,$treeKey);
                $minPid = min($minPid);
                $list = $this->tree($list,$treeKey,$minPid);
            }
        }
        return $list;
    }

    /**
     * 处理查询列表条件数组
     * @param use Illuminate\Http\Request;
     */
    public function HandleWhere($model,$request){
        if(!empty($request->keywords)){
            $model = $model->where('name','like','%'.$request->keywords.'%');
        }
        if(!empty($request->search)){
            $model = $this->HandleSearch($model,$request->search);
        }
        return $model;
    }

    /**
     * 处理查询列表条件数组
     * @param $model moxel
     * @param $search 搜索条件
     */
    public function HandleSearch($model,$search){
            $search = json_decode($search,true);
            $search = array_filter($search,function($v){
                if(!(empty($v) && $v != "0")){
                    return true;
                }
            });
            if(!empty($search)){
                if(isset($search['created_by'])){
                    unset($search['created_by']);
                }
                if(isset($search['updated_by'])){
                    unset($search['updated_by']);
                }
                $timeArray = ['created_at','updated_at'];
                foreach ($search as $key => $value) {
                    if(in_array($key,['name','english_name','title'])){
                        $model = $model->where($key,'like','%'.trim($value).'%');
                    } else if (in_array($key,$timeArray)){
                        if(is_array($value)){
                            $model = $model->whereBetween($key,$value);
                        }
                    } else if(is_array($value) && !in_array($key,$timeArray)){
                        $model = $model->whereIn($key,$value);
                    } else {
                        $model = $model->where($key,$value);
                    }
                }
            }
        return $model;
    }

        /**
     * 列表数据
     * @param array/string $filed 字段，全部则不传
     * @param $isTree 是否返回递归类型
     * @param $treeKey 递归类型的key
     * @param array $where 查询条件
     * @return array $res
     */
    public function GetListLabel($filed = '*',$isTree = false,$treeKey = 'parent_id',$where = [])
    {
        $model = self::query();
        foreach ($where as $key => $value) {
            if(is_array($value)){
                if($value[0] == 'like' && count($value) == 2){
                    $model = $model->where($key,$value[0],$value[1]);
                } else {
                    $model = $model->whereIn($key,$value);
                }
            } else {
                $model = $model->where($key,$value);
            }
        }
        $list = $model->select($filed)->get()->toArray();

        if($isTree){
            $list = $this->treeLabel($list,$treeKey);
        } else {
            $res = [];
            foreach ($list as $map) {
                $res[] = ['value' => $map['value'],'label' => $map['label']];
            }
            $list = $res;
        }
        return $list;
    }

    /**
     * 递归获取树状列表数据
     * @param $list
     * @param $key 需要递归的键值，这个键值的值必须为整数型
     * @param $parentId 父级默认值
     * @return array $res
     */
    public function treeLabel($list,$key,$parentId = 0) {
        $tree = [];
        foreach ($list as $item) {
            $res = [];
            if ($item[$key] == $parentId) {
                $children = $this->treeLabel($list,$key,$item['id']);
                if (!empty($children)) {
                    $res['children'] = $children;
                }
                $res['value'] = $item['value'];
                $res['label'] = $item['label'];
                $tree[] = $res;
            }
        }
        return $tree;
    }
}