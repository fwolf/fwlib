<?php
require __DIR__ . '/../../autoload.php';

use Fwlib\Test\Benchmark;
use Fwlib\Util\Env;
use Fwlib\Util\Uuid;

// Speed test for Uuid generate
$count = 10000;

$bm = new Benchmark();
$bm->start('Gen ' . $count . ' UUID');
$speed = 0;

for ($i = 0; $i < $count; $i ++) {
    Uuid::gen();
}
$usedTime = $bm->mark('Without check digit: average {speed1}/s');
$speed1 = round($count / $usedTime * 1000);

for ($i = 0; $i < $count; $i ++) {
    Uuid::gen('', '', true);
}
$usedTime = $bm->mark('With check digit: average {speed2}/s');
$speed2 = round($count / $usedTime * 1000);

// Replace {speed} in result
$rs = $bm->display(null, true);
$rs = str_replace(array('{speed1}', '{speed2}'), array($speed1, $speed2), $rs);

echo $rs;

Env::ecl('Without check digit: ' . Uuid::gen(null, null, false));
Env::ecl('With    check digit: ' . Uuid::gen(null, null, true));
