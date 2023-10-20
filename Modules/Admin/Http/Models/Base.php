<?php
namespace Modules\Admin\Http\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Stancl\Tenancy\Database\Concerns\ResourceSyncing;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
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
}