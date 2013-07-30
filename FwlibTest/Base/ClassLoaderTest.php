<?php
require __DIR__ . '/../../autoload.php';
$loader->addPrefix('FwlibTest\\Base', __DIR__ . '/../../');
$loader->addPrefix('Foo1', __DIR__ . '/Foo1.php');

$foo = new Foo1;

use FwlibTest\Base\Foo;

$foo = new Foo;
