<?
/**
 * Loader for reset.css
 *
 * @package		fwolflib
 * @subpackage	loader
 * @copyright	Copyright © 2011-2013, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.loader@gmail.com>
 * @since		2011-08-25
 */

header('Content-type: text/css');
echo file_get_contents(dirname(__FILE__) . '/../css/reset.css');
?>
