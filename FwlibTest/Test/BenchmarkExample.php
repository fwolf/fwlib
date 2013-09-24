<?Php
// Usage:
require __DIR__ . '/../../config.default.php';

use Fwlib\Test\Benchmark;

$bm = new Benchmark('autostart');


// Group #0
$bm->start('Test Group');

usleep(rand(100, 500));
$bm->mark('Mark1');

usleep(500);
$bm->mark('Mark2 lightblue', 'lightblue');

for ($i=1; $i<10; $i++) {
    usleep(rand(1, 1000));
    $bm->mark();
}

$bm->stop();


// Group #1
$bm->start();
$bm->mark();
$bm->mark();
$bm->stop();


$bm->display();
