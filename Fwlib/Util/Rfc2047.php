<?php
namespace Fwlib\Util;


/**
 * Rfc2047 util
 *
 * RFC 2047 - MIME (Multipurpose Internet Mail Extensions) Part Thr
 * @link    http://www.faqs.org/rfcs/rfc2047
 * @link    http://www.php.net/imap_utf8
 *
 * Usually used in mail header, attachment name etc.
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-27
 */
class Rfc2047
{
    /**
     * Decode string
     *
     * @param   string  $str
     * @param   string  $encoding
     * @return  string
     */
    public function decode($str, $encoding = 'utf-8')
    {
        // Find string encoding
        $ar = array();
        preg_match_all('/=\?(.{3,13})\?([B|Q])\?([^\?]*)\?\=/i', $str, $ar);
        // 0 is all-string pattern, 1 is encoding, 2 is string to base64_decode
        $i = count($ar[0]);
        if (0 < $i) {
            // Got match, process
            for ($j = 0; $j < count($i); $j++) {
                $s = '';
                if ('B' == strtoupper($ar[2][$j])) {
                    // Decode base64 first
                    $s = base64_decode($ar[3][$j]);
                } elseif ('Q' == strtoupper($ar[2][$j])) {
                    // quoted-printable encoding ? its format like '=0D=0A'
                    $s = quoted_printable_decode($ar[3][$j]);
                }

                // Then convert string to charset ordered
                if ($encoding != strtolower($ar[1][$j])) {
                    $s = mb_convert_encoding($s, $encoding, $ar[1][$j]);
                }

                // Then replace into original string
                if (!empty($s)) {
                    $str = str_replace($ar[0][$j], $s, $str);
                }
            }
            return $str;
        } else {
            // No match, return original string
            return $str;
        }
    }


    /**
     * Encode string
     *
     * No break in string(B encoding mode instead of Q, see
     * phpmailer::EncodeHeader, line 1156), because that possible
     * break chinese chars.
     *
     * @param   string  $str
     * @param   string  $encoding
     * @return  string
     */
    public function encode($str, $encoding = 'utf-8')
    {
        return "=?" . $encoding . "?B?" . base64_encode($str) . "?=";
    }
}
