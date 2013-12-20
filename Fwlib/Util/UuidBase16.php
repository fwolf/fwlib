<?php
namespace Fwlib\Util;

use Fwlib\Algorithm\Iso7064;
use Fwlib\Util\AbstractUtilAware;

/**
 * UUID generator using hex(0-9a-f)
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
class UuidBase16 extends AbstractUtilAware
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
    public function addCheckDigit($uuid)
    {
        if (36 == strlen($uuid)) {
            $separator = $uuid{8};
            $uuid = $this->delSeparator($uuid);
        }

        $uuid = Iso7064::encode(
            substr($uuid, 0, 31),
            '1716',
            true
        );
        $uuid = strtolower($uuid);

        if (isset($separator)) {
            $uuid = $this->addSeparator($uuid, $separator);
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
    protected function addSeparator($uuid, $separator)
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
    protected function delSeparator($uuid)
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
     * User can combine custom1 and custom2 to sort UUID.
     *
     * $custom is custom part 1 in UUID, 4 chars long, positioned in 3rd section,
     * leave empty will fill by '0'. In product envionment, $custom should start
     * from '0010', '0000'~'0009' is reserved for develop/test.
     *
     * $custom2 is custom part 2 in UUID, 8 chars long, positioned in 4th
     * section and start of 5th section.  If empty given, user client user
     * ip(hex) to fill, and random string if can't get ip.  If length <> 8,
     * will fill/cut to 8 with random chars after it.
     *
     * If $checkDigit is true, use last byte as check digit, by ISO 7064 Mod
     * 17,16 algorithm.
     *
     * @param   string  $custom1
     * @param   string  $custom2
     * @param   boolean $checkDigit
     * @return  string
     */
    public function generate(
        $custom1 = '0010',
        $custom2 = '',
        $checkDigit = false
    ) {
        $ar = explode(' ', microtime());

        // timeLow: 8 chars from right-side end of current timestamp
        // 2030-12-31 = 1924876800(dec) = 72bb4a00(hex)
        $timeLow = substr(str_repeat('0', 8) . base_convert($ar[1], 10, 16), -8);

        // timeMid: 4 chars from left-side start of current microsecond, to
        // make value lower than 65534(length 4) limit, * 100000 and div by
        // 2(max 50000)
        $timeMid = substr(base_convert(round($ar[0] * 100000 / 2), 10, 16), 0, 4);

        // custom1: 4 chars
        $custom1 = strval($custom1);
        if (4 != strlen($custom1)) {
            $custom1 = substr('0000' . $custom1, -4);
        }

        // custom2: 4 chars, split to 2 parts
        if (empty($cus2)) {
            $cus2 = $this->utilContainer->get('Ip')->toHex();
        }
        if (8 != strlen($cus2)) {
            $cus2 .= sprintf('%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff));
            $cus2 = substr($cus2, 0, 8);
        }
        $custom2p1 = substr($custom2, 0, 4);
        $custom2p2 = substr($custom2, -4);

        // Random1: 4chars
        $random1 = mt_rand(0, 0xFFFF);

        // Random2: 4chars
        $random2 = mt_rand(0, 0xFFFF);


        $rs = sprintf(
            //'%08s-%04s-%04s-%04s-%04s%04x%04x',
            '%08s%04s%04s%04s%04s%04x%04x',
            $timeLow,
            $timeMid,
            $custom1,
            $custom2p1,
            $custom2p2,
            $random1,
            $random2
        );

        // Add check digit/byte
        if ($checkDigit) {
            $rs = $this->addCheckDigit($rs, true);
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
    public function generateWithSeparator(
        $cus = '0000',
        $cus2 = '',
        $checkDigit = false,
        $separator = '-'
    ) {
        return $this->addSeparator(
            $this->generate($cus, $cus2, $checkDigit),
            $separator
        );
    }


    /**
     * Parse uuid, see what it means
     *
     * @param   string  $uuid
     * @return  array
     */
    public function parse($uuid)
    {
        if (36 == strlen($uuid)) {
            $uuid = $this->delSeparator($uuid);
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
                'ip'      => $this->utilContainer->get('Ip')->fromHex($custom2),
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
    public function verify($uuid, $withCheckDigit = false)
    {
        if (36 == strlen($uuid)) {
            $separator = $uuid{8};
            $uuidOrigin = $uuid;
            $uuid = $this->delSeparator($uuid);

            // Separator must same and position right
            if ($uuidOrigin != $this->addSeparator($uuid, $separator)) {
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
        if ($withCheckDigit && ($uuid != $this->addCheckDigit($uuid))) {
            return false;
        }

        return true;
    }
}
