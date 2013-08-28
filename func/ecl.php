<?php
/**
 * Smarty echo line, end with \n or <br /> according running mod
 * @package     fwolflib
 * @copyright   Copyright 2006-2010, Fwolf
 * @author      Fwolf <fwolf.aide@gmail.com>
 * @since		2006-10-27
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'func/env.php');


/*
 * Smarty echo line, end with \n or <br /> according running mod
 *
 * @deprecated      Use Fwlib\Util\Env::ecl()
 * @param	string	$str	Content to echo.
 * @param	int		$mode	Running mod, 0 for auto detect, 1 for web browser
 							2 for cli mode.
 * @param	int		$noecho	Return output string(1) instead of echo out(0).
 * @return	string
 */
function Ecl($str, $mode = 0, $noecho = 1) {
	if (0 == $mode)
		$mode = IsCli() ?  2 : 1;
	if (1 == $mode)
		// Add <br />
		$str = str_replace("\n", "<br />\n", $str) . "<br />\n";
	if (2 == $mode)
		// Only need to add a tail \n
		$str .= "\n";
	//$s_br = (2 == $mode) ? "\n" : "\n<br />";
	//$str .= $s_br;
	if (0 == $noecho)
		return($str);
	else
		echo($str);
	return($str);
} // end of function Ecl

?>
