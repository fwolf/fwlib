<?php
require __DIR__ . '/../../../autoload.php';

use Fwlib\Test\Benchmark;
use Fwlib\Util\Env;
use Fwlib\Util\UuidBase16;
use Fwlib\Util\UuidBase36;
use Fwlib\Util\UuidBase62;

// Speed test for Uuid generate
$count = 10000;

$bm = new Benchmark();
$bm->start('Gen ' . $count . ' UUID');
$speed = 0;


$arSpeed = array();
foreach (array('UuidBase16', 'UuidBase36', 'UuidBase62') as $k => $v) {
    $class = 'Fwlib\\Util\\' . $v;
    $v = str_pad($v, 10, ' ', STR_PAD_RIGHT);

    for ($i = 0; $i < $count; $i ++) {
        $class::gen();
    }
    $usedTime = $bm->mark("$v without check digit: average speed{$k}wt/s");
    $arSpeed["speed{$k}wt"] = round($count / $usedTime * 1000);

    for ($i = 0; $i < $count; $i ++) {
        $class::gen('', '', true);
    }
    $usedTime = $bm->mark("$v with    check digit: average speed{$k}wo/s");
    $arSpeed["speed{$k}wo"] = round($count / $usedTime * 1000);

}

// Replace {speed} in result
$rs = $bm->display(null, true);
$rs = str_replace(array_keys($arSpeed), $arSpeed, $rs);

echo $rs;


Env::ecl('UuidBase16 without check digit: ' . UuidBase16::gen('10', null, false));
Env::ecl('UuidBase16 with    check digit: ' . UuidBase16::gen('10', null, true));

Env::ecl('UuidBase16 without check digit: ' . UuidBase16::genWithSeparator('10', null, false));
Env::ecl('UuidBase16 with    check digit: ' . UuidBase16::genWithSeparator('10', null, true));

Env::ecl('UuidBase36 without check digit: ' . UuidBase36::gen('10', null, false));
Env::ecl('UuidBase36 with    check digit: ' . UuidBase36::gen('10', null, true));

Env::ecl('UuidBase62 without check digit: ' . UuidBase62::gen('10', null, false));
Env::ecl('UuidBase62 with    check digit: ' . UuidBase62::gen('10', null, true));
