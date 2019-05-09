<?php
/**
 * Test - MVC Module class
 *
 * @package		fwolflib
 * @subpackage	class.test
 * @copyright	Copyright 2012, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.class.test@gmail.com>
 * @since		2012-12-10
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
require_once(dirname(__FILE__) . '/adodb.php');
require_once(dirname(__FILE__) . '/mvc-module.php');
require_once(dirname(__FILE__) . '/../func/ecl.php');
require_once(dirname(__FILE__) . '/../func/request.php');
require_once(dirname(__FILE__) . '/../func/uuid.php');


class TestModule extends UnitTestCase {

	/**
	 * Module object
	 * @var	object
	 */
	protected $oModule = NULL;


	/**
	 * Constructor
	 */
	public function __construct () {
		$this->oModule = new ModuleTest();

		// Define dbprofile
		$this->oModule->SetCfg('dbprofile', array(
			'type'	=> 'mysqli',
			'host'	=> 'localhost',
			'user'	=> 'test',
			'pass'	=> '',
			'name'	=> 'test',
			'lang'	=> 'utf-8',
		));
		$this->oModule->oDb;
	} // end of func __construct


	function TestDbDiff () {
		// Create test table
		$this->oModule->oDb->Execute('
			CREATE TABLE t1 (
				uuid	CHAR(36) NOT NULL,
				i		INTEGER NOT NULL DEFAULT 0,
				ii		INTEGER NULL DEFAULT 0,
				s		VARCHAR(20) NULL,
				d		DATETIME NULL,
				PRIMARY KEY (uuid, i)
			);
		');
		$this->oModule->oDb->Execute('
			CREATE TABLE t2 (
				uuid	CHAR(36) NOT NULL,
				i		INTEGER NULL DEFAULT 0,
				ii		INTEGER NULL DEFAULT 0,
				s		VARCHAR(20) NULL,
				d		DATETIME NULL,
				PRIMARY KEY (uuid)
			);
		');


		// Test Adodb::GetDataByPk()
		$uuid = Uuid();
		$this->oModule->oDb->Execute('
			INSERT INTO t1
			VALUES ("' . $uuid . '", 12, 11, "blah"
				, "' . date('Y-m-d H:i:s') . '")
		');
		$this->assertEqual(12, $this->oModule->oDb->GetDataByPk(
			't1', $uuid, 'i', 'uuid'));
		$this->assertEqual(array('i' => 12, 's' => 'blah')
			, $this->oModule->oDb->GetDataByPk(
			't1', array($uuid, 12), ' i , s ,'));


		// Write data using DbDiff()
		$uuid = Uuid();
		$uuid2 = Uuid();

		// Error: New array has few PK
		$ar_new = array(
			'uuid'	=> $uuid,
//			'i'		=> mt_rand(0, 100),
			's'		=> RandomString(10),
			'd'		=> date('Y-m-d H:i:s'),
		);
		$ar_diff = $this->oModule->DbDiff(array('t1' => $ar_new));
		$this->assertEqual(-2, $ar_diff['code']);

		// New array has only PK
		$ar_new = array(
			'uuid'	=> $uuid,
			'i'		=> mt_rand(0, 100),
		);
		$ar_diff = $this->oModule->DbDiff(array('t1' => $ar_new));
		$this->assertEqual($ar_diff['diff']['t1'][0]['mode'], 'INSERT');
		$this->assertEqual(count($ar_diff['diff']['t1'][0]['pk']), 2);
		$this->assertEqual(count($ar_diff['diff']['t1'][0]['col']), 0);
		$ar_new = array(
			'uuid'	=> $uuid,
		);
		$ar_diff = $this->oModule->DbDiff(array('t2' => $ar_new));
		$this->assertEqual($ar_diff['diff']['t2'][0]['mode'], 'INSERT');
		$this->assertEqual(count($ar_diff['diff']['t2'][0]['pk']), 1);
		$this->assertEqual(count($ar_diff['diff']['t2'][0]['col']), 0);

		// Insert data
		$ar_new = array(
			'uuid'	=> $uuid,
			'i'		=> mt_rand(0, 100),
			's'		=> RandomString(10),
			'd'		=> date('Y-m-d H:i:s'),
		);
		$ar_diff = $this->oModule->DbDiffExec(array('t1' => $ar_new));
		$this->assertEqual($ar_diff['diff']['t1'][0]['mode'], 'INSERT');
		$this->assertEqual(count($ar_diff['diff']['t1'][0]['pk']), 2);
		$this->assertEqual(count($ar_diff['diff']['t1'][0]['col']), 2);
		$this->assertEqual($ar_diff['code'], 1);
		$this->assertEqual($ar_diff['flag'], 100);
		$ar_diff = $this->oModule->DbDiffExec(array('t2' => $ar_new));
		$this->assertEqual($ar_diff['diff']['t2'][0]['mode'], 'INSERT');
		$this->assertEqual(count($ar_diff['diff']['t2'][0]['pk']), 1);
		$this->assertEqual(count($ar_diff['diff']['t2'][0]['col']), 3);
		$this->assertEqual($ar_diff['code'], 1);
		$this->assertEqual($ar_diff['flag'], 100);

		// Insert mixed with update, multi table
		$ar_new2 = array($ar_new, array(
			'uuid'	=> $uuid2,
			'i'		=> mt_rand(0, 100),
			's'		=> RandomString(10),
			'd'		=> date('Y-m-d H:i:s'),
		));
		$ar_new3 = $ar_new2;
		$ar_new2[0]['s'] = RandomString(10);	// Make a update in t1
		$ar_diff = $this->oModule->DbDiffExec(array(
			't1' => $ar_new2,
			't2' => $ar_new3,
		));
		$this->assertEqual($ar_diff['diff']['t1'][0]['mode'], 'UPDATE');
		$this->assertEqual($ar_diff['diff']['t1'][1]['mode'], 'INSERT');
		$this->assertEqual($ar_diff['diff']['t2'][0]['mode'], 'INSERT');
		$this->assertEqual(count($ar_diff['diff']['t1'][0]['pk']), 2);
		$this->assertEqual(count($ar_diff['diff']['t1'][0]['col']), 1);
		$this->assertEqual(count($ar_diff['diff']['t2'][0]['pk']), 1);
		$this->assertEqual(count($ar_diff['diff']['t2'][0]['col']), 3);
		$this->assertEqual($ar_diff['code'], 3);
		$this->assertEqual($ar_diff['flag'], 100);

		// Db query fail
//		$ar_new2[1]['ii'] = 'blah';
//		$ar_diff = $this->oModule->DbDiffExec(array(
//			't1' => $ar_new2,
//			't2' => $ar_new2,
//		));
//		$this->assertEqual($ar_diff['diff']['t1'][0]['mode'], 'UPDATE');
//		$this->assertEqual($ar_diff['diff']['t2'][1]['mode'], 'UPDATE');
//		// Unknow column in fields list
//		$this->assertEqual($ar_diff['code'], -1054);
//		$this->assertEqual($ar_diff['flag'], 0);

		// Delete op
		// PK value NULL means delete
		$ar_new4 = array($ar_new, array(
			'uuid'	=> NULL,
			'i'		=> NULL,
		));
		$ar_diff = $this->oModule->DbDiffExec(array(
				't1' => $ar_new4,
				't2' => $ar_new4,
			), NULL, array(
				't1' => $ar_new3,	// Notice: Not same with exists value
				't2' => $ar_new3,
		));
		$this->assertEqual($ar_diff['diff']['t1'][0]['mode'], 'DELETE');
		$this->assertEqual($ar_diff['diff']['t2'][0]['mode'], 'DELETE');
		$this->assertEqual($ar_diff['code'], 2);
		$this->assertEqual($ar_diff['flag'], 100);


		// Rollback
		$uuid = Uuid();
		$ar_new = array(
			'uuid'	=> $uuid,
			'i'		=> mt_rand(100, 200),
			's'		=> 'aaa',
			'd'		=> date('Y-m-d H:i:s'),
		);
		$ar_new2 = array(
			'uuid'	=> $uuid2,
			'i'		=> mt_rand(100, 200),
			's'		=> 'aaa',
			'd'		=> date('Y-m-d H:i:s'),
		);
		// 1. insert
		$ar_new3 = array($ar_new, $ar_new2);
		$ar_diff_ins = $this->oModule->DbDiffExec(array(
				't1' => $ar_new3,
				't2' => $ar_new3,
		));
		$this->assertEqual('aaa', $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new['uuid'], $ar_new['i']), 's'));
		$this->assertEqual('aaa', $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new2['uuid'], $ar_new2['i']), 's'));
		$this->assertEqual('aaa', $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new['uuid'], 's'));
		$this->assertEqual('aaa', $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new2['uuid'], 's'));
		// 2. update 1, delete 1
		$ar_new4 = $ar_new3;
		$ar_new4[0]['s'] = 'bbb';
		$ar_new4[0]['s'] = 'bbb';
		$ar_new4[1]['uuid'] = NULL;
		$ar_new4[1]['i'] = NULL;
		$ar_diff = $this->oModule->DbDiffExec(array(
				't1' => $ar_new4,
				't2' => $ar_new4,
			), NULL, array(
				't1' => $ar_new3,
				't2' => $ar_new3,
		));
		$this->assertEqual('bbb', $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new['uuid'], $ar_new['i']), 's'));
		$this->assertEqual(NULL, $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new2['uuid'], $ar_new2['i']), 's'));
		$this->assertEqual('bbb', $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new['uuid'], 's'));
		$this->assertEqual(NULL, $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new2['uuid'], 's'));
		// 3. rollback update and delete
		$i = $this->oModule->DbDiffRollback($ar_diff);
		$this->assertEqual($i, 4);
		$this->assertEqual($ar_diff['flag'], -100);
		$this->assertEqual('aaa', $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new['uuid'], $ar_new['i']), 's'));
		$this->assertEqual('aaa', $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new2['uuid'], $ar_new2['i']), 's'));
		$this->assertEqual('aaa', $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new['uuid'], 's'));
		$this->assertEqual('aaa', $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new2['uuid'], 's'));
		// 4. after rollback, re-commit
		$i = $this->oModule->DbDiffCommit($ar_diff);
		$this->assertEqual($i, 4);
		$this->assertEqual($ar_diff['flag'], 100);
		$this->assertEqual('bbb', $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new['uuid'], $ar_new['i']), 's'));
		$this->assertEqual(NULL, $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new2['uuid'], $ar_new2['i']), 's'));
		$this->assertEqual('bbb', $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new['uuid'], 's'));
		$this->assertEqual(NULL, $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new2['uuid'], 's'));
		// 5. rollback insert done at beginning
		$i = $this->oModule->DbDiffRollback($ar_diff_ins);
		$this->assertEqual($i, 2);	// 2 rows is alread deleted previous
		$this->assertEqual($ar_diff_ins['flag'], -100);
		$this->assertEqual(NULL, $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new['uuid'], $ar_new['i']), 's'));
		$this->assertEqual(NULL, $this->oModule->oDb->GetDataByPk('t1'
			, array($ar_new2['uuid'], $ar_new2['i']), 's'));
		$this->assertEqual(NULL, $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new['uuid'], 's'));
		$this->assertEqual(NULL, $this->oModule->oDb->GetDataByPk('t2'
			, $ar_new2['uuid'], 's'));

		// :DEBUG:
		//$this->oModule->oDb->debug = true;
		///Ecl('<pre>' . var_export($ar_diff, true) . '</pre>');


		// Clean up
		$this->oModule->oDb->Execute('
			DROP TABLE t1;
		');
		$this->oModule->oDb->Execute('
			DROP TABLE t2;
		');
    } // end of func TestDbDiff


} // end of class TestModule


class ModuleTest extends Module {


	/**
	 * Constructor
	 */
	public function __construct () {
		parent::__construct();

	} // end of func __construct


	/**
	 * Connect to db, using func defined in include file, check error here.
	 *
	 * <code>
	 * $s = array(type, host, user, pass, name, lang);
	 * type is mysql/sybase_ase etc,
	 * name is dbname to select,
	 * lang is db server charset.
	 * </code>
	 *
	 * Useing my extended ADODB class now, little difference when new object.
	 * @var array	$dbprofile	Server config array
	 * @return object			Db connection object
	 */
	protected function DbConn ($dbprofile) {
		$conn = new Adodb($dbprofile);
		$conn->Connect();

		if (0 !=$conn->ErrorNo()) {
			// Display error
			$s = 'ErrorNo: ' . $conn->ErrorNo() . "<br />\nErrorMsg: " . $conn->ErrorMsg();
			return NULL;
		}
		else
			return $conn;
	} // end of func DbConn


	public function Init () {
		parent::Init();

		return $this;
	} // end of func Init


} // end of class ModuleTest


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('mvc-module.test.php' == $s_url) {
	$test = new TestModule();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
