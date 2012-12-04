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

/*
		// Json decode object has no class, so diff with original
		// Also array in object must be convert back from stdClass
		$this->oCh->SetCfg('cache-store-method', 2);
		$x = new Fwolflib;
		$this->assertEqual($x, $this->oCh->ValDecode(
			$this->oCh->ValEncode($x, 0), 0));

		$y = $this->oCh->ValDecode($this->oCh->ValEncode($x, 2), 2);
		$y->aCfg = (array)$y->aCfg;
		$this->assertEqual($x, $y);
		var_dump($x);
		var_dump($y);
*/

		// Expire time
		$x = 0;
		$this->assertEqual($x, $this->oCh->ExpireTime($x));

		$x = time() + 2592000;
		$this->assertEqual($x, $this->oCh->ExpireTime(2592000));

		$x = 2592001;
		$this->assertEqual($x, $this->oCh->ExpireTime(2592001));

		$x = time() + 2592000;
		$this->assertEqual($x, $this->oCh->ExpireTime());

		// Get log
		//var_dump(Cache::$aLogGet);

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
		$this->assertEqual(true, $this->oCh->Expire($key, strtotime('2012-1-1')));
		$this->assertEqual(false, $this->oCh->Expire($key, 10));
		$this->assertEqual(false, $this->oCh->Expire($key, 1));
		$this->assertEqual(false, $this->oCh->Expire($key, 0));
		$this->assertEqual(false, $this->oCh->Expire($key, NULL));

		// Cache get
		$this->assertEqual($v, $this->oCh->Get($key));
		$this->assertEqual(NULL, $this->oCh->Get($key, -10));
		$this->assertEqual($v, $this->oCh->Get($key, 0));
		$this->assertEqual($v, $this->oCh->Get($key, 5));
		$this->assertEqual($v, $this->oCh->Get($key, NULL));

		$v = '你好';
		$this->oCh->SetCfg('cache-store-method', 0);
		$this->oCh->Set($key, $v);
		$this->assertEqual($v, $this->oCh->Get($key));

		$v = array('你' => '好');
		$this->oCh->SetCfg('cache-store-method', 1);
		$this->oCh->Set($key, $v);
		$this->assertEqual($v, $this->oCh->Get($key));

		// Cache del
		$this->oCh->Del($key);
		$this->assertEqual(NULL, $this->oCh->Get($key));

*/
		// End of cache write test.

		// Get log
		//var_dump(Cache::$aLogGet);

	} // end of func TestCacheFile


    function TestCacheMemcached () {
		$this->oCh = Cache::Create('memcached');

		// Empty server list
		$ar = $this->oCh->oMemcached->getServerList();
		$this->assertEqual($ar, array());


		// Set server cfg
		$x = array(
			array(
				'host'		=> 'localhost',
				'port'		=> 11211,
				'weight'	=> 0,
			),
		);
		$this->oCh->SetCfg('cache-memcached-server', $x);
		unset($this->oCh->oMemcached);
		$ar = $this->oCh->oMemcached->getServerList();
		$this->assertEqual($ar, $x);

		$y = array('localhost', 11211);
		$this->oCh->SetCfgServer($y);
		$this->assertEqual($x, $this->oCh->oMemcached->getServerList());

		// Memcache server recognize by array position, not assoc key
		$y = array('h' => 'localhost', 'p' => 11211);
		$this->oCh->SetCfgServer($y);
		$this->assertEqual($x, $this->oCh->oMemcached->getServerList());

		// Skip cache write after test
/*
		// Multi server
		$x = array(
			// Dead one
			array(
				'host'		=> 'localhost',
				'port'		=> 11212,
				'weight'	=> 67
			),
			// Alive one
			array(
				'host'		=> 'localhost',
				'port'		=> 11211,
				'weight'	=> 33
			),
		);
		$this->oCh->SetCfgServer($x);
		$this->assertEqual(array($x[1])
			, $this->oCh->oMemcached->getServerList());

		// Cache write
		$key = RandomString(8, 'a0');
		$x = 'blah';
		$this->oCh->Set($key, $x, 60);
		$this->assertEqual($x, $this->oCh->Get($key));

		$x = array('blah', array('foo' => 'boo'));
		$this->oCh->Set($key, $x, 60);
		$this->assertEqual($x, $this->oCh->Get($key));

		// Cache expire
		$this->oCh->SetCfg('cache-memcached-autosplit', 1);
		$this->oCh->Set($key, $x, 60);
		$this->assertEqual(false, $this->oCh->Expire($key));
		$this->oCh->Set($key, $x, -10);
		$this->assertEqual(true, $this->oCh->Expire($key));
		$this->oCh->SetCfg('cache-memcached-autosplit', 0);
		$this->oCh->Set($key, $x, 60);
		$this->assertEqual(false, $this->oCh->Expire($key));
		$this->oCh->Set($key, $x, -10);
		$this->assertEqual(true, $this->oCh->Expire($key));

		// Cache del
		$this->oCh->Del($key);
		$this->assertEqual(NULL, $this->oCh->Get($key));

		// Long key
		$key = str_repeat('-', 300);
		$x = 'blah';
		$this->oCh->Set($key, $x, 60);
		$this->assertEqual($x, $this->oCh->Get($key));
		$this->oCh->Del($key);
		$this->assertEqual(NULL, $this->oCh->Get($key));

		// Empty key
		$key = '';
		$x = 'blah';
		$this->oCh->Set($key, $x, 60);
		$this->assertEqual($x, $this->oCh->Get($key));

		// Cache get with expire
		$key = RandomString(8, 'a0');
		$this->oCh->Set($key, $x, -10);
		$this->assertEqual(NULL, $this->oCh->Get($key));
		$this->oCh->Set($key, $x, 0);
		$this->assertEqual($x, $this->oCh->Get($key));
		$this->oCh->Set($key, $x, 5);
		$this->assertEqual($x, $this->oCh->Get($key));
		$this->oCh->Set($key, $x, NULL);
		$this->assertEqual($x, $this->oCh->Get($key));

		// Massive set
//  		$s = RandomString(2000000, 'a0');
//  		for ($i = 0; $i < 100; $i++) {
//  			$this->oCh->Set($i, $s, 3600);
//  		}
//  		$this->assertEqual(0, $this->oCh->oMemcached->getResultCode());

		// Big value exceed max item size
//		$s = RandomString(3000000, 'a0');
//		$this->oCh->Del($key);		// Clear previous setted value
//		$this->oCh->SetCfg('cache-memcached-autosplit', 1);
//		$this->oCh->Set($key, $s, 3600);
//		$this->assertEqual($s, $this->oCh->Get($key));
//		$this->assertEqual(false, $this->oCh->Expire($key));
//		$this->oCh->Del($key);
//		$this->assertEqual(NULL, $this->oCh->Get($key));
//		$this->assertEqual(true, $this->oCh->Expire($key));
//		$this->oCh->SetCfg('cache-memcached-autosplit', 0);
//		$this->oCh->Set($key, $s, 3600);
//		$this->assertEqual(NULL, $this->oCh->Get($key));

		// Big value size is computed AFTER compress if compress on
//		$s = RandomString(1500000, 'a0');
//		$this->oCh->oMemcached->setOption(Memcached::OPT_COMPRESSION
//			, false);
//		$this->oCh->SetCfg('cache-memcached-autosplit', 0);
//		$this->oCh->Set($key, $s, 3600);
//		$this->assertEqual(NULL, $this->oCh->Get($key));
//		$this->oCh->oMemcached->setOption(Memcached::OPT_COMPRESSION
//			, true);
//		$this->oCh->Set($key, $s, 3600);
//		$this->assertEqual($s, $this->oCh->Get($key));

*/
		// End of cache write test

		// Get log
		//var_dump(Cache::$aLogGet);

	} // end of func TestCacheMemcached


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
