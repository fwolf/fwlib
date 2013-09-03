<?php
namespace Fwlib\Util;

use Fwlib\Util\Ip;

/**
 * UUID generator
 *
 * UUID format:
 * [timeLow]-[timeMid]-[custom1]-[custom2](part1/2)
 *  -[custom2](part2/2)[random1][random2]
 * timeLow: 8 chars, seconds in microtime, hex format.
 * timeMid: 4 chars, micro-second in microtime, plus 10000, hex format.
 * custom1: 4 chars, user defined, '0000' if empty, hex format suggested.
 * custom2: 8 chars, user defined, hex of user ip if empty,
 *          and random hex string if user ip cannot get, hex format too.
 * random1: 4 chars, random string, hex format.
 * random2: 4 chars, random string, hex format.
 *
 * @link        http://us.php.net/uniqid
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2008-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2008-05-08
 */
class Uuid
{
    /**
     * Generate an uuid
     *
     * User can combine cus and cus2 to sort uuid.
     *
     * $cus is custom part 1 in uuid, 4 chars long,
     * positioned in 3rd section, default fill by '0'.
     *
     * $cus2 is custom part 2 in uuid, 8 chars long,
     * positioned in 4th section and start of 5th section.
     * If empty given, user client user ip(hex) to fill,
     * and random string if can't get ip.
     * If length <> 8, will fill/cut to 8 with random chars after it.
     *
     * @param   string  $cus
     * @param   string  $cus2
     * @return  string
     */
    public static function gen($cus = '0000', $cus2 = '')
    {
        $ar = explode(' ', microtime());

        // Prepare custom part 1
        $cus = strval($cus);
        if (4 != strlen($cus)) {
            $cus = substr('0000' . $cus, -4);
        }

        // Prepare custom part 2
        if (empty($cus2)) {
            $cus2 = Ip::toHex();
        }
        if (8 != strlen($cus2)) {
            $cus2 .= sprintf('%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff));
            $cus2 = substr($cus2, 0, 8);
        }

        return sprintf(
            '%08s-%04s-%04s-%04s-%04s%04x%04x',

            // Unixtime, 8 chars from right-side end
            // 2030-12-31 = 1924876800(dec) = 72bb4a00(hex)
            substr(str_repeat('0', 8) . base_convert($ar[1], 10, 16), -8),

            // Microtime, 4chars from left-side start
            // to exceed 65534(length 4) limit, * 100000 and div by 2(max 50000)
            substr(base_convert(round($ar[0] * 100000 / 2), 10, 16), 0, 4),

            // Custom part 1, default 4 chars
            $cus,

            // Custom part2-part1, length: 4
            substr($cus2, 0, 4),

            // Custom part2-part2, length: 4, used in 5th section
            substr($cus2, -4),

            // Random string, length: 4
            mt_rand(0, 0xffff),

            // Random string, length: 4
            mt_rand(0, 0xffff)
        );
    }


    /**
     * Parse uuid, see what it means
     *
     * @param   string  $uuid
     * @return  array
     */
    public static function parse($uuid)
    {
        $ar = array();
        $u = explode('-', $uuid);
        if (is_array($u) && (5 == count($u))) {
            $ar = array(
                'timeLow' => hexdec($u[0]),
                'timeMid' => hexdec($u[1]),
                'custom1' => $u[2],
                'custom2' => $u[3] . substr($u[4], 0, 4),
                'ip'      => Ip::fromHex($u[3] . substr($u[4], 0, 4)),
                'random1' => substr($u[4], 4, 4),
                'random2' => substr($u[4], 8)
            );
        }
        return $ar;
    }
}
