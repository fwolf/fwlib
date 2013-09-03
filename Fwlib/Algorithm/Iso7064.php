<?php
namespace Fwlib\Algorithm;


/**
 * ISO 7064
 *
 * ISO 7064:1983, ISO 7064:2003
 *
 * Supported Mod:
 * 17,16
 * 37,36
 *
 * @link https://zh.wikipedia.org/wiki/校验码系统
 * @link https://en.wikipedia.org/wiki/Global_Release_Identifier Use ISO 7064:1983 Mod 37, 36
 * @link https://code.google.com/p/checkdigits/wiki/CheckDigitSystems
 *
 * @package     Fwlib\Algorithm
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-09-03
 */
class Iso7064
{
    /**
     * Encode a string
     *
     * @param   string  $srce
     * @param   string  $algo
     * @param   string  $returnFull     Return full value or only check chars
     * @return  string
     */
    public static function encode($srce, $algo = '', $returnFull = false)
    {
        switch ($algo) {
            case '1716':
                $result = self::encode1716($srce, $returnFull);
                break;

            case '3736':
                $result = self::encode3736($srce, $returnFull);
                break;

            default:
                $result = null;
        }

        return $result;
    }


    /**
     * Encode with ISO 7064 Mod 17,16
     *
     * Not standard in ISO 7064, but used in old ISAN.
     * New ISAN use Mod 37,36.
     *
     * Input: AlphaNumeric
     * Output: 1 byte AlphaNumeric 0-9 A-F
     *
     * @link http://www.pruefziffernberechnung.de/Originaldokumente/wg1n130.pdf
     * @link http://tools.ietf.org/html/rfc4246
     * @link https://zh.wikipedia.org/wiki/ISAN
     *
     * @param   string  $srce
     * @param   string  $returnFull     Return full value or only check chars
     * @return  string
     */
    public static function encode1716($srce, $returnFull = false)
    {
        $srce = strtoupper($srce);
        $val = self::encodeModN($srce, 16);

        if ($returnFull) {
            return $srce . $val;
        } else {
            return $val;
        }
    }




    /**
     * Encode with ISO 7064 Mod 37,36
     *
     * Input: AlphaNumeric
     * Output: 1 byte AlphaNumeric
     *
     * @param   string  $srce
     * @param   string  $returnFull     Return full value or only check chars
     * @return  string
     */
    public static function encode3736($srce, $returnFull = false)
    {
        $srce = strtoupper($srce);
        $val = self::encodeModN($srce, 36);


        if ($returnFull) {
            return $srce . $val;
        } else {
            return $val;
        }
    }


    /**
     * Encode by mod N
     *
     * @param   string  $srce
     * @param   int     $mod
     * @return  string
     */
    protected static function encodeModN($srce, $mod)
    {
        static $dict = array(
            '0' => 0,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            'A' => 10,
            'B' => 11,
            'C' => 12,
            'D' => 13,
            'E' => 14,
            'F' => 15,
            'G' => 16,
            'H' => 17,
            'I' => 18,
            'J' => 19,
            'K' => 20,
            'L' => 21,
            'M' => 22,
            'N' => 23,
            'O' => 24,
            'P' => 25,
            'Q' => 26,
            'R' => 27,
            'S' => 28,
            'T' => 29,
            'U' => 30,
            'V' => 31,
            'W' => 32,
            'X' => 33,
            'Y' => 34,
            'Z' => 35,
        );

        $val = 0;

        $j = strlen($srce);
        for ($i = 0; $i < $j; $i ++) {
            $val += $dict[$srce{$i}];

            if ($val > $mod) {
                $val -= $mod;
            }

            $val *= 2;

            if ($val > $mod) {
                $val -= $mod + 1;
            }
        }

        $val = $mod + 1 - $val;
        if ($val == $mod) {
            $val = 0;
        }

        $val = array_search($val, $dict);

        return $val;
    }


    /**
     * Verify a string
     *
     * @param   string  $srce
     * @param   string  $algo
     * @return  string
     */
    public static function verify($srce, $algo = '')
    {
        switch ($algo) {
            case '3736':
                $s = substr($srce, 0, strlen($srce) - 1);
                $result = ($srce == self::encode($s, $algo, true));
                break;

            default:
                $result = true;
        }

        return $result;
    }
}
