<?php
require __DIR__ . '/../../autoload.php';
//$loader->addPrefix('Fwlib\\Base', __DIR__ . '/../../');
$loader->addPrefix('Foo1', __DIR__ . '/Foo1.php');

$foo = new Foo1;

use Fwlib\Base\Foo;

$foo = new Foo;
