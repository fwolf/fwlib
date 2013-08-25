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
 * Convert sec back to str describe
 *
 * No week in result.
 *
 * @deprecated      Use Fwlib\Util\DatetimeUtil::cvtSecToStr()
 * @param	int		$i_sec
 * @param	boolean	$b_simple				If true, use ymdhis instead of word
 * @return	string
 */
function SecToStr ($i_sec, $b_simple = true) {
	if (empty($i_sec) || !is_numeric($i_sec))
		return '';

	$ar_dict = array(
		array('c', -1,	'century',	'centuries'),
		array('y', 100,	'year',		'years'),
		// 12m != 1y, can't count month in.
//		array('m', 12,	'month',	'months'),
		array('d', 365,	'day',		'days'),
		array('h', 24,	'hour',		'hours'),
		array('i', 60,	'minute',	'minutes'),
		array('s', 60,	'second',	'seconds'),
	);
	$i = count($ar_dict);
	// Loop from end of $ar_dict
	$s = '';
	while (0 < $i && 0 < $i_sec) {
		// 1. for loop, 2. got current array index
		$i --;

		// Reach top level, end loop
		if (-1 == $ar_dict[$i][1]) {
			$s = $i_sec . $ar_dict[$i][(($b_simple) ? 0
				: ((1 == $i_sec) ? 2 : 3))]
				. ' ' . $s;
			break;
		}

		$j = $i_sec % $ar_dict[$i][1];
		if (0 != $j)
			$s = $j . $ar_dict[$i][(($b_simple) ? 0
				: ((1 == $i_sec) ? 2 : 3))]
				. ' ' . $s;
		$i_sec = floor($i_sec / $ar_dict[$i][1]);
	}

	return rtrim($s);
} // end of func SecToStr


/**
 * Convert str to seconds it means
 *
 * Like 1m, 20d or combined
 *
 * Solid: 1m = 30d, 1y = 365d
 *
 * @deprecated      Use Fwlib\Util\DatetimeUtil::cvtStrToSec()
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
 *
 * @deprecated      Use Fwlib\Util\DatetimeUtil::cvtTimeFromSybase()
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
