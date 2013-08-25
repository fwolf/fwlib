<?php
/**
 * Funcs about array
 *
 * @package		fwolflib
 * @subpackage	func
 * @copyright   Copyright © 2010-2011, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func@gmail.com>
 * @since		2010-01-25
 */


require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'func/string.php');


/**
 * Add value to array by key, if key not exist, init with value.
 *
 * @deprecated      Use Fwlib\Util\ArrayUtil::increaseByKey()
 * @param	array	&$ar_srce
 * @param	string	$key
 * @param	mixed	$val		Default val if not assigned.
 */
function ArrayAdd (&$ar_srce, $key, $val = 1) {
	if (isset($ar_srce[$key])) {
		if (is_string($val))
			$ar_srce[$key] .= $val;
		else
			$ar_srce[$key] += $val;
	}
	else
		$ar_srce[$key] = $val;

	return $ar_srce;
} // end of func ArrayAdd


/**
 * Eval string by replace tag with array value by index
 *
 * @deprecated      Use Fwlib\Util\StringUtil::evalWithTag()
 * @param	string	$s_eval
 * @param	array	$ar		Data array, must have assoc index.
 * @return	mixed
 */
function ArrayEval ($s_eval, $ar = array()) {
	if (empty($s_eval))
		return null;
	$s_eval = trim($s_eval);

	// Replace tag with array value
	if (!empty($ar))
		foreach ($ar as $k => $v)
			$s_eval = str_replace('{' . $k . '}', $v, $s_eval);

	// Add tailing ';'
	if (';' != substr($s_eval, -1))
		$s_eval .= ';';

	$rs = eval($s_eval);

	if (is_null($rs))
		// Need add return in eval str
		$rs = eval('return ' . $s_eval);

	return $rs;
} // end of func ArrayEval


/**
 * Insert data to assigned position in srce array by assoc key.
 *
 * Can also use on numeric indexed array.
 *
 * If key in ins array already exists in srce array, according ins pos
 * and original pos of the key, the later value overwrite before one,
 * and it pos also leave as the before one. So if you can't use this
 * to move item in array forward or backward.
 *
 * @deprecated      Use Fwlib\Util\ArrayUtil::insert()
 * @param	array	&$ar_srce
 * @param	mixed	$idx		Position idx, append @ end if not found.
 * @param	array	$ar_ins		Array to insert, can have multi item.
 * @param	integer	$i_pos		-1=insert before index, 0=replace index
 * 		1=insert after index, default=1.
 * 		If abs($i_pos)>0, eg: 2 means insert after 2-1 pos after $idx.
 * 		a    b     c    d   e		Index
 * 		  -2   -1  0  1   2			Insert position by $i_pos
 * @return	array
 */
function ArrayInsert (&$ar_srce, $idx, $ar_ins, $i_pos = 1) {
	if (empty($ar_ins))
		return $ar_srce;

	// Find ins position
	$ar_key = array_keys($ar_srce);
	$i_pos_ins = array_search($idx, $ar_key, true);
	if (false === $i_pos_ins) {
		// Idx not found, append.
		foreach ($ar_ins as $k => $v)
			if (isset($ar_srce[$k]))
				$ar_srce[] = $v;
			else
				$ar_srce[$k] = $v;
		return $ar_srce;
	}

	// Chg ins position by $i_pos
	$i_pos_ins += $i_pos + (0 >= $i_pos ? 1 : 0);
	$i_cnt_srce = count($ar_srce);
	if (0 > $i_pos_ins)
		$i_pos_ins = 0;
	if ($i_cnt_srce < $i_pos_ins)
		$i_pos_ins = $i_cnt_srce;

	// Loop to gen result ar
	$ar_rs = array();
	$i_srce = -1;		// Need loop to $i_cnt_srce, not $i_cnt_srce-1
	while ($i_srce < $i_cnt_srce) {
		$i_srce ++;
		if ($i_pos_ins == $i_srce) {
			// Got insert position
			foreach ($ar_ins as $k => $v)
				// Notice: if key exists, will be overwrite.
				$ar_rs[$k] = $v;
		}

		if ($i_srce == $i_cnt_srce)
			continue;
		// Insert original data
		$k = $ar_key[$i_srce];
		$ar_rs[$k] = $ar_srce[$k];
	}
	// Pos = 0, replace
	if (0 == $i_pos)
		unset($ar_rs[$ar_key[$i_pos_ins - 1]]);

	$ar_srce = $ar_rs;
	return $ar_srce;
} // end of func ArrayInsert


/**
 * Read value from array.
 *
 * @deprecated      Use Fwlib\Util\ArrayUtil::getIdx(), getEdx()
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
 * Sort array by one of its 2lv keys, and maintain assoc index.
 *
 * @deprecated      Use Fwlb\Util\ArrayUtil::sortByLevel2() or array_multisort()
 * @param	array	&$ar_srce	Array to be sort
 * @param	mixed	$key
 * @param	mixed	$b_asc		True = asc/false = desc, or use str.
 * @param	mixed	$joker		Use when val of key isn't set.
 * @return	array
 */
function ArraySort (&$ar_srce, $key, $b_asc = true, $joker = '') {
	$ar_val = array();
	foreach ($ar_srce as $k => $v)
		$ar_val[$k] = isset($v[$key]) ? $v[$key] : $joker;

	if (true === $b_asc || 'asc' == $b_asc)
		asort($ar_val);
	else
		arsort($ar_val);

	// Got currect order, write back.
	$ar_rs = array();
	foreach ($ar_val as $k => $v) {
		$ar_val[$k] = &$ar_srce[$k];
	}

	$ar_srce = $ar_val;
	return $ar_srce;
} // end of func ArraySort


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
 * @deprecated      Use Fwlib\Util\ArrayUtil::filterByWildcard()
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
