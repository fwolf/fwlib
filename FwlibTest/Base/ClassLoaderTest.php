<?php
require __DIR__ . '/../../autoload.php';


$loader->addPrefix('Fwlib\\Util', __DIR__ . '/../../');

use Fwlib\Util\DatetimeUtil;

new DatetimeUtil;


$loader->addPrefix('Rv', __DIR__ . '/../../class/rv/rv.php');
new Rv;
