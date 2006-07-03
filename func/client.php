<?php
/**
* @package     MaGod
* @copyright   Copyright 2003, Fwolf
* @author      Fwolf <fwolf001@tom.com>
* @version    $Id$
*/

/**
* 检查客户端的浏览器是NS还是IE
* @access   public
* @return   string
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
} // end function GetBrowserType

?>