<?php
require __DIR__ . '/../../../autoload.php';

use Fwlib\Test\Benchmark;
use Fwlib\Util\Env;
use Fwlib\Util\NumberUtil;

// Test duplicate times
$count = 10000;

$bm = new Benchmark();
$bm->start("Compute $count times");


// Length 20 > 11, will lose precision if use build-in base_convert()
$x = 'abcdef00001234567890';


$bm->mark('base_convert()');
for ($i = 0; $i < $count; $i ++) {
    $y = base_convert($x, 16, 10);
}
$bm->mark('Base 16 to base 10');
for ($i = 0; $i < $count; $i ++) {
    base_convert($y, 10, 36);
}
$bm->mark('Base 10 to base 36');


if (extension_loaded('bcmath')) {
    $ref = new \ReflectionMethod('Fwlib\Util\NumberUtil', 'baseConvertBcmath');
    $ref->setAccessible(true);
    $bm->mark('baseConvertBcmath()');

    for ($i = 0; $i < $count; $i ++) {
        $y = $ref->invokeArgs(null, array($x, 16, 62));
    }
    $bm->mark('Base 16 to base 62');
    for ($i = 0; $i < $count; $i ++) {
        $ref->invokeArgs(null, array($y, 62, 16));
    }
    $bm->mark('Base 62 to base 16');
}


if (extension_loaded('gmp')) {
    $ref = new \ReflectionMethod('Fwlib\Util\NumberUtil', 'baseConvertGmp');
    $ref->setAccessible(true);
    $bm->mark('baseConvertGmp()');

    for ($i = 0; $i < $count; $i ++) {
        $y = $ref->invokeArgs(null, array($x, 16, 62));
    }
    $bm->mark('Base 16 to base 62');
    for ($i = 0; $i < $count; $i ++) {
        $ref->invokeArgs(null, array($y, 62, 16));
    }
    $bm->mark('Base 62 to base 16');
}


if (extension_loaded('gmp') && version_compare(PHP_VERSION, '5.3.2', '>=')) {
    $ref = new \ReflectionMethod('Fwlib\Util\NumberUtil', 'baseConvertGmpSimple');
    $ref->setAccessible(true);
    $bm->mark('baseConvertGmpSimple()');

    for ($i = 0; $i < $count; $i ++) {
        $y = $ref->invokeArgs(null, array($x, 16, 62));
    }
    $bm->mark('Base 16 to base 62');
    for ($i = 0; $i < $count; $i ++) {
        $ref->invokeArgs(null, array($y, 62, 16));
    }
    $bm->mark('Base 62 to base 16');
}


$bm->display();
