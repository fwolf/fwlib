<?php
namespace Fwlib\Util;


/**
 * Array util
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2009-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2010-01-25
 */
class ArrayUtil
{
    /**
     * Return default if array key is not set or empty
     *
     * @link    http://stackoverflow.com/questions/14086980/php-return-if-isset
     * @param   array   $ar
     * @param   string  $key
     * @param   mixed   $default
     * @return  mixed
     */
    public static function getEdx($ar, $key, $default = null)
    {
        if (isset($ar[$key]) && !empty($ar[$key])) {
            return $ar[$key];
        } else {
            return $default;
        }
    }


    /**
     * Return default if array key is not set
     *
     * @param   array   $ar
     * @param   string  $key
     * @param   mixed   $default
     * @return  mixed
     */
    public static function getIdx($ar, $key, $default = null)
    {
        if (isset($ar[$key])) {
            return $ar[$key];
        } else {
            return $default;
        }
    }
}
