<?php
/**
 * @package		fwolflib
 * @copyright	Copyright 2007-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib@gmail.com>
 * @since		2007-08-15
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');


/**
 * Match content using preg, return result array or '' if non-match
 * To read content currectly, content parsing is nesessary
 * Return value maybe string or array, use careful and
 *   remind which value you use it for.
 * @param	string	$preg
 * @param	string	$str
 * @param 	int		$csrts	Convert single result to str(array -> str)
 * @return	mixed
 * @access	public
 */
function RegexMatch($preg, $str = '', $csrts = true) {
	if (empty($preg) || empty($str)) return '';
	$i = preg_match_all($preg, $str, $ar, PREG_SET_ORDER);
	if (0 == $i || false === $i)
		// Got none match or Got error
		$ar = '';
	elseif (1 == $i)
	{
		// Got 1 match, return as string or array(2 value in 1 match)
		$ar = $ar[0];
		array_shift($ar);
		if (1 == count($ar) && true == $csrts)
			$ar = $ar[0];
	}
	else
	{
		// Got more than 1 match return array contains string or sub-array
		foreach ($ar as &$row)
		{
			array_shift($row);
			if (1 == count($row))
				$row = $row[0];
		}
	}
	return $ar;
} // end of func RegexMatch
?>
