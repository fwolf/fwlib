<?php
namespace Fwlib\Util;

use Fwlib\Util\AbstractUtilAware;

/**
 * Ip util
 *
 * @copyright   Copyright 2006-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
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
    public function fromHex($hex)
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
    public function toHex($ip = '')
    {
        $hex = '';

        // @codeCoverageIgnoreStart
        if (empty($ip)) {
            $ip = $this->getUtil('HttpUtil')->getClientIp();
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