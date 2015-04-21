<?php
require __DIR__ . '/../autoload.php';

use Fwlib\Test\Benchmark\Benchmark;
use Fwlib\Util\UtilContainer;
use Fwlib\Util\Uuid\GeneratorInterface;

// Speed test for Uuid generate
$count = 10000;

$utilContainer = UtilContainer::getInstance();
$bm = new Benchmark();
$bm->setUtilContainer($utilContainer);

$bm->start('Gen ' . $count . ' UUID');
$speed = 0;


$uuidBase16 = $utilContainer->getUuidBase16();
$uuidBase36 = $utilContainer->getUuidBase36();
$uuidBase36Short = $utilContainer->getUuidBase36Short();
$uuidBase62 = $utilContainer->getUuidBase62();

$algorithms = [
    'Base16',
    'Base36',
    'Base36Short',
    'Base62'
];
$arSpeed = [];
foreach ($algorithms as $k => $v) {
    $class = 'Fwlib\\Util\\Uuid\\' . $v;
    $instanceName = 'uuid' . $v;
    /** @var GeneratorInterface $instance */
    $instance = $$instanceName;

    $v = str_pad($v, 11, ' ', STR_PAD_RIGHT);   // For display later

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


$env = $utilContainer->getEnv();
$env->ecl('Base16      without check digit: ' . $uuidBase16->generate('10', null, false));
$env->ecl('Base16      with    check digit: ' . $uuidBase16->generate('10', null, true));

$env->ecl('Base16      without check digit: ' . $uuidBase16->generateWithSeparator('10', null, false));
$env->ecl('Base16      with    check digit: ' . $uuidBase16->generateWithSeparator('10', null, true));

$env->ecl('Base36      without check digit: ' . $uuidBase36->generate('10', null, false));
$env->ecl('Base36      with    check digit: ' . $uuidBase36->generate('10', null, true));

$env->ecl('Base36Short without check digit: ' . $uuidBase36Short->generate('1', null, false));
$env->ecl('Base36Short with    check digit: ' . $uuidBase36Short->generate('1', null, true));

$env->ecl('Base62      without check digit: ' . $uuidBase62->generate('10', null, false));
$env->ecl('Base62      with    check digit: ' . $uuidBase62->generate('10', null, true));
