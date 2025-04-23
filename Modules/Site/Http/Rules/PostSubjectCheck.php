<?php

namespace Modules\Site\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
use Modules\Site\Http\Models\PostSubject;

class PostSubjectCheck implements Rule {

    protected $type;
    protected $id;
    protected $userId;
    protected $attributeName;
    protected $customMessage;

    public function __construct($type, $id = null, $userId = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->userId = $userId;
    }

    public function passes($attribute, $value)
    {
        $this->attributeName = $attribute;
        
        if ($attribute === 'name') {
            return $this->validateNameUnique($value);
        } elseif ($attribute === 'product_id') {
            return $this->validateProductId($value);
        }
        
        return false;
    }

    public function message()
    {
        return $this->customMessage ?? '验证失败';
    }

    protected function validateNameUnique($name)
    {
        if (empty($name)) {
            return true;
        }

        $query = PostSubject::where('type', $this->type);
        
        if ($this->id) {
            $query->where('id', '!=', $this->id);
        }

        if ($this->type == PostSubject::TYPE_POST_SUBJECT) {
            $exists = $query->where('name', $name)->exists();
            if ($exists) {
                $this->customMessage = '已存在相同名称的[课题]';
                return false;
            }
        } elseif ($this->type == PostSubject::TYPE_POST_ARTICLE) {
            $exists = $query->where('name', $name)
                           ->where('accepter', $this->userId)
                           ->exists();
            if ($exists) {
                $this->customMessage = '已存在相同的[观点]';
                return false;
            }
        } else {
            $this->customMessage = '未选择[类型]';
            return false;
        }

        return true;
    }

    protected function validateProductId($productId)
    {
        if ($this->type != PostSubject::TYPE_POST_SUBJECT) {
            return true;
        }

        if (empty($productId)) {
            $this->customMessage = '[课题]类型必须填写[报告id]';
            return false;
        }

        $query = PostSubject::where('type', $this->type)
                          ->where('product_id', $productId);
        
        if ($this->id) {
            $query->where('id', '!=', $this->id);
        }

        if ($query->exists()) {
            $this->customMessage = '已存在相同[报告ID]的[课题]';
            return false;
        }

        return true;
    }
}
