<?php
require __DIR__ . '/../autoload.php';

use Fwlib\Test\Benchmark\Benchmark;
use Fwlib\Util\Common\NumberUtil;
use Fwlib\Util\UtilContainer;

// Test duplicate times
$count = 10000;

$bm = new Benchmark();
$bm->setUtilContainer(UtilContainer::getInstance());

$bm->start("Compute $count times");


// Length 20 > 11, will lose precision if use build-in base_convert()
/** @noinspection SpellCheckingInspection */
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


$numberUtil = new NumberUtil;


if (extension_loaded('bcmath')) {
    $bm->mark('baseConvertBcmath()');

    for ($i = 0; $i < $count; $i ++) {
        $y = $numberUtil->baseConvertBcmath($x, 16, 62);
    }
    $bm->mark('Base 16 to base 62');
    for ($i = 0; $i < $count; $i ++) {
        $numberUtil->baseConvertBcmath($y, 62, 16);
    }
    $bm->mark('Base 62 to base 16');
}


if (extension_loaded('gmp')) {
    $bm->mark('baseConvertGmp()');

    for ($i = 0; $i < $count; $i ++) {
        $y = $numberUtil->baseConvertGmp($x, 16, 62);
    }
    $bm->mark('Base 16 to base 62');
    for ($i = 0; $i < $count; $i ++) {
        $numberUtil->baseConvertGmp($y, 62, 16);
    }
    $bm->mark('Base 62 to base 16');
}


if (extension_loaded('gmp') && version_compare(PHP_VERSION, '5.3.2', '>=')) {
    $bm->mark('baseConvertGmpSimple()');

    for ($i = 0; $i < $count; $i ++) {
        $y = $numberUtil->baseConvertGmpSimple($x, 16, 62);
    }
    $bm->mark('Base 16 to base 62');
    for ($i = 0; $i < $count; $i ++) {
        $numberUtil->baseConvertGmpSimple($y, 62, 16);
    }
    $bm->mark('Base 62 to base 16');
}


$bm->display();
