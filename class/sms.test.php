<?php
/**
 * Test - class Sms
 *
 * @package     fwolflib
 * @subpackage	class.test
 * @copyright   Copyright 2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.class.test@gmail.com>
 * @since		2010-11-23
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
require_once('../fwolflib.php');
require_once(FWOLFLIB . 'class/adodb.php');
require_once(FWOLFLIB . 'class/sms.php');
require_once(FWOLFLIB . 'func/request.php');


class TestClassSms extends UnitTestCase {

	function __construct() {
		$ar_db = array(
			'type'	=> 'mysqli',
			'host'	=> 'localhost',
			'user'	=> 't-sms',
			'pass'	=> '',
			'name'	=> 't-sms',
			'lang'	=> 'utf-8',
		);
		$o_db = new Adodb($ar_db);
		$o_db->Connect();
		$this->oSms = new Sms($o_db);
		// Cat of debug sms
		$this->iCat = 1020000;
	} // end of func __construct


	function TestCountPart () {
		$s_sms = '';	// 0
		$this->assertEqual(0, $this->oSms->CountPart($s_sms));
		$s_sms = '01234567890123456789012345678901234567890123456789'
			. '01234567890123456789012345678901234567890123456789'
			. '0123456789012345678901234567890123456789';	// 140
		$this->assertEqual(1, $this->oSms->CountPart($s_sms));
		$s_sms = '01234567890123456789012345678901234567890123456789'
			. '01234567890123456789012345678901234567890123456789
01234567890123456789012345678901234567890';	// 141
		$this->assertEqual(2, $this->oSms->CountPart($s_sms));

		$s_sms = '一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十'
			. '一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十
一二三四五六七八';	// 69
		$this->assertEqual(1, $this->oSms->CountPart($s_sms));
		$s_sms = '0123456789一二三四五六七八九十一二三四五六七八九十'
			. '一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十'
			. '一二三四五六七八九十';	// 70
		$this->assertEqual(1, $this->oSms->CountPart($s_sms));
		$s_sms = '0123456789一二三四五六七八九十一二三四五六七八九十'
			. '一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十'
			. '一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十'
			. '一二三四五六七八九十一二三四五六七八九十一二三四五六七八九十'
			. '一二三四五六七八九十一二三四五六七八九十';	// 140
		$this->assertEqual(2, $this->oSms->CountPart($s_sms));
	} // end of func TestCountPart


    function TestDestParse () {
		$s_dest = "13912345678
			13912345678,;
			008613912345678 ，\t
			13921345678\r\n\t
			1392345678\r\n\t
			+8613912345678;
			+8613912345678,end
		";
		$ar_dest = array('13912345678', '13921345678');
		$this->assertEqual($ar_dest, $this->oSms->DestParse($s_dest));

		$s_dest = "13012345678,13012345679";
		$ar_dest = array('13012345678', '13012345679');
		$this->assertEqual($ar_dest, $this->oSms->DestParse($s_dest));
    } // end of func TestDestParse

    function TestSendUsingGammuSmsdInject () {
		$this->oSms->SendUsingGammuSmsdInject('1391234567'
			, '测试短信', $this->iCat);
    } // end of func TestSendUsingGammuSmsdInject

} // end of class TestClassSms


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('sms.test.php' == $s_url) {
	$test = new TestClassSms();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
