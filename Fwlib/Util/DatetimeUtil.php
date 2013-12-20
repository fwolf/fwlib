<?php
namespace Fwlib\Util;


/**
 * Datetime util
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2009-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2009-02-24
 */
class DatetimeUtil
{
    /**
     * Convert sec back to str describe.
     *
     * No week in result.
     *
     * @param   int     $sec
     * @param   boolean $simple         If true, use ymdhis instead of word
     * @return  string
     */
    public function convertSecToStr($sec, $simple = true)
    {
        if (empty($sec) || !is_numeric($sec)) {
            return '';
        }

        $arDict = array(
            array('c', -1,  'century',  'centuries'),
            array('y', 100, 'year',     'years'),
            // 12m != 1y, can't count month in.
            //array('m', 12,  'month',    'months'),
            array('d', 365, 'day',      'days'),
            array('h', 24,  'hour',     'hours'),
            array('i', 60,  'minute',   'minutes'),
            array('s', 60,  'second',   'seconds'),
        );
        $i = count($arDict);
        // Loop from end of $arDict
        $s = '';
        while (0 < $i && 0 < $sec) {
            // 1. for loop, 2. got current array index
            $i --;

            // Reach top level, end loop
            if (-1 == $arDict[$i][1]) {
                $s = $sec . $arDict[$i][(($simple) ? 0
                    : ((1 == $sec) ? 2 : 3))]
                    . ' ' . $s;
                break;
            }

            $j = $sec % $arDict[$i][1];
            if (0 != $j) {
                $s = $j . $arDict[$i][(($simple) ? 0
                    : ((1 == $sec) ? 2 : 3))]
                    . ' ' . $s;
            }
            $sec = floor($sec / $arDict[$i][1]);
        }

        return rtrim($s);
    }


    /**
     * Convert str to seconds it means
     *
     * Like 1m, 20d or combined
     *
     * Solid: 1m = 30d, 1y = 365d
     *
     * @param   string  $str
     * @return  integer
     */
    public function convertStrToSec($str)
    {
        if (empty($str)) {
            return 0;
        }

        // All number, return directly
        if (is_numeric($str)) {
            return $str;
        }

        // Parse c, y, m, w, d, h, i, s
        $str = strtolower($str);
        $str = strtr(
            $str,
            array(
                'sec'       => 's',
                'second'    => 's',
                'seconds'   => 's',
                'min'       => 'i',
                'minute'    => 'i',
                'minutes'   => 'i',
                'hour'      => 'h',
                'hours'     => 'h',
                'day'       => 'd',
                'days'      => 'd',
                'week'      => 'w',
                'weeks'     => 'w',
                'month'     => 'm',
                'months'    => 'm',
                'year'      => 'y',
                'years'     => 'y',
                'century'   => 'c',
                'centuries' => 'c',
            )
        );
        $str = preg_replace(
            array(
                '/([+-]?\d+)s/',
                '/([+-]?\d+)i/',
                '/([+-]?\d+)h/',
                '/([+-]?\d+)d/',
                '/([+-]?\d+)w/',
                '/([+-]?\d+)m/',
                '/([+-]?\d+)y/',
                '/([+-]?\d+)c/',
            ),
            array(
                '+$1 ',
                '+$1 * 60 ',
                '+$1 * 3600 ',
                '+$1 * 86400 ',
                '+$1 * 604800 ',
                '+$1 * 2592000 ',
                '+$1 * 31536000 ',
                '+$1 * 3153600000 ',),
            $str
        );
        // Fix +-
        $str = preg_replace('/\+\s*\-/', '-', $str);
        $str = preg_replace('/\-\s*\+/', '-', $str);
        $str = preg_replace('/\+\s*\+/', '+', $str);
        eval('$sec = ' . $str . ';');
        return $sec;
    }


    /**
     * Convert sybase time to normal
     *
     * Sybase's time end with ':000', probably because using dblib.
     *
     * @param   string  $time
     * @return  integer
     */
    public function convertTimeFromSybase($time)
    {
        if (!empty($time)) {
            // Remove tail add by sybase
            $time = preg_replace('/:\d{3}/', '', $time);
        }
        return strtotime($time);
    }
}
