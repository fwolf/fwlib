<?php
/**
 * UUID generator
 * 
 * Using method nodkz at mail dot ru post on <http://us.php.net/uniqid>,
 * but format changed.
 * 
 * <code>
 * UUID format:
 * [time_low]-[time_mid]-[custom_1]-[random_1]-[custom_2]-[random_2]
 * time_low:	8 chars, seconds in microtime, hex format.
 * time_mid:	4 chars, micro-second in microtime, plus 10000, hex format.
 * custom_1:	4 chars, user defined, '0000' if empty, hex format suggested.
 * random_1:	4 chars, random string, hex format.
 * custom_2:	8 chars, user defined, hex of user ip if empty,
 * 					and random hex string if user ip cannot get, hex format too.
 * random_2:	4 chars, random string, hex format.
 * </code>
 * @package		fwolflib
 * @subpackage	func
 * @copyright	Copyright 2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-func@gmail.com>
 * @since		2008-05-08
 * @version		$Id$
 */

require_once('fwolflib/func/ecl.php');
require_once('fwolflib/func/client.php');

/**
 * Get a uuid
 * @param	string	$s_cus	Custom part in uuid, 4 chars long, 
 * 							positioned in 3rd section,
 *							default fill by '0'.
 * @param	string	$s_cus2	Custom part2 in uuid, 8 chars long,
 *							Positioned in start of 5 section,
 *							default fill by client user ip(hex),
 *							and random string if can't get ip.
 * @return	string
 * @link	http://us.php.net/uniqid
 */
function Uuid($s_cus = '0000', $s_cus2 = '') {
    $ar = explode(" ", microtime());
    
    // Prepare custom string 2
    if (empty($s_cus2) || (8 != strlen($s_cus2)))
    	$s_cus2 = ClientIpToHex();
    if (empty($s_cus2) || (8 != strlen($s_cus2)))
    	$s_cus2 = sprintf('%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    
    return sprintf('%08s-%04s-%04s-%04x-%08s%04x',
    	// Unixtime, 8 chars from right-side end
    	// 2030-12-31 = 1924876800(dec) = 72bb4a00(hex)
    	substr(str_repeat('0', 8) . base_convert($ar[1], 10, 16), -8),
    	// Microtime, 4chars from right-side end
    	substr(str_repeat('0', 4) . base_convert($ar[0] * 10000, 10, 16), -4),
    	// Custom part 1, default 4 chars
    	$s_cus,
    	// Random string, length: 4
    	mt_rand(0, 0xffff),
    	// Custon part2, default user ip address
		(empty($s_cus2) || 8 != strlen($s_cus2)) ? ClientIpToHex() : $s_cus2,
    	// Random string, length: 4
    	mt_rand(0, 0xffff)
    	);
    /*
    return sprintf( '%04x-%08s-%08s-%04s-%04x%04x',
        $serverID,
        clientIPToHex(),
        substr("00000000".dechex($t[1]),-8),   // get 8HEX of unixtime
        substr("0000".dechex(round($t[0]*65536)),-4), // get 4HEX of microtime
        mt_rand(0,0xffff), mt_rand(0,0xffff));
	*/
} // end of func Uuid


/**
 * Parse uuid, see what it means
 * @param	string	$uuid
 * @return	array
 * @link	http://us.php.net/uniqid
 */
function UuidParse($uuid) {
	$ar = array();
	$u = explode('-', $uuid);
	if (is_array($u) && (5 == count($u))) {
		$ar = array(
			'time_low'	=> hexdec($u[0]),
			'time_mid'	=> hexdec($u[1]),
			'custom_1'	=> $u[2],
			'random_1'	=> $u[3],
			'custom_2'	=> substr($u[4], 0, 8),
			'ip'		=> ClientIpFromHex(substr($u[4], 0, 8)),
			'random_2'	=> substr($u[4], 8)
			);
	}
	return $ar;
	/*
  $rez=Array();
    $u=explode("-",$uuid);
    if(is_array($u)&&count($u)==5) {
        $rez=Array(
            'serverID'=>$u[0],
            'ip'=>clientIPFromHex($u[1]),
            'unixtime'=>hexdec($u[2]),
            'micro'=>(hexdec($u[3])/65536)
        );
    }
    return $rez;
	*/
} // end of func UuidParse


/**
 * Test how many uuid can this program generate per second
 * 
 * @param	long	$num	Number of uuid generated in test, the more the result more currect.
 * @param	string	$file	If assigned, result will be write to this file, 1 uuid per line.
 */
function UuidSpeedTest($num = 100, $file = '') {
	if (!is_numeric($num))
		return '';
	else
		$num = round($num);
	// Start time
	$t_start = microtime(true);
	$i = 0;
	$s = '';
	while ($num > $i) {
		$s .= Uuid() . "\n";
		$i ++;
	}
	// End time
	$t_end = microtime(true);
	// Compute
	$t_used = round($t_end - $t_start, 4);
	$speed = round($num / $t_used);
	// Outputis_numeric
	ecl("$num UUID generated, cost $t_used second(s), average $speed/s.");
	// Write to file ?
	if (!empty($file))
		file_put_contents($file, $s);
} // end of function UuidSpeedTest 
	
?>