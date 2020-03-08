<?php

namespace App\Rules;

use App\Libraries\MultiDB;
use App\User;
use App\Traits\SettingsSaver;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class ValidSettingsRule
 * @package App\Http\ValidationRules
 */
class ValidSettingsRule implements Rule
{
    use SettingsSaver;
    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */

    public $return_data;

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $data = $this->validateSettings($value);
        if (is_array($data)) {
            $this->return_data = $data;
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return string
     */
    public function message()
    {
        return $this->return_data[0] . " is not a valid " . $this->return_data[1] . " ( " . $this->return_data[2] . " )";
    }
}
