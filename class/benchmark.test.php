<?Php
// Usage:
require_once('benchmark.php');

$bm = new Benchmark('autostart');
$bm->Start('Test Group');

usleep(rand(100,500));

$bm->Mark('Mark1');

usleep(500);

$bm->Mark('Mark2', 'blue');

for ($i=1; $i<10; $i++) {
	usleep(rand(1,1000));
	$bm->Mark();
}

$bm->Stop();

$bm->Display();

?>
