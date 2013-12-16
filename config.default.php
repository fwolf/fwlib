<?php
/**
 * Configure file
 *
 * Usage:
 *  1. Copy to a new file named 'config.php'
 *  2. Remove code outside of 'Config define area' (Optional)
 *  3. Change defines
 *  4. Remove defines no change needed (Suggested)
 *
 * For defines which need compute to get final result:
 *  1. Remove from 'config.php', use compute job in 'config.default.php'
 *  2. Do compute in 'config.php'
 *
 * DO NOT MODIFY 'config.default.php' DIRECTLY.
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-07-26
 */

use Fwlib\Base\ClassLoader;
use Fwlib\Config\ConfigGlobal;
use Fwlib\Util\ArrayUtil;

// Init global config array
if ('config.default.php' == basename(__FILE__)) {
    // Record running start time, usefull for count total process time cost
    // As of PHP 5.4.0, $_SERVER['REQUEST_TIME_FLOAT'] is build-in.
    if (0 > version_compare(PHP_VERSION, '5.4.0')) {
        list($msec, $sec) = explode(' ', microtime(false));
        $_SERVER['REQUEST_TIME_FLOAT'] = $sec . substr($msec, 1);
    }


    $config = array();


    // Load user config if exists
    if (file_exists(__DIR__ . '/config.php')) {
        require __DIR__ . '/config.php';
    }
    $configUser = $config;


    // Load requirement lib autoload file
    // Fwlib
    if (!isset($config['lib.path.fwlib'])) {
        $config['lib.path.fwlib'] = 'fwlib/';
    }
    require $config['lib.path.fwlib'] . 'autoload.php';
}


/***********************************************************
 * Config define area
 *
 * Use $configUser to compute value if needed.
 *
 * In config.php, code outside this area can be removed.
 **********************************************************/


// Config to use in compute later NEED use user config if set
$config['group.keyForCompute'] = ArrayUtil::getIdx(
    $configUser,
    'group.keyForCompute',
    'default value'
);

// External library path, local dir end with tailing '/'
$config['lib.path.adodb'] = 'adodb/';
$config['lib.path.fwlib'] = 'fwlib/';
$config['lib.path.jquery'] = '/js/jquery.js';
$config['lib.path.phpmailer'] = 'phpmailer/';
$config['lib.path.phpunit'] = '/usr/share/php/';
$config['lib.path.smarty'] = 'smarty/';


/**
 * Database setting, for db relate test
 *
 * Leave host empty to skip this db server.
 */
// Mysql db
$config['dbserver.mysql.type'] = 'mysqli';
$config['dbserver.mysql.host'] = 'localhost:3306';
$config['dbserver.mysql.user'] = 'test';
$config['dbserver.mysql.pass'] = '';
$config['dbserver.mysql.name'] = 'test';
$config['dbserver.mysql.lang'] = 'utf8';
// Sybase db
$config['dbserver.sybase.type'] = 'sybase_ase';
$config['dbserver.sybase.host'] = '';
$config['dbserver.sybase.user'] = 'username';
$config['dbserver.sybase.pass'] = 'secretpass';
$config['dbserver.sybase.name'] = 'database_name';
$config['dbserver.sybase.lang'] = 'cp936';
// Default db is mysql
$config['dbserver.default.type'] = $config['dbserver.mysql.type'];
$config['dbserver.default.host'] = $config['dbserver.mysql.host'];
$config['dbserver.default.user'] = $config['dbserver.mysql.user'];
$config['dbserver.default.pass'] = $config['dbserver.mysql.pass'];
$config['dbserver.default.name'] = $config['dbserver.mysql.name'];
$config['dbserver.default.lang'] = $config['dbserver.mysql.lang'];


/**
 * Memcached
 */
$config['memcached.server'] = array(
    array(
        'host'      => '127.0.0.1',
        'port'      => 11211,
        'weight'    => 100,
    ),
);


/**
 * Smarty
 */
$config['smarty.cacheDir'] = '/tmp/';
$config['smarty.compileDir'] = '/tmp/';


/***********************************************************
 * Config define area end
 **********************************************************/


// Merge user and default config
if ('config.default.php' == basename(__FILE__)) {
    $config = array_merge($config, $configUser);

    // Deal with $config
    // Or store with ConfigGlobal class
    ConfigGlobal::load($config);


    // Autoload register of external library
    $classLoader = ClassLoader::getInstance();

    // Adodb, which doesn't use PSR standard
    // Use ADOFetchObj class for faster dummy new object
    $classLoader->addPrefix(
        'ADOFetchObj',
        $config['lib.path.adodb'] . 'adodb.inc.php'
    );

    // Markdown
    $classLoader->addPrefix('Michelf', 'markdown/Michelf/');

    // PHPUnit, some demo use it, only need when not exec by phpunit command
    if (!class_exists('PHPUnit_Framework_TestCase', false)) {
        $classLoader->addPrefix(
            'PHPUnit',
            $config['lib.path.phpunit'] . 'PHPUnit/'
        );
    }

    // PHPMailer, use its own autoloader
    require $config['lib.path.phpmailer'] . 'PHPMailerAutoload.php';

    // Smarty 3.1.x
    $classLoader->addPrefix(
        'Smarty',
        $config['lib.path.smarty'] . 'Smarty.class.php'
    );
}
