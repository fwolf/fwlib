<?php
/**
 * Configure file
 *
 * Usage:
 *  1. Create a new php file named 'config.php'
 *  2. Write config in it, like assignment in 'Config define area'
 *  3. Require 'config.default.php' in php bootstrap file
 *
 * This file contains default config, user config in 'config.php' will
 * overwrite default config value.
 *
 * @package     Fwlib\Base
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 * @since       2013-07-26
 */

use Fwlib\Base\ClassLoader;
use Fwlib\Config\GlobalConfig;

if ('config.default.php' == basename(__FILE__)) {
    // Record running start time, usefull for count total process time cost,
    // as of PHP 5.4.0, $_SERVER['REQUEST_TIME_FLOAT'] is build-in.
    if (0 > version_compare(PHP_VERSION, '5.4.0')) {
        list($msec, $sec) = explode(' ', microtime(false));
        $_SERVER['REQUEST_TIME_FLOAT'] = $sec . substr($msec, 1);
    }


    // Init config data array
    $config = array();


    // Load user config if exists
    if (file_exists(__DIR__ . '/config.php')) {
        require __DIR__ . '/config.php';
    }
    $userConfig = $config;
}


/***********************************************************
 * Config define area
 *
 * In config.php, code outside this area can be removed.
 **********************************************************/

/**
 * For configure need to use in later compute, need check and try to load user
 * config, see example below.
 */
$config['group.keyForCompute'] = isset($userConfig['group.keyForCompute'])
    ? $userConfig['group.keyForCompute']
    : 'default value';

/**
 * Assignment need compute with other config value should define in this file
 * rather than user config. But if the compute and assign job is done in user
 * config, it should still leave a default assign in this file, as a fallback
 * for user fogot to do so, or to make code more readable. These assigned
 * value will be overwriten by user config at end by array_merge().
 */


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
 * External library path
 *
 * Dir path in local file system should end with tailing '/'.
 */
$config['lib.path.adodb'] = 'adodb/';
$config['lib.path.fwlib'] = 'fwlib/';
$config['lib.path.jquery'] = '/js/jquery.js';
$config['lib.path.phpmailer'] = 'phpmailer/';
$config['lib.path.phpunit'] = '/usr/share/php/';
$config['lib.path.smarty'] = 'smarty/';


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


if ('config.default.php' == basename(__FILE__)) {
    // Overwrite default config with user config
    $config = array_merge($config, $userConfig);

    // Include autoloader of Fwlib, need before other library
    require $config['lib.path.fwlib'] . 'autoload.php';

    // Deal with config, store in GlobalConfig instance
    GlobalConfig::getInstance()->load($config);


    // Register autoload of other external library, $classLoader is declared
    // in autoload.php of Fwlib, can use below.


    // Adodb, which doesn't use PSR standard
    // Use ADOFetchObj class as dummy, new it will trigger include of
    // adodb.inc.php, all needed function in it is useable.
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
