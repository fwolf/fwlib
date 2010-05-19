<?Php
// Usage example

// :TODO: Avoid duplicate run

// Config
$s_url_remote = 'http://local.fwolf.com/dev/fwolflib/class/curl-comm.test.php';


// Include
if (0 <= version_compare(phpversion(), '5.3.0')) {
	require_once(__DIR__ . '/curl-comm.php');
} else {
	require_once(dirname(__FILE__) . '/curl-comm.php');
}


if (empty($_POST)) {
	// Act as client
	$ar_cfg = array(
		'url_remote'	=> $s_url_remote,
	);
	$o_cc = new CurlComm($ar_cfg);
	$o_cc->TestRemote();

	echo $o_cc->LogGet(1);
} else {
	// Act as server
	echo 'Nice to meet you!';
}
?>
