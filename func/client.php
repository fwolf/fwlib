<?php
/**
 * Funcs about client side info
 *
 * @package		fwolflib
 * @subpackage	func
 * @copyright	Copyright 2003-2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.func@gmail.com>
 * @since		2006-07-03
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');


/**
 * Convert user ip from hex string
 *
 * @param	string	$hex
 * @return	string
 * @link	http://us.php.net/uniqid
 */
function ClientIpFromHex($hex) {
	$ip = "";
	if (8 == strlen($hex)) {
		$ip .= hexdec(substr($hex ,0 ,2)) . '.';
		$ip .= hexdec(substr($hex ,2 ,2)) . '.';
		$ip .= hexdec(substr($hex ,4 ,2)) . '.';
		$ip .= hexdec(substr($hex ,6 ,2));
	}
    return $ip;
} // end of func ClientIpFromHex


/**
 * Convert user ip to hex string
 *
 * @param	string	$ip
 * @return	string
 * @link	http://us.php.net/uniqid
 */
function ClientIpToHex($ip = "") {
	$hex = "";
	if('' == $ip)
		//$ip = getenv('REMOTE_ADDR');
		$ip = GetClientIp();
	if (false == ip2long($ip))
		return '';
	else {
		$part = explode('.', $ip);
		if (4 != count($part))
			return '';
		else
			for ($i=0; $i<=count($part)-1; $i++) {
				$hex .= substr('0' . dechex($part[$i]), -2);
			}
	}
	return $hex;
} // end of func ClientIpToHex


/**
 * 检查客户端的浏览器是NS还是IE
 * @return	string
 */
function GetBrowserType()
{
	$str = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	if (false === strpos($str, 'MSIE')) {
	    return('NS');
	}
	else {
	    return('IE');
	}
} // end func GetBrowserType


/**
 * Get ip of client
 *
 * @return	string
 * @link http://roshanbh.com.np/2007/12/getting-real-ip-address-in-php.html
 */
function GetClientIp() {
	$s = '';

	// Original way: check ip from share internet
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$s = $_SERVER['HTTP_CLIENT_IP'];
	}
	// Using proxy ?
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$s = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	// Another way
	elseif (!empty($_SERVER['REMOTE_ADDR'])) {
		$s = $_SERVER['REMOTE_ADDR'];
	}
	else {
		$s = '';
	}
	return $s;
} // end of func GetClientIp


?>
