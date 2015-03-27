<?php
namespace Fwlib\Net;

use Fwlib\Util\UtilContainerAwareTrait;

/**
 * Helper class for easy curl usage
 *
 * @copyright   Copyright 2007-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class Curl
{
    use UtilContainerAwareTrait;


    /**
     * File to save cookie
     *
     * @var string
     */
    protected $cookieFile = '';

    /**
     * Debug mode, will log more information
     *
     * @var boolean
     */
    public $debug = false;

    /**
     * cURL handle
     *
     * @var resource
     */
    protected $handle;

    /**
     * Result read from web server
     *
     * @var string
     */
    protected $html = '';

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
     * Destructor
     */
    public function __destruct()
    {
        curl_close($this->getHandle());
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
        $handle = $this->getHandle();

        curl_setopt($handle, CURLOPT_HTTPGET, true);

        // Remove tailing '?" from url
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

        curl_setopt($handle, CURLOPT_URL, $url . $param);
        $this->html = curl_exec($handle);

        // Log
        if ($this->debug) {
            $this->log('Get: ' . $url . $param);
        }
        if (0 != curl_errno($handle)) {
            $this->log(curl_error($handle));
        }

        return $this->html;
    }


    /**
     * Get and initialize curl handle
     *
     * @return  resource
     */
    protected function getHandle()
    {
        if (is_null($this->handle)) {
            $this->handle = curl_init();
            $this->setoptCommon($this->handle);
        }

        return $this->handle;
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
        $handle = $this->getHandle();

        $i = curl_getinfo($handle, CURLINFO_HTTP_CODE);

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
        $handle = $this->getHandle();

        $s = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);

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
            $this->getUtilContainer()->getEnv()->ecl($msg);

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
     * Regex should surround wih '/', and mark match target with '()'.
     *
     * @param   string  $preg
     * @param   string  $html   If omitted, use $this->html
     * @return  string|array
     */
    public function match($preg, $html = '')
    {
        // Param check
        if (empty($preg)) {
            return null;
        }
        if (empty($html)) {
            $html = $this->html;
        }


        $matchCount = preg_match_all($preg, $html, $matches, PREG_SET_ORDER);
        if (0 == $matchCount || false === $matchCount) {
            // Got none match or Got error
            $matches = null;

        } elseif (1 == $matchCount) {
            // Got 1 match, return as string or array(2 value in 1 match)
            $matches = $matches[0];
            array_shift($matches);
            if (1 == count($matches)) {
                $matches = $matches[0];
            }

        } else {
            // Got more than 1 match return array contains string or sub-array
            foreach ($matches as &$row) {
                array_shift($row);
                if (1 == count($row)) {
                    $row = $row[0];
                }
            }
        }

        return $matches;
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
        $handle = $this->getHandle();

        curl_setopt($handle, CURLOPT_POST, true);

        // Parse param, convert array to string
        if (is_array($param)) {
            $s = '';
            foreach ($param as $key => $val) {
                $s .= urlencode($key) . '=' . urlencode($val) . '&';
            }
            $param = substr($s, 0, strlen($s) - 1);
        }

        curl_setopt($handle, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handle, CURLOPT_URL, $url);
        $this->html = curl_exec($handle);

        // Log
        if ($this->debug) {
            $this->log('Post: ' . $url . substr($param, 0, 80));
        }
        if (0 != curl_errno($handle)) {
            $this->log(curl_error($handle), 4);
        }

        return $this->html;
    }


    /**
     * Set common options using curl_setopt
     *
     * @param   resource    $handle
     * @return  static
     */
    protected function setoptCommon($handle)
    {
        $this->setoptCookieFile('');
        $this->setoptUserAgent($this->userAgent);

        curl_setopt($handle, CURLOPT_AUTOREFERER, true);
        // If got http error, report.
        curl_setopt($handle, CURLOPT_FAILONERROR, true);

        // CURLOPT_FOLLOWLOCATION cannot set when open_basedir is set.
        // Also safe_mode, which are DEPRECATED in 5.3.0 and REMOVED in 5.4.0.
        if ('' == ini_get('open_basedir')) {
            curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        }

        // Return result instead of display it.
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 300);
        curl_setopt($handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($handle, CURLOPT_MAXREDIRS, 10);
        curl_setopt($handle, CURLOPT_TIMEOUT, 300);

        // Accept all supported encoding(identity, deflate, gzip)
        // See CURLOPT_ACCEPT_ENCODING in libcurl
        // Set this to get uncompressed html content
        curl_setopt($handle, CURLOPT_ENCODING, '');

        curl_setopt($handle, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');

        return $this;
    }


    /**
     * Set cookie option
     *
     * If filename is not given, use default,
     * If file is given, use & set it as default.
     *
     * @param   string  $cookieFile
     */
    public function setoptCookieFile($cookieFile = '')
    {
        $handle = $this->getHandle();

        $this->cookieFile = $cookieFile;

        if (!empty($cookieFile) && (is_writable($cookieFile))) {
            curl_setopt($handle, CURLOPT_COOKIEFILE, $cookieFile);
            curl_setopt($handle, CURLOPT_COOKIEJAR, $cookieFile);
        }
    }


    /**
     * Set proxy option
     *
     * @param   int     $type  0-no proxy, 1-http, 2-socks5
     * @param   string  $host
     * @param   int     $port
     * @param   string  $auth  [username]:[password]
     */
    public function setoptProxy($type, $host, $port, $auth = '')
    {
        $handle = $this->getHandle();

        if (0 == $type) {
            // Some server refuse http proxy tunnel, it's useless settings.
            //curl_setopt($handle, CURLOPT_HTTPPROXYTUNNEL, false);
            curl_setopt($handle, CURLOPT_PROXY, null);

        } else {
            //curl_setopt($handle, CURLOPT_HTTPPROXYTUNNEL, true);

            curl_setopt($handle, CURLOPT_PROXY, $host);

            curl_setopt(
                $handle,
                CURLOPT_PROXYTYPE,
                (1 == $type) ? CURLPROXY_HTTP : CURLPROXY_SOCKS5
            );

            curl_setopt($handle, CURLOPT_PROXYPORT, $port);
            if (!empty($auth)) {
                curl_setopt($handle, CURLOPT_PROXYUSERPWD, $auth);
            }
        }
    }


    /**
     * Set http referrer url
     *
     * @param   string  $url
     */
    public function setoptReferrer($url = null)
    {
        $handle = $this->getHandle();

        if (!empty($url)) {
            curl_setopt($handle, CURLOPT_REFERER, $url);
        }
    }


    /**
     * Enable or disable ssl verify function
     *
     * Ssl verify is enabled by curl in default.
     *
     * @param   boolean $enable
     */
    public function setoptSslVerify($enable = true)
    {
        $handle = $this->getHandle();

        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, $enable);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, $enable);
    }


    /**
     * Set browser agent option
     *
     * @param   string  $userAgent
     */
    public function setoptUserAgent($userAgent)
    {
        $ua = [
            'ff14'  => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:14.0) Gecko/20100101 Firefox/14',
            'ie6'   => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
            'googlebot' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ];

        $handle = $this->getHandle();

        if (isset($ua[$userAgent])) {
            curl_setopt($handle, CURLOPT_USERAGENT, $ua[$userAgent]);
        } else {
            curl_setopt($handle, CURLOPT_USERAGENT, $userAgent);
        }
    }
}
