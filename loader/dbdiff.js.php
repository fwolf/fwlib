<?
/**
 * Loader for dbdiff.js
 *
 * @package		fwolflib
 * @subpackage	loader
 * @copyright	Copyright © 2012-2013, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.loader@gmail.com>
 * @since		2012-12-26
 */

header('Content-type: application/javascript');
echo file_get_contents(dirname(__FILE__) . '/../js/dbdiff.js');
?>
