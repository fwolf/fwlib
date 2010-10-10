<?php
/**
 * Global define file for other class and func.
 *
 * Every php file should include this file using code like:
 * require_once(dirname(__FILE__) . '/../fwolflib.php');
 * Then user can use path const FWOLFLIB anywhere
 * after included any class or func, eg func/config.php.
 *
 * Or, you can define FWOLFLIB manually before first include like:
 * define('FWOLFLIB', 'fwolflib/');
 *
 * @package		fwolflib
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib@gmail.com>
 * @since		2010-10-10
 */


if (!defined('FWOLFLIB')) {
	if (0 <= version_compare(phpversion(), '5.3.0')) {
		define('FWOLFLIB', __DIR__ . '/');
	} else {
		define('FWOLFLIB', dirname(__FILE__) . '/');
	}
}
?>
