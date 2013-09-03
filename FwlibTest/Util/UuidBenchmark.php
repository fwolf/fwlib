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

// Compute
$used = round($end - $start, 4);
$speed = round($count / $used);

// Output
Env::ecl("$count UUID generated, cost $used second(s), average $speed/s.");
