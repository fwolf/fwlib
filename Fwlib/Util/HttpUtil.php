<?php
namespace Fwlib\Util;

use Fwlib\Util\AbstractUtilAware;

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
class HttpUtil extends AbstractUtilAware
{
    /**
     * Download content as a file
     *
     * @codeCoverageIgnore
     *
     * @param   string  $content    Content to download
     * @param   string  $filename   Download file name, send to client, not path on server.
     * @param   string  $mime       Mime type of file
     * @return  boolean
     */
    public function download(
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
        $result = $this->downloadFile($tmpfilename, $filename, $mime);

        unlink($tmpfilename);
        return $result;
    }


    /**
     * Download a file
     *
     * @codeCoverageIgnore
     *
     * @param   string  $filepath   Full path to download file.
     * @param   string  $filename   Download file name, send to client, not path on server.
     * @param   string  $mime       Mime type of file
     * @return  boolean
     */
    public function downloadFile(
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
        if ('trident' == $this->getBrowserType()) {
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
     * @codeCoverageIgnore
     *
     * @param   string  $agentStr   Custom agent string
     * @param   string  $default
     * @return  string
     */
    public function getBrowserType($agentStr = null, $default = 'gecko')
    {
        $arrayUtil = $this->getUtil('Array');

        // @codeCoverageIgnoreStart
        if (is_null($agentStr)) {
            $agentStr = $arrayUtil->getIdx($_SERVER, 'HTTP_USER_AGENT');
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
    public function getClientIp()
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


    /**
     * Get variant from $_COOKIE
     *
     * @codeCoverageIgnore
     *
     * @param   string  $var
     * @param   mixed   $default
     * @return  mixed
     */
    public function getCookie($var, $default = null)
    {
        return $this->getRequest($_COOKIE, $var, $default);
    }


    /**
     * Get variant from $_GET
     *
     * @codeCoverageIgnore
     *
     * @param   string  $var
     * @param   mixed   $default
     * @return  mixed
     */
    public function getGet($var, $default = null)
    {
        return $this->getRequest($_GET, $var, $default);
    }


    /**
     * Get variant from $_POST
     *
     * @codeCoverageIgnore
     *
     * @param   string  $var
     * @param   mixed   $default
     * @return  mixed
     */
    public function getPost($var, $default = null)
    {
        return $this->getRequest($_POST, $var, $default);
    }


    /**
     * Get variant from $_REQUEST
     *
     * @codeCoverageIgnore
     *
     * @param   array   $request    $_REQUEST, include $_GET/$_POST etc...
     * @param   string  $var        Name of variant
     * @param   mixed   $default    If variant is not given, return this
     * @return  mixed
     */
    public function getRequest(&$request, $var, $default = null)
    {
        if (isset($request[$var])) {
            $val = $request[$var];

            // Deal with special chars in parameters
            // magic_quotes_gpc is deprecated from php 5.4.0
            if (version_compare(PHP_VERSION, '5.4.0', '>=')
                || !get_magic_quotes_gpc()
            ) {
                $val = $this->getUtil('StringUtil')->addSlashesRecursive($val);
            }

            return $val;
        } else {
            return $default;
        }
    }


    /**
     * Get self url which user visit
     *
     * @codeCoverageIgnore
     *
     * @param   boolean $withGetParam   Include get param in url, default yes
     * @return  string
     * @link http://stackoverflow.com/a/8891890/1759745
     */
    public function getSelfUrl($withGetParam = true)
    {
        if (empty($_SERVER['HTTP_HOST'])) {
            return '';
        }

        if (!empty($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) {
            $ssl = true;
        } else {
            $ssl = false;
        }
        $url = ($ssl) ? 'https' : 'http';
        $url .= '://';

        $url .= $_SERVER['HTTP_HOST'] .
            (($withGetParam) ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME']);

        return $url;
    }


    /**
     * Get variant from $_SESSION，will also rewrite SESSION to keep it
     *
     * @codeCoverageIgnore
     *
     * @param   string  $var
     * @param   mixed   $default
     * @return  mixed
     */
    public function getSession($var, $default = null)
    {
        $_SESSION[$var] = $this->getRequest($_SESSION, $var, $default);

        return $_SESSION[$var];
    }


    /**
     * Get and return modified url param
     *
     * If $k is string, then $v is string too and means add $k=$v.
     * if $k is array, then $v is array to, and k-v/values in $k/$v is
     * added/removed to/from url param.
     *
     * @codeCoverageIgnore
     *
     * @param   mixed   $k          Key of url param, or array of k/v to add
     * @param   mixed   $v          Val of url param, or array of key to remove
     * @param   boolean $withSelfUrl    If true, return value include self url
     * @return  string              '?' and '&' included.
     */
    public function getUrlParam(
        $k = null,
        $v = null,
        $withSelfUrl = false
    ) {
        $param = $this->getUtil('StringUtil')->addSlashesRecursive($_GET);

        // $k is string
        if (is_string($k) && !empty($k)) {
            $param[addslashes($k)] = addslashes($v);
        } else {
            // Add
            if (is_array($k) && !empty($k)) {
                foreach ($k as $key => $val) {
                    $param[addslashes($key)] = addslashes($val);
                }
            }

            // Remove
            if (!empty($v)) {
                foreach ((array)$v as $val) {
                    unset($param[$val]);
                }
            }
        }

        // Combine param
        $s = '';
        foreach ((array)$param as $key => $val) {
            $s .= '&' . $key . '=' . $val;
        }
        if (!empty($s)) {
            $s{0} = '?';
        }

        // Add self url
        if (true == $withSelfUrl) {
            $s = $this->getSelfUrl(false) . $s;
        }

        return $s;
    }


    /**
     * Get url plan from url or self
     *
     * eg: http://www.google.com/, plan = http
     *
     * @param   string  $url    Default: self url
     * @return  string
     */
    public function getUrlPlan($url = '')
    {
        if (empty($url)) {
            $url = $this->getSelfUrl();
        }

        $i = preg_match('/^(\w+):\/\//', $url, $ar);
        if (1 == $i) {
            return strtolower($ar[1]);
        } else {
            return '';
        }
    }
}
