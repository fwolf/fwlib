<?php
use Fwlib\Test\AbstractDbRelateTest;
use FwlibTest\Aide\TestServiceContainer;

$db = TestServiceContainer::getInstance()->getDb();

$ref = new \ReflectionProperty(AbstractDbRelateTest::class, 'tableUser');
$ref->setAccessible(true);
$tableUser = $ref->getValue(AbstractDbRelateTest::class);

$ref = new \ReflectionMethod(AbstractDbRelateTest::class, 'createTable');
$ref->setAccessible(true);
$ref->invokeArgs(null, [$db]);


return $db;
