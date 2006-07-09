<?php
/**
 * @package		fwolflib
 * @copyright	Copyright 2006, Fwolf
 * @author		Fwolf <fwolf.aide@gmail.com>
 * @version		$Id$
 */

/**
 * 判断 ip 格式的 php 程序代码
 * @param	$str	string
 * @return	boolean
 */
function ValidateIp($str)
{
    if (!strcmp(long2ip(sprintf("%u",ip2long($ip))),$ip))
		return true;
    else
		return false;
} // end of function ValidateIp


/**
 * 老版本的检查ip函数
 * @param	$str	string
 * @return	boolean
 */
function ValidateIpOld($str)
{
    $ip = explode(".", $str);
    if (count($ip)<4 || count($ip)>4) return false;
    foreach($ip as $ip_addr) {
        if ( !is_numeric($ip_addr) ) return false;
        if ( $ip_addr<0 || $ip_addr>255 ) return false;
    }
    return true;
} // end of function ValidateIpOld

//如果简单的判断格式a.b.c.d而不考虑abcd的值的话：
//return (preg_match("/^([0-9]{1,3}\.){3}[0-9]{1,3}$/is", $str));
//不过如果需要真的ip的时候就不好玩了
?>
