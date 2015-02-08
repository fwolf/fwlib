<?php
namespace Fwlib\Util;

/**
 * Http util
 *
 * @codeCoverageIgnore
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 *
 * @copyright   Copyright 2006-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpUtil extends AbstractUtilAware
{
    /**
     * Clear all session content, but still keep it started
     */
    public function clearSession()
    {
        $_SESSION = array();
    }


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
            list($msec, $sec) = explode(' ', microtime());
            $msec = substr(strval($msec), 2, 3);
            $filename = $sec . $msec;
        }

        $filePath = sys_get_temp_dir();
        if ('/' != substr($filePath, -1)) {
            $filePath .= '/';
        }

        // Then got full path of tmp file
        $tmpFileName = $filePath . $filename;

        file_put_contents($tmpFileName, $content);
        $result = $this->downloadFile($tmpFileName, $filename, $mime);

        unlink($tmpFileName);
        return $result;
    }


    /**
     * Download a file
     *
     * @codeCoverageIgnore
     *
     * @param   string  $filePath   Full path to download file.
     * @param   string  $filename   Download file name, send to client, not path on server.
     * @param   string  $mime       Mime type of file
     * @return  boolean
     */
    public function downloadFile(
        $filePath,
        $filename = '',
        $mime = 'application/force-download'
    ) {
        // Check and fix parameters
        if (!is_file($filePath) || !is_readable($filePath)) {
            return false;
        }

        // If no client filename given, use original name
        if (empty($filename)) {
            $filename = basename($filePath);
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
        $totalSize = filesize($filePath);
        $downloadedSize = 0;   // Avoid infinite loop
        $stepSize = 1024 * 64;  // Control download speed

        $fp = fopen($filePath, 'rb');
        // Start buffered download
        // Reset time limit for big files
        set_time_limit(0);
        while (!feof($fp) && ($totalSize > $downloadedSize)) {
            print(fread($fp, $stepSize));
            $downloadedSize += $stepSize;
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
        $arrayUtil = $this->getUtilContainer()->getArray();

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
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            // Original way: check ip from share internet
            $rs = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Using proxy ?
            $rs = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            // Another way
            $rs = $_SERVER['REMOTE_ADDR'];
        } else {
            $rs = '';
        }

        return $rs;
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
                $val = $this->getUtilContainer()->getString()
                    ->addSlashesRecursive($val);
            }

            return $val;
        } else {
            return $default;
        }
    }


    /**
     * Get host and before parts of self url
     *
     * The host name did not contains tailing '/', eg: http://www.fwolf.com
     *
     * @link http://stackoverflow.com/a/8891890/1759745
     * @return  string
     */
    public function getSelfHostUrl()
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

        return $url . $_SERVER['HTTP_HOST'];
    }


    /**
     * Get self url which user visit, with get parameter
     *
     * @param   boolean $withGetParameter   For back compatible
     * @return  string
     */
    public function getSelfUrl($withGetParameter = true)
    {
        if (!$withGetParameter) {
            return $this->getSelfUrlWithoutParameter();
        }

        $url = $this->getSelfHostUrl();

        return empty($url) ? '' : $url . $_SERVER['REQUEST_URI'];
    }


    /**
     * Get self url without get parameter
     *
     * @return  string
     */
    public function getSelfUrlWithoutParameter()
    {
        $url = $this->getSelfHostUrl();

        return empty($url) ? '' : $url . $_SERVER['SCRIPT_NAME'];
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
     * Get id of PHP session
     *
     * @return  string
     */
    public function getSessionId()
    {
        return session_id();
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
        $param = $this->getUtilContainer()->getString()
            ->addSlashesRecursive($_GET);

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


    /**
     * Set value to cookie
     *
     * Notice: Cookies will not become visible until the next loading of a
     * page that the cookie should be visible for.
     *
     * @param   string  $name
     * @param   mixed   $value
     * @param   integer $expire
     * @param   string  $path
     * @param   string  $domain
     * @param   boolean $secure
     * @param   boolean $httpOnly
     */
    public function setCookie(
        $name,
        $value,
        $expire = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = false
    ) {
        if (is_null($domain)) {
            if (is_null($path)) {
                setcookie($name, $value, $expire);

            } else {
                setcookie($name, $value, $expire, $path);
            }

        } else {
            setcookie(
                $name,
                $value,
                $expire,
                $path,
                $domain,
                $secure,
                $httpOnly
            );
        }
    }


    /**
     * Set value to session
     *
     * @param   string  $name
     * @param   mixed   $value
     * @return  HttpUtil
     */
    public function setSession($name, $value)
    {
        $_SESSION[$name] = $value;

        return $this;
    }


    /**
     * Start session if its not started
     *
     * PHP 5.4.0+ can use session_status() to check if session is started.
     * If has output before(mostly by PHPUnit), ignore session start.
     *
     * @param   boolean $forcenew
     */
    public function startSession($forcenew = false)
    {
        static $started = false;

        if (0 < strlen(session_id())) {
            $started = true;
        }

        if (false === ob_get_length()) {
            if ($forcenew && $started) {
                session_destroy();
            }

            if ($forcenew || !$started) {
                session_start();
            }

        } else {
            // Fix PHPUnit start session without id, but the id regeneration
            // not work, still need mock session method in test case.
            if (0 == strlen(session_id()) || $forcenew) {
                session_regenerate_id();
            }
        }

        $started = true;
    }


    /**
     * Unset a cookie
     *
     * @param   string  $name
     */
    public function unsetCookie($name)
    {
        $this->setCookie($name, null);

        unset($_COOKIE[$name]);
    }
}
