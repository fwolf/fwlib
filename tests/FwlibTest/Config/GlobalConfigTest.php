<?php
namespace FwlibTest\Config;

use Fwlib\Bridge\PHPUnitTestCase;
use Fwlib\Config\GlobalConfig;

/**
 * @copyright   Copyright 2013-2014 Fwolf
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL-3.0+
 */
class GlobalConfigTest extends PHPunitTestCase
{
    private $globalConfig;
    public static $output = '';


    public function __construct()
    {
        $this->globalConfig = $this->buildMock();
    }


    protected function buildMock()
    {
        // Assign array() to param methods here will cause mock to be a stub,
        // reason unknown, assign any method will fix it, so use constructor
        // in parent class.
        $globalConfig = $this->getMock(
            'Fwlib\Config\GlobalConfig',
            array('__construct')
        );

        return $globalConfig;
    }


    public function testLimitServerId()
    {
        $globalConfig = $this->globalConfig;

        // Using phpunit/test_helpers
        // @link https://github.com/php-test-helpers/php-test-helpers
        // @link http://thedeveloperworldisyours.com/php/phpunit-tips/
        if (!extension_loaded('test_helpers')) {
            $this->markTestSkipped('Need extension test_helpers');
        }
        set_exit_overload(
            function ($output) {
                GlobalConfigTest::$output = $output;
                return false;
            }
        );


        $serverIdBackup = $globalConfig->get('server.id');
        unset($globalConfig->config['server']['id']);


        // Test exit with msg
        $globalConfig->limitServerId(1);
        $this->assertEquals(
            'Server id not set.',
            self::$output
        );

        $globalConfig->set('server.id', 2);
        $this->assertEquals(true, $globalConfig->limitServerId(2));

        $globalConfig->limitServerId(1);
        $this->assertEquals(
            self::$output,
            'This program can only run on server 1.'
        );

        $globalConfig->limitServerId(array(1, 3));
        $this->assertEquals(
            self::$output,
            'This program can only run on these servers: 1, 3.'
        );


        unset_exit_overload();


        // Fail, but not exit
        $this->assertFalse(
            $globalConfig->limitServerId(array(1, 3), false)
        );

        $this->assertFalse($globalConfig->limitServerId(10, false));


        $globalConfig->set('server.id', $serverIdBackup);
    }


    public function testLoad()
    {
        $globalConfig = $this->globalConfig;

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
