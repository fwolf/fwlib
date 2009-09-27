<?php
/**
 * Provide date or time relate function
 * @package     fwolflib
 * @copyright   Copyright 2009, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		2009-02-24
 * @version		$Id$
 */


/**
 * strtotime added remove of ':000' in sybase time(probably because dblib)
 * @param	string	$time
 * @return	integer
 */
function Strtotime1($time) {
	if (!empty($time)) {
		$time = preg_replace('/:\d{3}/', '', $time);
	}
	return strtotime($time);
} // end of func Strtotime1

?>
