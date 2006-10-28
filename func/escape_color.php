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
 *	 Notice: $in must be htmlspecialchars() out of func, or sth will be bad,
 *	 or make sure this will not appear < or > char.
 * @param 	string	$in		string to be convert, including escape color str
 * @return	string
 */
function EscapeColor($in)
{
	//Auto fix format like ESC[01;32m (01 -> 1)
	//$in = str_replace("\x1b[01;", "\x1b[1;", $in);
	//$in = str_replace("\x1b[00;", "\x1b[0;", $in);
	//$in = str_replace("\x1b[00m", "\x1b[0m", $in);
	
	//attr:
	$in = preg_replace("/\x1b\[0*m/",
		"</span>", $in);	//0 - reset
	$in = preg_replace("/\x1b\[0?[278];([\d;]+)m/", 
		"\x1b[\\1m", $in);	//2,7,8 - dim(dark?),reverse,hidden - ignore
	$in = preg_replace("/\x1b\[0?1;([\d;]+)m/", 
		"<span style=\"font-weight: bold;\">\x1b[\\1m", $in);	//1 - bold
	$in = preg_replace("/\x1b\[0?3;([\d;]+)m/", 
		"<span style=\"text-decoration: underline;\">\x1b[\\1m", $in);	//3 - underline
	$in = preg_replace("/\x1b\[0?5;([\d;]+)m/", 
		"<span style=\"text-decoration: blink;\">\x1b[\\1m", $in);	//5 - blink
	
	//fg colors:
	$fgcolor = array(
		30	=>	'black',
		31	=>	'red',
		32	=>	'green',
		33	=>	'yellow',
		34	=>	'blue',
		35	=>	'magenta',
		36	=>	'cyan',
		37	=>	'white');
	$key = array(); $replace = array();
	foreach ($fgcolor as $k=>$v)
	{
		//$key[] = "/\x1b[${k};?(m[^\x1b]*)/";
		$key[] = "/\x1b\[${k};?(\d{0,2};?)m/";
		$replace[] = "<span style=\"color: $v;\">\x1b[\\1m";
	}
	$in = preg_replace($key, $replace, $in);

	//bg colors ??
	
	//remove un-recoginized colors
	$in = preg_replace("/\x1b\[[\d;]*m/", '', $in);
	
	//merge duplicate <span> markup
	$in = preg_replace("/<span style=\"([^>]*)\"><span style=\"([^>]*)\">/",
		"<span style=\"\\1 \\2\">", $in);

	//merge duplicate </span> markup
	$in = preg_replace("/<\/span>([^<]*)<\/span>/",
		"</span>\\1", $in);
	$in = preg_replace("/[\r\n]([^<]*)<\/span>([^<])*[\r\n]/",
		"\\1\\2", $in);
	$in = preg_replace("/[\r\n]([^<]*)<\/span>/", '\1', $in);

	//remove \t
	$in = str_replace("\x07", '', $in);
	
	//add losted </span> sometimes
	//this must run twice because the second <span> used in the 1st replace
	//will not be tract as the beginning <span> in remain search
	//it means, it was 'skipped'
	$in = preg_replace("/<span([^>]*)>([^<\n]*)<span/",
		"<span\\1>\\2</span><span", $in);
	$in = preg_replace("/<span([^>]*)>([^<\n]*)<span/",
		"<span\\1>\\2</span><span", $in);
	$in = preg_replace("/<span([^>]*)>([^<]*)[\n\r]/",
		"<span\\1>\\2</span>\n", $in);
	
	//clean escape control chars
	$escape_control = array(
		"/\x1b\\[(\\d+;)?\\d*[ABCDGJKnr]/",
		"/\x1b\\[(\\d+;)?\\d*[fH]/",
		//below is some chars which i don't know what it is .
		"/\x1b\\[\\??\\d*[hl]/",
		"/\x1b[>\\=]/",
		"/\x1b\&gt;/",
		);
	$in = preg_replace($escape_control, "", $in);
	
	//clean remain esc code	
	//$in = str_replace("\x1b[", 'ESC[', $in);
	
	return($in);
	
	/* Old useless code
	//define colors
	$colormap = array(
		'black'			=>	"\x1b[0;30m",
		'red'			=>	"\x1b[0;31m",
		'green'			=>	"\x1b[0;32m",
		'brown'			=>	"\x1b[0;33m",
		'blue'			=>	"\x1b[0;34m",
		'purple'		=>	"\x1b[0;35m",
		'cyan'			=>	"\x1b[0;36m",
		'lightgrey'		=>	"\x1b[0;37m",
		'darkgrey'		=>	"\x1b[1;30m",
		'lightred'		=>	"\x1b[1;31m",
		'lightgreen'	=>	"\x1b[1;32m",
		'yellow'		=>	"\x1b[1;33m",
		'lightblue'		=>	"\x1b[1;34m",
		'lightpurple'	=>	"\x1b[1;35m",
		'lightcyan'		=>	"\x1b[1;36m",
		'white'			=>	"\x1b[1;37m",
		'default'		=>	"\x1b[0m"
		);
	//---------------------------------------
	//define colors
	$colormap = array(
		'black'			=>	"\x1b[0;30m",
		'red'			=>	"\x1b[0;31m",
		'green'			=>	"\x1b[0;32m",
		'brown'			=>	"\x1b[0;33m",
		'blue'			=>	"\x1b[0;34m",
		'purple'		=>	"\x1b[0;35m",
		'cyan'			=>	"\x1b[0;36m",
		'lightgrey'		=>	"\x1b[0;37m",
		'darkgrey'		=>	"\x1b[1;30m",
		'lightred'		=>	"\x1b[1;31m",
		'lightgreen'	=>	"\x1b[1;32m",
		'yellow'		=>	"\x1b[1;33m",
		'lightblue'		=>	"\x1b[1;34m",
		'lightpurple'	=>	"\x1b[1;35m",
		'lightcyan'		=>	"\x1b[1;36m",
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
	*/
}// end of function EscapeColor

/*
	http://linuxgazette.net/issue65/padala.html
	
	The Color Code:     <ESC>[{attr};{fg};{bg}m

	 I'll explain the escape sequence to produce colors. The sequence to be printed or echoed to the terminal is

 	<ESC>[{attr};{fg};{bg}m

	The first character is ESC which has to be printed by pressing CTRL+V and then ESC on the Linux console or in xterm, konsole, kvt, etc. ("CTRL+V ESC" is also the way to embed an escape character in a document in vim.) Then {attr}, {fg}, {bg} have to be replaced with the correct value to get the corresponding effect. attr is the attribute like blinking or underlined etc.. fg and bg are foreground and background colors respectively. You don't have to put braces around the number. Just writing the number will suffice.

	{attr} is one of following
	0	Reset All Attributes (return to normal mode)
	1	Bright (Usually turns on BOLD)
	2 	Dim
	3	Underline
	5	Blink
	7 	Reverse
	8	Hidden
	
	{fg} is one of the following
	30	Black
	31	Red
	32	Green
	33	Yellow
	34	Blue
	35	Magenta
	36	Cyan
	37	White
	
	{bg} is one of the following
	40	Black
	41	Red
	42	Green
	43	Yellow
	44	Blue
	45	Magenta
	46	Cyan
	47	White
	
	So to get a blinking line with Blue foreground and Green background, the combination to be used should be
	
	echo "^[[5;34;42mIn color"
	which actually is very ugly. :-) Revert back with
	echo "^[0;37;40m"

*/
?>
