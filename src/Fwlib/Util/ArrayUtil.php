<?php
namespace Fwlib\Util;

/**
 * Array util
 *
 * @copyright   Copyright 2009-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class ArrayUtil
{
    use UtilContainerAwareTrait;


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
     * @param   integer     $offset     Insert offset
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
        $duplicate = array_intersect_key($source, $insert);

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
     * Pick specific keys from given array
     *
     * All set key in source can be picked, if noEmpty parameter is true, keys
     * have empty value will not be picked.
     *
     * Callback can be apply to value before return result array. If callback
     * and noEmpty are both present, will do empty check on callback result.
     *
     * The result array will use same keys of $keys, if $keys uses number
     * index, the value of $keys will be used, this is also key in $sources.
     * Eg:
     *      // Key replace mode, $rs will be array('paramA' => 1)
     *      $rs = pick(array('a' => 1), array('paramA' => 'a'))
     *      // Normal mode, $rs will be array('a' => 1)
     *      $rs = pick(array('a' => 1), array('a'))
     *
     * @param   array    $sources
     * @param   array    $keys
     * @param   boolean  $noEmpty
     * @param   callable $callback
     * @return  array
     */
    public function pick(
        array $sources,
        array $keys,
        $noEmpty = false,
        $callback = null
    ) {
        $results = [];
        foreach ($keys as $keyOfKeys => $valueOfKeys) {
            if (array_key_exists($valueOfKeys, $sources)) {
                // '1' is number, so do string check instead
                $keyOfResult = is_string($keyOfKeys)
                    ? $keyOfKeys
                    : $valueOfKeys;
                $results[$keyOfResult] = $sources[$valueOfKeys];
            }
        }

        if (is_callable($callback)) {
            foreach ($results as &$value) {
                $value = $callback($value);
            }
            unset($value);
        }

        if ($noEmpty) {
            $results = array_filter($results, function ($result) {
                return !empty($result);
            });
        }

        return $results;
    }


    /**
     * Search item in an array by wildcard rules
     *
     * Wildcard rules is a string include many part joined by ',', each part
     * can include * and ?, head by '+'(default) or '-', they means find
     * elements suit the rules in source array, and add_to/remove_from result
     * array.
     *
     * Parts operate sequence is by occur position in rules string, an item
     * can be added or removed multiple times.
     *
     * Rules example: a*, -*b, -??c, +?d*
     *
     * @param   array   $sources    Source data
     * @param   string  $rules      Wildcard rule string
     * @param   string  $delimiter  Default ','
     * @return  array
     */
    public function searchByWildcard($sources, $rules, $delimiter = ',')
    {
        // Check empty input or rules
        if (empty($sources) && empty($rules)) {
            return [];
        }

        // Read rules
        $arRules = explode($delimiter, $rules);

        $results = [];
        foreach ($arRules as $rule) {
            $rule = trim($rule);

            // Empty rule means 'all'
            if (empty($rule)) {
                $rule = '*';
            }

            // + or - ?
            if ('-' == $rule[0]) {
                $op = '-';
                $rule = substr($rule, 1);
            } else {
                $op = '+';
                $rule = trim($rule, '+');
            }

            // Loop source array
            $stringUtil = $this->getUtilContainer()->getString();
            foreach ($sources as $k => $source) {
                if ($stringUtil->matchWildcard($source, $rule)) {
                    // Got element to +/-
                    if ('+' == $op) {
                        // Add to result
                        $results[$k] = $source;
                    } else {
                        // Remove from result
                        unset($results[$k]);
                    }
                }
            }
        }

        return $results;
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
        $arVal = [];
        foreach ($source as $k => $v) {
            $arVal[$k] = self::getIdx($v, $key, $joker);
        }

        if (true === $order || 'ASC' == strtoupper($order)) {
            asort($arVal);
        } else {
            arsort($arVal);
        }

        // Got current order, write back.
        $rs = [];
        foreach ($arVal as $k => $v) {
            $rs[$k] = &$source[$k];
        }

        // Re-order numeric array key
        $source = array_merge($rs, []);

        return $source;
    }
}
