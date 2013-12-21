<?php
namespace Fwlib\Net;

use Fwlib\Util\AbstractUtilAware;

/**
 * Helper class to use curl efficiency
 *
 * Very useful in write a game bot, or an information thief program.
 *
 * @codeCoverageIgnore
 *
 * @package     Fwlib\Net
 * @copyright   Copyright 2007-2013 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2007-03-14
 */
class Curl extends AbstractUtilAware
{
    /**
     * File to save cookie
     *
     * @var string
     */
    protected $cookieFile = '/dev/null';

    /**
     * Debug mode, will log more infomation
     *
     * @var boolean
     */
    public $debug = false;

    /**
     * cURL handle
     *
     * @var object
     */
    public $handle;

    /**
     * Result read from webserver
     *
     * @var string
     */
    public $html = null;

    /**
     * File to save log
     *
     * Empty for direct print out(default), or set to a valid file to save, or
     * set to /dev/null to do nothing.
     *
     * @var string
     */
    public $logFile = null;


    /**
     * User agent profile or raw string
     *
     * @var string
     */
    public $userAgent = 'ff14';


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->handle = curl_init();
        $this->setoptCommon();
    }


    /**
     * Destructor
     */
    public function __destruct()
    {
        curl_close($this->handle);
    }


    /**
     * Http get method
     *
     * @param   string  $url    Host address
     * @param   mixed   $param  Get parameter, can be string or array
     * @return  string
     */
    public function get($url, $param = null)
    {
        curl_setopt($this->handle, CURLOPT_HTTPGET, true);

        // Remove endding '?" from url
        if ('?' == substr($url, -1, 1)) {
            $url = substr($url, 0, strlen($url) - 1);
        }

        // Char used between url & param
        $linker = (false === strpos($url, '?')) ? '?' : '&';

        // Parse param, join array and fix linker char with url
        if (is_array($param) && !empty($param)) {
            $s = '';
            foreach ($param as $k => $v) {
                $s .= '&' . urlencode($k) . '=' . urlencode($v);
            }
            $param = $s;
        }
        if (!empty($param)) {
            $param{0} = $linker;
        }

        curl_setopt($this->handle, CURLOPT_URL, $url . $param);
        $this->html = curl_exec($this->handle);

        // Log
        if ($this->debug) {
            $this->log('Get: ' . $url . $param);
        }
        if (0 != curl_errno($this->handle)) {
            $this->log(curl_error($this->handle));
        }

        return $this->html;
    }


    /**
     * Get server return code of last curl_exec
     *
     * 200-ok, 404-missing file, etc
     *
     * @return  int
     */
    public function getLastCode()
    {
        $i = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
        return intval($i);
    }


    /**
     * Get server return content type of last curl_exec
     *
     * text/html, image/png, etc
     *
     * @return  string
     */
    public function getLastContentType()
    {
        $s = curl_getinfo($this->handle, CURLINFO_CONTENT_TYPE);
        return $s;
    }


    /**
     * Log curl action
     *
     * @param   string  $msg
     */
    protected function log($msg)
    {
        // Prepend msg with timestamp, append with newline
        $msg = date('[Y-m-d H:i:s] ') . $msg . PHP_EOL;

        if (empty($this->logFile)) {
            // Print
            $this->getUtil('Env')->ecl($msg);

        } elseif (is_writable($this->logFile)) {
            // Write to log file
            file_put_contents($this->logFile, $msg, FILE_APPEND);
        }

        // Invalid $this->logFile will do nothing.
    }


    /**
     * Match content to variables using preg
     *
     * Return value maybe string(for single result) or array(for multiple
     * result), use carefully and remind which value you use it for.
     *
     * @param   string  $preg
     * @param   string  $str    If obmitted, use $this->html
     * @return  mixed
     */
    public function match($preg, $str = '')
    {
        // Param check
        if (empty($preg)) {
            return null;
        }
        if (empty($str)) {
            $str = &$this->html;
        }


        $i = preg_match_all($preg, $str, $ar, PREG_SET_ORDER);
        if (0 == $i || false === $i) {
            // Got none match or Got error
            $ar = null;

        } elseif (1 == $i) {
            // Got 1 match, return as string or array(2 value in 1 match)
            $ar = $ar[0];
            array_shift($ar);
            if (1 == count($ar)) {
                $ar = $ar[0];
            }

        } else {
            // Got more than 1 match return array contains string or sub-array
            foreach ($ar as &$row) {
                array_shift($row);
                if (1 == count($row)) {
                    $row = $row[0];
                }
            }
        }

        return $ar;
    }


    /**
     * Http post method
     *
     * @param   string  $url    Host address
     * @param   mixed   $param  Post parameter, can be string or array
     * @return  string
     */
    public function post($url, $param = '')
    {
        curl_setopt($this->handle, CURLOPT_POST, true);

        // Parse param, convert array to string
        if (is_array($param)) {
            $s = '';
            foreach ($param as $key => $val) {
                $s .= urlencode($key) . '=' . urlencode($val) . '&';
            }
            $param = $s;
        }

        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $param);
        curl_setopt($this->handle, CURLOPT_URL, $url);
        $this->html = curl_exec($this->handle);

        // Log
        if ($this->debug) {
            $this->log('Post: ' . $url . substr($param, 0, 80));
        }
        if (0 != curl_errno($this->handle)) {
            $this->log(curl_error($this->handle), 4);
        }

        return $this->html;
    }


    /**
     * Set common options using curl_setopt
     */
    public function setoptCommon()
    {
        $this->setoptCookie();
        $this->setoptUserAgent($this->userAgent);

        curl_setopt($this->handle, CURLOPT_AUTOREFERER, true);
        // If got http error, report.
        curl_setopt($this->handle, CURLOPT_FAILONERROR, true);

        // CURLOPT_FOLLOWLOCATION cannot set when open_basedir is set.
        // Also safe_mode, which are DEPRECATED in 5.3.0 and REMOVED in 5.4.0.
        if ('' == ini_get('open_basedir')) {
            curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, true);
        }

        // Return result restead of display it.
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($this->handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->handle, CURLOPT_MAXREDIRS, 10);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, 300);

        // Accept all supported encoding(identity, deflate, gzip)
        // See CURLOPT_ACCEPT_ENCODING in libcurl
        // Set this to get uncompressed html content
        curl_setopt($this->handle, CURLOPT_ENCODING, '');
    }


    /**
     * Set cookie option
     *
     * If filename is not given, use default,
     * If file is given, use & set it as default.
     *
     * @param   string  $cookieFile
     */
    public function setoptCookie($cookieFile = '')
    {
        if (!empty($cookieFile)) {
            $this->cookieFile = $cookieFile;
        }

        // /dev/null can set as a dummy cookie file which does nothing.
        if (!empty($this->cookieFile) && (is_writable($this->cookieFile))) {
            curl_setopt($this->handle, CURLOPT_COOKIEFILE, $this->cookieFile);
            curl_setopt($this->handle, CURLOPT_COOKIEJAR, $this->cookieFile);
        }
    }


    /**
     * Set proxy option
     *
     * @param   int     $ptype  0-no proxy, 1-http, 2-socks5
     * @param   string  $phost
     * @param   int     $pport
     * @param   string  $pauth  [username]:[password]
     */
    public function setoptProxy($ptype, $phost, $pport, $pauth = '')
    {
        if (0 == $ptype) {
            // Some server refuse http proxy tunnel, it's useless settings.
            //curl_setopt($this->handle, CURLOPT_HTTPPROXYTUNNEL, false);
        } else {
            //curl_setopt($this->handle, CURLOPT_HTTPPROXYTUNNEL, true);

            curl_setopt($this->handle, CURLOPT_PROXY, $phost);

            curl_setopt(
                $this->handle,
                CURLOPT_PROXYTYPE,
                (1 == $ptype) ? CURLPROXY_HTTP : CURLPROXY_SOCKS5
            );

            curl_setopt($this->handle, CURLOPT_PROXYPORT, $pport);
            if (!empty($pauth)) {
                curl_setopt($this->handle, CURLOPT_PROXYUSERPWD, $pauth);
            }
        }
    }


    /**
     * Set http referer url
     *
     * @param   string  $url
     */
    public function setoptReferer($url = null)
    {
        if (!empty($url)) {
            curl_setopt($this->handle, CURLOPT_REFERER, $url);
        }
    }


    /**
     * Enable or disable ssl verify functin
     *
     * Ssl verify is enabled by curl in default.
     *
     * @param   boolean $enable
     */
    public function setoptSslverify($enable = true)
    {
        if (false === $enable) {
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, false);
        }
    }


    /**
     * Set browser agent option
     *
     * @param   string  $userAgent
     */
    public function setoptUserAgent($userAgent)
    {
        $ua = array(
            'ff14'  => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:14.0) Gecko/20100101 Firefox/14',
            'ie6'   => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
            'googlebot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        );

        if (isset($ua[$userAgent])) {
            curl_setopt($this->handle, CURLOPT_USERAGENT, $ua[$userAgent]);
        } else {
            curl_setopt($this->handle, CURLOPT_USERAGENT, $userAgent);
        }
    }
}
