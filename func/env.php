<?php
/**
 * Functions about runtime environment and server env variant
 *
 * Original is_cli.php and nixos.php merged in this.
 * @package		fwolflib
 * @copyright	Copyright 2006-2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib@gmail.com>
 * @since		2006-07-08
 * @version		$Id$
 */


/**
 * Force page visit through https only
 */
function ForceHttps() {
	if (!isset($_SERVER['HTTPS']) || 'on' != $_SERVER['HTTPS']) {
		$s = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		header("Location: $s");
	}
} // end of function ForceHttps


/**
 * Check if this program is running under cli mod, or is viewing in browser.
 *
 * Tested in nix os only
 * @return	boolean
 */
function IsCli() {
/*
	if (isset($_ENV['_']) && (('/usr/bin/php' == substr($_ENV['_'], 0, 12))
		|| ($_SERVER["SCRIPT_FILENAME"] == $_ENV['_'])))    //chmod +x xxx.php and run itself
*/
	if (!empty($_ENV['_']))
		$is_cli = true;
	else
		$is_cli = false;
	return($is_cli);
} // end of func IsCli


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


/**
 * Generate path from script to project root - P2R
 *
 * Can use in both cli mod and www mod.
 *
 * <code>
 * define('P2R', P2r('relate_path_to_proj_root'))
 * </code>
 * @param	string	$path	Relate path from root to here, start with no '/',
 * 							Better end with '/'.
 * @return	string
 */
function P2r($path)
{
	$s = '';
	if (true == IsCli())
		$s = dirname($_SERVER['SCRIPT_NAME']) . '/./' . $path;
	else
		$s = './' . $path;

	return $s;
} // end of func P2r
?>
