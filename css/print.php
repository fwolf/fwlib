<?
/**
 * Loader for print.css
 *
 * @package		fwolflib
 * @subpackage	css
 * @copyright	Copyright © 2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.css@gmail.com>
 * @since		2011-08-25
 */

header('Content-type: text/css');
echo file_get_contents(dirname(__FILE__) . '/print.css');
?>
