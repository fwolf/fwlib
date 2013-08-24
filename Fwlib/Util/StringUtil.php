<?php
namespace Fwlib\Util;


/**
 * String util
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2004-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       Before 2008-04-07
 */
class StringUtil
{
    /**
     * Eval string by replace tag with array value by index
     *
     * @param   string  $str
     * @param   array   $ar     Data array, should have assoc index.
     * @param   string  $delimiterLeft  Default '{'
     * @param   string  $delimiterRight Default '}'
     * @return  mixed
     */
    public static function evalWithTag(
        $str,
        $ar = array(),
        $delimiterLeft = '{',
        $delimiterRight = '}'
    ) {
        if (empty($str)) {
            return null;
        }
        $str = trim($str);

        // Replace tag with array value
        foreach ((array)$ar as $k => $v) {
            $str = str_replace($delimiterLeft . $k . $delimiterRight, $v, $str);
        }

        // Add tailing ';'
        if (';' != substr($str, -1)) {
            $str .= ';';
        }

        $rs = eval($str);

        if (is_null($rs)) {
            // Try if it need add return in eval str
            $rs = eval('return ' . $str);
        }

        return $rs;
    }
}
