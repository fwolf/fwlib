<?Php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-05-19
 */

// Usage example

// Include
if (0 <= version_compare(phpversion(), '5.3.0')) {
	require_once(__DIR__ . '/curl-comm.php');
} else {
	require_once(dirname(__FILE__) . '/curl-comm.php');
}


// Config
$s_url_remote = 'http://local.fwolf.com/dev/fwolflib/class/curl-comm.test.php';
$s_crypt_key = 'blahblahblah';


if (empty($_POST)) {
	// Act as client

	// Avoid duplicate run
	$f_lock = sys_get_temp_dir() . '/curl-comm.test.lock';
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
	);
	$o_cc = new CurlComm($ar_cfg);
	if (1 == $o_cc->CommSendTest()) {
		echo $o_cc->LogGet(1);
		Ecl('Conn test error.');
		unlink($f_lock);
		die();
	}

	echo $o_cc->LogGet(3);

	unlink($f_lock);
} else {
	// Act as server
	// Server need dup run, lock un-needed.
	$ar_cfg = array(
		'crypt_key'		=> $s_crypt_key,
	);
	$o_cc = new CurlComm($ar_cfg);
}

?>
