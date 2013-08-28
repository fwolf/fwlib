<?php
namespace Fwlib\Util;

use Fwlib\Util\ArrayUtil;

/**
 * Http util
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Util
 * @copyright   Copyright 2006-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2006-07-03
 */
class HttpUtil
{
    /**
     * User browser type
     *
     * Type is kernel of browser: gecko/trident/webkit
     * @link https://en.wikipedia.org/wiki/Web_browser_engine
     * @link http://www.useragentstring.com/pages/Browserlist/
     *
     * @param   string  $agentStr   Custom agent string
     * @param   string  $default
     * @return  string
     */
    public static function getBrowserType($agentStr = null, $default = 'gecko')
    {
        // @codeCoverageIgnoreStart
        if (is_null($agentStr)) {
            $agentStr = ArrayUtil::getIdx($_SERVER, 'HTTP_USER_AGENT');
        }
        // @codeCoverageIgnoreEnd

        if (empty($agentStr)) {
            return $default;
        }

        $arAgent = array(
            'AppleWebKit'   => 'webkit',
            'Trident'       => 'trident',
            'Gecko'         => 'gecko',
        );

        foreach ($arAgent as $k => $v) {
            if (false !== strpos($agentStr, $k)) {
                return $v;
            }
        }

        return $default;
    }


    /**
     * Get ip of client
     *
     * @codeCoverageIgnore
     *
     * @return  string
     * @link http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
     */
    public static function getClientIp()
    {
        $s = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // Original way: check ip from share internet
            $s = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Using proxy ?
            $s = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            // Another way
            $s = $_SERVER['REMOTE_ADDR'];
        } else {
            $s = '';
        }

        return $s;
    }
}
