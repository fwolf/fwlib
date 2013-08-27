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
     * Get substr by display width, and ignore html tag's length
     *
     * Using mb_strimwidth()
     *
     * Attention: No consider of html complement.
     *
     * @param   string  $str    Source string
     * @param   int     $len    Length
     * @param   string  $marker If str length exceed, cut & fill with this
     * @param   int     $start  Start position
     * @param   string  $encoding   Default is utf-8
     * @return  string
     * @link http://www.fwolf.com/blog/post/133
     */
    public static function substrIgnHtml(
        $str,
        $len,
        $marker = '...',
        $start = 0,
        $encoding = 'utf-8'
    ) {
        $i = preg_match_all('/<[^>]*>/i', $str, $ar);
        if (0 == $i) {
            // No html in $str
            $str = htmlspecialchars_decode($str);
            $str = mb_strimwidth($str, $start, $len, $marker, $encoding);
            $str = htmlspecialchars($str);
            return $str;
        } else {
            // Have html tags, need split str into parts by html
            $ar = $ar[0];
            $arParts = array();
            for ($i = 0; $i < count($ar); $i ++) {
                // Find sub str
                $j = strpos($str, $ar[$i]);
                // Add to new ar: before, tag
                if (0 != $j) {
                    $arParts[] = substr($str, 0, $j);
                }
                $arParts[] = $ar[$i];
                // Trim origin str, so we start from 0 again next loop
                $str = substr($str, $j + strlen($ar[$i]));
            }
            // Tail of $str, which after html tags
            $arParts[] = $str;

            // Loop to cut needed length
            $result = '';
            $length = $len - mb_strwidth($marker, $encoding);
            $tagDepth = 0;     // In html tag ?
            $i = 0;
            while ($i < count($arParts)) {
                $s = $arParts[$i];
                $i ++;

                // Is it self-end html tag ?
                if (0 < preg_match('/\/\s*>/', $s)) {
                    $result .= $s;
                } elseif (0 < preg_match('/<\s*\//', $s)) {
                    // End of html tag ?
                    // When len exceed, only end tag allowed
                    if (0 < $tagDepth) {
                        $result .= $s;
                        $tagDepth --;
                    }
                } elseif (0 < strpos($s, '>')) {
                    // Begin of html tag ?
                    // When len exceed, no start tag allowed
                    if (0 < $length) {
                        $result .= $s;
                        $tagDepth ++;
                    }
                } else {
                    // Real string
                    $s = htmlspecialchars_decode($s);
                    if (0 == $length) {
                        // Already got length
                        continue;
                    } elseif (mb_strwidth($s, $encoding) < $length) {
                        // Can add to rs completely
                        $length -= mb_strwidth($s, $encoding);
                        $result .= htmlspecialchars($s);
                    } else {
                        // Need cut then add to rs
                        $result .= htmlspecialchars(
                            mb_strimwidth($s, 0, $length, '', $encoding)
                        ) . $marker;
                        $length = 0;
                    }
                }
            }

            return $result;
        }
        return '';
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


    /**
     * Convert to camelCase
     *
     * @param   string  $srce
     * @return  string
     */
    public static function toCamelCase($srce)
    {
        return lcfirst(self::toStudlyCaps($srce));
    }


    /**
     * Convert to snake case
     *
     * @param   string  $srce
     * @param   string  $separator
     * @param   boolean $ucwords
     * @return  string
     */
    public static function toSnakeCase(
        $srce,
        $separator = '_',
        $ucwords = false
    ) {
        // Split to words
        $s = preg_replace('/([A-Z])/', ' \1', $srce);

        // Remove leading space
        $s = trim($s);

        // Merge non-words char and replace by space
        $s = preg_replace('/[ _\-\.]+/', ' ', $s);

        if ($ucwords) {
            $s = ucwords($s);
        } else {
            $s = strtolower($s);
        }

        // Replace space with separator
        $s = str_replace(' ', $separator, $s);

        return $s;
    }


    /**
     * Convert to StudlyCaps
     *
     * @param   string  $srce
     * @return  string
     */
    public static function toStudlyCaps($srce)
    {
        return self::toSnakeCase($srce, '', true);
    }
}
