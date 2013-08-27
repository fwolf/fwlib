<?php
namespace Fwlib\Mis;


/**
 * Cin code
 *
 * Cin = Citizen identification number
 *
 * @package     Fwlib\Mis
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-27
 * @link    https://zh.wikisource.org/wiki/GB_11643-1999_公民身份号码
 */
class CinCode
{
    /**
     * Generate pin code
     *
     * @return  string
     */
    public static function gen()
    {
        $cin = '';


        // First 6 bit: 行政区划代码
        // @link http://www.stats.gov.cn/tjbz/index.htm

        // Province
        $province = array_merge(
            range(11, 15),
            range(21, 23),
            range(31, 37),
            range(41, 46),
            range(50, 54),
            range(61, 65),
            array(71, 81, 82)
        );
        $k = array_rand($province);
        $cin .= $province[$k];

        // City
        $cin .= sprintf('%02s', mt_rand(0, 10));

        // Country
        $country = array_merge(
            range(0, 5),
            range(21, 31),
            range(81, 85)
        );
        $k = array_rand($country);
        $cin .= sprintf('%02s', $country[$k]);


        // 7-14, date
        $cin .= date('Ymd', mt_rand(strtotime('1970-1-1'), time()));


        // Sequence number
        $cin .= sprintf('%03s', mt_rand(1, 200));


        // Compute for currect pin
        $cin = self::to18(substr($cin, 0, 6) . substr($cin, 8), 19);

        return $cin;
    }


    /**
     * Convert 18 bit pin to 15 bit long
     *
     * @param   string  $cin
     * @return  string
     */
    public static function to15($cin)
    {
        if (18 != strlen($cin)) {
            return $cin;
        }

        return substr($cin, 0, 6) . substr($cin, 8, 9);
    }


    /**
     * Convert 15 bit pin to 18 bit long
     *
     * @param   string  $cin
     * @param   int     $prefix
     * @return  string
     */
    public static function to18($cin, $prefix = 19)
    {
        if (15 != strlen($cin)) {
            // Error, which value should I return ?
            return $cin;
        }

        $s = substr($cin, 0, 6) . strval($prefix) . substr($cin, 6);

        $n = 0;
        for ($i = 17; 0 < $i; $i --) {
            $n += (pow(2, $i) % 11) * intval($s{17 - $i});
        }
        $n = $n % 11;
        switch ($n) {
            case 0:
                $sLast = '1';
                break;
            case 1:
                $sLast = '0';
                break;
            case 2:
                $sLast = 'X';
                break;
            default:
                $sLast = strval(12 - $n);
                break;
        }

        return $s . $sLast;
    }


    /**
     * Validate pin code, for 18 bit only
     *
     * @param   string  $cin
     * @return  boolean
     */
    public static function validate($cin)
    {
        if (18 != strlen($cin)) {
            return false;
        }

        $prefix = substr($cin, 6, 2);
        return ($cin == self::to18(self::to15($cin), $prefix));
    }
}
