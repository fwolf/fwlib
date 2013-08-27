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
     * Addslashes for any string|array, recursive
     *
     * @param   mixed   $srce
     * @return  mixed
     */
    public static function addSlashesRecursive($srce)
    {
        if (empty($srce)) {
            return $srce;
        }

        if (is_string($srce)) {
            return addslashes($srce);
        } elseif (is_array($srce)) {
            $rs = array();
            foreach ($srce as $k => $v) {
                $rs[addslashes($k)] = self::addSlashesRecursive($v);
            }
            return $rs;
        } else {
            // Other data type, return original
            return $srce;
        }
    }


    /**
     * Encode string for html output
     *
     * @param   string  $str
     * @return  string
    */
    public static function encodeHtml($str)
    {
        $ar = array(
            '&'     => '&amp;',
            '<'     => '&lt;',
            '>'     => '&gt;',
            chr(9)  => '　　',
            chr(34) => '&quot;',
            '  '    => '&nbsp; ',
            ' '     => '&nbsp;',
            '&nbsp;&nbsp;'  => '&nbsp; ',
            chr(13) => '<br />',
        );
        $search = array_keys($ar);
        $replace = array_values($ar);

        return str_replace($search, $replace, $str);
    }


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


    /**
     * Match a string with rule including wildcard
     *
     * Eg: 'abcd' match rule '*c?'
     *
     * @param   string  $str
     * @param   string  $rule
     * @return  boolean
     */
    public static function matchWildcard($str, $rule)
    {
        // Convert wildcard rule to regex
        $rule = str_replace('*', '.+', $rule);
        $rule = str_replace('?', '.{1}', $rule);
        $rule = '/' . $rule . '/';

        // Must match whole string, same length
        if ((1 == preg_match($rule, $str, $ar_match))
            && (strlen($ar_match[0]) == strlen($str))
        ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Generate random string
     *
     * In $mode:
     *  a means include a-z
     *  A means include A-Z
     *  0 means include 0-9
     *
     * @param   int     $len
     * @param   string  $mode
     * @return  string
     */
    public static function random($len, $mode = 'a0')
    {
        $str = '';
        if (preg_match('/[a]/', $mode)) {
            $str .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if (preg_match('/[A]/', $mode)) {
            $str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (preg_match('/[0]/', $mode)) {
            $str .= '0123456789';
        }

        $result = '';
        $strLen = strlen($str);

        // Algorithm
        // 1. rand by str length, faster than 2
        // 2. rand then mode by str length
        for ($i = 0; $i < $len; $i ++) {
            $result .= $str[mt_rand(0, $strLen - 1)];
        }
        return $result;
    }


    /**
     * Convert string to array by splitter
     *
     * @param   string  $srce
     * @param   string  $splitter
     * @param   boolean $trim
     * @param   boolean $removeEmpty
     * @return  array
     */
    public static function toArray(
        $srce,
        $splitter = ',',
        $trim = true,
        $removeEmpty = true
    ) {
        if (!is_string($srce)) {
            $srce = strval($srce);
        }

        $rs = explode($splitter, $srce);

        if ($trim) {
            foreach ($rs as &$v) {
                $v = trim($v);
            }
            unset($v);
        }

        if ($removeEmpty) {
            foreach ($rs as $k => $v) {
                if (empty($v)) {
                    unset($rs[$k]);
                }
            }
            // Re generate array index
            $rs = array_merge($rs, array());
        }

        return $rs;
    }
}
