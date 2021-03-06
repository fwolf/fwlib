<?php
namespace Fwlib\Util\Algorithm;

/**
 * ISO 7064
 *
 * ISO 7064:1983, ISO 7064:2003
 *
 * Supported Mod:
 * 11-2
 * 17,16
 * 37,36
 *
 * @link https://zh.wikipedia.org/wiki/校验码系统
 * @link https://en.wikipedia.org/wiki/Global_Release_Identifier Use ISO 7064:1983 Mod 37, 36
 * @link https://code.google.com/p/checkdigits/wiki/CheckDigitSystems
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Iso7064
{
    /**
     * Encode a string
     *
     * @param   string  $source
     * @param   string  $algorithm
     * @param   string  $returnFull     Return full value or only check chars
     * @return  string
     */
    public function encode($source, $algorithm = '', $returnFull = false)
    {
        switch ($algorithm) {
            case '112':
                $result = $this->encode112($source, $returnFull);
                break;

            case '1716':
                $result = $this->encode1716($source, $returnFull);
                break;

            case '3736':
                $result = $this->encode3736($source, $returnFull);
                break;

            default:
                $result = null;
        }

        return $result;
    }


    /**
     * Encode with ISO 7064 Mod 11-2
     *
     * Input: Numeric
     * Output: 1 byte AlphaNumeric 0-9 X
     *
     * @link http://andrecatita.com/code-snippets/iso-7064-mod-112-php/
     *
     * @param   string  $source
     * @param   string  $returnFull     Return full value or only check chars
     * @return  string
     */
    public function encode112($source, $returnFull = false)
    {
        $val = 0;
        $mod = 11;

        $j = strlen($source);
        for ($i = 0; $i < $j; $i ++) {
            $val += intval($source{$i});

            $val *= 2;
        }

        $val = $val % $mod;
        $val = ($mod + 1 - $val) % 11;
        if (10 == $val) {
            $val = 'X';
        } else {
            $val = strval($val);
        }

        if ($returnFull) {
            return $source . $val;
        } else {
            return $val;
        }
    }


    /**
     * Encode with ISO 7064 Mod 17,16
     *
     * Not standard in ISO 7064, but used in old ISAN.
     * New ISAN use Mod 37,36.
     *
     * Input: AlphaNumeric 0-9 A-F
     * Output: 1 byte AlphaNumeric 0-9 A-F
     *
     * @link http://www.pruefziffernberechnung.de/Originaldokumente/wg1n130.pdf
     * @link http://tools.ietf.org/html/rfc4246
     * @link https://zh.wikipedia.org/wiki/ISAN
     *
     * @param   string  $source
     * @param   string  $returnFull     Return full value or only check chars
     * @return  string
     */
    public function encode1716($source, $returnFull = false)
    {
        $source = strtoupper($source);
        $val = $this->encodeModN($source, 16);

        if ($returnFull) {
            return $source . $val;
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
     * @param   string  $source
     * @param   string  $returnFull     Return full value or only check chars
     * @return  string
     */
    public function encode3736($source, $returnFull = false)
    {
        $source = strtoupper($source);
        $val = $this->encodeModN($source, 36);


        if ($returnFull) {
            return $source . $val;
        } else {
            return $val;
        }
    }


    /**
     * Encode by mod N
     *
     * @param   string  $source
     * @param   int     $mod
     * @return  string
     */
    protected function encodeModN($source, $mod)
    {
        static $dict = [
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
        ];

        $val = 0;

        $j = strlen($source);
        for ($i = 0; $i < $j; $i ++) {
            $val += $dict[$source{$i}];

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
     * @param   string  $source
     * @param   string  $algorithm
     * @return  string
     */
    public function verify($source, $algorithm = '')
    {
        switch ($algorithm) {
            case '112':
            case '1716':
            case '3736':
                $s = substr($source, 0, strlen($source) - 1);
                $result = ($source == $this->encode($s, $algorithm, true));
                break;

            default:
                $result = true;
        }

        return $result;
    }
}
