<?php
/**
* @package      fwolflib
* @copyright    Copyright 2007-2008, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib@gmail.com>
*/

require_once('fwolflib/class/curl.php');
require_once('fwolflib/func/download.php');
require_once('fwolflib/func/env.php');
require_once('fwolflib/func/request.php');
require_once('fwolflib/func/url.php');

/**
 * Convert css, js, image in a html file, to save it in ONE file like mht.
 *
 * @package		fwolflib
 * @copyright	Copyright 2007-2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib@gmail.com>
 * @since		2007-04-06
 * @version		$Id$
 */
class ToDataUri extends Curl
{
	/**
	 * Cache of src already retrieved
	 * Format: url=>base64_data
	 * @var	array
	 */
	protected $mCache = array();

	/**
	 * Charset of original web page
	 * Show in info block.
	 * @var	string
	 */
	protected $mCharset = '';

	/**
	 * Running in cli mode
	 * Will echo some message directly
	 * @var	boolean
	 */
	protected $mCliMode = false;

	/**
	 * URI which got error when get
	 * Only for debug or output propose
	 * @var	array
	 */
	protected $mGetFailed = array();

	/**
	 * URI which success retrieved
	 * @var	array
	 */
	protected $mGetOk = array();

	/**
	 * Html code get from target
	 * Change is also done here, so this can be output directly
	 * @var	string
	 */
	public $mHtml = '';

	/**
	 * Information of Process, display in footer. (obsolete?)
	 * @var	string
	 * @see	$mMsg
	 */
	public	$mInfo = '';

	/**
	 * Simple reponse message
	 * Display below form
	 * @var	string
	 * @see	$mInfo
	 */
	public	$mMsg = '';

	/**
	 * Retrieve html data
	 * Auto retrieve html data by url on default, if set to false, $this->mHtml must be set manually.
	 * @var boolean
	 * @see $mHtml
	 */
	public	$mRetrieveHtml = true;

	/**
	 * Original url
	 * The web page, which contains css, js, image
	 * @var	string
	 */
	public	$mUrl = '';

	/**
	 * Baseurl of target webpage
	 * eg: http://tld.com/dir/index.html, baseurl is http://tld.com/dir/
	 * @var	string
	 */
	protected $mUrlBase = '';

	/**
	 * http or https, for Baseurl
	 * @var	string
	 */
	protected $sUrlPlan = '';


	/**
	 * Construce
	 * @param	string	$url
	 */
	public function __construct($url = '')
	{
		parent::__construct();
		$this->SetUrl($url);
		$this->SetoptSslverify(false);

		// Detect cli mode
		if (IsCli())
			$this->mCliMode = true;
	} // end of func __construct


	/**
	 * Add process information to dom, display at bottom of page
	 * @param	DOMDocument	$dom
	 */
	protected function AddInfo(&$dom)
	{
		// :TODO: original url & this script url
		// Using dom now, $this->mInfo is string, so...it's obsolete?

		$dom_info_ul = $dom->createElement('ul');
		$dom_info_ul->setAttribute('style', 'text-align: left');
		// Original url
		$a = $dom->createElement('a', htmlspecialchars($this->mUrl));
		$a->setAttribute('href', $this->mUrl);
		$li = $dom->createElement('li', "Original url: ");
		$li->appendChild($a);
		$dom_info_ul->appendChild($li);
		// Original charset
		$li = $dom->createElement('li', htmlspecialchars("Original charset: {$this->mCharset}"));
		$dom_info_ul->appendChild($li);
		// Base url
		//$a = $dom->createElement('a', htmlspecialchars($this->mUrlBase));
		//$a->setAttribute('href', $this->mUrlBase);
		//$li = $dom->createElement('li', "Baseurl: ");
		//$li->appendChild($a);
		//$dom_info_ul->appendChild($li);
		// Url of this script
		if ($this->mCliMode) {
			$li = $dom->createElement('li', "Generate using Fwolf's 'Save html all in one file' tools(cli mode php script).");
		} else {
			$a = $dom->createElement('a', "Fwolf's 'Save html all in one file' tools");
			$a->setAttribute('href', GetSelfUrl(false));
			$li = $dom->createElement('li', "Generate using: ");
			$li->appendChild($a);
		}
		$dom_info_ul->appendChild($li);
		// Generate time
		$li = $dom->createElement('li', htmlspecialchars("Generate time: " . date('Y-m-d G:i:s')));
		$dom_info_ul->appendChild($li);
		// Resources
		$i_getok = count($this->mGetOk);
		$i_getfailed = count($this->mGetFailed);
		$li = $dom->createElement('li', "Resources(" . ($i_getok + $i_getfailed) . " : √ $i_getok, × $i_getfailed): ");
		$dom_info_ul->appendChild($li);

		// Baseurl & charset has been set when processed, add resources here
		//$this->mInfo .= "Resources: <span style='cursor: hand;'>+</span>";
		//$this->mInfo .= "\n<br />√: " . implode($this->mGetOk, "\n<br />√: ");
		//$this->mInfo .= "\n<br />×: " . implode($this->mGetFailed, "\n<br />×: ");
		$span = $dom->createElement('span', "+++");
		$span->setAttribute('style', 'cursor: pointer;');
		$span->setAttribute('onclick', "javascript:obj=getElementById('fwolf_todatauri_info_resources_list');if ('none'==obj.style.display || ''==obj.style.display) {obj.style.display='block'; this.textContent='---';} else {obj.style.display='none';this.textContent='+++';}");
		$dom_info_ul->lastChild->appendChild($span);

		// Append resources detail list as sub-ol
		$dom_resources_ol = $dom->createElement('ol');
		$dom_resources_ol->setAttribute('id', 'fwolf_todatauri_info_resources_list');
		$dom_resources_ol->setAttribute('style', 'display: none;');
		foreach ($this->mGetOk as $val)
		{
			$val = htmlspecialchars($val);
			$a = $dom->createElement('a', $val);
			$a->setAttribute('href', $val);
			$li = $dom->createElement('li', '√: ');
			//$li = $dom->createElement('li', $val);
			$li->appendChild($a);
			$dom_resources_ol->appendChild($li);
		}
		foreach ($this->mGetFailed as $val)
		{
			$val = htmlspecialchars($val);
			$a = $dom->createElement('a', $val);
			$a->setAttribute('href', $val);
			$li = $dom->createElement('li', '×: ');
			//$li = $dom->createElement('li', $val);
			$li->appendChild($a);
			$dom_resources_ol->appendChild($li);
		}
		$dom_info_ul->appendChild($dom_resources_ol);
		if ($this->mCliMode)
			echo "[Done ] Resources: √: " . count($this->mGetOk) . ", ×: " . count($this->mGetFailed) . ".\n";

		// If html contents like this, it have not <body>, so we must create it
		// <html>
		// <meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
		// </html>
		$dom_body = $dom->getElementsByTagName('body');
		if (0 == $dom_body->length) {
			// There is no <body> in html, we create it
			$body = $dom->createElement('body');
			$dom->getElementsByTagName('html')->item(0)->appendChild($body);
		} else {
			$body = $dom->getElementsByTagName('body')->item(0);
		}

		$div = $dom->createElement('div');
		$div->setAttribute('id', 'fwolf_save_file_all_in_one_info');
		$div->setAttribute('style', 'clear: both;');
		$hr = $dom->createElement('hr');
		$hr->setAttribute('style', 'border: 0px; height: 1px; color: #B0C4DE; background-color: #B0C4DE;');
		$div->appendChild($hr);
		$div->appendChild($dom_info_ul);
		$body->appendChild($div);
	} // end of func AddInfo


	/**
	 * With a dom object, do changes I need
	 * Change all $tag's $attr in dom to data:URI style
	 * @param	DOMDocument	$dom	DOMDocument object
	 * @param	string	$tag
	 * @param	string	$attr
	 * @param	array	$cond	Condition, eg: type=>'text/css' for link css
	 */
	protected function DomChange(&$dom, $tag, $attr, $cond=array())
	{
		$items = $dom->getElementsByTagName($tag);
		for ($i=0; $i<$items->length; $i++)
		{
			$item = $items->item($i);

			// Check condition by element attribute
			$check = true;
			if (!empty($cond))
			{
				foreach ($cond as $k=>$v)
					if ($v != $item->getAttribute($k))
						$check = false;
			}
			// In-document js have text/javascript also, but src is empty
			if (('script' == $tag) && ('' == $item->getAttribute('src')))
				$check = false;

			// Do change
			if (true == $check)
			{
				$src = $item->getAttribute($attr);
				$src = $this->ParseUrl($src);
				// If parse failed, use original src
				if (!empty($src))
					$item->setAttribute($attr, $src);
			}
		}
	} // end of func DomChange


	/**
	 * Change embemmed style url in dom
	 * Linked style alread parse by:
	 *   $this->DomChange($dom, 'link', 'href', array('rel'=>'stylesheet'));
	 * @param	DOMDocument	$dom	DOMDocument object
	 */
	protected function DomChangeStyle(&$dom)
	{
		$items = $dom->getElementsByTagName('style');
		for ($i=0; $i<$items->length; $i++)
		{
			$item = $items->item($i);

			$src = $item->nodeValue;
			if (empty($src)) continue;

			// Example1, with @import, no url(
			// @import "mystyle.css";
			// @import "../hide2.css";
			$ar_regex[0] = "/(@import\s*\(?['\"]([^'\"\(\)\{\}]+)['\"]\s*\)?)/i";
			// Example2, with url(, recardness @import
			// url("../hide1a.css");
			// url(../hide1b.css);
			$ar_regex[1] = "/(url\s*\(['\"]?\s*([^'\"\(\)\{\}]+)['\"]?\s*\))/i";

			foreach ($ar_regex as $regex) {
				//$ar = $this->Match('/(<style[^<]+url\(\s*(\S+)\s*\)[^<]+<\/style>)/i', $src);
				$ar = $this->Match($regex, $src);
				if (!empty($ar)) {
					// Do as multi match
					if (!is_array($ar[0])) {
						$ar1 = array(0=>$ar);
						$ar = $ar1;
						unset($ar1);
					}
					// Begin loop
					foreach ($ar as $val) {
						$s = $this->ParseUrl($val[1]);
						if (!empty($s)) {
							// Use whole match to do str_replace, because url can be used multi times.
							$s = str_replace($val[1], $s, $val[0]);
							$src = str_replace($val[0], $s, $src);
						}
					}
					// Write result to dom
					$item->nodeValue = $src;
				}
			}
		}

		// Embemmed style
		// :QUESTION: Is these tags slow down treatment?
		$ar_tags = array('a', 'blockquote', 'body', 'button', 'code', 'dd', 'del', 'div', 'dl', 'dt', 'form', 'hr', 'img', 'input', 'li', 'ol', 'option', 'p', 'pre', 'q', 'select', 'small', 'span', 'strong', 'table', 'td', 'textarea', 'th', 'tr', 'ul');
		foreach ($ar_tags as $tag) {
			$items = $dom->getElementsByTagName($tag);
			$i_items = $items->length;
			for ($i=0; $i<$i_items; $i++)
			{
				$item = $items->item($i);

				$src = $item->getAttribute('style');
				if (empty($src)) continue;

				// Example2 only, with url(, recardness @import
				// url("../hide1a.css");
				// url(../hide1b.css);
				$regex = "/(url\s*\(['\"]?\s*([^'\"]+)['\"]?\s*\))/i";

				$ar = $this->Match($regex, $src);
				if (!empty($ar)) {
					// Do as multi match
					if (!is_array($ar[0])) {
						$ar1 = array(0=>$ar);
						$ar = $ar1;
						unset($ar1);
					}
					// Begin loop
					foreach ($ar as $val) {
						$s = $this->ParseUrl($val[1]);
						if (!empty($s)) {
							// Use whole match to do str_replace, because url can be used multi times.
							$s = str_replace($val[1], $s, $val[0]);
							$src = str_replace($val[0], $s, $src);
						}
					}
					// Write result to dom
					$item->setAttribute('style', $src);
				}
			}
		}
		/*
		// Example 1
		// <style type="text/css" media="screen">@import url( http://theme.cache.yo2.cn/wp-content/user_themes/37/3729/style.css );</style>
		$ar = $this->Match('/(<style[^<]+url\(\s*(\S+)\s*\)[^<]+<\/style>)/i', $this->mHtml);
		if (!empty($ar)) {
			// Do as multi match
			if (!is_array($ar[0])) {
				$ar1 = array(0=>$ar);
				$ar = $ar1;
				unset($ar1);
			}
			// Begin loop
			foreach ($ar as $val) {
				$s = $this->ParseUrl($val[1]);
				if (!empty($s)) {
					// Use whole match to do str_replace, because url can be used multi times.
					$s = str_replace($val[1], $s, $val[0]);
					$this->mHtml = str_replace($val[0], $s, $this->mHtml);
				}
			}
		}
		*/
	} // end of func DomChangeStyle


	/**
	 * Get baseurl from init get
	 * Baseurl used in get css, js, images
	 * Must execute close to the init curl_exec
	 * Baseurl not eq hostname, it may include some dir
	 * If not, crul stats will change by other get action
	 */
	protected function GetBaseUrl()
	{
		// Input URL is a dir or a file -> Use the url webserver uses
		// But still will got wrong when url like this:
		// $url = 'http://131.2.101.10/sys/phpinfo.php/aa';
		// :TODO: check what link will browser gerenate in upper situation

		// Uri need add http/https manually
		// curl_getinfo can recoginize dir/file of an address
		// so here cannot use $this->mUrl + preg_replace to compute baseurl
		$baseurl = curl_getinfo($this->mSh, CURLINFO_EFFECTIVE_URL);
		// Got the path part of url, should end with '/', exclude this:
		// http://131.2.101.10
		$baseurl = preg_replace('/(http|https)(:\/\/.+)\/[^\/]*$/i', '\1\2', $baseurl);
		// Add the missing tailing '/' in some special condition
		if ('/' != $baseurl{strlen($baseurl) - 1})
			$baseurl .= '/';
		$this->mUrlBase = $baseurl;

		// Url plan
		$this->sUrlPlan = UrlPlan($this->mUrlBase);

		$this->mInfo .= "Baseurl: $baseurl<br />\n";
		if ($this->mCliMode)
			echo "[Curl ] Baseurl: $baseurl\n";
	} // end of func GetBaseUrl


	/**
	 * Check if user input url is safe to retrieve
	 * @param	string	$url
	 * @return	boolean
	 */
	protected function IsSafe($url)
	{
		$safe = true;
		if (13 > strlen($url)) $safe = false;
		$url_http = strtolower(substr($url, 0, 8));
		if (('http://' != substr($url_http, 0, 7)) && ('https://' != $url_http))
			$safe = false;
		$hostname = preg_replace('/^(http|https):\/\/([^\/]+)\/?.*/i', '\2', $url);
		if ('localhost' == substr($hostname, 0, 9)) $safe = false;
		if ('127.0.0.1' == substr($hostname, 0, 9)) $safe = false;
		if ('2130706433' == substr($hostname, 0, 9)) $safe = false;
		if ('192.168.0.' == substr($hostname, 0, 10)) $safe = false;
		// :TODO: Can't do with my self

		if (false == $safe)
			$this->mMsg .= "目标网址不安全，不要折腾我的服务器啦～拜托(" . ip2long($hostname) . ")<br />\n";
		return $safe;
	} // end of func IsSafe


	/**
	 * Convert content html to utf8
	 * <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
	 * @see $mHtml
	 */
	protected function MbConvert()
	{
		// Find charset webpage use current
		//<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		//$ar = $this->Match('/(<meta[^;]+;[\s]*charset=(\S+)\"[^>]*>)/i');
		$ar = $this->Match('/(<meta[^>]+content=[^>]+charset=([\w\d-_]+)[\"\'][^>]*>)/i');
		$charset = '';
		// For multi charset declaration
		if ((isset($ar[0])) && (is_array($ar[0])))
			$ar = $ar[0];
		if (1 < count($ar)) {
			$charset = $ar[1];
		}
		//$charset = (1 < count($ar)) ? $ar[1] : '';
		$charset = strtolower($charset);
		// Check charset got is valid, if no, detect it
		// Discuz! error, I have no other ways to detect current encoding
		// v4.0.0, printed page:
		//<meta http-equiv="Content-Type" content="text/html; charset=CHARSET">
		if ('charset' == $charset)
		{
			$charset = mb_detect_encoding($this->mHtml, "gb2312, gbk, big5, utf-8");
			$charset = strtolower($charset);
		}
		// :THINK: Use mb_check_encoding check again?

		// Meta Content-type
		$meta = '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		if (!empty($charset)) {
			// Remove old markup <!-- charset declare deleted -->
			$this->mHtml = str_replace($ar[0], '', $this->mHtml);
			// Put meta close to head, so no non-ascii will occur before it
			$this->mHtml = preg_replace('/<head[^>]*>/i', $meta, $this->mHtml);
			if ('utf-8' != $charset) {
				$this->mHtml = mb_convert_encoding($this->mHtml, 'utf-8', $charset);
			}
			$this->mInfo .= "Original charset: $charset<br />\n";
		} else {
			// Doc has no charset meta, force added
			$charset = strtolower(mb_detect_encoding($this->mHtml));
			if ('utf-8' != $charset)
			{
				$this->mHtml = mb_convert_encoding($this->mHtml, 'utf-8', $charset);
				$this->mInfo .= "Original charset: $charset<br />\n";
			}
			//$this->mHtml = $meta . $this->mHtml;
			$this->mHtml = preg_replace('/<head[^>]*>/i', $meta, $this->mHtml);
		}

		$this->mCharset = $charset;
		if ($this->mCliMode)
			echo "[Curl ] Original charset: $charset.\n";
	} // end of func MbConvert


	/*
	 * Output - using download
	 */
	public function OutputDownload()
	{
		// Name
		$filename = preg_replace('/^(http|https):\/\/(.*)/i', '\2', $this->mUrl);
		$ar = array('/', '?', '&', ';', '=', ':');
		$filename = str_replace($ar, '_', $filename) . '.html';
		Download($this->mHtml, $filename);
	} // end of func OutputDownload


	/**
	 * Begin get webpage & parse it
	 */
	public function Parse()
	{
		if (!empty($this->mUrl))
		{
			if ($this->mCliMode)
				echo "[Curl ] Get html content from $this->mUrl ";
			$this->SetoptReferer($this->mUrl);
			if (true == $this->mRetrieveHtml)
				$this->mHtml = $this->Get($this->mUrl);
			else {
				// Do an dummy Get action, mRs is used in Match() (and/or etc...)
				$this->Get($this->mUrl);
				$this->mRs = $this->mHtml;
			}
			//$this->GetBaseUrl();
			if (0 == strlen($this->mHtml))
			{
				// Some error happen
				$this->mMsg .= curl_error($this->mSh);
				if ($this->mCliMode)
					echo "... Failed.\n";
			}
			else
			{
				if ($this->mCliMode)
					echo "... Ok.\n";
				$this->GetBaseUrl();
				// Go ahead
				$this->MbConvert();

				// Do some cleanup with html code
				$this->PreParse();

				$dom = new DOMDocument();
				// Keep original format when output
				$dom->preserveWhiteSpace = true;
				//$dom->strictErrorChecking = false;

				// :TODO: parse un-wellform html error ?
				// This way can erase some un-wellformed html error, like un-supported/un-readable chars etc.
				$this->mHtml = mb_convert_encoding($this->mHtml, 'HTML-ENTITIES', "UTF-8");
				// Seems these warning message can't be erased.
				@$dom->loadHTML($this->mHtml);
				// :TODO: If parse all relative link href, can I make a proxy ?

				// Embemmed style, modify html directly, do this 'slow' step first, or maybe with longer html string will take more time.
				$this->DomChangeStyle($dom);

				$this->DomChange($dom, 'img', 'src');
				//$this->DomChange($dom, 'link', 'href', array('rel'=>'stylesheet', 'type'=>'text/css'));
				$this->DomChange($dom, 'link', 'href', array('rel'=>'stylesheet'));
				$this->DomChange($dom, 'script', 'src', array('type'=>'text/javascript'));

				$this->AddInfo($dom);
				$this->mHtml = $dom->saveHTML();

			}
		}
	} // end of func Parse


	/**
	 * Get a url & parse it
	 * Return value is data:URI format
	 * @param	string	$url
	 * @return	string
	 */
	protected function ParseUrl($url)
	{
		if (empty($url))
			return '';
		// Uri start from http
		$src = strtolower($url);
		if (('http://' == substr($src, 0, 7)) || ('https://' == substr($src, 0, 8)))
			return $this->ParseUrl2Data($url);
		elseif ('//' == substr($src, 0, 2)) {
			// For IBM developerworks
			return $this->ParseUrl2Data($this->sUrlPlan . ':' . $url);
		} else {
			// Link baseurl with file needed to parse
			if ('/' == $url{0})
			{
				// Absolute path, compute start from host name
				$baseurl = preg_replace('/(http|https)(:\/\/[^\/]+)\/.*/i', '\1\2', $this->mUrlBase);
				$objurl = $baseurl . $url;
			}
			else
			{
				// Relative path
				$objurl = $this->mUrlBase . $url;
			}

			// Got result url, parse & return
			return $this->ParseUrl2Data($objurl);
		}
	} // end of func ParseUrl


	/**
	 * Retrieve a http object & return data:URI
	 * Return empty string when retrieve failed.
	 * @param	string	$url
	 * @return	string
	 */
	protected function ParseUrl2Data($url)
	{
		if (isset($this->mCache[$url]))
			$data = $this->mCache[$url];
		else
		{
			$rs = $this->Get($url);
			if (0 < strlen($this->mRs))
			{
				$rs_code = $this->GetLastCode();
				$rs_type = $this->GetLastContentType();

				$data = 'data:' . $rs_type . ';base64,' . base64_encode($rs);
				$this->mCache[$url] = $data;
				$this->mGetOk[] = $url;
				if ($this->mCliMode)
					echo "[" . substr('000' . strval(count($this->mGetOk) + count($this->mGetFailed)), -3) . "  ] √: $url\n";
			}
			else
			{
				// Fail
				$data = '';
				$this->mGetFailed[] = $url;
				if ($this->mCliMode)
					echo "[" . substr('000' . strval(count($this->mGetOk) + count($this->mGetFailed)), -3) . "  ] ×: $url\n";
			}
		}
		return $data;
	} // end of func ParseUrl2Data


	/**
	 * Cleanup html code before parse
	 */
	protected function PreParse() {
		// These extra xml markup can't be treat well by DOM, remove them.

		// Remove <?xml version="1.0" encoding="utf-8"..
		$this->mHtml = preg_replace('/<\?xml version=[^>]+>/i', '', $this->mHtml);
		// Remove xmlns from:
		// <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		$this->mHtml = preg_replace('/<html\s+xmlns=[^>]+>/i', '<html>', $this->mHtml);
	} // end of func PrePare


	/**
	 * Set url of web page to process
	 * @param	string	$url
	 */
	public	function SetUrl($url)
	{
		if (!empty($url) && $this->IsSafe($url))
			$this->mUrl = $url;
	} // end of func SetUrl

} // end of class ToDataUri

?>
