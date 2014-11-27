<?php
/**
 * AutoLoader register
 *
 * @package     Fwlib
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */

require __DIR__ . '/Fwlib/Base/ClassLoader.php';

use Fwlib\Base\ClassLoader;

$classLoader = ClassLoader::getInstance();

// Add resource lookup path
$classLoader->addPrefix('Fwlib', __DIR__ . '/Fwlib/');

// Search include_path at last
$classLoader->useIncludePath = true;

// Register autoloader
$classLoader->register();
