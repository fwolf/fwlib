<?
/**
 * Loader for reset.css
 *
 * @package		fwolflib
 * @subpackage	css
 * @copyright	Copyright © 2011, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.js@gmail.com>
 * @since		2011-08-25
 */

header('Content-type: text/css');
echo file_get_contents(__DIR__ . '/reset.css');
?>
