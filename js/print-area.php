<?
/**
 * Loader for print-area.js
 *
 * @package		fwolflib
 * @subpackage	js
 * @copyright	Copyright © 2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js@gmail.com>
 * @since		2012-12-27
 */

header('Content-type: application/javascript');
echo file_get_contents(dirname(__FILE__) . '/print-area.js');
?>
