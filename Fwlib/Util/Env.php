<?php
namespace Fwlib\Util;


/**
 * Runtime or server environment
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2006-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2006-07-08
 */
class Env
{
    /*
     * Smart 'echo line', end with \n or <br /> according to run mod
     *
     * @codeCoverageIgnore
     *
     * @param   array   $str    Content to echo
     * @param   boolean $noecho Do not print
     * @return  string
     */
    public static function ecl($str = '', $noecho = false)
    {
        if (self::isCli()) {
            $lineEnding = "\n";
        } else {
            $lineEnding = "<br />\n";
        }

        if (is_array($str)) {
            $rs = '';
            foreach ($str as $v) {
                $rs .= self::ecl($v, $noecho);
            }
            return $rs;
        }

        // Replace line ending in str
        $str = preg_replace('/[\r\n]/', $lineEnding, trim($str));

        // Add new line
        $str .= $lineEnding;

        if (!$noecho) {
            echo $str;
        }

        return $str;
    }


    /**
     * Force page visit through https only
     *
     * @codeCoverageIgnore
     */
    public static function forceHttps()
    {
        if (!isset($_SERVER['HTTPS']) || 'on' != $_SERVER['HTTPS']) {
            $s = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $s);
        }
    }


    /**
     * Check if is running under cli mod
     *
     * @codeCoverageIgnore
     *
     * @return  boolean
     */
    public static function isCli()
    {
        return 'cli' == PHP_SAPI;
    }


    /**
     * Check if is running in *nix host
     *
     * @codeCoverageIgnore
     *
     * @return boolean
     */
    public static function isNixOs()
    {
        return 'Windows' != PHP_OS;
    }
}
