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
     * Download content as a file
     *
     * @param   string  $content    Content to download
     * @param   string  $filename   Download file name, send to client, not path on server.
     * @param   string  $mime       Mime type of file
     * @return  boolean
     */
    public static function download(
        $content,
        $filename = '',
        $mime = 'application/force-download'
    ) {
        // Use timestamp as filename if not provide
        if (empty($filename)) {
            list($usec, $sec) = explode(' ', microtime());
            $usec = substr(strval($usec), 2, 3);
            $filename = $sec . $usec;
        }

        $filepath = sys_get_temp_dir();
        if ('/' != substr($filepath, -1)) {
            $filepath .= '/';
        }

        // Then got full path of tmp file
        $tmpfilename = $filepath . $filename;

        file_put_contents($tmpfilename, $content);
        $result = self::downloadFile($tmpfilename, $filename, $mime);

        unlink($tmpfilename);
        return $result;
    }


    /**
     * Download a file
     *
     * @param   string  $filepath   Full path to download file.
     * @param   string  $filename   Download file name, send to client, not path on server.
     * @param   string  $mime       Mime type of file
     * @return  boolean
     */
    public static function downloadFile(
        $filepath,
        $filename = '',
        $mime = 'application/force-download'
    ) {
        // Check and fix parameters
        if (!is_file($filepath) || !is_readable($filepath)) {
            return false;
        }

        // If no client filename given, use original name
        if (empty($filename)) {
            $filename = basename($filepath);
        }

        // Begin writing headers
        header("Cache-Control:");
        header("Cache-Control: public");

        //Use the switch-generated Content-Type
        header("Content-Type: $mime");

        // Treat IE bug with multiple periods/dots in filename
        // eg: setup.abc.exe becomes setup[1].abc.exe
        if ('trident' == self::getBrowserType()) {
            // count is reference (&count) in str_replace, so can't use it.
            $filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
        }

        header("Content-Disposition: attachment; filename=\"$filename\"");

        header("Accept-Ranges: bytes");

        // Read temp file & output
        $size = filesize($filepath);
        $size_downloaded = 0;   // Avoid infinite loop
        $size_step = 1024 * 64;  // Control download speed

        $fp = fopen($filepath, 'rb');
        // Start buffered download
        // Reset time limit for big files
        set_time_limit(0);
        while (!feof($fp) && ($size > $size_downloaded)) {
            print(fread($fp, $size_step));
            $size_downloaded += $size_step;
        }

        fclose($fp);

        return true;
    }


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
