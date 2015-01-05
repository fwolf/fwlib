<?php
// Run with php, not phpunit

use Fwlib\Base\ClassLoader;
use Fwlib\Util\DatetimeUtil;

require __DIR__ . '/../../../autoload.php';

new \Fwlib\Base\ReturnValue;

// $classLoader already exists(define in autoload.php), define it again here
// will make code easier to understand.
$classLoader = ClassLoader::getInstance();

$classLoader->addPrefix('Fwlib\\Util', __DIR__ . '/../../../Fwlib/Util/');
new DatetimeUtil;


$classLoader->addPrefix('Rv', __DIR__ . '/../../../class/rv/rv.php');
new Rv;
