<?php
/**
 * AutoLoader register
 *
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */

require __DIR__ . '/src/Fwlib/Base/ClassLoader.php';
require __DIR__ . '/vendor/autoload.php';

use Fwlib\Base\ClassLoader;

// The autoload of composer is main autoloader, this autoloader is for some
// old libraries.
$classLoader = ClassLoader::getInstance();

// Search include_path at last, for back compatible
$classLoader->useIncludePath = true;

// Register autoloader
$classLoader->register();

return $classLoader;
