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

namespace Fwlib;

require __DIR__ . '/Core/ClassLoader.php';

use \Fwlib\Core\ClassLoader;

$loader = new ClassLoader;

// Search include_path at last
$loader->useIncludePath = true;

// Add resource lookup path
$loader->addPrefix('Fwlib', __DIR__ . '/../');

// Register autoloader
$loader->register();
