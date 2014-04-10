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
 * 1. Get UtilContainer instance then use it to get util instance is about 2
 * times slower.
 *
 * 2. If initial a variable to stores util instance, method call through '->'
 * is about 1% faster than through '::'.
 *
 * So, operate around UtilContainer is slow, should store its instance local
 * for reuse. Consider dependence injection, UtilContainer can inject to its
 * client, and use it to get util instance for usage, this have some speed
 * cost, but helpful for test or extend. For speedup util usage in loop, store
 * instance in local variable.
 *
 * Util class are helper class, their client class have dependence on it, but
 * this is different with other object dependence, util class can be replaced
 * by other ways, like function with namespace, like replace with copied
 * private method, so I'm not treat util class as normal dependenct inject (in
 * constructor), just declare a protected property $utilContainer and public
 * setter setUtilContainer(), then invoke setter in constructor is enough.
 *
 * @see Fwlib\Util\AbstractUtilAware
 */

use Fwlib\Test\Benchmark;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\Test\UtilContainerBenchmarkDummy as ArrayUtil;

require __DIR__ . '/../autoload.php';

require __DIR__ . '/Dummy/UtilContainerBenchmarkDummy.php';


// Instance ArrayUtil, will reuse in below get
UtilContainer::getInstance()->get('Array');


$bench = new Benchmark;
$loopCount = 10000;


$bench->start("Test loop $loopCount times");


// Use static call
for ($i = 0; $i < $loopCount; $i ++) {
    ArrayUtil::getIdx(array(), 'foo', 'bar');
}
$bench->mark('ArrayUtil::getIdx()');


// Use UtilContainer instance
$utilContainer = UtilContainer::getInstance();
for ($i = 0; $i < $loopCount; $i ++) {
    $utilContainer->get('Array')->getIdx(array(), 'foo', 'bar');
}
$bench->mark('$utilContainer->get(\'Array\')->getIdx()');


// Use util instance
$arrayUtil = $utilContainer->get('Array');
for ($i = 0; $i < $loopCount; $i ++) {
    $arrayUtil->getIdx(array(), 'foo', 'bar');
}
$bench->mark('$arrayUtil->getIdx()');


$bench->display();
