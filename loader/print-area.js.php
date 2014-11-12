<?php
/**
 * Loader for print-area.js
 *
 * @package		fwolflib
 * @subpackage	loader
 * @copyright	Copyright © 2012-2014, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.loader@gmail.com>
 * @since		2012-12-27
 */

header('Content-type: application/javascript');
echo file_get_contents(dirname(__FILE__) . '/../js/print-area.js');
