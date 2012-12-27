<?
/**
 * Loader for reset.css
 *
 * @package		fwolflib
 * @subpackage	css
 * @copyright	Copyright © 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.css@gmail.com>
 * @since		2011-08-25
 */

header('Content-type: text/css');
echo file_get_contents(dirname(__FILE__) . '/reset.css');
?>
