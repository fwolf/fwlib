<?Php
// Usage:
require __DIR__ . '/../config.default.php';

use Fwlib\Test\Benchmark;

$bm = new Benchmark();


// Group #0
$bm->start('Test Group');

usleep(rand(100, 500));
$bm->mark('Mark #0');

usleep(500);
$bm->mark('Mark #1 cyan', 'cyan');

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
