<?php
namespace Fwlib\Util;

use Fwlib\Algorithm\Iso7064;
use Fwlib\Util\Ip;

/**
 * UUID generator
 *
 * UUID format:
 *
 * [timeLow]-[timeMid]-[custom1]-[custom2](part1/2)
 *  -[custom2](part2/2)[random1][random2]
 *
 * timeLow: 8 chars, seconds in microtime, hex format.
 * timeMid: 4 chars, micro-second in microtime, plus 10000, hex format.
 * custom1: 4 chars, user defined, '0000' if empty, hex format suggested.
 * custom2: 8 chars, user defined, hex of user ip if empty,
 *          and random hex string if user ip cannot get, hex format too.
 * random1: 4 chars, random string, hex format.
 * random2: 4 chars, random string, hex format.
 *
 * Separator '-' is optional and default off. Length of UUID is 32 bytes, and
 * 36 bytes with separator.
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
     * Add check digit to an Uuid
     *
     * Check digit will replace last byte.
     *
     * Origin separator will kept.
     *
     * @param   string  $uuid
     */
    public static function addCheckDigit($uuid)
    {
        if (36 == strlen($uuid)) {
            $separator = $uuid{8};
            $uuid = self::delSeparator($uuid);
        }

        $uuid = Iso7064::encode(
            substr($uuid, 0, 31),
            '1716',
            true
        );
        $uuid = strtolower($uuid);

        if (isset($separator)) {
            $uuid = self::addSeparator($uuid, $separator);
        }

        return $uuid;
    }


    /**
     * Add separator to UUID, extend it to 36 digit
     *
     * Please make SURE send in UUID's length is 32.
     *
     * @param   string  $uuid
     * @param   string  $separator
     * @return  string
     */
    protected static function addSeparator($uuid, $separator)
    {
        return substr($uuid, 0, 8) . $separator .
            substr($uuid, 8, 4) . $separator .
            substr($uuid, 12, 4) . $separator .
            substr($uuid, 16, 4) . $separator .
            substr($uuid, 20);
    }


    /**
     * Del separator in UUID, shink it to 32 digit
     *
     * Please make SURE send in UUID's length is 36.
     *
     * @param   string  $uuid
     * @return  string
     */
    protected static function delSeparator($uuid)
    {
        return substr($uuid, 0, 8) .
            substr($uuid, 9, 4) .
            substr($uuid, 14, 4) .
            substr($uuid, 19, 4) .
            substr($uuid, 24);
    }


    /**
     * Generate an UUID
     *
     * User can combine cus and cus2 to sort UUID.
     *
     * $cus is custom part 1 in UUID, 4 chars long,
     * positioned in 3rd section, default fill by '0'.
     *
     * $cus2 is custom part 2 in UUID, 8 chars long,
     * positioned in 4th section and start of 5th section.
     * If empty given, user client user ip(hex) to fill,
     * and random string if can't get ip.
     * If length <> 8, will fill/cut to 8 with random chars after it.
     *
     * If $checkDigit is true, use last byte as check digit,
     * by ISO 7064 Mod 17,16 algorithm.
     *
     * @param   string  $cus
     * @param   string  $cus2
     * @param   boolean $checkDigit
     * @return  string
     */
    public static function gen($cus = '0000', $cus2 = '', $checkDigit = false)
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

        $rs = sprintf(
            //'%08s-%04s-%04s-%04s-%04s%04x%04x',
            '%08s%04s%04s%04s%04s%04x%04x',

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

        // Add check digit/byte
        if ($checkDigit) {
            $rs = self::addCheckDigit($rs, true);
        }

        return $rs;
    }


    /**
     * Generate an uuid split by separator
     *
     * Don't use 0-9,a-z,A-Z as separator !
     *
     * @param   string  $cus
     * @param   string  $cus2
     * @param   boolean $checkDigit
     * @param   string  $separator
     * @return  string
     */
    public static function genWithSeparator(
        $cus = '0000',
        $cus2 = '',
        $checkDigit = false,
        $separator = '-'
    ) {
        return self::addSeparator(
            self::gen($cus, $cus2, $checkDigit),
            $separator
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
        if (36 == strlen($uuid)) {
            $uuid = self::delSeparator($uuid);
        }

        if (32 == strlen($uuid)) {
            $timeLow = hexdec(substr($uuid, 0, 8));
            $timeMid = hexdec(substr($uuid, 8, 4));
            $custom2 = substr($uuid, 16, 8);
            return array(
                'timeLow' => $timeLow,
                'timeMid' => $timeMid,
                'time'    => date('Y-m-d H:i:s', $timeLow),
                'custom1' => substr($uuid, 12, 4),
                'custom2' => $custom2,
                'ip'      => Ip::fromHex($custom2),
                'random1' => substr($uuid, 24, 4),
                'random2' => substr($uuid, 28, 4)
            );
        } else {
            return null;
        }
    }


    /**
     * Verify Uuid
     *
     * @param   string  $uuid
     * @param   boolean $withCheckDigit     Source includes check digit
     * @return  boolean
     */
    public static function verify($uuid, $withCheckDigit = false)
    {
        if (36 == strlen($uuid)) {
            $separator = $uuid{8};
            $uuidOrigin = $uuid;
            $uuid = self::delSeparator($uuid);

            // Separator must same and position right
            if ($uuidOrigin != self::addSeparator($uuid, $separator)) {
                return false;
            }
        }

        if (32 != strlen($uuid)) {
            return false;
        }

        // AlphaNumeric 0-9 a-f
        $uuid = strtolower($uuid);
        if ('' !== preg_replace('/[0-9a-f]/', '', $uuid)) {
            return false;
        }

        // Check digit
        if ($withCheckDigit && ($uuid != self::addCheckDigit($uuid))) {
            return false;
        }

        return true;
    }
}
