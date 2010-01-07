<?php
/**
 * Test - Cache class
 * @package     fwolflib
 * @subpackage	class-test
 * @copyright   Copyright 2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.class.test@gmail.com>
 * @since		2010-01-07
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
require_once('fwolflib/class/cache.php');
require_once('fwolflib/func/ecl.php');
require_once('fwolflib/func/request.php');
require_once('fwolflib/func/string.php');

class TestCache extends UnitTestCase {

	/**
	 * Cache object
	 * @var	object
	 */
	protected $oCh = null;


	/**
	 * Constructor
	 */
	public function __construct() {
		$ar_cfg = array(
			'dir'		=> '/tmp/cache/',
			'rule'		=> '1142',
		);
		$this->oCh = new CacheTest($ar_cfg);
	} // end of func __construct


    function TestPath() {
		$this->oCh->CacheSetCfg(array(
			'dir'	=> '/tmp/cache/',
			'rule'	=> '1140',
		));
		$key = 'site/index';

		//Ecl(md5($key));

		$x = '/tmp/cache/d0/ex/3ed0dc6e';
		$y = $this->oCh->CachePath($key);
		$this->assertEqual($x, $y);

		$this->oCh->CacheSetCfg(array('rule' => '1131'));
		$x = '/tmp/cache/d0/te/3ed0dc6e';
		$y = $this->oCh->CachePath($key);
		$this->assertEqual($x, $y);

		// Notice: Directly use key's part as path may cause wrong
		$this->oCh->CacheSetCfg(array('rule' => '2342'));
		$x = '/tmp/cache/57//i/3ed0dc6e';
		$y = $this->oCh->CachePath($key);
		$this->assertEqual($x, $y);

		// Common usage
		$this->oCh->CacheSetCfg(array('rule' => '1011'));
		$x = '/tmp/cache/3e/d0/3ed0dc6e';
		$y = $this->oCh->CachePath($key);
		$this->assertEqual($x, $y);

		// Common usage 2
		$this->oCh->CacheSetCfg(array('rule' => '2021'));
		$x = '/tmp/cache/b6/9c/3ed0dc6e';
		$y = $this->oCh->CachePath($key);
		$this->assertEqual($x, $y);

		//Ecl($y);

		// Read/write
		$v = $this->oCh->CacheLoad($key, 1);
		var_dump($v);
    } // end of func TestPath

} // end of class TestCache


class CacheTest extends Cache {
	protected function CacheGenVal($key) {
		$this->sDummy = RandomString(30, 'a0');
		return $this;
	} // end of func CacheGenVal

	public function CacheLifetime($key) {
		return 10;
	} // end of func CacheLifetime
} // end of class CacheTest


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
