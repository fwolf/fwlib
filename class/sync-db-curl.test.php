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


// Config
$s_url_remote = 'http://local.fwolf.com/dev/fwolflib/class/sync-db-curl.test.php';
$s_crypt_key = 'blahblahblah';
$ar_db_client = array(
	'type'	=> 'mysql',
	'host'	=> 'localhost',
	'user'	=> 'test',
	'pass'	=> '',
	'name'	=> 't-sync-db-curl1',
	'lang'	=> 'utf-8',
);
$ar_db_server = $ar_db_client;
$ar_db_server['name'] = 't-sync-db-curl2';


if (empty($_POST)) {
	// Act as client

	// Avoid duplicate run
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
		'url_remote'	=> $s_url_remote,
		'db_prof'		=> $ar_db_client,
	);
	$o_sdc = new SyncDbCurl($ar_cfg);

	// Test curl conn
	if (1 == $o_sdc->CommSendTest()) {
		echo $o_sdc->LogGet(1);
		Ecl('Conn test error.');
		unlink($f_lock);
		die();
	}

	// Test local db conn
	if (0 != $o_sdc->TestDb()) {
		echo $o_sdc->LogGet(5);
		unlink($f_lock);
		die('');
	}


	echo $o_sdc->LogGet(3);
	unlink($f_lock);
} else {
	// Act as server
	// Server need dup run, lock un-needed.
	$ar_cfg = array(
		'crypt_key'		=> $s_crypt_key,
		'db_prof'		=> $ar_db_server,
	);
	$o_sdc = new SyncDbCurl($ar_cfg);
}

?>
