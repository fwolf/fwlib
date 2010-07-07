<?php
/**
 * Funcs about array
 *
 * @package		fwolflib
 * @subpackage	func
 * @copyright   Copyright 2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func@gmail.com>
 * @since		2010-01-25
 */


if (0 <= version_compare(phpversion(), '5.3.0')) {
	require_once(__DIR__ . '/string.php');
} else {
	require_once(dirname(__FILE__) . '/string.php');
}


/**
 * Read value from array.
 *
 * @param	array	$ar
 * @param	mixed	$key
 * @param	mixed	$val_default
 * @return	mixed
 */
function ArrayRead($ar, $key, $val_default = null) {
	if (isset($ar[$key]))
		$val_return = $ar[$key];
	elseif (!is_null($val_default))
		$val_return = $val_default;
	else
		$val_return = null;

    return $val_return;
} // end of func ArrayRead


/**
 * Filter an array by wildcard rules.
 *
 * Wildcard rules is a string include many part joined by ',',
 * each part can include * and ?, head by '+'(default) or '-',
 * they means find elements suit the rules in source array,
 * and add_to/remove_from result array.
 *
 * Parts operate sequence is by occur position in rules string.
 *
 * Rules example: a*, -*b, -??c, +?d*
 *
 * @param	array	$ar_srce	Source data.
 * @param	string	$rules		Wildcard rule string.
 * @return	array
 */
function FilterWildcard($ar_srce, $rules) {
	$ar_result = array();

	// Check srce ar
	if (empty($ar_srce))
		return $ar_result;

	// Read rules
	$ar_rule = explode(',', $rules);
	if (empty($ar_rule))
		return $ar_result;

	// Use rules
	foreach ($ar_rule as $rule) {
		$rule = trim($rule);
		// + or - ?
		if ('+' == $rule[0]) {
			$i_op = '+';
			$rule = substr($rule, 1);
		}
		elseif ('-' == $rule[0]) {
			$i_op = '-';
			$rule = substr($rule, 1);
		}
		else
			$i_op = '+';

		// Loop srce ar
		foreach ($ar_srce as $srce) {
			if (true == MatchWildcard($srce, $rule)) {
				// Got element to +/-
				$i = array_search($srce, $ar_result);
				if ('+' == $i_op) {
					// Add to ar if not in it.
					if (false === $i)
						$ar_result = array_merge($ar_result, array($srce));
				}
				else {
					// Remove from ar if exists.
					if (! (false === $i))
						unset($ar_result[$i]);
				}
			}
		}
	}

	return $ar_result;
} // end of func FilterWildcard


?>
