<?php
require __DIR__ . '/../config.default.php';

use Fwlib\Util\UuidBase16;

if (2 > $argc) {
    $basename = basename(__FILE__);
    echo <<<EOF
Usage: $basename Uuid


EOF;
    exit;
}

$uuidBase16 = new UuidBase16;
$info = $uuidBase16->parse($argv[1]);

echo "Uuid: {$argv[1]}" . PHP_EOL;
echo PHP_EOL;
foreach ($info as $key => $value) {
    echo "  " . str_pad("$key: ", 9, ' ', STR_PAD_LEFT) . $value . PHP_EOL;
}
echo PHP_EOL;