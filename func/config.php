<?php
/**
 * Provide GetCfg and SetCfg function
 * @package     fwolflib
 * @copyright   Copyright 2007, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		2007-10-23
 * @version		$Id$
 */


/**
 * Get global config setting
 * Multidimensional array style setting, convert to string $cfg by seperate several dimension with dot, eg: dbserver.dbprofile.type .
 * @param	string	$cfg
 * @return	mixed
 */
function GetCfg($cfg) {
	global $config;
	if (false === strpos($cfg, '.')) {
		return($config[$cfg]);
	} else {
		// Recoginize the dot
		$ar = explode('.', $cfg);
		$c = $config;
		foreach ($ar as $val) {
			// Every dimision will go 1 level deeper
			$c = &$c[$val];
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
 * Set global config setting
 * Multidimensional array style setting, convert to string $cfg by seperate several dimension with dot, eg: dbserver.dbprofile.type .
 * @param	string	$cfg
 * @param	mixed	$value
 */
function SetCfg($cfg, $value) {
	global $config;
	if (false === strpos($cfg, '.')) {
		$config[$cfg] = $value;
	} else {
		// Recoginize the dot
		$ar = explode('.', $cfg);
		$c = &$config;
		$j = count($ar) - 1;
		// Every loop will go 1 level sub array
		for ($i=0; $i<$j; $i++) {
			// 'a.b.c', if b is not set, create it as an empty array
			if (!isset($c[$ar[$i]]))
				$c[$ar[$i]] = array();
			$c = &$c[$ar[$i]];
		}
		// Set the value
		$c[$ar[$i]] = $value;
	}
} // end of func SetCfg


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
