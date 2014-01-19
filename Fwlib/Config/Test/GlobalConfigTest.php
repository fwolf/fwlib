<?php
namespace Fwlib\Config\Test;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\GlobalConfig;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @author      Fwolf <fwolf.aide+Fwlib@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-22
 */
class GlobalConfigTest extends PHPunitTestCase
{
    /**
     * Backup config in GlobalConfig and recover after test
     *
     * For other testcase use GlobalConfig to work properly.
     *
     * @var array
     */
    protected static $configBackup = null;

    public static $output = '';


    public static function setUpBeforeClass()
    {
        self::$configBackup = GlobalConfig::getInstance()->config;
    }


    public static function tearDownAfterClass()
    {
        GlobalConfig::getInstance()->config = self::$configBackup;
    }


    public function testLimitServerId()
    {
        $globalConfig = GlobalConfig::getInstance();

        $globalConfig->load(array());
        $this->assertEquals(false, $globalConfig->limitServerId(10, false));
    }


    public function testLimitServerId2()
    {
        $config = GlobalConfig::getInstance();

        // Using phpunit/test_helpers
        // @link https://github.com/php-test-helpers/php-test-helpers
        // @link http://thedeveloperworldisyours.com/php/phpunit-tips/
        if (!extension_loaded('test_helpers')) {
            return;
        }
        set_exit_overload(
            function ($output) {
                GlobalConfigTest::$output = $output;
                return false;
            }
        );


        $serverIdBackup = $config->get('server.id');
        unset($config->config['server']['id']);


        // Test exit with msg
        $config->limitServerId(1);
        $this->assertEquals(
            self::$output,
            'Server id not set.'
        );

        $config->set('server.id', 2);
        $this->assertEquals(true, $config->limitServerId(2));

        $config->limitServerId(1);
        $this->assertEquals(
            self::$output,
            'This program can only run on server 1.'
        );

        $config->limitServerId(array(1, 3));
        $this->assertEquals(
            self::$output,
            'This program can only run on these servers: 1, 3.'
        );


        unset_exit_overload();


        // Fail, but not exit
        $this->assertEquals(
            false,
            $config->limitServerId(array(1, 3), false)
        );


        $config->set('server.id', $serverIdBackup);
    }


    public function testLoad()
    {
        $globalConfig = GlobalConfig::getInstance();

        // Empty config value
        $globalConfig->load(null);
        $this->assertEquals(null, $globalConfig->get('foo'));
        $this->assertEquals(42, $globalConfig->get('foo', 42));


        // Normal get
        $config = array(
            'a'         => 1,
            'b.b1'      => 2,
        );

        $globalConfig->load($config);

        $this->assertEquals(1, $globalConfig->get('a'));
        $this->assertEquals(array('b1' => 2), $globalConfig->get('b'));


        // Set
        $globalConfig->set('c.c1.c2', 3);
        $this->assertEquals(
            array('c1' => array('c2' => 3)),
            $globalConfig->get('c')
        );
    }
}
