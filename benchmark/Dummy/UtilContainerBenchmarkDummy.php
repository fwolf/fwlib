<?php
namespace Fwlib\Util\Test;


class UtilContainerBenchmarkDummy
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
