<?
/**
 * Loader for alert.js
 *
 * @package		fwolflib
 * @subpackage	js
 * @copyright	Copyright © 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js@gmail.com>
 * @since		2011-08-25
 */

header('Content-type: application/javascript');
echo file_get_contents(dirname(__FILE__) . '/alert.js');
?>
