<?php
// Run with php, not phpunit

use Fwlib\Base\ClassLoader;
use Fwlib\Util\Env;

require __DIR__ . '/../src/Fwlib/Base/ClassLoader.php';
// In this demo we did not use autoload, which is more convenience, like:
//require __DIR__ . '/../autoload.php';


// $classLoader already exists(define in autoload.php), define it again here
// will make code easier to understand.
$classLoader = ClassLoader::getInstance();
$classLoader->register();


$classLoader->addPrefix('Fwlib\Util', __DIR__ . '/../src/Fwlib/Util/');

// Use full qualified class name works but not convenient
new \Fwlib\Util\Env;

$envUtil = new Env;

$envUtil->ecl('Load class with namespace success!');


$classLoader->addPrefix(
    'ClassLoaderDummy',
    __DIR__ . '/ClassLoaderDummy.php'
);

new \ClassLoaderDummy;

$envUtil->ecl('Load class without namespace success!');
