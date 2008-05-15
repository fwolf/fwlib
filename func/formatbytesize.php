<?php
/**
 * @package		fwolflib
 * @copyright	Copyright 2006, Fwolf
 * @author		Fwolf, fwolf.aide@gmail.com
 * @since		2006-09-27
 * @version		$Id$
 */

/**
 * Convert variant byte size to human readable format string
 * @param	long	$size	Size byte
 * @param	int		$decimal_place	How many decimal place to be returned
 * @return	string
 */
function FormatByteSize($size, $decimal_place=0)
{
	if (1024 >= $size)
		return(round($size, $decimal_place) . 'B');

	$ranks = array('B', 'K', 'M', 'G', 'T');
	$i = 0;	//point to which rank to use
	while ((1024 < $size) && (3 > $i))
	{
		$size /= 1024;
		$i ++;
	}
	return(round($size, $decimal_place) . $ranks[$i]);
} // end of function FormatByteSize

?>