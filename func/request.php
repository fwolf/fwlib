<?php
/**
 * @package		fwolflib
 * @subpackage	func
 * @copyright	Copyright 2007-2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.func@gmail.com>
 * @since		2007-01-21
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(dirname(__FILE__) . '/string.php');


/**
 * Get variant from $_COOKIE
 *
 * @deprecated      Use Fwlib\Util\HttpUtil::getCookie()
 * @param	string	$var		Name of variant
 * @param	mixed	$default	If variant is not given, return this.
 * @return	mixed
 */
function GetCookie($var, $default='')
{
	return GetRequest($_COOKIE, $var, $default);
} // end of func GetCookie


/**
 * Get variant from $_GET
 *
 * @deprecated      Use Fwlib\Util\HttpUtil::getGet()
 * @param	string	$var		Name of variant
 * @param	mixed	$default	If variant is not given, return this.
 * @return	mixed
 */
function GetGet ($var, $default='') {
	return GetRequest($_GET, $var, $default);
	/*
	if (isset($_GET[$var]))
		$val = $_GET[$var];
	else
		$val = $default;
	return $val;
	*/
} // end of func GetGet


/**
 * Get and return modified url param
 *
 * If $k is string, then $v is string too and means add $k=$v.
 * if $k is array, then $v is array to,
 * and k-v/values in $k/$v is added/removed to/from url param.
 *
 * @deprecated      Use Fwlib\Util\HttpUtil::getUrlParam()
 * @param	mixed	$k
 * @param	mixed	$v
 * @param	boolean	$b_with_url	If true, return value include self url.
 * @return	string	'?' and '&' included.
 */
function GetParam ($k = '', $v = '', $b_with_url = false) {
	$ar_param = $_GET;
	if (!empty($ar_param) && !get_magic_quotes_gpc()) {
		foreach ($ar_param as &$p) {
			$p = addslashes($p);
		}
	}

	// $k $v is string
	if (!is_array($k) && !is_array($v) && '' != $k) {
		$ar_param[addslashes($k)] = addslashes($v);
	}

	// $k $v is array
	if (is_array($k)) {
		foreach ($k as $key => $val)
			$ar_param[addslashes($key)] = addslashes($val);
		if (!is_array($v))
			$v = array($v);
		foreach ($v as $val)
			if (isset($ar_param[$val]))
				unset($ar_param[$val]);
	}

	// Combine param
	$s = '';
	if (!empty($ar_param))
		foreach ($ar_param as $k => $v)
			$s .= "&$k=$v";
	if (!empty($s))
		$s{0} = '?';

	// Add self url
	if (true == $b_with_url)
		$s = GetSelfUrl(false) . $s;

	return $s;
} // end of func GetParam


/**
 * Get variant from $_POST
 *
 * @deprecated      Use Fwlib\Util\HttpUtil::getPost()
 * @param	string	$var		Name of variant
 * @param	mixed	$default	If variant is not given, return this.
 * @return	mixed
 */
function GetPost ($var, $default='') {
	return GetRequest($_POST, $var, $default);
	/*
	if (isset($_POST[$var]))
		$val = $_POST[$var];
	else
		$val = $default;
	return $val;
	*/
} // end of func GetPost


/**
 * Get variant from $_REQUEST
 *
 * @deprecated      Use Fwlib\Util\HttpUtil::getRequest()
 * @param	array	$r		Request, $_GET/$_POST etc...
 * @param	string	$var	Name of variant
 * @param	mixed	$default	If variant is not given, return this
 * @return	mixed
 */
function GetRequest (&$r, $var, $default = null) {
	if (isset($r[$var])) {
		$val = $r[$var];

		// Deal with special chars in parameters
		// magic_quotes_gpc is deprecated from php 5.4.0
		if (version_compare(PHP_VERSION, '5.4.0', '>=')
			|| !get_magic_quotes_gpc())
			$val = AddslashesRecursive($val);
	}
	else
		$val = $default;
	return $val;
} // end of func GetRequest


/**
 * Get self url which user visit
 *
 * @deprecated      Use Fwlib\Util\HttpUtil::getSelfUrl()
 * @param	boolean	$with_get_param	// Include get param in url, default yes.
 * @return	string
 */
function GetSelfUrl($with_get_param = true) {
	if (isset($_SERVER["HTTPS"]) && 'on' == $_SERVER["HTTPS"])
		$url = 'https://';
	else
		$url = 'http://';

	$s_t = ($with_get_param) ? $_SERVER['REQUEST_URI'] : $_SERVER["SCRIPT_NAME"];

	$url .= $_SERVER["HTTP_HOST"] . $s_t;
	return $url;
} // end of func GetSelfUrl


/**
 * Get variant from $_SESSION，will also rewrite SESSION to keep it
 *
 * @deprecated      Use Fwlib\Util\HttpUtil::getSession()
 * @param	string	$var		Name of variant
 * @param	mixed	$default	If variant is not given, return this.
 * @return	mixed
 */
function GetSession($var, $default='') {
	$_SESSION[$var] = GetRequest($_SESSION, $var, $default);
	return $_SESSION[$var];
} // end of func GetSession


/**
 * Get url plan from url or self
 *
 * eg: http://www.google.com/, plan = http
 *
 * @deprecated      Use Fwlib\Util\HttpUtil::getUrlPlan()
 * @param	string	$url	Default: self url
 * @return	string
 */
function GetUrlPlan($url = '') {
	if (empty($url))
		$url = GetSelfUrl();
	$i = preg_match('/^(\w+):\/\//', $url, $ar);
	if (1 == $i)
		return $ar[1];
	else
		return '';
} // end of func GetUrlPlan

?>
