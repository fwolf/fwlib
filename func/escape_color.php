<?php
/**
 * 转换escape颜色
 * @package     fwolflib
 * @copyright   Copyright 2006, Fwolf
 * @author      Fwolf <fwolf.aide@gmail.com>
 * @since		2006-07-17
 * @version		$Id$
 */

/*
 * covert escape color to html code
 *   escape color example: red = \\033[0;31m
 * @param 	string	$in		string to be convert, including escape color str
 * @return	string
 */
function EscapeColor($in)
{
	$colormap = array(
		'black'			=>	"\x1b[0;30m",
		'red'			=>	"\x1b[0;31m",
		'green'			=>	"\x1b[0;32m",
		'brown'			=>	"\x1b[0;33m",
		'blue'			=>	"\x1b[0;34m",
		'purple'		=>	"\x1b[0;35m",
		'cyan'			=>	"\x1b[0;36m",
		'lightgrey'	=>	"\x1b[0;37m",
		'darkgrey'		=>	"\x1b[1;30m",
		'lightred'		=>	"\x1b[1;31m",
		'lightgreen'	=>	"\x1b[1;32m",
		'yellow'		=>	"\x1b[1;33m",
		'lightblue'	=>	"\x1b[1;34m",
		'lightpurple'	=>	"\x1b[1;35m",
		'lightcyan'	=>	"\x1b[1;36m",
		'white'			=>	"\x1b[1;37m",
		'default'		=>	"\x1b[0m"
		);
	//color begin with every color set
	$color_begin = 'white';
	//color end with
	$color_end = 'default';

	//del the begin color from colormap and $in
	$in = str_replace($colormap[$color_begin], '', $in);
	unset($colormap[$color_begin]);
	
	$search = array_values($colormap);
	$replace = array_keys($colormap);

	//do some format to replacers
	for ($i=0; $i<count($replace); $i++)
	{
		$val = &$replace[$i];
		if ($val == $color_end)
			$val = '</span>';
		else
			$val = "<span style=\"color: $val;\">";
	}
		
	$str = str_replace($search, $replace, $in);
	//fix some html repeat
	$str = str_replace('</span></span>', '</span>', $str);
	return $str;
}// end of function EscapeColor

?>
