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
     * Json encode with all JSON_HEX_(TAG|AMP|APOS|QUOT) options
     *
     * Option JSON_UNESCAPED_UNICODE is NOT included.
     *
     * @codeCoverageIgnore
     * @param   mixed   $val
     * @param   integer $option     Use if only some of HEX option needed
     * @return  string
     */
    public static function jsonEncodeHex($val, $option = null)
    {
        if (is_null($option)) {
            $option = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;
        }

        // Check json extension
        if (!extension_loaded('json')) {
            error_log('StringUtil::jsonEncodeHex(): json extension is not loaded.');
            return null;
        }

        if (is_int($val) || is_numeric($val)) {
            return $val;
        }

        $jsonStr = '';
        if (0 <= version_compare(PHP_VERSION, '5.3.0')) {
            $jsonStr = json_encode($val, $option);
        } else {
            // Json treat list/array in different way([] vs {}).
            if (is_array($val) || is_object($val)) {
                $isList = is_array($val) && (empty($val)
                    || array_keys($val) === range(0, count($val) - 1));

                if ($isList) {
                    $art = array();
                    foreach ($val as $v) {
                        $art[] = self::jsonEncodeHex($v, $option);
                    }
                    $jsonStr = '[' . implode(',', $art) . ']';
                } else {
                    $art = array();
                    foreach ($val as $k => $v) {
                        $art[] = self::jsonEncodeHex($k, $option)
                            . ':'
                            . self::jsonEncodeHex($v, $option);
                    }
                    $jsonStr = '{' . implode(',', $art) . '}';
                }
            } elseif (is_string($val)) {
                // Manual replace chars
                $jsonStr = json_encode($val);
                $jsonStr = substr($jsonStr, 1);
                $jsonStr = substr($jsonStr, 0, strlen($jsonStr) - 1);

                $search = array();
                $replace = array();
                if ($option & JSON_HEX_TAG) {
                    $search = array_merge($search, array('<', '>'));
                    $replace = array_merge($replace, array('\u003C', '\u003E'));
                }
                if ($option & JSON_HEX_APOS) {
                    $search = array_merge($search, array('\''));
                    $replace = array_merge($replace, array('\u0027'));
                }
                if ($option & JSON_HEX_QUOT) {
                    $search = array_merge($search, array('\"'));
                    $replace = array_merge($replace, array('\u0022'));
                }
                if ($option & JSON_HEX_AMP) {
                    $search = array_merge($search, array('&'));
                    $replace = array_merge($replace, array('\u0026'));
                }

                $jsonStr = str_replace($search, $replace, $jsonStr);
                $jsonStr = '"' . $jsonStr . '"';
            } else {
                // Int, floats, bools, null
                $jsonStr = '"' . json_encode($val) . '"';
            }
        }
        return $jsonStr;
    }
}
