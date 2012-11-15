<?php
/**
 * Test - Cache class
 * @package     fwolflib
 * @subpackage	class.test
 * @copyright   Copyright 2012, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.class.test.cache@gmail.com>
 * @since		2012-11-06
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
require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'class/cache/cache.php');
require_once(FWOLFLIB . 'func/ecl.php');
require_once(FWOLFLIB . 'func/request.php');
require_once(FWOLFLIB . 'func/string.php');

class TestCache extends UnitTestCase {

	/**
	 * Cache object
	 * @var	object
	 */
	protected $oCh = null;


	/**
	 * Constructor
	 */
	public function __construct () {

	} // end of func __construct


	public function TestCacheDefault () {
		//$this->oCh = new Cache();
		//$this->oCh = Cache::Create('unsupported');
		//$this->assertEqual(NULL, $this->oCh);

		$this->oCh = Cache::Create('');
		$this->assertEqual('', $this->oCh->aCfg['cache-type']);

		$key = 'key';
		$val = 'val';
		$this->oCh->Set($key, $val);
		$this->assertEqual($val, $this->oCh->Get($key));

		$this->oCh->Del($key);
		$this->assertEqual(NULL, $this->oCh->Get($key));


		// Val encode and decode
		$x = 'This is string.';
		$this->assertEqual($x, $this->oCh->ValDecode(
			$this->oCh->ValEncode($x, 0), 0));
		// Invalid flag
		$this->assertEqual($x, $this->oCh->ValDecode(
			$this->oCh->ValEncode($x, 100), 100));

		$x = array('a' => 'b');
		$this->assertEqual($x, $this->oCh->ValDecode(
			$this->oCh->ValEncode($x, 0), 0));
		$this->assertEqual($x, $this->oCh->ValDecode(
			$this->oCh->ValEncode($x, 1), 1));

		$x = new Fwolflib;
		$this->assertEqual($x, $this->oCh->ValDecode(
			$this->oCh->ValEncode($x, 0), 0));
/*
		// Json decode object has no class, so diff with original
		// Also array in object must be convert back from stdClass
		$y = $this->oCh->ValDecode($this->oCh->ValEncode($x, 2), 2);
		$y->aCfg = (array)$y->aCfg;
		$this->assertEqual($x, $y);
		var_dump($x);
		var_dump($y);
*/

	} // end of func TestCacheDefault


	public function TestCacheFile () {
/*
		// Wrong cfg test
		$this->oCh = Cache::Create('file'
			, array('cache-file-dir' => '/proc/'));
		$this->assertEqual(false, $this->oCh->ChkCfg());

		$this->oCh = Cache::Create('file'
			, array('cache-file-rule' => '0blah'));
		$this->assertEqual(false, $this->oCh->ChkCfg());
*/

		$this->oCh = Cache::Create('file');
		$this->oCh->SetCfg('cache-file-rule', '1140');
		$key = 'site/index';

		$x = '/tmp/cache/d0/ex/3ed0dc6e';
		$y = $this->oCh->FilePath($key);
		$this->assertEqual($x, $y);

		$this->oCh->SetCfg(array('cache-file-rule' => '1131'));
		$x = '/tmp/cache/d0/te/3ed0dc6e';
		$y = $this->oCh->FilePath($key);
		$this->assertEqual($x, $y);

		// Notice: Directly use key's part as path may cause wrong
		$this->oCh->SetCfg(array('cache-file-rule' => '2342'));
		$x = '/tmp/cache/57//i/3ed0dc6e';
		$y = $this->oCh->FilePath($key);
		$this->assertEqual($x, $y);

		// Common usage
		$this->oCh->SetCfg(array('cache-file-rule' => '1011'));
		$x = '/tmp/cache/3e/d0/3ed0dc6e';
		$y = $this->oCh->FilePath($key);
		$this->assertEqual($x, $y);

		// Common usage 2
		$this->oCh->SetCfg(array('cache-file-rule' => '2021'));
		$x = '/tmp/cache/b6/9c/3ed0dc6e';
		$y = $this->oCh->FilePath($key);
		$this->assertEqual($x, $y);

		// Common usage 3
		$this->oCh->SetCfg(array('cache-file-rule' => '55'));
		$x = '/tmp/cache/89/3ed0dc6e';
		$y = $this->oCh->FilePath($key);
		$this->assertEqual($x, $y);


		// Skip cache write after test.
/*
		// Cache set
		$v = 'blah';
		$this->oCh->SetCfg('cache-store-method', 1);
		$this->oCh->Set($key, $v);
		$this->assertEqual(json_encode($v), file_get_contents($x));

		// Cache expire
		$this->assertEqual(true, $this->oCh->Expire($key, -10));
		$this->assertEqual(false, $this->oCh->Expire($key, 10));
		$this->assertEqual(false, $this->oCh->Expire($key, 0));

		// Cache get
		$this->assertEqual($v, $this->oCh->Get($key));

		$v = '你好';
		$this->oCh->SetCfg('cache-store-method', 0);
		$this->oCh->Set($key, $v);
		$this->assertEqual($v, $this->oCh->Get($key));

		$v = array('你' => '好');
		$this->oCh->SetCfg('cache-store-method', 1);
		$this->oCh->Set($key, $v);
		$this->assertEqual($v, $this->oCh->Get($key));
*/
		// End of cache write test.

	} // end of func TestCacheFile


} // end of class TestCache


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('cache.test.php' == $s_url) {
	$test = new TestCache();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
