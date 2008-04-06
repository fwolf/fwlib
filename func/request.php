<?php
/**
 * @package		fwolflib
 * @copyright	Copyright 2007, Fwolf
 * @author		Fwolf <fwolf.aide@gmail.com>
 * @since		2007-01-21
 * @version		$Id$
 */

/**
 * Get variant from $_GET
 * @param	string	$var		Name of variant
 * @param	mixed	$default	If variant is not given, return this.
 * @return	mixed
 */
function GetGet($var, $default='')
{
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
 * Get variant from $_POST
 * @param	string	$var		Name of variant
 * @param	mixed	$default	If variant is not given, return this.
 * @return	mixed
 */
function GetPost($var, $default='')
{
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
 * @param	array	$r		Request, $_GET/$_POST etc...
 * @param	string	$var	Name of variant
 * @param	mixed	$default	If variant is not given, return this
 * @return	mixed
 */
function GetRequest(&$r, $var, $default = null)
{
	if (isset($r[$var]))
	{
		$val = $_POST[$var];
		if (!get_magic_quotes_gpc())
			$val = addslashes($val);
	}
	else
		$val = $default;
	return $val;
} // end of func GetRequest


/**
 * Get self url which user visit
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
?>
