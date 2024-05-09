<?php

namespace Modules\Site\Http\Rules;

use Illuminate\Contracts\Validation\Rule;
use Modules\Site\Services\SenWordsService;

class SensitiveWord implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return !SenWordsService::checkFitter($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        //return trans('validation.error');
        return '该报告含有敏感词';
    }
}
