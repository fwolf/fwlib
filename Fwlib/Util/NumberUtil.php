<?php
namespace Fwlib\Util;


/**
 * Number util
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2006-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2006-09-27
 */
class NumberUtil
{
    /**
     * Convert size to human readable format string
     *
     * @param   long    $size
     * @param   int     $precision
     * @param   int     $step       Compute by 1024 or 1000 ?
     * @return  string
     */
    public static function toHumanSize($size, $precision = 1, $step = 1024)
    {
        $ranks = array('B', 'K', 'M', 'G', 'T', 'P');
        // Total 6 levels, loop from 0 to 5 just fit $ranks index
        $i = 0;
        while ($size > $step && $i <5) {
            $size = $size / $step;
            $i ++;
        }

        // Cut zero tail
        $size = round($size, $precision);
        if (0 == ($size - floor($size))) {
            $size = floor($size);
        }

        return $size . $ranks[$i];
    }
}
