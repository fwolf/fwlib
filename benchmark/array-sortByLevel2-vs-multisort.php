<?php
/**
 * Compare Array::sortByLevel2() vs array_multisort()
 *
 * @link http://php.net/manual/en/function.array-multisort.php
 *
 * @copyright   Copyright 2015 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */

use Fwlib\Util\UtilContainer;

require_once __DIR__ . '/../config.default.php';

$utilContainer = UtilContainer::getInstance();
$envUtil = $utilContainer->getEnv();
$arrayUtil = $utilContainer->getArray();

$x = [
    'a' => ['volume' => 67, 'edition' => 2],
    'b' => ['volume' => 86, 'edition' => 1],
    'c' => ['volume' => 85, 'edition' => 6],
    'd' => ['volume' => 98, 'edition' => 2],
    'e' => ['volume' => 86, 'edition' => 6],
    'f' => ['volume' => 67, 'edition' => 7],
];
// Notice: sortByLevel2() will sort by 'edition' if 'volume' are same, while
// array_multisort() will not.
$y1 = [
    'd' => ['volume' => 98, 'edition' => 2],
    'e' => ['volume' => 86, 'edition' => 6],
    'b' => ['volume' => 86, 'edition' => 1],
    'c' => ['volume' => 85, 'edition' => 6],
    'f' => ['volume' => 67, 'edition' => 7],
    'a' => ['volume' => 67, 'edition' => 2],
];
$y2 = [
    'd' => ['volume' => 98, 'edition' => 2],
    'b' => ['volume' => 86, 'edition' => 1],
    'e' => ['volume' => 86, 'edition' => 6],
    'c' => ['volume' => 85, 'edition' => 6],
    'a' => ['volume' => 67, 'edition' => 2],
    'f' => ['volume' => 67, 'edition' => 7],
];


$loopCount = 1000;
$result = $x;


$startTime = microtime(true);
for ($i = 0; $i < $loopCount; $i ++) {
    $result = $x;
    $arrayUtil->sortByLevel2($result, 'volume', 'DESC');
}
$endTime = microtime(true);
$envUtil->ecl(
    'sortByLevel2()    cost ' . ($endTime - $startTime) . ' seconds.' . "\n"
);
if ($y1 !== $result) {
    $envUtil->ecl('sortByLevel2() got wrong result.');
}


$startTime = microtime(true);
for ($i = 0; $i < $loopCount; $i ++) {
    $result = $x;
    $volume = [];
    foreach ($result as $k => $v) {
        $volume[$k] = $v['volume'];
    }
    array_multisort($volume, SORT_DESC, $result);
}
$endTime = microtime(true);
$envUtil->ecl(
    'array_multisort() cost ' . ($endTime - $startTime) . ' seconds.' . "\n"
);
if ($y2 !== $result) {
    $envUtil->ecl('array_multisort() got wrong result.');
}
