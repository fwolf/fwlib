<?php
/**
 * Loader for print.css
 *
 * @package		fwolflib
 * @subpackage	loader
 * @copyright	Copyright © 2012-2014, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.loader@gmail.com>
 * @since		2011-08-25
 */

header('Content-type: text/css');
echo file_get_contents(dirname(__FILE__) . '/../css/print.css');
