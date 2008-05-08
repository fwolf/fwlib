<?php
/**
 * Funcs about client side info
 * 
 * @package		fwolflib
 * @subpackage	func
 * @copyright	Copyright 2003-2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		2006-07-03
 * @version		$Id$
 */


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
		$ip = getenv('REMOTE_ADDR');
	if (false == ip2long($ip))
		return '';
	else {
		$part = explode('.', $ip);
		for ($i=0; $i<=count($part)-1; $i++) {
			$hex .= substr("0" . dechex($part[$i]), -2);
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
	if (false === strpos($str, 'MSIE'))
	{
	    return('NS');
	}
	else
	{
	    return('IE');
	}
} // end func GetBrowserType

?>