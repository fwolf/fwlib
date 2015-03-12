<?php
namespace Fwlib\Util\Common;

use Fwlib\Util\UtilContainerAwareTrait;

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
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.Superglobals)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @copyright   Copyright 2006-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class HttpUtil
{
    use FilterInputTrait;
    use UtilContainerAwareTrait;


    /**
     * Download content as a file
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
     * @return  string
     * @link http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
     */
    public function getClientIp()
    {
        // Sort key by prior order
        $definition = array_fill_keys(
            [
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'REMOTE_ADDR',
            ],
            [
                'filter' => FILTER_DEFAULT, // Or FILTER_VALIDATE_IP ?
            ]
        );

        $ips = $this->filterInputArray(INPUT_SERVER, $definition, false);

        return empty($ips) ? '' : array_shift($ips);
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
     * Get plan and host parts of self url
     *
     * Without request uri and query string.
     *
     * The host name did not contains tailing '/', eg: http://www.fwolf.com
     *
     * @link http://stackoverflow.com/a/8891890/1759745
     * @return  string
     */
    public function getSelfHostUrl()
    {
        $envUtil = $this->getUtilContainer()->getEnv();

        $host = $envUtil->getServer('HTTP_HOST');
        if (empty($host)) {
            return '';
        }

        $url = ($this->isHttps() ? 'https' : 'http') . '://' . $host;

        return $url;
    }


    /**
     * Get self url, with or without query string
     *
     * @param   boolean $withQueryString For back compatible
     * @return  string
     */
    public function getSelfUrl($withQueryString = true)
    {
        if (!$withQueryString) {
            return $this->getSelfUrlWithoutQueryString();
        }

        $url = $this->getSelfHostUrl();

        $envUtil = $this->getUtilContainer()->getEnv();

        return empty($url) ? '' : $url . $envUtil->getServer('REQUEST_URI');
    }


    /**
     * Get self url without query string
     *
     * Old name: getSelfUrlWithoutParameter()
     *
     * @return  string
     */
    public function getSelfUrlWithoutQueryString()
    {
        $url = $this->getSelfHostUrl();

        $envUtil = $this->getUtilContainer()->getEnv();

        return empty($url) ? '' : $url . $envUtil->getServer('SCRIPT_NAME');
    }


    /**
     * Get and return modified url param
     *
     * If $k is string, then $v is string too and means add $k=$v.
     * if $k is array, then $v is array to, and k-v/values in $k/$v is
     * added/removed to/from url param.
     *
     * Notice: Will not addslashes anymore.
     *
     * Notice: Use UrlGenerator instead.
     * @see \Fwlib\Mvc\UrlGenerator
     * @deprecated
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
        $params = $this->getGets();

        // $k is string
        if (is_string($k) && !empty($k)) {
            $params[$k] = $v;

        } else {
            // Add
            if (!empty($k)) {
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
        $url = '';
        foreach ($params as $key => $val) {
            $url .= '&' . $key . '=' . $val;
        }
        if (!empty($url)) {
            $url{0} = '?';
        }

        // Add self url
        if ($fullUrl) {
            $url = $this->getSelfUrlWithoutQueryString() . $url;
        }

        return $url;
    }


    /**
     * Get url plan from url or self
     *
     * eg: http://domain.tld/, plan = http
     *
     * @param   string  $url    Default: self url
     * @return  string          Always lower cased
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
     * Is current using https:// protocol ?
     *
     * @return  bool
     */
    public function isHttps()
    {
        $envUtil = $this->getUtilContainer()->getEnv();

        $plan = $envUtil->getServer('HTTPS');

        return 'on' == $plan;
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
