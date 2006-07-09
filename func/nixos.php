<?php
/**
 * @package		fwolflib
 * @copyright	Copyright 2006, Fwolf
 * @author		Fwolf, fwolf.aide@gmail.com
 * @since		2006-07-08
 * @version		$Id$
 */


/**
 * 判断当前主机是否nix操作系统
 * @return boolean
 */
function NixOs()
{
	//采用判断执行文件全路径的第一个字符的方式
	if ('/' == $_SERVER['SCRIPT_FILENAME']{0})
		return true;
	else
		return false;
}

