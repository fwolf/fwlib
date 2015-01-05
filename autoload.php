<?php
/**
 * AutoLoader register
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */

require __DIR__ . '/src/Fwlib/Base/ClassLoader.php';

use Fwlib\Base\ClassLoader;

$classLoader = ClassLoader::getInstance();

// Add resource lookup path
$classLoader->addPrefix('Fwlib', __DIR__ . '/src/Fwlib/');
$classLoader->addPrefix('FwlibTest', __DIR__ . '/tests/FwlibTest/');

// Search include_path at last
$classLoader->useIncludePath = true;

// Register autoloader
$classLoader->register();
