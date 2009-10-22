<?php
/**
 * Test - crypt func
 * @package     fwolflib
 * @subpackage	func-test
 * @copyright   Copyright 2009, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func-test@gmail.com>
 * @since		2009-10-22
 * @version		$Id$
 */

// Define like this, so test can run both under eclipse and web alone.
// {{{
if (! defined('SIMPLE_TEST')) {
	define('SIMPLE_TEST', 'simpletest/');
	require_once(SIMPLE_TEST . 'autorun.php');
}
// Then set output encoding
//header('Content-Type: text/html; charset=utf-8');
// }}}

// Require library define file which need test
require_once('fwolflib/func/crypt.php');

class TestFuncCrypt extends UnitTestCase {

    function TestMcryptSmplIv() {
		$s_key = 'blahblahblah';
		$s_data = '加密的东东';
		$s_algo = 'xtea';

		$s_data_encrypted = McryptSmplIvEncrypt($s_data, $s_key, $s_algo);
		$this->assertEqual(base64_encode($s_data_encrypted),
			'8vAJEMIdSmH3udoxZ3va');

		$s_data_decrypted = McryptSmplIvDecrypt($s_data_encrypted, $s_key, $s_algo);
		$this->assertEqual($s_data_decrypted, $s_data);
    } // end of func TestMcryptSmplIv

} // end of class TestFuncCrypt


// Change output charset in this way.
// {{{
$test = new TestFuncCrypt();
$test->run(new HtmlReporter('utf-8'));
// }}}
?>
