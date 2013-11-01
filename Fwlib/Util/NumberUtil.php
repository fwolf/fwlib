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
    public static $baseConvertMap = array(
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    );

    public static $baseConvertMapReverse = array(
        '0' => 0,   '1' => 1,   '2' => 2,   '3' => 3,   '4' => 4,
        '5' => 5,   '6' => 6,   '7' => 7,   '8' => 8,   '9' => 9,
        'a' => 10,  'b' => 11,  'c' => 12,  'd' => 13,  'e' => 14,
        'f' => 15,  'g' => 16,  'h' => 17,  'i' => 18,  'j' => 19,
        'k' => 20,  'l' => 21,  'm' => 22,  'n' => 23,  'o' => 24,
        'p' => 25,  'q' => 26,  'r' => 27,  's' => 28,  't' => 29,
        'u' => 30,  'v' => 31,  'w' => 32,  'x' => 33,  'y' => 34,
        'z' => 35,
        'A' => 36,  'B' => 37,  'C' => 38,  'D' => 39,  'E' => 40,
        'F' => 41,  'G' => 42,  'H' => 43,  'I' => 44,  'J' => 45,
        'K' => 46,  'L' => 47,  'M' => 48,  'N' => 49,  'O' => 50,
        'P' => 51,  'Q' => 52,  'R' => 53,  'S' => 54,  'T' => 55,
        'U' => 56,  'V' => 57,  'W' => 58,  'X' => 59,  'Y' => 60,
        'Z' => 61,
    );

    /**
     * Number equal or larger than 100000000000000(1.0E+14) will represent by
     * Scientific Notation(科学记数法), cause base_convert() loose precision.
     * So for larger number, use BC Math or GMP.
     *
     * This array is used to check number string length per base, if string is
     * longer than the array value, it should not use build-in base_convert().
     *
     * @link https://gist.github.com/fwolf/7250392
     */
    public static $baseConvertSafeLength = array(
        2  => 46,   //  2^46 = 70368744177664
        3  => 29,   //  3^29 = 68630377364883
        4  => 23,   //  4^23 = 70368744177664
        5  => 20,   //  5^20 = 95367431640625
        6  => 17,   //  6^17 = 16926659444736
        7  => 16,   //  7^16 = 33232930569601
        8  => 15,   //  8^15 = 35184372088832
        9  => 14,   //  9^14 = 22876792454961
        10 => 13,   // 10^13 = 10000000000000
        11 => 13,   // 11^13 = 34522712143931
        12 => 12,   // 12^12 = 8916100448256
        13 => 12,   // 13^12 = 23298085122481
        14 => 12,   // 14^12 = 56693912375296
        15 => 11,   // 15^11 = 8649755859375
        16 => 11,   // 16^11 = 17592186044416
        17 => 11,   // 17^11 = 34271896307633,
        18 => 11,   // 18^11 = 64268410079232,
        19 => 11,   // 19^10 = 6131066257801,
        20 => 10,   // 20^10 = 10240000000000,
        21 => 10,   // 21^10 = 16679880978201,
        22 => 10,   // 22^10 = 26559922791424,
        23 => 10,   // 23^10 = 41426511213649,
        24 => 10,   // 24^10 = 63403380965376,
        25 => 10,   // 25^10 = 95367431640625,
        26 => 9,    // 26^9  = 5429503678976,
        27 => 9,    // 27^9  = 7625597484987,
        28 => 9,    // 28^9  = 10578455953408,
        29 => 9,    // 29^9  = 14507145975869,
        30 => 9,    // 30^9  = 19683000000000,
        31 => 9,    // 31^9  = 26439622160671,
        32 => 9,    // 32^9  = 35184372088832,
        33 => 9,    // 33^9  = 46411484401953,
        34 => 9,    // 34^9  = 60716992766464,
        35 => 9,    // 35^9  = 78815638671875,
        36 => 8,    // 36^8  = 2821109907456,
        37 => 8,    // 37^8  = 3512479453921,
        38 => 8,    // 38^8  = 4347792138496,
        39 => 8,    // 39^8  = 5352009260481,
        40 => 8,    // 40^8  = 6553600000000,
        41 => 8,    // 41^8  = 7984925229121,
        42 => 8,    // 42^8  = 9682651996416,
        43 => 8,    // 43^8  = 11688200277601,
        44 => 8,    // 44^8  = 14048223625216,
        45 => 8,    // 45^8  = 16815125390625,
        46 => 8,    // 46^8  = 20047612231936,
        47 => 8,    // 47^8  = 23811286661761,
        48 => 8,    // 48^8  = 28179280429056,
        49 => 8,    // 49^8  = 33232930569601,
        50 => 8,    // 50^8  = 39062500000000,
        51 => 8,    // 51^8  = 45767944570401,
        52 => 8,    // 52^8  = 53459728531456,
        53 => 8,    // 53^8  = 62259690411361,
        54 => 8,    // 54^8  = 72301961339136,
        55 => 8,    // 55^8  = 83733937890625,
        56 => 8,    // 56^8  = 96717311574016,
        57 => 7,    // 57^7  = 1954897493193,
        58 => 7,    // 58^7  = 2207984167552,
        59 => 7,    // 59^7  = 2488651484819,
        60 => 7,    // 60^7  = 2799360000000,
        61 => 7,    // 61^7  = 3142742836021,
        62 => 7,    // 62^7  = 3521614606208,
    );


    /**
     * Convert number string base, 2 to 62
     *
     * @param   string  $number
     * @param   int     $fromBase
     * @param   int     $toBase
     * @return  string
     */
    public static function baseConvert($number, $fromBase, $toBase)
    {
        if (2 > $fromBase || 2 > $toBase || 62 < $fromBase || 62 < $toBase) {
            throw new \InvalidArgumentException('Base must between 2 and 62.');
        }


        $number = trim((string)$number);
        if (empty($number)) {
            return '0';
        }


        // Simple convert use build-in base_convert()
        if (36 >= $fromBase && 36 >= $toBase &&
            strlen($number) <= self::$baseConvertSafeLength[$fromBase]
        ) {
            return strtolower(base_convert($number, $fromBase, $toBase));
        }


        // Convert using BC Math or GMP
        // @codeCoverageIgnoreStart
        if (extension_loaded('gmp')) {
            $number = ltrim($number, '0');

            // In 5.3.2, base was extended to 2~62
            if (version_compare(PHP_VERSION, '5.3.2', '>=')) {
                return self::baseConvertGmpSimple($number, $fromBase, $toBase);
            } else {
                return self::baseConvertGmp($number, $fromBase, $toBase);
            }

        } elseif (extension_loaded('bcmath')) {
            return self::baseConvertBcmath($number, $fromBase, $toBase);

        } else {
            throw new \Exception('Number too large and BC Math or GMP not loaded.');
        }
        // @codeCoverageIgnoreEnd
    }


    /**
     * Convert number string base using BC Math
     *
     * @requires extension bcmath
     */
    protected static function baseConvertBcmath($number, $fromBase, $toBase)
    {
        if (empty($number)) {
            return '0';
        }

        // @codeCoverageIgnoreStart

        if (10 == $fromBase) {
            $base10 = $number;
        } else {
            $base10 = 0;
            for ($i = 0, $j = strlen($number); $i < $j; $i ++) {
                $n = self::$baseConvertMapReverse[$number{$i}];
                $base10 = bcadd($n, bcmul($base10, $fromBase));
            }
        }

        if (10 == $toBase) {
            return $base10;
        } else {
            $baseN = '';
            while (0 < bccomp($base10, '0', 0)) {
                $r = intval(bcmod($base10, $toBase));
                $baseN = self::$baseConvertMap[$r] . $baseN;
                $base10 = bcdiv($base10, $toBase, 0);
            }
            return $baseN;
        }

        // @codeCoverageIgnoreEnd
    }


    /**
     * Convert number string base using GMP
     *
     * @requires extension gmp
     */
    protected static function baseConvertGmp($number, $fromBase, $toBase)
    {
        if (empty($number)) {
            return '0';
        }

        // @codeCoverageIgnoreStart

        // Almost same as bcmath, not fully tested
        if (10 == $fromBase) {
            $base10 = gmp_init("$number");
        } else {
            $base10 = gmp_init('0');
            for ($i = 0, $j = strlen($number); $i < $j; $i ++) {
                $n = self::$baseConvertMapReverse[$number{$i}];
                $base10 = gmp_add("$n", gmp_mul($base10, "$fromBase"));
            }
        }

        if (10 == $toBase) {
            return gmp_strval($base10);
        } else {
            $baseN = '';
            while (0 < gmp_cmp($base10, '0')) {
                list($base10, $r) = gmp_div_qr($base10, "$toBase");
                $r = intval(gmp_strval($r));
                $baseN = self::$baseConvertMap[$r] . $baseN;
            }
            return $baseN;
        }

        // @codeCoverageIgnoreEnd
    }


    /**
     * Convert number string base using GMP gmp_strval()
     *
     * @requires extension gmp
     * @requires PHP 5.3.2
     */
    protected static function baseConvertGmpSimple($number, $fromBase, $toBase)
    {
        if (empty($number)) {
            return '0';
        }

        // @codeCoverageIgnoreStart

        // GMP use 0-9a-z for base 11~36, and 0-9A-Za-z for base 37~62, so we
        // need swap upper and lower case.
        // @link http://stackoverflow.com/questions/2259666
        if (36 < $fromBase) {
            $number = strtolower($number) ^ strtoupper($number) ^ $number;
        }

        $number = gmp_strval(gmp_init($number, $fromBase), $toBase);

        if (36 < $toBase) {
            $number = strtolower($number) ^ strtoupper($number) ^ $number;
        }

        return $number;

        // @codeCoverageIgnoreEnd
    }


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
