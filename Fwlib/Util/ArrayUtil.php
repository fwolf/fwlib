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


    /**
     * Filter an array by wildcard rules
     *
     * Wildcard rules is a string include many part joined by ',',
     * each part can include * and ?, head by '+'(default) or '-',
     * they means find elements suit the rules in source array,
     * and add_to/remove_from result array.
     *
     * Parts operate sequence is by occur position in rules string.
     *
     * Rules example: a*, -*b, -??c, +?d*
     *
     * @param   array   $arSrce     Source data.
     * @param   string  $rules      Wildcard rule string.
     * @param   string  $delimiter  Default ','
     * @return  array
     */
    public static function filterByWildcard($arSrce, $rules, $delimiter = ',')
    {
        $arResult = array();

        // Check empty input
        if (empty($arSrce)) {
            return $arResult;
        }
        if (empty($rules)) {
            return $arSrce;
        }

        // Read rules
        $arRule = explode($delimiter, $rules);

        // Use rules
        foreach ($arRule as $rule) {
            $rule = trim($rule);

            // Empty rule means 'all'
            if (empty($rule)) {
                $rule = '*';
            }

            // + or - ?
            if ('+' == $rule[0]) {
                $op = '+';
                $rule = substr($rule, 1);
            } elseif ('-' == $rule[0]) {
                $op = '-';
                $rule = substr($rule, 1);
            } else {
                $op = '+';
            }

            // Loop srce ar
            foreach ($arSrce as $k => $srce) {
                if (true == StringUtil::matchWildcard($srce, $rule)) {
                    // Got element to +/-
                    $i = array_search($srce, $arResult);
                    if ('+' == $op) {
                        // Add to ar if not in it.
                        if (false === $i) {
                            $arResult = array_merge($arResult, array($k => $srce));
                        }
                    } else {
                        // Remove from ar if exists.
                        if (false !== $i) {
                            unset($arResult[$i]);
                        }
                    }
                }
            }
        }

        return $arResult;
    }


    /**
     * Add value to array by key
     *
     * If key is unset, set with the value.
     *
     * @param   array   &$arSrce
     * @param   mixed   $key
     * @param   mixed   $val        Value to increase of set
     */
    public static function increaseByKey(&$arSrce, $key, $val = 1)
    {
        if (isset($arSrce[$key])) {
            // Force type of result value by param $val
            if (is_string($val)) {
                $arSrce[$key] .= $val;
            } else {
                $arSrce[$key] += $val;
            }
        } else {
            $arSrce[$key] = $val;
        }

        return $arSrce;
    }


    /**
     * Insert data to assigned position in srce array by key
     *
     * If key in $ins already exists in $srce, it will only assign new value
     * on old key. So if you want to use this move item in array forward or
     * backward, need unset them from array first.
     *
     * @param   array   &$srce
     * @param   mixed   $idx        Position idx, append at end if not found.
     * @param   array   $ins        Array to insert, can have multi item.
     *      This muse be array, bcs array($non-array-val) always have index 0.
     * @param   integer $mode      -1=insert before index, 0=replace index
     *      1=insert after index, default=1.
     *      If $mode!=0, eg: 2, means insert between origin idx 1 and 2
     *      a    b     c    d   e       Original index
     *        -2   -1  0  1   2         Insert position by $mode
     * @return  array
     */
    public static function insert(&$srce, $idx, $ins, $mode = 1)
    {
        if (empty($ins)) {
            return $srce;
        }


        // Find ins position
        $keySrce = array_keys($srce);
        $insPos = array_search($idx, $keySrce, true);
        if (false === $insPos) {
            // Idx not found, append.
            $srce = array_merge($srce, $ins);
            return $srce;
        }


        // Compute actual ins position by $mode
        $insPos += $mode + (0 >= $mode ? 1 : 0);
        $cntSrce = count($srce);
        if (0 > $insPos) {
            $insPos = 0;
        } elseif ($cntSrce < $insPos) {
            $insPos = $cntSrce;
        }

        // Loop to gen result ar
        $rs = array();
        // Need loop to $cntSrce, not $cntSrce-1,
        // for append after all exists keys.
        $iSrce = 0;
        while ($iSrce <= $cntSrce) {
            if ($insPos == $iSrce) {
                // Got insert position
                foreach ($ins as $k => $v) {
                    // Notice: if key exists, will be overwrite.
                    $rs[$k] = $v;
                }
            }

            if ($iSrce != $cntSrce) {
                // Insert original data
                $k = $keySrce[$iSrce];
                $rs[$k] = $srce[$k];
            }

            $iSrce ++;
        }
        // Replace mode will remove original key
        if (0 == $mode) {
            unset($rs[$keySrce[$insPos - 1]]);
        }

        // Final result
        $srce = $rs;
        return $srce;
    }


    /**
     * Sort multi-dimension array according to it's 2nd level value
     *
     * @param   array   &$arSrce    Array to be sort
     * @param   mixed   $key        Sort by this key's value in 2nd-dimension
     * @param   mixed   $order      True = ASC/false = DESC, or use str.
     * @param   mixed   $joker      Use when val of key isn't set.
     * @return  array
     */
    public static function sortByLevel2(
        &$arSrce,
        $key,
        $order = true,
        $joker = ''
    ) {
        $arVal = array();
        foreach ($arSrce as $k => $v) {
            $arVal[$k] = self::getIdx($v, $key, $joker);
        }

        if (true === $order || 'ASC' == strtoupper($order)) {
            asort($arVal);
        } else {
            arsort($arVal);
        }

        // Got currect order, write back.
        $rs = array();
        foreach ($arVal as $k => $v) {
            $rs[$k] = &$arSrce[$k];
        }

        // Re-order numeric array key
        $arSrce = array_merge($rs, array());

        return $arSrce;
    }
}
