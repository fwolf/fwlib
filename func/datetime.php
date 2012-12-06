<?php
/**
 * Provide date or time relate function
 *
 * @package     fwolflib
 * @subpackage	func.datetime
 * @copyright   Copyright 2009-2012, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func.datetime@gmail.com>
 * @since		2009-02-24
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');


/**
 * Convert str to seconds it means
 *
 * Like 1m, 20d or combined
 *
 * Solid: 1m = 30d, 1y = 365d
 *
 * @param	string	$str
 * @return	integer
 */
function StrToSec ($str) {
	if (empty($str))
		return 0;

	// All number, return directly
	if (is_numeric($str))
		return $str;

	// Parse c, y, m, w, d, h, i, s
	$str = strtolower($str);
	$str = strtr($str, array(
		'sec'		=> 's',
		'second'	=> 's',
		'seconds'	=> 's',
		'min'		=> 'i',
		'minute'	=> 'i',
		'minutes'	=> 'i',
		'hour'		=> 'h',
		'hours'		=> 'h',
		'day'		=> 'd',
		'days'		=> 'd',
		'week'		=> 'w',
		'weeks'		=> 'w',
		'month'		=> 'm',
		'months'	=> 'm',
		'year'		=> 'y',
		'years'		=> 'y',
		'century'	=> 'c',
		'centuries'	=> 'c',
	));
	$str = preg_replace(array(
		'/([+-]?\d+)s/',
		'/([+-]?\d+)i/',
		'/([+-]?\d+)h/',
		'/([+-]?\d+)d/',
		'/([+-]?\d+)w/',
		'/([+-]?\d+)m/',
		'/([+-]?\d+)y/',
		'/([+-]?\d+)c/',
	), array(
		'+$1 ',
		'+$1 * 60 ',
		'+$1 * 3600 ',
		'+$1 * 86400 ',
		'+$1 * 604800 ',
		'+$1 * 2592000 ',
		'+$1 * 31536000 ',
		'+$1 * 3153600000 ',
	), $str);
	// Fix +-
	$str = preg_replace('/\+\s*\-/', '-', $str);
	$str = preg_replace('/\-\s*\+/', '-', $str);
	$str = preg_replace('/\+\s*\+/', '+', $str);
	eval('$i_sec = ' . $str . ';');
	return $i_sec;
} // end of func StrToSec


/**
 * strtotime added remove of ':000' in sybase time(probably because dblib)
 * @param	string	$time
 * @return	integer
 */
function Strtotime1 ($time) {
	if (!empty($time)) {
		$time = preg_replace('/:\d{3}/', '', $time);
	}
	return strtotime($time);
} // end of func Strtotime1


?>
