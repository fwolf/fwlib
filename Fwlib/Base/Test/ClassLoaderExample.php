<?php
// Run with php, not phpunit

use Fwlib\Base\ClassLoader;
use Fwlib\Util\DatetimeUtil;

require __DIR__ . '/../../../autoload.php';

new \Fwlib\Base\ReturnValue;


ClassLoader::addPrefix('Fwlib\\Util', __DIR__ . '/../../../Fwlib/Util/');
new DatetimeUtil;


ClassLoader::addPrefix('Rv', __DIR__ . '/../../../class/rv/rv.php');
new Rv;
