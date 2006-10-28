<?php
/**
 * Check if this program is running under cli mod, or is viewing in browser
 * @package     fwolflib
 * @copyright   Copyright 2006, Fwolf
 * @author      Fwolf <fwolf.aide@gmail.com>
 * @since		2006-10-27
 * @version		$Id$
 */

/*
 * Check if this program is running under cli mod, or is viewing in browser
 * return	boolean
 */
function IsCli()
{
	if (('/usr/bin/php' == $_ENV['_'])
		|| ($_SERVER["SCRIPT_FILENAME"] == $_ENV['_']))    //chmod +x xxx.php and run itself
		$is_cli = true;
	else
		$is_cli = false;
	return($is_cli);
} // end of func IsCli

?>
