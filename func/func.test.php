<?php
/**
 * Group Test - func
 *
 * @package     fwolflib
 * @subpackage	func.test
 * @copyright   Copyright 2009-2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func.test@gmail.com>
 * @since		2009-10-22
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

$test = new TestSuite('Group test for func/*');

$test->addFile('array.test.php');
$test->addFile('client.test.php');
$test->addFile('crypt.test.php');
$test->addFile('filesystem.test.php');
$test->addFile('request.test.php');
$test->addFile('string.test.php');
$test->addFile('url.test.php');
$test->addFile('uuid.test.php');
$test->addFile('validate.test.php');

$test->run(new HtmlReporter('utf-8'));

// Want to remove duplicate report ?
// Remove "run report" part in each unit test file.

/*
// Change output charset in this way.
// {{{
$test = new TestFuncCrypt();
$test->run(new HtmlReporter('utf-8'));
// }}}
*/
?>
