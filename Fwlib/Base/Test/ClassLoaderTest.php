<?php
// Run with php, not phpunit

use Fwlib\Base\ClassLoader;

require __DIR__ . '/../../../autoload.php';


ClassLoader::addPrefix('Fwlib\\Util', __DIR__ . '/../../../');

use Fwlib\Util\DatetimeUtil;

new DatetimeUtil;


ClassLoader::addPrefix('Rv', __DIR__ . '/../../../class/rv/rv.php');
new Rv;
