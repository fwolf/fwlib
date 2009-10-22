<?php
/**
 * Group Test - func
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


/*
require_once('simpletest/unit_tester.php');
require_once('simpletest/reporter.php');
*/

$test = &new TestSuite('Group test for func/*');

$test->addTestFile('client.test.php');
$test->addTestFile('crypt.test.php');
$test->addTestFile('string.test.php');
$test->addTestFile('uuid.test.php');

$test->run(new HtmlReporter('utf-8'));


/*
// Change output charset in this way.
// {{{
$test = new TestFuncCrypt();
$test->run(new HtmlReporter('utf-8'));
// }}}
*/
?>
