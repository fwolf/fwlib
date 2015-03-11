<?php
namespace Fwlib\Util;

/**
 * Http util
 *
 * Notice: For session relate operate, use
 * {@see \Fwlib\Auth\SessionHandler\PhpSession}.
 *
 *
 * PHP 5.4 removed get_magic_quotes_gpc(), so we do not add slashes to
 * requests anymore.
 *
 * If still want magic quotes, consider {@see filter_input()} with
 * FILTER_SANITIZE_MAGIC_QUOTES filter, this function is also wrapped here as
 * {@see filterInput()}, same with empty filter in default.
 *
 * DO remember to addslashes to them when not using PDO to access DB, or use
 * proper filter.
 *
 *
 * @codeCoverageIgnore
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.Superglobals)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @copyright   Copyright 2006-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpUtil
{
    use UtilContainerAwareTrait;


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
     * Get input via filter_input() function
     *
     * Compare with original function, added default value.
     *
     * @param   int       $type
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function filterInput(
        $type,
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    ) {
        $result = filter_input($type, $name, $filter, $options);

        if (is_null($result)) {
            $result = $default;
        }

        return $result;
    }


    /**
     * Get input array via filter_input_array() function
     *
     * Compare with original function, added filter replication; always return
     * array, even empty.
     *
     * @param   int       $type
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function filterInputArray($type, $definition, $addEmpty = true)
    {
        $map = [
            INPUT_GET    => $_GET,
            INPUT_POST   => $_POST,
            INPUT_COOKIE => $_COOKIE,
            INPUT_SERVER => $_SERVER,
            INPUT_ENV    => $_ENV,
        ];
        if (!is_array($definition)) {
            $keys = array_keys($map[$type]);

            if (!empty($keys)) {
                $definition = array_fill_keys($keys, $definition);
            }
        }

        $result = filter_input_array($type, $definition, $addEmpty);

        $result = is_null($result) ? [] : $result;

        return $result;
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

        $arAgent = [
            'AppleWebKit'   => 'webkit',
            'Trident'       => 'trident',
            'Gecko'         => 'gecko',
        ];

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
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function getCookie(
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    ) {
        return $this->filterInput(
            INPUT_COOKIE,
            $name,
            $default,
            $filter,
            $options
        );
    }


    /**
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function getCookies($definition = FILTER_DEFAULT, $addEmpty = true)
    {
        return $this->filterInputArray(INPUT_COOKIE, $definition, $addEmpty);
    }


    /**
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function getGet(
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    ) {
        return $this->filterInput(
            INPUT_GET,
            $name,
            $default,
            $filter,
            $options
        );
    }


    /**
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function getGets($definition = FILTER_DEFAULT, $addEmpty = true)
    {
        return $this->filterInputArray(INPUT_GET, $definition, $addEmpty);
    }


    /**
     * @param   string    $name
     * @param   mixed     $default Default value if name is not found in input
     * @param   int       $filter
     * @param   int|array $options
     * @return  string|int
     */
    public function getPost(
        $name,
        $default = null,
        $filter = FILTER_DEFAULT,
        $options = null
    ) {
        return $this->filterInput(
            INPUT_POST,
            $name,
            $default,
            $filter,
            $options
        );
    }


    /**
     * @param   array|int $definition
     * @param   bool      $addEmpty
     * @return  array
     */
    public function getPosts($definition = FILTER_DEFAULT, $addEmpty = true)
    {
        return $this->filterInputArray(INPUT_POST, $definition, $addEmpty);
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
     * Get and return modified url param
     *
     * If $k is string, then $v is string too and means add $k=$v.
     * if $k is array, then $v is array to, and k-v/values in $k/$v is
     * added/removed to/from url param.
     *
     * Notice: Use UrlGenerator instead.
     *
     * @codeCoverageIgnore
     *
     * @param   string|array $k           Key of url param,
     *                                    or array of keys/values to add
     * @param   string|array $v           Value of url param,
     *                                    or array of keys to remove
     * @param   boolean      $fullUrl     Include 'http://...' part if true
     * @return  string                    '?' and '&' included.
     */
    public function getUrlParam(
        $k = null,
        $v = null,
        $fullUrl = false
    ) {
        $params = $this->getUtilContainer()->getString()
            ->addSlashesRecursive($_GET);

        // $k is string
        if (is_string($k) && !empty($k)) {
            $params[addslashes($k)] = addslashes($v);

        } else {
            // Add
            if (!empty($k)) {
                $k = array_map('addslashes', $k);
                foreach ($k as $key => $value) {
                    $params[$key] = $value;
                }
            }

            // Remove
            if (!empty($v)) {
                $v = (array)$v;
                $params = array_diff_key($params, array_fill_keys($v, null));
            }
        }

        // Combine param
        $s = '';
        foreach ((array)$params as $key => $val) {
            $s .= '&' . $key . '=' . $val;
        }
        if (!empty($s)) {
            $s{0} = '?';
        }

        // Add self url
        if ($fullUrl) {
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
     * Pick values from all get parameters
     *
     * @param   string[]    $keys
     * @param   boolean     $noEmpty
     * @param   callable    $callback
     * @return  string[]
     */
    public function pickGets(array $keys, $noEmpty = false, $callback = null)
    {
        $arrayUtil = $this->getUtilContainer()->getArray();

        $params = $this->getGets();

        return $arrayUtil->pick($params, $keys, $noEmpty, $callback);
    }


    /**
     * Pick values from all post parameters
     *
     * @param   string[]    $keys
     * @param   boolean     $noEmpty
     * @param   callable    $callback
     * @return  string[]
     */
    public function pickPosts(array $keys, $noEmpty = false, $callback = null)
    {
        $arrayUtil = $this->getUtilContainer()->getArray();

        $params = $this->getPosts();

        return $arrayUtil->pick($params, $keys, $noEmpty, $callback);
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
