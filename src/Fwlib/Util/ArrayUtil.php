<?php
namespace Fwlib\Util;

/**
 * Array util
 *
 * @copyright   Copyright 2009-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ArrayUtil extends AbstractUtilAware
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
    public function getEdx($ar, $key, $default = null)
    {
        if (array_key_exists($key, $ar) && !empty($ar[$key])) {
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
    public function getIdx($ar, $key, $default = null)
    {
        if (array_key_exists($key, $ar)) {
            return $ar[$key];
        } else {
            return $default;
        }
    }


    /**
     * Add value to array by key
     *
     * If key is unset, set with the value.
     *
     * @param   array   &$source
     * @param   mixed   $key
     * @param   mixed   $val        Value to increase of set
     * @return  array
     */
    public function increaseByKey(&$source, $key, $val = 1)
    {
        if (isset($source[$key])) {
            // Force type of result value by param $val
            if (is_string($val)) {
                $source[$key] .= $val;
            } else {
                $source[$key] += $val;
            }
        } else {
            $source[$key] = $val;
        }

        return $source;
    }


    /**
     * Insert data to assigned position in source array by key
     *
     * If key in $ins already exists in $source, it will only assign new value
     * on old key. So if you want to use this move item in array forward or
     * backward, need unset them from array first.
     *
     * Insert offset:
     *  * -1 = insert before index
     *  * 0 = replace index
     *  * 1 = insert after index, default=1.
     * Eg:
     *      a    b     C    d   e       Original index
     *        -2   -1  0  1   2         Insert position by $mode
     * When insert position is 2 and offset is -1, the data will be insert
     * between 'b' and 'C', and 'C' will be push behind inserted data.
     *
     * @param   array       &$source
     * @param   string|int  $idx        Position idx, append if not found
     * @param   array       $insert     Array to insert, can have multi item
     * @param   integer     $offset       Insert offset
     * @return  array
     */
    public function insert(&$source, $idx, array $insert, $offset = 1)
    {
        if (empty($insert)) {
            return $source;
        }


        // Find insert position
        $sourceKeys = array_keys($source);
        $insertPosition = array_search($idx, $sourceKeys, true);
        if (false === $insertPosition) {
            // Idx not found, append
            $source = array_merge($source, $insert);
            return $source;

        } elseif (0 == $offset) {
            // Implement replace
            unset($source[$sourceKeys[$insertPosition]]);
        }

        // When combines result, drop keys from source exist in insert
        $duplicate = array_intersect($source, $insert);

        // Compute actual insert position by offset
        $insertPosition += $offset;
        if (0 > $offset) {
            $insertPosition += 1;
        }

        // Different combine method by insert position
        if (0 >= $insertPosition) {
            $source = array_diff_key($source, $duplicate);
            $source = array_merge($insert, $source);

        } elseif (count($source) < $insertPosition) {
            $source = array_merge($source, $insert);

        } else {
            $before = array_slice($source, 0, $insertPosition);
            $after = array_slice($source, $insertPosition + ((0 == $offset) ? 1 : 0));

            $before = array_diff_key($before, $duplicate);
            $after = array_diff_key($after, $duplicate);

            $source = array_merge($before, $insert, $after);
        }

        return $source;
    }


    /**
     * Search item in an array by wildcard rules
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
     * @param   array   $sources     Source data.
     * @param   string  $rules      Wildcard rule string.
     * @param   string  $delimiter  Default ','
     * @return  array
     */
    public function searchByWildcard($sources, $rules, $delimiter = ',')
    {
        // Check empty input or rules
        if (empty($sources) && empty($rules)) {
            return array();
        }

        // Read rules
        $arRule = explode($delimiter, $rules);

        $arResult = array();

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

            // Loop source array
            $stringUtil = $this->getUtil('StringUtil');
            foreach ($sources as $k => $source) {
                if (true == $stringUtil->matchWildcard($source, $rule)) {
                    // Got element to +/-
                    $i = array_search($source, $arResult);
                    if ('+' == $op) {
                        // Add to ar if not in it.
                        if (false === $i) {
                            $arResult =
                                array_merge($arResult, array($k => $source));
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
     * Sort multi-dimension array according to it's 2nd level value
     *
     * @param   array   &$source    Array to be sort
     * @param   mixed   $key        Sort by this key's value in 2nd-dimension
     * @param   mixed   $order      True = ASC/false = DESC, or use str.
     * @param   mixed   $joker      Use when val of key isn't set.
     * @return  array
     */
    public function sortByLevel2(
        &$source,
        $key,
        $order = true,
        $joker = ''
    ) {
        $arVal = array();
        foreach ($source as $k => $v) {
            $arVal[$k] = self::getIdx($v, $key, $joker);
        }

        if (true === $order || 'ASC' == strtoupper($order)) {
            asort($arVal);
        } else {
            arsort($arVal);
        }

        // Got current order, write back.
        $rs = array();
        foreach ($arVal as $k => $v) {
            $rs[$k] = &$source[$k];
        }

        // Re-order numeric array key
        $source = array_merge($rs, array());

        return $source;
    }
}
