<?php
use Fwlib\Test\AbstractDbRelateTest;

$ref = new \ReflectionMethod(AbstractDbRelateTest::class, 'dropTable');
$ref->setAccessible(true);
$ref->invokeArgs(null, [$db]);
