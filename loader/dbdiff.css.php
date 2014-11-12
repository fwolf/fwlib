<?php
/**
 * Loader for dbdiff.css
 *
 * @package		fwolflib
 * @subpackage	loader
 * @copyright	Copyright © 2013-2014, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.loader@gmail.com>
 * @license		http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since		2013-01-17
 */

header('Content-type: text/css');
echo file_get_contents(dirname(__FILE__) . '/../css/dbdiff.css');
