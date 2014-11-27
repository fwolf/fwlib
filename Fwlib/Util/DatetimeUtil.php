<?php
namespace Fwlib\Util;

/**
 * Datetime util
 *
 * @copyright   Copyright 2009-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 */
class DatetimeUtil
{
    /**
     * Symbol of Time unit
     *
     * @var array
     */
    protected $timeUnitSymbol = array(
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
    );


    /**
     * How many seconds a time unit equals
     *
     * @var array
     */
    protected $timeUnitWeight = array(
        's' => 1,
        'i' => 60,
        'h' => 3600,
        'd' => 86400,
        'w' => 604800,
        'm' => 2592000,
        'y' => 31536000,
        'c' => 3153600000,
    );


    /**
     * Convert second back to string description.
     *
     * No week and month in result, Because 12m != 1y, it can't convert month
     * and year by solid ratio, so do week.
     *
     * One year are 365 days, no consider of 366 days, because it didn't know
     * which year it is.
     *
     * @param   int     $second
     * @param   boolean $useSimpleUnit  Use y instead of full word year.
     * @return  string
     */
    public function convertSecondToString($second, $useSimpleUnit = true)
    {
        if (empty($second) || !is_numeric($second)) {
            return '';
        }

        $unitDict = array(
            array('c', -1,  'century',  'centuries'),
            array('y', 100, 'year',     'years'),
            array('d', 365, 'day',      'days'),
            array('h', 24,  'hour',     'hours'),
            array('i', 60,  'minute',   'minutes'),
            array('s', 60,  'second',   'seconds'),
        );

        // Loop from smallest unit
        $i = count($unitDict);
        $result = '';
        while (0 < $i && 0 < $second) {
            $i --;
            // $i is index of current unit now

            // Reach top level, end loop
            if (-1 == $unitDict[$i][1]) {
                $unitIndex = ($useSimpleUnit) ? 0
                    : ((1 == $second) ? 2 : 3);

                $result = $second . $unitDict[$i][$unitIndex] . ' ' . $result;
                break;
            }

            $j = $second % $unitDict[$i][1];
            if (0 != $j) {
                $unitIndex = ($useSimpleUnit) ? 0
                    : ((1 == $second) ? 2 : 3);

                $result = $j . $unitDict[$i][$unitIndex] . ' ' . $result;
            }

            $second = floor($second / $unitDict[$i][1]);
        }

        return rtrim($result);
    }


    /**
     * Convert string to seconds it means
     *
     * Month and week are allowed here, with solid convertion ratio:
     *
     * 1month = 30days
     * 1week = 7days
     *
     * One year equals 365days, same with covertSecondToString().
     *
     * @param   string  $string
     * @return  integer
     */
    public function convertStringToSecond($string)
    {
        if (empty($string)) {
            return 0;
        }

        // All number, return directly
        if (is_numeric($string)) {
            return $string;
        }

        // Replace time unit word with symbol(single letter)
        $string = strtolower($string);
        $string = strtr($string, $this->timeUnitSymbol);

        $i = preg_match_all('/([+-]?\s*)(\d+)([cymwdhis])/', $string, $match);
        if (0 < $i) {
            $second = 0;
            foreach ($match[0] as $key => $value) {
                if ('-' == trim($match[1][$key])) {
                    $second -= $match[2][$key] *
                        $this->timeUnitWeight[$match[3][$key]];

                } else {
                    $second += $match[2][$key] *
                        $this->timeUnitWeight[$match[3][$key]];
                }
            }

            return $second;

        } else {
            return 0;
        }

        return $second;
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


    /**
     * Get microtime as float with all decimal place
     *
     * @return  string  Float microtime in string format, length: 10.8 .
     */
    public function getMicroTime()
    {
        list($msec, $sec) = explode(' ', microtime());

        return $sec . substr($msec, 1);
    }
}
