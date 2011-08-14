<?php
/**
 * Test - Dict class
 *
 * @package		fwolflib
 * @subpackage	class.test
 * @copyright	Copyright 2011, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.class.test@gmail.com>
 * @since		2011-07-15
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
require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'class/adodb.php');
require_once(FWOLFLIB . 'class/dict.php');
require_once(FWOLFLIB . 'func/ecl.php');
require_once(FWOLFLIB . 'func/request.php');


class TestDict extends UnitTestCase {

	/**
	 * Db object
	 * @var	object
	 */
	protected $oDb = null;

	/**
	 * Dict object
	 * @var	object
	 */
	protected $oDict = null;


	/**
	 * Constructor
	 */
	public function __construct () {
		$this->oDict = new DictTest();
	} // end of func __construct


    function TestDictSet () {
		//var_dump($this->oDict->aData);
		$this->assertEqual(3, count($this->oDict->aData));

		//Ecl($this->oDict->LogGet(1));
    } // end of func TestDictSet


    function TestDictGet () {
		$this->assertEqual('a', $this->oDict->Get(123));
		$this->assertEqual(2, $this->oDict->Get('bac'));
		$this->assertEqual(array(123 => 'a', 321 => 'c')
			, $this->oDict->Get(array(123, 321)));

		$this->assertEqual(array('bac'
				=> array('code' => 'bac', 'title' => 2))
			, $this->oDict->GetList('!is_numeric("{code}")'));
		$this->assertEqual(array(123 => array('code' => 123, 'title' => 'a')
				, 321 => array('code' => 321, 'title' => 'c'))
			, $this->oDict->GetList('"2" == substr("{code}", 1, 1)'));

		// GetList with assign cols
		$this->assertEqual(array(321 => 'c')
			, $this->oDict->GetList('"c" == "{title}"', 'title'));
	} // end of func TestDictGet


/*
	function TestDictGetSql () {
		$ar_db = array(
			'type'	=> 'mysqli',
			'host'	=> 'localhost',
			'user'	=> 't-2008-zbb',
			'pass'	=> '',
			'name'	=> 't-2008-zbb',
			'lang'	=> 'utf-8',
		);
		$this->oDb = new Adodb($ar_db);
		$this->oDb->Connect();
		Ecl($this->oDict->GetSql($this->oDb));
	} // end of func TestDictGetSql
*/


} // end of class TestCache


class DictTest extends Dict {
	public function Init () {
		parent::Init();

		$this->Set(array(
			array(123,	'a'),
			array('bac',	2),
		))->Set(array(321,	'c'));

		return $this;
	} // end of func Init


	public function SetStruct () {
		parent::SetStruct();

		$this->SetCfg('dict-cols-pk', 'code');
	} // end of func SetStruct


} // end of class DictTest


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('dict.test.php' == $s_url) {
	$test = new TestDict();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
