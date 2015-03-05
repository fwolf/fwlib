<?php
namespace Fwlib\Dummy;

class ArrayUtil
{
    public static function getIdx($ar, $key, $default = null)
    {
        if (isset($ar[$key])) {
            return $ar[$key];
        } else {
            return $default;
        }
    }
}
