<?php

namespace Modules\Site\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValueInRange implements Rule {
    private $min;
    private $max;

    public function __construct($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
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
        return $value >= $this->min && $value <= $this->max;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message() {
        return ' :attribute 的值必须在 ' . $this->min . ' and ' . $this->max."这个范围内";
    }
}
