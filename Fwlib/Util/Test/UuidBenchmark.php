<?php
require __DIR__ . '/../../../autoload.php';

use Fwlib\Test\Benchmark;
use Fwlib\Util\UtilContainer;

// Speed test for Uuid generate
$count = 10000;

$utilContainer = UtilContainer::getInstance();
$bm = new Benchmark();
$bm->setUtilContainer($utilContainer);

$bm->start('Gen ' . $count . ' UUID');
$speed = 0;


$uuidBase16 = $utilContainer->get('UuidBase16');
$uuidBase36 = $utilContainer->get('UuidBase36');
$uuidBase62 = $utilContainer->get('UuidBase62');

$arSpeed = array();
foreach (array('UuidBase16', 'UuidBase36', 'UuidBase62') as $k => $v) {
    $class = 'Fwlib\\Util\\' . $v;
    $instanceName = lcfirst($v);
    $instance = $$instanceName;

    $v = str_pad($v, 10, ' ', STR_PAD_RIGHT);   // For display later

    for ($i = 0; $i < $count; $i ++) {
        $instance->generate();
    }
    $usedTime = $bm->mark("$v without check digit: average speed{$k}wt/s");
    $arSpeed["speed{$k}wt"] = round($count / $usedTime * 1000);

    for ($i = 0; $i < $count; $i ++) {
        $instance->generate('', '', true);
    }
    $usedTime = $bm->mark("$v with    check digit: average speed{$k}wo/s");
    $arSpeed["speed{$k}wo"] = round($count / $usedTime * 1000);

}

// Replace {speed} in result
$rs = $bm->display(null, true);
$rs = str_replace(array_keys($arSpeed), $arSpeed, $rs);

echo $rs;


$env = $utilContainer->get('Env');
$env->ecl('$uuidBase16 without check digit: ' . $uuidBase16->generate('10', null, false));
$env->ecl('$uuidBase16 with    check digit: ' . $uuidBase16->generate('10', null, true));

$env->ecl('$uuidBase16 without check digit: ' . $uuidBase16->generateWithSeparator('10', null, false));
$env->ecl('$uuidBase16 with    check digit: ' . $uuidBase16->generateWithSeparator('10', null, true));

$env->ecl('$uuidBase36 without check digit: ' . $uuidBase36->generate('10', null, false));
$env->ecl('$uuidBase36 with    check digit: ' . $uuidBase36->generate('10', null, true));

$env->ecl('$uuidBase62 without check digit: ' . $uuidBase62->generate('10', null, false));
$env->ecl('$uuidBase62 with    check digit: ' . $uuidBase62->generate('10', null, true));
