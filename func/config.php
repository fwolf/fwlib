<?php
/**
 * Provide GetCfg and SetCfg function
 * @package     fwolflib
 * @copyright   Copyright 2007, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		2007-10-23
 * @version		$Id$
 */

require_once(dirname(__FILE__) . '/../fwolflib.php');


/**
 * Get global config setting from global var $config
 *
 * String split by '.' means multidimensional array.
 *
 * @see		SetCfg()
 * @param	string	$key
 * @return	mixed
 */
function GetCfg ($key) {
	global $config;
	if (false === strpos($key, '.')) {
		if (isset($config[$key]))
			return($config[$key]);
		else
			return null;
	} else {
		// Recoginize the dot
		$ar = explode('.', $key);
		$c = $config;
		foreach ($ar as $val) {
			// Every dimision will go 1 level deeper
			if (isset($c[$val]))
				$c = &$c[$val];
			else
				return null;
		}
		return($c);
	}
} // end of func GetCfg


/**
 * Limit program can only run on prefered server, identify by serverid
 * If check failed, die and exit.
 * SetCfg('serverid', 0);
 * @param	mixed	$id		string/int -> on this server, array -> in any of these server
 * @param	boolean	$die	If check false, true -> die(), false -> return false and continue, default is true.
 * @see	SetCfg()
 * @see GetCfg()
 * @return	boolean
 */
function LimitServerId($id, $die = true) {
	$serverid = GetCfg('server.id');
	if (empty($serverid))
		$serverid = GetCfg('serverid');

	$msg = '';
	if (is_array($id)) {
		if (!in_array($serverid, $id))
			$msg = 'This program can only run on these servers: ' . implode(',', $id) . '.';
	} elseif ($serverid != $id)
		$msg = 'This program can only run on server ' . $id . '.';

	if (!empty($msg))
		if (true == $die)
			die($msg);
		else
			return false;
	else
		return true;
} // end of func LimitServerId


/**
 * Set global config var $config
 *
 * Multidimensional array style setting supported,
 * If $key is string including '.', its converted to array by it recurrently.
 * eg: system.format.time => $config['system']['format']['time']
 *
 * @param	string	$key
 * @param	mixed	$val
 */
function SetCfg ($key, $val) {
	global $config;
	if (false === strpos($key, '.')) {
		$config[$key] = $val;
	} else {
		// Recoginize the dot
		$ar = explode('.', $key);
		$c = &$config;
		$j = count($ar) - 1;
		// Every loop will go 1 level sub array
		for ($i = 0; $i < $j; $i ++) {
			// 'a.b.c', if b is not set, create it as an empty array
			if (!isset($c[$ar[$i]]))
				$c[$ar[$i]] = array();
			$c = &$c[$ar[$i]];
		}
		// Set the value
		$c[$ar[$i]] = $val;
	}
} // end of func SetCfg


/**
 * Set default value of global config var $config
 *
 * Same with SetCfg() except: SetCfgDefault() will only change setting
 * if the config key is not setted before.
 *
 * @param	string	$key
 * @param	mixed	$val
 */
function SetCfgDefault ($key, $val) {
	if (is_null(GetCfg($key)))
		SetCfg($key, $val);
} // end of func SetCfgDefault


// Debug
/*
require_once('config.default.php');
require_once('fwolflib/func/ecl.php');
ecl(GetCfg('serverid'));
ecl(GetCfg('dbserver.default.type'));
SetCfg('test.1.2.3', 3);
SetCfg('test.1.2.4', 4);
ecl(GetCfg('test.1.2.3'));
ecl(GetCfg('test.1.2.4'));
print_r(GetCfg('dbserver.default'));
*/
?>
