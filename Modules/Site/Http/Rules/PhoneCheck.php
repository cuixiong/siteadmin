<?php

namespace Modules\Site\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneCheck implements Rule {

    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value) {
        // 使用正则表达式验证手机号码格式
        return preg_match('/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\d{8}$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return ' :input 不是正确的手机号码';
    }
}
