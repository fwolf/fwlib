<?php
/**
 * UtilContainer is common used like this:
 *
 * $arrayUtil = UtilContainer::getInstance()->get('Array');
 * $arrayUtil->getIdx();
 *
 * Will this be slower than Class::method() ? How slow ?
 *
 *
 * Result:
 *
 * 1. Get UtilContainer instance then use it to get util instance is much
 * slower, more than 3 times slower.
 *
 * 2. If initial a variable stores util instance, method call through '->' is
 * a bit faster than through '::', no more than 1% faster.
 *
 * So, avoid duplicate getInstance() and get(), store util in local variable
 * to use or use in loop is fine. Additional, as util instance it not 'hard'
 * dependence compare with other object dependence, so I think define class
 * property for util instance is too much.
 */

use Fwlib\Test\Benchmark;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\Test\UtilContainerBenchmarkDummy as ArrayUtil;

require __DIR__ . '/../../../autoload.php';

require __DIR__ . '/UtilContainerBenchmarkDummy.php';


// Instance ArrayUtil, will reuse in below get
UtilContainer::getInstance()->get('Array');


$bench = new Benchmark;
$loopCount = 10000;


$bench->start("Test loop $loopCount times");


// Using static call
for ($i = 0; $i < $loopCount; $i ++) {
    ArrayUtil::getIdx(array(), 'foo', 'bar');
}
$bench->mark('ArrayUtil::getIdx()');


// Get instance each loop
for ($i = 0; $i < $loopCount; $i ++) {
    $arrayUtil = UtilContainer::getInstance()->get('Array');
    $arrayUtil->getIdx(array(), 'foo', 'bar');
}
$bench->mark('$arrayUtil->getIdx()');


// Store instance in local variable
for ($i = 0; $i < $loopCount; $i ++) {
    $arrayUtil->getIdx(array(), 'foo', 'bar');
}
$bench->mark('$arrayUtil->getIdx() without getInstance()');


$bench->display();
