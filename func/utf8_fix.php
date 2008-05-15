<?php
/*
 * @package		fwolflib
 * @copyright	Copyright 2006, Fwolf
 * @author		Fwolf <fwolf.aide@gmail.com>
 * @since		2006-07-12
 * @version		$Id$
 */

/*
 * Convert string like '_D0_D0_D0' to normal string
 * These string mostly gerenated by using unicode method to read gb2312 string in some software.
 *
 * @param	string	$in				string to be fixed
 * @param	boolean	$keep_gb2312	return gb2312 string instead of utf8 str
 * @return	string
 */
function Utf8Fix($in, $keep_gb2312=false)
{
	$out = '';
	for ($i=0; $i<strlen($in); $i++)
	{
		$c = $in{$i};
		if ('_' == $c)
		{
			//begin convert
			$out .= chr(hexdec($in{$i+1} . $in{$i+2}));
			$i += 2;
		}
		else
		{
			//copy original
			$out .= $c;
		}
	}
	if (false == $keep_gb2312)
		$out = mb_convert_encoding($out, 'utf-8', 'gbk');
	return $out;
} // end of function Utf8Fix
 
?>