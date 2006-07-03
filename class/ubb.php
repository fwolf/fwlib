<?php
/**
* @package      MaGod
* @copyright    Copyright 2004, Fwolf
* @author       Fwolf <fwolf001@tom.com>
*/

require_once('MaGod/MaGod.php');

/**
* Ubb类
* 完成对UBB代码的转换
*
* @package    MaGod
* @copyright  Copyright 2004, Fwolf
* @author     Fwolf <fwolf001@tom.com>
* @since      2004-02-18 22:10:55
* @access     public
* @version    $Id$
*/

class Ubb
{

	/**
	 * 从UBB的URL格式中取得链接地址
	 *
	 * @param	string	$url
	 * @access	public
	 * @return string
	 */
	function Url2Link($url)
	{
		$str = '';
		//格式一：[url=地址]名称[/url]
		$patterns = '/\[url=(\S+)\](\S+)\[\/url\]/i';
		$replace = '\\1';
		$str = preg_replace( $patterns, $replace, $url );
		if ( empty($str) )
		{
			//格式二：[url]地址[/url]
		    $patterns = '/\[url\](\S+)\[\/url\]/i';
			$replace = '\\1';
			$str = preg_replace( $patterns, $replace, $url );
		}
		return($str);
	} // end of function Url2Link


	/**
	 * 从UBB的URL格式中取得链接名称
	 *
	 * @param	string	$url
	 * @access	public
	 * @return string
	 */
	function Url2Name($url)
	{
		$str = '';
		//格式一：[url=地址]名称[/url]
		$patterns = '/\[url=(\S+)\](\S+)\[\/url\]/i';
		$replace = '\\2';
		$str = preg_replace( $patterns, $replace, $url );
		return($str);
	} // end of function Url2Name

} // end of class Ubb
?>