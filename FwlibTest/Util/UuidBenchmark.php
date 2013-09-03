<?php
require __DIR__ . '/../../autoload.php';

use Fwlib\Util\Env;
use Fwlib\Util\Uuid;

// Speed test for Uuid generate
$count = 10000;

$start = microtime(true);
for ($i = 0; $i < $count; $i ++) {
    Uuid::gen();
}
$end = microtime(true);

$used = sprintf('%.4f', $end - $start);
$speed = round($count / $used);

Env::ecl("$count UUID generated * 2");
Env::ecl("Without check digit: cost $used second(s), average $speed/s.");


$start = microtime(true);
for ($i = 0; $i < $count; $i ++) {
    Uuid::gen('', '', true);
}
$end = microtime(true);

$used = sprintf('%.4f', $end - $start);
$speed = round($count / $used);

Env::ecl("With check digit:    cost $used second(s), average $speed/s.");
Env::ecl(Uuid::gen(null, null, false));
Env::ecl(Uuid::gen(null, null, true));
