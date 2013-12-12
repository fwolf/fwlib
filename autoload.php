<?php
/**
 * AutoLoader register
 *
 * @package     Fwlib
 * @copyright   Copyright © 2013, Fwolf
 * @author      Fwolf <fwolf.aide+fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-07-27
 */

require __DIR__ . '/Fwlib/Base/ClassLoader.php';

use Fwlib\Base\ClassLoader;

// Add resource lookup path
ClassLoader::addPrefix('Fwlib', __DIR__ . '/Fwlib/');

// Search include_path at last
ClassLoader::$useIncludePath = true;

// Register autoloader
ClassLoader::register();
