<?php
namespace FwlibTest\Config;

use Fwlib\Config\Config;

/**
 * Test for Fwlib\Config\Config
 *
 * @package     FwlibTest\Config
 * @copyright   Copyright 2013 Fwolf
 * @author      Fwolf <fwolf.aide+FwlibTest@gmail.com>
 * @license     http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @since       2013-08-20
 */
class ConfigTest extends \PHPunit_Framework_TestCase
{
    private $config = null;
    public static $output = '';


    public function __construct()
    {
        $this->config = new Config();
    }


    public function testLimitServerId()
    {
        // Using phpunit/test_helpers
        // @link https://github.com/php-test-helpers/php-test-helpers
        // @link http://thedeveloperworldisyours.com/php/phpunit-tips/
        set_exit_overload(
            function ($output) {
                ConfigTest::$output = $output;
                return false;
            }
        );


        $serverIdBackup = $this->config->get('server.id');
        unset($this->config->config['server']['id']);


        // Test exit with msg
        $this->config->limitServerId(1);
        $this->assertEquals(
            self::$output,
            'Server id not set.'
        );

        $this->config->set('server.id', 2);
        $this->assertEquals(true, $this->config->limitServerId(2));

        $this->config->limitServerId(1);
        $this->assertEquals(
            self::$output,
            'This program can only run on server 1.'
        );

        $this->config->limitServerId(array(1, 3));
        $this->assertEquals(
            self::$output,
            'This program can only run on these servers: 1, 3.'
        );


        unset_exit_overload();


        // Fail, but not exit
        $this->assertEquals(
            false,
            $this->config->limitServerId(array(1, 3), false)
        );


        $this->config->set('server.id', $serverIdBackup);
    }


    public function testSetGet()
    {
        // Single value
        $this->config->set('foo', 'bar');
        $this->assertEquals($this->config->get('foo'), 'bar');

        // Value with separator turns to array
        $this->config->set('foo1.bar', 42);
        $this->assertEquals($this->config->get('foo1'), array('bar' => 42));

        // Value with empty middle level
        $this->config->set('a.b.c', 42);
        $this->assertEquals($this->config->get('a.b.c', 43), 42);
        $this->assertEquals(
            $this->config->get('a'),
            array(
                'b' => array(
                    'c' => 42,
                ),
            )
        );

        // Default value
        $this->assertEquals($this->config->get('foo2.bar', 42), 42);
    }
}
