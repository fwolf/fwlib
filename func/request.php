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
function GetGet($var, $default='') {
	if (isset($_GET[$var]))
		$val = $_GET[$var];
	else
		$val = $default;
	return $val;
} // end of func GetGet


/**
 * Get variant from $_POST
 * @param	string	$var		Name of variant
 * @param	mixed	$default	If variant is not given, return this.
 * @return	mixed
 */
function GetPost($var, $default='') {
	if (isset($_POST[$var]))
		$val = $_POST[$var];
	else
		$val = $default;
	return $val;
} // end of func GetPost

?>
