<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function checkParamsExits($array, $arguments)
    {
        if ($arguments && is_array($arguments)) {
            foreach ($arguments as $key => $val) {
                if (!array_key_exists($val, $array)) {
                    return false;
                }
                foreach ($array as $m => $n) {
                    if ($val == $m) {
                        $tmp = isset($array[$m]) ? $array[$m] : '';
                        if (empty($tmp)) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }
    }

    public function Error($error, $extends = [])
    {
        $errors = [];
        $errors['code'] = -1;
        $errors['message'] = $error;
        array_merge($errors, $extends);

        return json_encode($errors);
    }

    public function Success($success, $extends = [])
    {
        $successes = [];
        $successes['code'] = 0;
        $successes['message'] = $success;
        $successes = array_merge($successes, $extends);

        return json_encode($successes);
    }
}
