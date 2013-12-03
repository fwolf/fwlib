<?php
namespace Fwlib\Util;

use Fwlib\Util\Env;

/**
 * Validator
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2006-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2006-07-09
 */
class Validator
{
    /**
     * Validate email address
     *
     * @link http://www.linuxjournal.com/article/9585
     * @param   string  $email
     * @return  boolean
     */
    public static function email($email)
    {
        $valid = true;

        $atIndex = strrpos($email, '@');
        if (false === $atIndex) {
            return false;
        }

        $domain = substr($email, $atIndex + 1);
        $local = substr($email, 0, $atIndex);
        $localLen = strlen($local);
        $domainLen = strlen($domain);

        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $valid = false;
        } elseif ($domainLen < 1 || $domainLen > 255) {
            // domain part length exceeded
            $valid = false;
        } elseif ($local[0] == '.' || $local[$localLen-1] == '.') {
            // local part starts or ends with '.'
            $valid = false;
        } elseif (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $valid = false;
        } elseif (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $valid = false;
        } elseif (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $valid = false;
        } elseif (!preg_match(
            '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
            str_replace("\\\\", "", $local)
        )) {
            // character not valid in local part unless
            // local part is quoted
            if (!preg_match(
                '/^"(\\\\"|[^"])+"$/',
                str_replace("\\\\", "", $local)
            )) {
                $valid = false;
            }
        }

        // @codeCoverageIgnoreStart

        // :NOTICE: Some network provider will return fake A record if
        // a dns query return fail, usually disp some ads.
        // So we only check MX record.
        if ($valid && Env::isNixOs() &&
            !checkdnsrr($domain, 'MX')
        ) {
            $valid = false;
        }

        // @codeCoverageIgnoreEnd

        return $valid;
    }


    /**
     * Validate ipv4
     *
     * @param   string  $ip
     * @return  boolean
     */
    public static function ipv4($ip)
    {
        if (!strcmp(long2ip(sprintf("%u", ip2long($ip))), $ip)) {
            return true;
        } else {
            return false;
        }
    }
}
