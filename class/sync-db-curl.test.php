<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-05-25
 */

// Usage example

// Include
if (0 <= version_compare(phpversion(), '5.3.0')) {
	require_once(__DIR__ . '/sync-db-curl.php');
} else {
	require_once(dirname(__FILE__) . '/sync-db-curl.php');
}


// Config, can be multi-server
// Global/default config
$ar_default = array(
	'url'		=> '',
	'db_client'	=> array(
		'type'	=> 'mysql',
		'host'	=> 'localhost',
		'user'	=> 'test',
		'pass'	=> '',
		'name'	=> 't-sync-db-curl1',
		'lang'	=> 'utf-8',
	),
	'db_server'	=> array(
		'type'	=> 'mysql',
		'host'	=> 'localhost',
		'user'	=> 'test',
		'pass'	=> '',
		'name'	=> 't-sync-db-curl',
		'lang'	=> 'utf-8',
	),
	'pull'		=> '',
	'push'		=> '',
);
$s_crypt_key = 'blahblahblah';


// Per server config, if any part is missing, use default.
// Key is name of server, will be recorded in db log tbl.
$ar_server['test server'] = array(
	'url'	=> 'http://local.fwolf.com/dev/fwolflib/class/sync-db-curl.test.php',
);
$ar_server['test server']['db_server'] = array(
	'type'	=> 'mysql',
	'host'	=> 'localhost',
	'user'	=> 'test',
	'pass'	=> '',
	'name'	=> 't-sync-db-curl2',
	'lang'	=> 'utf-8',
);
$ar_server['test server2'] = array(
	'url'	=> 'http://local.fwolf.com/dev/fwolflib/class/sync-db-curl.test1.php',
);


if (empty($_POST)) {
	// Act as client

	// Avoid duplicate run at client side.
	$f_lock = sys_get_temp_dir() . '/sync-db-curl.test.lock';
	if (file_exists($f_lock)) {
		// Already running
		Ecl('Previous run not end or clean.');
		Ecl('Lock file: ' . $f_lock);
		die();
	}
	file_put_contents($f_lock, '');

	$ar_cfg = array(
		'crypt_key'		=> $s_crypt_key,
		'default'		=> $ar_default,
		'server'		=> $ar_server,
	);
	$o_sdc = new SyncDbCurl($ar_cfg);
	echo $o_sdc->LogGet(3);

	unlink($f_lock);
} else {
	// Act as server
	// Server need dup run, lock un-needed.
	$ar_cfg = array(
		'crypt_key'		=> $s_crypt_key,
		'default'		=> $ar_default,
	);
	$o_sdc = new SyncDbCurl($ar_cfg);
}

?>
