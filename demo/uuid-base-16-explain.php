<?php
require __DIR__ . '/../config.default.php';

use Fwlib\Util\Uuid\Base16;

if (!isset($argc)) {
    exit('Can only run in cli mode.');
}

if (2 > $argc) {
    $basename = basename(__FILE__);
    echo <<<EOF
Usage: $basename Uuid


EOF;
    exit;
}

$uuidBase16 = new Base16;
$info = $uuidBase16->parse($argv[1]);

echo "Uuid: {$argv[1]}" . PHP_EOL;
echo PHP_EOL;
foreach ($info as $key => $value) {
    echo "  " . str_pad("$key: ", 9, ' ', STR_PAD_LEFT) . $value . PHP_EOL;
}
echo PHP_EOL;
