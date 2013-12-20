<?php
namespace Fwlib\Util;

use Fwlib\Util\AbstractUtilAware;

/**
 * Ip util
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2006-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2006-07-03
 */
class Ip extends AbstractUtilAware
{
    /**
     * Convert ip from hex string
     *
     * For ipv4 only.
     *
     * @param   string  $hex
     * @return  string
     */
    public static function fromHex($hex)
    {
        $ip = '';
        if (8 == strlen($hex)) {
            $ip .= hexdec(substr($hex, 0, 2)) . '.';
            $ip .= hexdec(substr($hex, 2, 2)) . '.';
            $ip .= hexdec(substr($hex, 4, 2)) . '.';
            $ip .= hexdec(substr($hex, 6, 2));
        }
        return $ip;
    }


    /**
     * Convert ip to hex string
     *
     * For ipv4 only.
     *
     * @param   string  $ip
     * @return  string
     */
    public static function toHex($ip = '')
    {
        $hex = '';

        // @codeCoverageIgnoreStart
        if (empty($ip)) {
            $ip = $this->utilContainer->get('HttpUtil')->getClientIp();
        }
        // @codeCoverageIgnoreEnd

        if (false == ip2long($ip)) {
            return '';
        } else {
            $part = explode('.', $ip);
            if (4 != count($part)) {
                // @codeCoverageIgnoreStart
                return '';
                // @codeCoverageIgnoreEnd
            } else {
                for ($i = 0; $i <= count($part)-1; $i ++) {
                    $hex .= substr('0' . dechex($part[$i]), -2);
                }
            }
        }
        return $hex;
    }
}
