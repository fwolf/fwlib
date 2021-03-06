<?php
require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'func/array.php');


/**
* A class aimed to use curl function efficiency
*
* Very useful in write a game bot, or an information thief program.
*
* @deprecated   Use Fwlib\Net\Curl
* @package    fwolflib
* @copyright  Copyright © 2007-2012, 2014, Fwolf
* @author     Fwolf <fwolf.aide+fwolflib@gmail.com>
* @since      2007-03-14
*/
class Curl extends Fwolflib {

	/**
	 * File to save cookie
	 * @var	string
	 * @access	protected
	 */
	protected $mCookiefile = '/dev/null';

	/**
	 * File to save log
	 * Set to empty string to direct echo out(default),
	 * Or set to a file to save in,
	 * Or set to /dev/null to do nothing.
	 * @var	string
	 * @access	public
	 */
	public $mLogfile = '';

	/**
	 * Result read from webserver
	 * @var	string
	 * @access	public
	 */
	public $mRs = '';

	/**
	 * Curl session resource
	 * @var	object
	 * @access	public
	 */
	public $mSh;


	/**
	 * Construct function
	 * @access public
	 */
	function __construct() {
		parent::__construct();

		$this->mSh = curl_init();
		$this->SetoptCommon();
	} // end of func __construct


	/**
	 * Destruct function
	 * @access public
	 */
	function __destruct() {
		curl_close($this->mSh);

		// Write log to file
		if (!empty($this->mLogfile))
			file_put_contents($this->mLogfile, $this->LogGet(), FILE_APPEND);

		parent::__destruct();
	} // end of func __destruct


	/**
	 * Http get content from host
	 *
	 * @param	string	$url	Host address
	 * @param	mixed	$param	Get parameter, can be string or array.
	 * @access	public
	 * @return	string
	 */
	public function Get ($url, $param = '') {
		curl_setopt($this->mSh, CURLOPT_HTTPGET, true);

		// Remove endding '?" of url
		if ('?' == substr($url, -1, 1))
			$url = substr($url, 0, strlen($url - 1));

		// Char used between url & param
		if (false === strpos($url, '?'))
			$s_linker = '?';
		else
			$s_linker = '&';

		// Parse param, join array and fix linker char with url
		if (is_array($param) && 0 < count($param))
		{
			$s = '';
			foreach ($param as $k => $v)
				$s .= "&" . urlencode($k) . '=' . urlencode($v);
			$param = $s;
		}
		if (!empty($param))
			$param{0} = $s_linker;

		//$this->Log($url . $param);
		curl_setopt($this->mSh, CURLOPT_URL, $url . $param);
		$this->mRs = curl_exec($this->mSh);

		if (0 != curl_errno($this->mSh))
			$this->Log(curl_error($this->mSh));

		return $this->mRs;
	} // end of func Get


	/**
	 * Get server return code of last curl_exec
	 * 200-ok, 404-missing file, etc...
	 * @return	int
	 */
	public function GetLastCode()
	{
		$i = curl_getinfo($this->mSh, CURLINFO_HTTP_CODE);
		return intval($i);
	} // end of func GetLastCode


	/**
	 * Get server return content type of last curl_exec
	 * text/html, image/png, etc...
	 * @return	string
	 */
	public function GetLastContentType()
	{
		$s = curl_getinfo($this->mSh, CURLINFO_CONTENT_TYPE);
		return $s;
	} // end of func GetLastContentType


	/**
	 * Match content to variables using preg
	 * To read content currectly, content parsing is nesessary
	 * Return value maybe string or array, use careful and
	 *   remind which value you use it for.
	 * @param	string	$preg
	 * @param	string	$str	If obmitted, use $this->mRs
	 * @return	mixed
	 * @see		$mRs
	 * @access	public
	 */
	public function Match($preg, $str = '')
	{
		if (empty($preg)) return '';
		if (empty($str))
			$str = &$this->mRs;
		$i = preg_match_all($preg, $str, $ar, PREG_SET_ORDER);
		if (0 == $i || false === $i)
			// Got none match or Got error
			$ar = '';
		elseif (1 == $i)
		{
			// Got 1 match, return as string or array(2 value in 1 match)
			$ar = $ar[0];
			array_shift($ar);
			if (1 == count($ar))
				$ar = $ar[0];
		}
		else
		{
			// Got more than 1 match return array contains string or sub-array
			foreach ($ar as &$row)
			{
				array_shift($row);
				if (1 == count($row))
					$row = $row[0];
			}
		}
		return $ar;
	} // end of func Match


	/**
	 * Http post content from host
	 *
	 * @param	string	$url	Host address
	 * @param	mixed	$param	Post parameter, can be string or array.
	 * @return	string
	 */
	public function Post ($url, $param = '') {
		curl_setopt($this->mSh, CURLOPT_POST, true);

		// Parse param, convert array to string
		if (is_array($param)) {
			$s = '';
			foreach ($param as $key=>$val)
				$s .= "$key=$val&";
			$param = $s;
		}

		curl_setopt($this->mSh, CURLOPT_POSTFIELDS, $param);
		curl_setopt($this->mSh, CURLOPT_URL, $url);
		$this->mRs = curl_exec($this->mSh);

		if (0 != curl_errno($this->mSh))
			$this->Log(curl_error($this->mSh), 4);

		return $this->mRs;
	} // end of func Post


	/**
	 * Set some common options using curl_setopt
	 * @access	public
	 */
	public function SetoptCommon () {
		$this->SetoptCookie();
		$this->SetoptUseragent('ff14');

		curl_setopt($this->mSh, CURLOPT_AUTOREFERER, true);
		// If got http error, report.
		curl_setopt($this->mSh, CURLOPT_FAILONERROR, true);

		// CURLOPT_FOLLOWLOCATION cannot set when open_basedir is set.
		// Also safe_mode, which are DEPRECATED in 5.3.0 and REMOVED in 5.4.0.
		if ('' == ini_get('open_basedir'))
			curl_setopt($this->mSh, CURLOPT_FOLLOWLOCATION, true);

		// Return result restead of display it.
		curl_setopt($this->mSh, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->mSh, CURLOPT_CONNECTTIMEOUT, 300);
		curl_setopt($this->mSh, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($this->mSh, CURLOPT_MAXREDIRS, 10);
		curl_setopt($this->mSh, CURLOPT_TIMEOUT, 300);

		// Accept all supported encoding(identity, deflate, gzip)
		// See CURLOPT_ACCEPT_ENCODING in libcurl
		// Set this to get uncompressed html content
		curl_setopt($this->mSh, CURLOPT_ENCODING, '');

        curl_setopt($this->mSh, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
	} // end of func SetoptCommon


	/**
	 * Set cookie option
	 *
	 * If filename is not given, use default,
	 * If file is given, use & set it as default.
	 *
	 * @param	string	$cookiefile
	 * @access	public
	 */
	public function SetoptCookie ($cookiefile = '') {
		if (!empty($cookiefile))
			$this->mCookiefile = $cookiefile;
		// /dev/null is useless cookie file, so does empty filename
		if (!empty($this->mCookiefile)
			&& ('/dev/null' != $this->mCookiefile)) {
			curl_setopt($this->mSh, CURLOPT_COOKIEFILE, $this->mCookiefile);
			curl_setopt($this->mSh, CURLOPT_COOKIEJAR, $this->mCookiefile);
		}
	} // end of func SetoptCookie


	/**
	 * Set proxy option
	 * @param	int		$ptype	0-no proxy, 1-http, 2-socks5
	 * @param	string	$phost
	 * @param	int		$pport
	 * @param	string	$pauth	[username]:[password]
	 * @access	public
	 */
	public function SetoptProxy($ptype, $phost, $pport, $pauth = '')
	{
		if (0 == $ptype) {
			// Some server refuse http proxy tunnel, it's useless settings.
			//curl_setopt($this->mSh, CURLOPT_HTTPPROXYTUNNEL, false);
		} else {
			//curl_setopt($this->mSh, CURLOPT_HTTPPROXYTUNNEL, true);
			curl_setopt($this->mSh, CURLOPT_PROXY, $phost);
			if (1 == $ptype)
				curl_setopt($this->mSh, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			if (2 == $ptype)
				curl_setopt($this->mSh, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
			curl_setopt($this->mSh, CURLOPT_PROXYPORT, $pport);
			if (!empty($pauth))
				curl_setopt($this->mSh, CURLOPT_PROXYUSERPWD, $pauth);
		}
	} // end of func SetoptProxy


	/**
	 * Set http referer url
	 * @param	string	$url
	 */
	public function SetoptReferer($url)
	{
		if (!empty($url))
			curl_setopt($this->mSh, CURLOPT_REFERER, $url);
	} // end of func SetoptReferer


	/**
	 * Enable or disable ssl verify functin
	 * Ssl verify is enabled by curl in default
	 *
	 * @param	boolean	$en		True to enable, false to disable
	 */
	public function SetoptSslverify ($en = true)	{
		if (false === $en) {
		    curl_setopt($this->mSh, CURLOPT_SSL_VERIFYPEER, false);
		    curl_setopt($this->mSh, CURLOPT_SSL_VERIFYHOST, false);
		}
	} // end of func SetoptSslverify


	/**
	 * Set browser agent option
	 * @param	string	$browser
	 * @access	public
	 */
	public function SetoptUseragent ($browser) {
		$b['ff14'] = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:14.0) Gecko/20100101 Firefox/14';
		$b['ie6'] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
		$b['googlebot'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

		if (isset($b[$browser]))
			curl_setopt($this->mSh, CURLOPT_USERAGENT, $b[$browser]);
	} // end of func SetoptUseragent


} // end of class Curl
?>
